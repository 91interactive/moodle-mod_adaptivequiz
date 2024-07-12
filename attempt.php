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
 * @package    mod_catadaptivequiz
 * @copyright  2013 onwards Remote-Learner {@link http://www.remote-learner.ca/}
 * @copyright  2022 onwards Vitaly Potenko <potenkov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/mod/catadaptivequiz/locallib.php');
require_once($CFG->dirroot . '/tag/lib.php');
require_once($CFG->libdir . '/questionlib.php');

use mod_catadaptivequiz\local\adaptive_quiz_requires;
use mod_catadaptivequiz\local\attempt\attempt;
use mod_catadaptivequiz\local\attempt\cat_calculation_steps_result;
use mod_catadaptivequiz\local\catalgorithm\catalgo;
use mod_catadaptivequiz\local\fetchquestion;
use mod_catadaptivequiz\local\itemadministration\item_administration;
use mod_catadaptivequiz\local\question\question_answer_evaluation;
use mod_catadaptivequiz\local\question\questions_answered_summary_provider;
use mod_catadaptivequiz\local\report\questions_difficulty_range;
use mod_catadaptivequiz\local\catalgorithm\determine_next_difficulty_result;
use mod_catadaptivequiz\local\repository\questions_repository;

$id = required_param('cmid', PARAM_INT); // Course module id.
$uniqueid  = optional_param('uniqueid', 0, PARAM_INT);  // Unique id of the attempt.
$attempteddifficultylevel  = optional_param('dl', 0, PARAM_INT);
if (!$cm = get_coursemodule_from_id('catadaptivequiz', $id)) {
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
	$adaptivequiz  = $DB->get_record('catadaptivequiz', array('id' => $cm->instance), '*', MUST_EXIST);
} catch (dml_exception $e) {
	$url = new moodle_url('/mod/catadaptivequiz/attempt.php', array('cmid' => $id));
	$debuginfo = '';

	if (!empty($e->debuginfo)) {
		$debuginfo = $e->debuginfo;
	}

	throw new moodle_exception('invalidmodule', 'error', $url, $e->getMessage(), $debuginfo);
}

// Setup page global for standard viewing.
$viewurl = new moodle_url('/mod/catadaptivequiz/view.php', array('id' => $cm->id));
$PAGE->set_url('/mod/catadaptivequiz/view.php', array('cmid' => $cm->id));
$PAGE->set_title(format_string($adaptivequiz->name));
$PAGE->set_context($context);
$PAGE->activityheader->disable();
$PAGE->add_body_class('limitedwidth');

// Check if the user has the attempt capability.
require_capability('mod/catadaptivequiz:attempt', $context);

try {
	(new adaptive_quiz_requires())
		->deferred_feedback_question_behaviour_is_enabled();
} catch (moodle_exception $activityavailabilityexception) {
	throw new moodle_exception(
		'activityavailabilitystudentnotification',
		'catadaptivequiz',
		new moodle_url('/mod/catadaptivequiz/view.php', ['id' => $cm->id])
	);
}

// Check if the user has any previous attempts at this activity.
$count = adaptivequiz_count_user_previous_attempts($adaptivequiz->id, $USER->id);

if (!adaptivequiz_allowed_attempt($adaptivequiz->attempts, $count)) {
	throw new moodle_exception('noattemptsallowed', 'catadaptivequiz');
}

// Create an instance of the module renderer class.
$output = $PAGE->get_renderer('mod_catadaptivequiz');
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
$data_for_r_server = new stdClass;

// If uniqueid is not empty the process respones.
if (!empty($uniqueid) && confirm_sesskey()) {
	// Check if the uniqueid belongs to the same attempt record the user is currently using.
	if (!adaptivequiz_uniqueid_part_of_attempt($uniqueid, $cm->instance, $USER->id)) {
		throw new moodle_exception('uniquenotpartofattempt', 'catadaptivequiz');
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

			$questionanswerevaluation = new question_answer_evaluation($quba);
			$questionanswerevaluationresult = $questionanswerevaluation->perform();

			// CS: calling R-Server for next question
			// CS: preparing data for r-server call			
			$data_for_r_server = new stdClass;
			$data_for_r_server->courseID = $course->id;  // course id
			$data_for_r_server->testID =  $adaptivequiz->id; // test id


			// CS: get the quiz category 
			$category = $DB->get_record('catadaptivequiz_question', ['instance' => $adaptivequiz->id]);
			// CS: get all question from selected question categories in question pool
			$categoryList = adaptivequiz_get_selected_question_cateogires($adaptivequiz->id);

			$qf = new question_finder();
			// create question bank via quategoryIds
			$questionIdsFromCategories = $qf->get_questions_from_categories($categoryList, null);
			$questionBankWithIdNumber = question_load_questions($questionIdsFromCategories, 'qbe.idnumber');

			// CS: prepare itempool for R-Server
			$data_for_r_server->itempool = new stdClass;
			$data_for_r_server->itempool->items = [];
			foreach ($questionBankWithIdNumber as $question) {
				// CS: reset object for each question
				$itemsArray = new stdClass;
				$itemsArray->diff = [];
				$itemsArray->content_area = [];
				$itemsArray->disc = [];
				// $itemsArray->max = null;
				// $itemsArray->answer = [];
				$itemsArray->cluster = null;

				$itemsArray->enemys = [];
				$itemsArray->ID = null;

				$itemsArray->ID = $question->idnumber;
				$itemsArray->dbID = $question->id;
				//get tags for each question
				$tags =  core_tag_tag::get_item_tags_array('core_question', 'question', $question->id);
				$itemsArray = attempt::distribute_used_tags($tags, $itemsArray);

				// push itemsArray to itempool items
				array_push($data_for_r_server->itempool->items, $itemsArray);
			}

			// CS: prepare settings for R-Server
			$data_for_r_server->settings = new stdClass;
			// Settings $adaptivequiz
			$data_for_r_server->settings->maxItems = $adaptivequiz->maximumquestions;
			$data_for_r_server->settings->minItems = $adaptivequiz->minimumquestions;
			$data_for_r_server->settings->minStdError = $adaptivequiz->standarderror;
			$data_for_r_server->settings->criteria_not_adaptive = $adaptivequiz->selecttasktypes == 0 ? 'random' : 'sequential';
			$data_for_r_server->settings->ncl_calib = $adaptivequiz->numbercalibrationclusters;
			$data_for_r_server->settings->ncl_link = $adaptivequiz->numberlinkingclusters;
			$data_for_r_server->settings->ncl_adaptive = $adaptivequiz->numberadaptiveclusters;
			// debugging('Contents of $adaptivequiz->personalparameterestimation: ' . print_r($adaptivequiz->personalparameterestimation, true), DEBUG_DEVELOPER);
			// debugging('Contents of $adaptivequiz->adaptivepart: ' . print_r($adaptivequiz->adaptivepart, true), DEBUG_DEVELOPER);
			switch ($adaptivequiz->personalparameterestimation) {
				case '0':
					$data_for_r_server->settings->pers_est = "MAP";
					break;
				case '1':
					$data_for_r_server->settings->pers_est = "EAP";
					break;
				case '2':
					$data_for_r_server->settings->pers_est = "WLE";
					break;
				case '3':
					$data_for_r_server->settings->pers_est = "MLE";
					break;
			}

			switch ($adaptivequiz->adaptivepart) {
				case '0':
					$data_for_r_server->settings->criteria_adaptive = "MI";
					break;
				case '1':
					$data_for_r_server->settings->criteria_adaptive = "MEPV";
					break;
				case '2':
					$data_for_r_server->settings->criteria_adaptive = "MEI";
					break;
				case '3':
					$data_for_r_server->settings->criteria_adaptive = "IKL";
					break;
			}

			$exposure = new stdClass();
			$exposure->enabled = $adaptivequiz->randomesque_exposure_control == "1" ? true : false;
			$exposure->nitems_exposure = $adaptivequiz->suitabletasks;
			$data_for_r_server->settings->exposure = $exposure;

			$contentareas = new stdClass();
			$contentareas->enabled = $adaptivequiz->contentareas; //Warning: Undefined property: stdClass::$contentareas in /var/www/html/mod/catadaptivequiz/attempt.php on line 247
			$contentareas->area1 = $adaptivequiz->contentarea1;
			$contentareas->area2 = $adaptivequiz->contentarea2;
			$contentareas->area3 = $adaptivequiz->contentarea3;
			$contentareas->area4 = $adaptivequiz->contentarea4;
			$data_for_r_server->settings->content_areas = $contentareas;

			//CS: user data for r-server
			$data_for_r_server->person = new stdClass;
			$data_for_r_server->person->personID = $USER->id;

			// CS: prepare test data for R-Server 
			$data_for_r_server->test = new stdClass;
			$data_for_r_server->test->itemID = [];
			$data_for_r_server->test->item = [];
			$data_for_r_server->test->scoredResponse = [];

			$quSlots = $quba->get_slots();
			foreach ($quSlots as $slot) {
				$questionBySlot = $quba->get_question($slot);

				array_push($data_for_r_server->test->itemID, $questionBySlot->idnumber);
				// add index of item with id questionBySlot->idnumber in itempool to $data_for_rs_server->test->item
				$index = array_search($questionBySlot->idnumber, array_column($data_for_r_server->itempool->items, 'ID'));
				array_push($data_for_r_server->test->item, $index);

				// $questionBySlot->id



				// scoredResponse
				$qa = $quba->get_attempt_iterator()->offsetGet($slot);
				$fraction = $qa->get_fraction();
				$scoredResponse = $quba->get_question_mark($slot); //$fraction * $qa->get_question()->max;
				array_push($data_for_r_server->test->scoredResponse, $scoredResponse);
			}
			$data_for_r_server->test->itemtime = array(0.23, 23.12, 120.33); // todo rm: write correct times
			$data_for_r_server->test->timeout = false; // todo rm: calculate correct value

			// CS: prepare answered questions, testsettings and questions for R-Server
			$data_for_r_server->answeredquestions = $adaptiveattempt->read_attempt_data()->detaildtestresults;
			$data_for_r_server->testsettings = $adaptivequiz;

			// $quizid = $adaptivequiz->id;
			// $quiz = $DB->get_record('quiz', array('id' => $quizid), '*', MUST_EXIST);

			// // Get the quiz slots (question ids are stored in slots)
			// $slots = $DB->get_records('quiz_slots', array('quizid' => $quiz->id), 'slot');

			// // Fetch each question
			// $questions = array();
			// // foreach ($slots as $slot) {
			// //     $question = $DB->get_record('question', array('id' => $slot->questionid), '*', MUST_EXIST);
			// //     $questions[] = $question;
			// // }

			// $data_for_r_server->questionsDatas = $questions;

			// $data_for_r_server->questionsDatas = $questions;	// Warning: Undefined variable $questions in /var/www/html/mod/catadaptivequiz/attempt.php on line 283	

			// CS in response we get the next question and the next difficulty level
			// c server call here:
			$r_server_response = $adaptiveattempt->call_r_server($data_for_r_server);

			// add check if response is valide
			if ($r_server_response == null) {
				throw new moodle_exception('rserverresponseerrornull', 'catadaptivequiz');
			}
			// check if response has SE, personID, theta, nextItem and terminated
			if (!property_exists($r_server_response, 'SE') || !property_exists($r_server_response, 'personID') || !property_exists($r_server_response, 'theta') || !property_exists($r_server_response, 'nextItem') || !property_exists($r_server_response, 'terminated')) {
				throw new moodle_exception('rserverresponseerrormissingvalue', 'catadaptivequiz');
			}


			$standarderror = $r_server_response->SE;
			$determinenextdifficultylevelresult = $r_server_response->terminated ? "CAT terminated" : null;

			try {

				// CS: adding list with object of questionId as key and raw and rated answer as value
				$objList = null;
				$quSlots = $quba->get_slots();
				$lastSlot = end($quSlots);
				$qa = $quba->get_attempt_iterator()->offsetGet($lastSlot);

				$currentDBentry = $DB->get_record('catadaptivequiz_attempt', array('uniqueid' => $uniqueid), '*', MUST_EXIST);

				$currentDBdetaildtestresults = $currentDBentry->detaildtestresults ? json_decode($currentDBentry->detaildtestresults) ?? '' : '';

				// check if question is answered (graded)
				if ($qa->get_state()->is_graded() && $currentDBdetaildtestresults != "null") {

					// get question id 
					$quID = $qa->get_question_id();

					$qu = new stdClass();
					$qu->questionId = $quID;
					$qu->name = $qa->get_question()->name;
					$qu->rawAnswer = $qa->get_response_summary();
					$qu->ratedAnswer = $quba->get_question_mark($lastSlot);
					$qu->theta = $r_server_response->theta;
					// questionId, raw answers and rated answers

					$qu->standarderror = $r_server_response->SE;

					// question history in single attempt
					if ($currentDBdetaildtestresults != null && $currentDBdetaildtestresults != "" && $currentDBdetaildtestresults != "null") {
						$tmpArray = [];
						if (gettype($currentDBdetaildtestresults->$uniqueid) == "object") {
							$tmpArray = [$currentDBdetaildtestresults->$uniqueid, $qu];
						} else {
							foreach ($currentDBdetaildtestresults->$uniqueid as $key => $value) {
								array_push($tmpArray, $value);
							}
							array_push($tmpArray, $qu);
						}
						$mergedObj[$uniqueid] = $tmpArray;
					} else {
						$mergedObj[$uniqueid] = $qu;
					}
				}

				$adaptiveattempt->update_after_question_answered_with_r_response(0.0, $r_server_response->SE ?? $standarderror, $r_server_response->theta ?? 0, time(), json_encode($mergedObj));
			} catch (Exception $exception) {
				throw new moodle_exception(
					'unableupdatediffsum',
					'catadaptivequiz',
					new moodle_url('/mod/catadaptivequiz/attempt.php', ['cmid' => $id])
				);
			}
		}
	} catch (question_out_of_sequence_exception $e) {
		$url = new moodle_url('/mod/catadaptivequiz/attempt.php', array('cmid' => $id));
		throw new moodle_exception('submissionoutofsequencefriendlymessage', 'question', $url);
	} catch (Exception $e) {
		$url = new moodle_url('/mod/catadaptivequiz/attempt.php', array('cmid' => $id));
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
	? question_engine::make_questions_usage_by_activity('mod_catadaptivequiz', $context)
	: question_engine::load_questions_usage_by_activity($qubaid);
if ($qubaid == 0) {
	$quba->set_preferred_behaviour(attempt::ATTEMPTBEHAVIOUR);
}

$adaptivequiz->context = $context;
$adaptivequiz->cm = $cm;

$fetchquestion = new fetchquestion($adaptivequiz, 1, $adaptivequiz->lowestlevel, $adaptivequiz->highestlevel);

$nextIndex = null;
// get the id of the element (from $data_for_r_server->itempool->items) with the idnumber that is stored in $r_server_response->nextItem
// check if $data_for_r_server hast property itempool

if ($data_for_r_server != null && property_exists($data_for_r_server, 'itempool') && $data_for_r_server->itempool != null && $data_for_r_server->itempool->items != null && is_array($data_for_r_server->itempool->items)) {
	$index = array_search($r_server_response->nextItem, array_column($data_for_r_server->itempool->items, 'ID'));
	$nextIndex = $data_for_r_server->itempool->items[$index]->dbID;
}
if ($nextIndex == null) {
	debugging('nextIndex is null ' . json_encode($data_for_r_server), DEBUG_DEVELOPER);
}

$itemadministration = new item_administration($quba, $fetchquestion);
$itemadministrationevaluation = $itemadministration->evaluate_ability_to_administer_next_item(
	$adaptiveattempt,
	$adaptivequiz,
	$adaptiveattempt->read_attempt_data()->questionsattempted,
	$attempteddifficultylevel,
	$nextIndex,
	$determinenextdifficultylevelresult
);

// Check item administration evaluation.
if ($itemadministrationevaluation->item_administration_is_to_stop()) {

	$noquestionsfetchedforattempt = $uniqueid == 0;
	if ($noquestionsfetchedforattempt) {
		// The script will try to complete an 'empty' attempt as it couldn't fetch the first question for some reason.
		// This is an invalid behaviour, which could be caused by a misconfigured questions pool. Stop it here.
		throw new moodle_exception(
			'attemptnofirstquestion',
			'catadaptivequiz',
			(new moodle_url('/mod/catadaptivequiz/view.php', ['id' => $cm->id]))->out()
		);
	}

	$adaptiveattempt->complete($context, $r_server_response->SE, $itemadministrationevaluation->stoppage_reason(), time());

	redirect(new moodle_url(
		'/mod/catadaptivequiz/attemptfinished.php',
		['cmid' => $cm->id, 'id' => $cm->instance, 'uattid' => $uniqueid]
	));
}

// Retrieve the question slot id.
$slot = $itemadministrationevaluation->next_item()->slot();

$level = $itemadministrationevaluation->next_item()->difficulty_level();

$headtags = $output->init_metadata($quba, $slot);
$PAGE->requires->js_init_call(
	'M.mod_catadaptivequiz.init_attempt_form',
	array($viewurl->out(), $adaptivequiz->browsersecurity),
	false,
	$output->adaptivequiz_get_js_module()
);

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
		$mform->set_data(array('message' => get_string('wrongpassword', 'catadaptivequiz')));
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
