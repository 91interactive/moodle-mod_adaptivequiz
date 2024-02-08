<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Adaptive quiz attempt script.
 *
 * @package    mod_adaptivequiz
 * @copyright  2013 onwards Remote-Learner {@link http://www.remote-learner.ca/}
 * @copyright  2022 onwards Vitaly Potenko <potenkov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/mod/adaptivequiz/locallib.php');
require_once($CFG->dirroot . '/tag/lib.php');

use mod_adaptivequiz\local\adaptive_quiz_requires;
use mod_adaptivequiz\local\attempt\attempt;
use mod_adaptivequiz\local\attempt\cat_calculation_steps_result;
use mod_adaptivequiz\local\catalgorithm\catalgo;
use mod_adaptivequiz\local\fetchquestion;
use mod_adaptivequiz\local\itemadministration\item_administration;
use mod_adaptivequiz\local\question\question_answer_evaluation;
use mod_adaptivequiz\local\question\questions_answered_summary_provider;
use mod_adaptivequiz\local\report\questions_difficulty_range;
use mod_adaptivequiz\local\catalgorithm\determine_next_difficulty_result;

$id = required_param('cmid', PARAM_INT); // Course module id.
$uniqueid  = optional_param('uniqueid', 0, PARAM_INT);  // Unique id of the attempt.
$attempteddifficultylevel  = optional_param('dl', 0, PARAM_INT);

if (!$cm = get_coursemodule_from_id('adaptivequiz', $id)) {
    throw new moodle_exception('invalidcoursemodule');
}
if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
    throw new moodle_exception('coursemisconf');
}

global $USER, $DB, $SESSION;

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
$passwordattempt = false;

try {
    $adaptivequiz  = $DB->get_record('adaptivequiz', array('id' => $cm->instance), '*', MUST_EXIST);
} catch (dml_exception $e) {
    $url = new moodle_url('/mod/adaptivequiz/attempt.php', array('cmid' => $id));
    $debuginfo = '';

    if (!empty($e->debuginfo)) {
        $debuginfo = $e->debuginfo;
    }

    throw new moodle_exception('invalidmodule', 'error', $url, $e->getMessage(), $debuginfo);
}

// Setup page global for standard viewing.
$viewurl = new moodle_url('/mod/adaptivequiz/view.php', array('id' => $cm->id));
$PAGE->set_url('/mod/adaptivequiz/view.php', array('cmid' => $cm->id));
$PAGE->set_title(format_string($adaptivequiz->name));
$PAGE->set_context($context);
$PAGE->activityheader->disable();
$PAGE->add_body_class('limitedwidth');

// Check if the user has the attempt capability.
require_capability('mod/adaptivequiz:attempt', $context);

try {
    (new adaptive_quiz_requires())
        ->deferred_feedback_question_behaviour_is_enabled();
} catch (moodle_exception $activityavailabilityexception) {
    throw new moodle_exception(
        'activityavailabilitystudentnotification',
        'adaptivequiz',
        new moodle_url('/mod/adaptivequiz/view.php', ['id' => $cm->id])
    );
}

// Check if the user has any previous attempts at this activity.
$count = adaptivequiz_count_user_previous_attempts($adaptivequiz->id, $USER->id);

if (!adaptivequiz_allowed_attempt($adaptivequiz->attempts, $count)) {
    throw new moodle_exception('noattemptsallowed', 'adaptivequiz');
}

// Create an instance of the module renderer class.
$output = $PAGE->get_renderer('mod_adaptivequiz');
// Setup password required form.
$mform = $output->display_password_form($cm->id);
// Check if a password is required.
if (!empty($adaptivequiz->password)) {
    // Check if the user has alredy entered in their password.
    $condition = adaptivequiz_user_entered_password($adaptivequiz->id);

    if (empty($condition) && $mform->is_cancelled()) {
        // Return user to landing page.
        redirect($viewurl);
    } else if (empty($condition) && $data = $mform->get_data()) {
        $SESSION->passwordcheckedadpq = array();

        if (0 == strcmp($data->quizpassword, $adaptivequiz->password)) {
            $SESSION->passwordcheckedadpq[$adaptivequiz->id] = true;
        } else {
            $SESSION->passwordcheckedadpq[$adaptivequiz->id] = false;
            $passwordattempt = true;
        }
    }
}

$adaptiveattempt = attempt::find_in_progress_for_user($adaptivequiz, $USER->id);
if ($adaptiveattempt === null) {
    $adaptiveattempt = attempt::create($adaptivequiz, $USER->id);
}

$algo = new stdClass();
$standarderror = 0.0;

$determinenextdifficultylevelresult = null;
$r_server_response = null;
// If uniqueid is not empty the process respones.
if (!empty($uniqueid) && confirm_sesskey()) {
    // Check if the uniqueid belongs to the same attempt record the user is currently using.
    if (!adaptivequiz_uniqueid_part_of_attempt($uniqueid, $cm->instance, $USER->id)) {
        throw new moodle_exception('uniquenotpartofattempt', 'adaptivequiz');
    }

    // Process student's responses.
    try {
        // Set a time stamp for the actions below.
		// CS: Start time?
        $time = time();
        // Load the user's current usage from the DB.
        $quba = question_engine::load_questions_usage_by_activity((int) $uniqueid);
        // Update the actions done to the question.
        $quba->process_all_actions($time);
        // Finish the grade attempt at the question.
        $quba->finish_all_questions($time);
        // Save the data about the usage to the DB.
        question_engine::save_questions_usage_by_activity($quba);

        if (!empty($attempteddifficultylevel)) {
            // Check if the minimum number of attempts have been reached.
            $minattemptreached = adaptivequiz_min_attempts_reached($uniqueid, $cm->instance, $USER->id);

            // Create an instance of the CAT algo class.
            $algo = new catalgo($minattemptreached, (int) $attempteddifficultylevel);

            $questionanswerevaluation = new question_answer_evaluation($quba);
            $questionanswerevaluationresult = $questionanswerevaluation->perform();

			
			// CS: calling R-Server for next question
			
			$data_for_r_server = new stdClass;
			
			$data_for_r_server->user_id = $USER->id;
			$data_for_r_server->attempt_id = $uniqueid;
			$data_for_r_server->slots = $quba->get_slots();
			$data_for_r_server->measure = $algo->get_measure();
			$data_for_r_server->standarderror = $algo->get_standarderror();
			$data_for_r_server->logit = $algo->get_levellogit();
			$data_for_r_server->answeredquestions = $adaptiveattempt->read_attempt_data()->detaildtestresults;
			$data_for_r_server->testsettings = $adaptivequiz;
			
			// get all enemy and category tags from the database
			$allEnemyTags = $DB->get_records_sql("SELECT DISTINCT name FROM mdl_tag WHERE name LIKE 'enemy_id_%' AND name NOT LIKE 'adpq_%'");
			$allCategoryTags = $DB->get_records_sql("SELECT DISTINCT name FROM mdl_tag WHERE name LIKE 'cat_%' AND name NOT LIKE 'adpq_%'");
			$data_for_r_server->enemyTags = $allEnemyTags;
			$data_for_r_server->categoryTags = $allCategoryTags;
			// CS in der Serverresponse ist die ID der nächsten Frage hinterlegt, welche aus der QuestionDB gezogen wird.
			$r_server_response = $adaptiveattempt->call_r_server($data_for_r_server);
			
			
			//CS
			// falls der R-Server die gleichen Werte zurück gibt, kann das hier wieder mit entsprechnder Response einkommentiert werden
            // $determinenextdifficultylevelresult = $algo->determine_next_difficulty_level(
				//     (float) $adaptiveattempt->read_attempt_data()->difficultysum,
				//     (int) $adaptiveattempt->read_attempt_data()->questionsattempted,
				//     questions_difficulty_range::from_activity_instance($adaptivequiz),
				//     (float) $adaptivequiz->standarderror,
				//     $questionanswerevaluationresult,
				//     (new questions_answered_summary_provider($quba))->collect_summary()
				// );   
				
			// Determine the next difficulty level or whether there is an error.
			// desweiteren benötigt man vom R-Server folgende Werte für $catcalculationresult = cat_calculation_steps_result::from_floats($difflogit, $standarderror, $algo->get_measure()); in aktuell Zeile 284
			$determinenextdifficultylevelresult = new determine_next_difficulty_result($r_server_response->errormessage, $r_server_response->nextdifficultylevel);



            // Increment difficulty level for attempt.
			// CS im folgenden wird der Schwierigkeitsgrad erhöht und eine Frage aus dessen Pool wird ausgefwählt und dargestellt
            $difflogit = $algo->get_levellogit();
            if (is_infinite($difflogit)) {
                throw new moodle_exception('unableupdatediffsum', 'adaptivequiz',
                    new moodle_url('/mod/adaptivequiz/attempt.php', ['cmid' => $id]));
            }

            // $standarderror = $algo->get_standarderror();
			$standarderror = $r_server_response->standarderror;

            try {
				
				// CS: adding list with object of questionId as key and raw and rated answer as value
				$objList = null;
				$quSlots = $quba->get_slots();
				$lastSlot = end($quSlots);
				$qa = $quba->get_attempt_iterator()->offsetGet($lastSlot);
								
				$currentDBentry = $DB->get_record('adaptivequiz_attempt',array('uniqueid'=>$uniqueid), '*', MUST_EXIST);
				
				$currentDBdetaildtestresults = json_decode($currentDBentry->detaildtestresults) ?? '';
					// check if question is answered (graded)
					if ($qa->get_state()->is_graded() && $currentDBdetaildtestresults != "null") {

						// get question id 
						$quID = $qa->get_question_id();
						
						$qu = new stdClass();
				
						// get question text
						$qu->question = $qa->get_question()->questiontext;
						
						// get given answer
						$qu->givenAnswer = $qa->get_response_summary();
						$compareWith = $qu->givenAnswer;
						$answers = $qa->get_question()->answers;
						// filter answer object according to given answer to get the fraction later
						$fractionArray = array_filter($answers, function($answer) use ($compareWith){
							if($answer->answer == $compareWith){
								return $answer;
							}
						});
						// get fraction of given answer
						if(!empty($fractionArray)){	
							$keys = array_keys($fractionArray);
							$qu->fractionAnswer = $fractionArray[$keys[0]]->fraction;
						}
						$qu->standarderror = $r_server_response->standarderror;
						$qu->measure = $r_server_response->measure;
						$qu->score = $r_server_response->score;


						$objList[$quID] = $qu;
						$array1 = (array) $objList;
						$array2 = (array) $currentDBdetaildtestresults;
						$mergedObj = $array1 + $array2; 
					}
					
				

                // $catcalculationresult = cat_calculation_steps_result::from_floats($difflogit, $standarderror, $algo->get_measure());
				// $adaptiveattempt->update_after_question_answered($catcalculationresult, time(), json_encode($mergedObj));
				$adaptiveattempt->update_after_question_answered_with_r_response($r_server_response->difficultsum ?? 0.0, $r_server_response->standarderror ?? $standarderror,$r_server_response->measure ?? 0, time(), json_encode($mergedObj));

			
            } catch (Exception $exception) {
                throw new moodle_exception('unableupdatediffsum', 'adaptivequiz',
                    new moodle_url('/mod/adaptivequiz/attempt.php', ['cmid' => $id]));
            }

			

        }
    } catch (question_out_of_sequence_exception $e) {
        $url = new moodle_url('/mod/adaptivequiz/attempt.php', array('cmid' => $id));
        throw new moodle_exception('submissionoutofsequencefriendlymessage', 'question', $url);

    } catch (Exception $e) {
        $url = new moodle_url('/mod/adaptivequiz/attempt.php', array('cmid' => $id));
        $debuginfo = '';

        if (!empty($e->debuginfo)) {
            $debuginfo = $e->debuginfo;
        }

        throw new moodle_exception('errorprocessingresponses', 'question', $url, $e->getMessage(), $debuginfo);
    }
}

// Initialize quba.
$qubaid = $adaptiveattempt->read_attempt_data()->uniqueid;
$quba = ($qubaid == 0)
    ? question_engine::make_questions_usage_by_activity('mod_adaptivequiz', $context)
    : question_engine::load_questions_usage_by_activity($qubaid);
if ($qubaid == 0) {
    $quba->set_preferred_behaviour(attempt::ATTEMPTBEHAVIOUR);
}

$adaptivequiz->context = $context;
$adaptivequiz->cm = $cm;

$fetchquestion = new fetchquestion($adaptivequiz, 1, $adaptivequiz->lowestlevel, $adaptivequiz->highestlevel);

$itemadministration = new item_administration($quba, $fetchquestion);
$itemadministrationevaluation = $itemadministration->evaluate_ability_to_administer_next_item($adaptiveattempt, $adaptivequiz,
    $adaptiveattempt->read_attempt_data()->questionsattempted, $attempteddifficultylevel, $r_server_response != null ? $r_server_response->id_next_question : null, $determinenextdifficultylevelresult );

// Check item administration evaluation.
if ($itemadministrationevaluation->item_administration_is_to_stop()) {
    // Set the attempt to complete, update the standard error and attempt message, then redirect the user to the attempt-finished
    // page.
    if ($algo instanceof catalgo) {
        $standarderror = $algo->get_standarderror();
    }

    $noquestionsfetchedforattempt = $uniqueid == 0;
    if ($noquestionsfetchedforattempt) {
        // The script will try to complete an 'empty' attempt as it couldn't fetch the first question for some reason.
        // This is an invalid behaviour, which could be caused by a misconfigured questions pool. Stop it here.
        throw new moodle_exception('attemptnofirstquestion', 'adaptivequiz',
            (new moodle_url('/mod/adaptivequiz/view.php', ['id' => $cm->id]))->out());
    }

    $adaptiveattempt->complete($context, $standarderror, $itemadministrationevaluation->stoppage_reason(), time());

    redirect(new moodle_url('/mod/adaptivequiz/attemptfinished.php',
        ['cmid' => $cm->id, 'id' => $cm->instance, 'uattid' => $uniqueid]));
}

// Retrieve the question slot id.
$slot = $itemadministrationevaluation->next_item()->slot();

$level = $itemadministrationevaluation->next_item()->difficulty_level();


			

$headtags = $output->init_metadata($quba, $slot);
$PAGE->requires->js_init_call('M.mod_adaptivequiz.init_attempt_form', array($viewurl->out(), $adaptivequiz->browsersecurity),
    false, $output->adaptivequiz_get_js_module());

// Init secure window if enabled.
if (!empty($adaptivequiz->browsersecurity)) {
    $PAGE->blocks->show_only_fake_blocks();
    $output->init_browser_security();
} else {
    $PAGE->set_heading(format_string($course->fullname));
}

echo $output->header();
// Check if the user entered a password.
$condition = adaptivequiz_user_entered_password($adaptivequiz->id);

if (!empty($adaptivequiz->password) && empty($condition)) {
    if ($passwordattempt) {
        $mform->set_data(array('message' => get_string('wrongpassword', 'adaptivequiz')));
    }

    $mform->display();
} else {
    $attemptdata = $adaptiveattempt->read_attempt_data();

    if ($adaptivequiz->showattemptprogress) {
        echo $output->container_start('attempt-progress-container');
        echo $output->attempt_progress($attemptdata->questionsattempted, $adaptivequiz->maximumquestions);
        echo $output->container_end();
    }

    echo $output->question_submit_form($id, $quba, $slot, $level, $attemptdata->questionsattempted + 1);
}

echo $output->print_footer();
