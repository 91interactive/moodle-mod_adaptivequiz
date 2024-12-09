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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/catadaptivequiz/locallib.php');

use mod_catadaptivequiz\local\repository\questions_repository;

/**
 * Definition of activity settings form.
 *
 * @package    mod_catadaptivequiz
 * @copyright  2013 Remote-Learner {@link http://www.remote-learner.ca/}
 * @copyright  2022 onwards Vitaly Potenko <potenkov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_catadaptivequiz_mod_form extends moodleform_mod
{

	/**
	 * Form definition.
	 */
	public function definition()
	{
		global $PAGE;

		$mform = $this->_form;

		$pluginconfig = get_config('catadaptivequiz');

		// Adding the "general" fieldset, where all the common settings are showed.
		$mform->addElement('header', 'general', get_string('general', 'form'));

		// Adding the standard "name" field.
		$mform->addElement('text', 'name', get_string('catadaptivequizname', 'catadaptivequiz'), ['size' => '64']);
		if (!empty($CFG->formatstringstriptags)) {
			$mform->setType('name', PARAM_TEXT);
		} else {
			$mform->setType('name', PARAM_CLEANHTML);
		}
		$mform->addRule('name', null, 'required', null, 'client');
		$mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
		$mform->addHelpButton('name', 'catadaptivequizname', 'catadaptivequiz');

		// Adding the standard "intro" and "introformat" fields.
		// Use the non deprecated function if it exists.
		if (method_exists($this, 'standard_intro_elements')) {
			$this->standard_intro_elements();
		} else {
			// Deprecated as of Moodle 2.9.
			$this->add_intro_editor();
		}

		// Number of attempts.
		$attemptoptions = ['0' => get_string('unlimited')];
		for ($i = 1; $i <= ADAPTIVEQUIZMAXATTEMPT; $i++) {
			$attemptoptions[$i] = $i;
		}
		$mform->addElement('select', 'attempts', get_string('attemptsallowed', 'catadaptivequiz'), $attemptoptions);
		$mform->setDefault('attempts', 0);
		$mform->addHelpButton('attempts', 'attemptsallowed', 'catadaptivequiz');

		// Require password to begin adaptivequiz attempt.
		$mform->addElement('passwordunmask', 'password', get_string('requirepassword', 'catadaptivequiz'));
		$mform->setType('password', PARAM_TEXT);
		$mform->addHelpButton('password', 'requirepassword', 'catadaptivequiz');

		// Browser security choices.
		$options = [
			get_string('no'),
			get_string('yes'),
		];
		$mform->addElement('select', 'browsersecurity', get_string('browsersecurity', 'catadaptivequiz'), $options);
		$mform->addHelpButton('browsersecurity', 'browsersecurity', 'catadaptivequiz');
		$mform->setDefault('browsersecurity', 0);

		// Retireve a list of available course categories.
		adaptivequiz_make_default_categories($this->context);
		$options = adaptivequiz_get_question_categories($this->context);
		$selquestcat = adaptivequiz_get_selected_question_cateogires($this->_instance);

		$select = $mform->addElement('select', 'questionpool', get_string('questionpool', 'catadaptivequiz'), $options);
		$mform->addHelpButton('questionpool', 'questionpool', 'catadaptivequiz');
		$select->setMultiple(true);
		$mform->addRule('questionpool', null, 'required', null, 'client');
		$mform->getElement('questionpool')->setSelected($selquestcat);

		/* todo rm: check
		// Adding the standard "starting level of difficulty" field.
		$mform->addElement(
			'text',
			'startinglevel',
			get_string('startinglevel', 'catadaptivequiz'),
			['size' => '3', 'maxlength' => '3']
		);
		$mform->addHelpButton('startinglevel', 'startinglevel', 'catadaptivequiz');
		$mform->addRule('startinglevel', get_string('formelementempty', 'catadaptivequiz'), 'required', null, 'client');
		$mform->addRule('startinglevel', get_string('formelementnumeric', 'catadaptivequiz'), 'numeric', null, 'client');
		$mform->setType('startinglevel', PARAM_INT);
		$mform->setDefault('startinglevel', $pluginconfig->startinglevel);



		// Adding the standard "lowest level of difficulty" field.
		$mform->addElement(
			'text',
			'lowestlevel',
			get_string('lowestlevel', 'catadaptivequiz'),
			['size' => '3', 'maxlength' => '3']
		);
		$mform->addHelpButton('lowestlevel', 'lowestlevel', 'catadaptivequiz');
		$mform->addRule('lowestlevel', get_string('formelementempty', 'catadaptivequiz'), 'required', null, 'client');
		$mform->addRule('lowestlevel', get_string('formelementnumeric', 'catadaptivequiz'), 'numeric', null, 'client');
		$mform->setType('lowestlevel', PARAM_INT);
		$mform->setDefault('lowestlevel', $pluginconfig->lowestlevel);


		// Adding the standard "highest level of difficulty" field.
		$mform->addElement(
			'text',
			'highestlevel',
			get_string('highestlevel', 'catadaptivequiz'),
			['size' => '3', 'maxlength' => '3']
		);
		$mform->addHelpButton('highestlevel', 'highestlevel', 'catadaptivequiz');
		$mform->addRule('highestlevel', get_string('formelementempty', 'catadaptivequiz'), 'required', null, 'client');
		$mform->addRule('highestlevel', get_string('formelementnumeric', 'catadaptivequiz'), 'numeric', null, 'client');
		$mform->setType('highestlevel', PARAM_INT);
		$mform->setDefault('highestlevel', $pluginconfig->highestlevel);
*/


		// Adding the standard "attempt feedback" field.
		$mform->addElement(
			'textarea',
			'attemptfeedback',
			get_string('attemptfeedback', 'catadaptivequiz'),
			'wrap="virtual" rows="10" cols="50"'
		);
		$mform->setType('attemptfeedback', PARAM_NOTAGS);

		// Adding the standard "attempt feedback" field.
		// $mform->addElement('editor', 'attemptfeedback', get_string('attemptfeedback', 'catadaptivequiz'), array('rows' => 10), array(
		// 	'subdirs'=>0,
		// 	'maxbytes'=>0,
		// 	'maxfiles'=>0,
		// 	'changeformat'=>0,
		// 	'context'=>null,
		// 	'noclean'=>0,
		// 	'trusttext'=>0,
		// 	'enable_filemanagement' => false)
		// );
		// $mform->setType('attemptfeedback', PARAM_RAW); // no XSS prevention here, users must be trusted


		$mform->addHelpButton('attemptfeedback', 'attemptfeedback', 'catadaptivequiz');

		// Adding the standard "show ability measure to students" field.
		$mform->addElement(
			'select',
			'showabilitymeasure',
			get_string('showabilitymeasure', 'catadaptivequiz'),
			[get_string('no'), get_string('yes')]
		);
		$mform->addHelpButton('showabilitymeasure', 'showabilitymeasure', 'catadaptivequiz');
		$mform->setDefault('showabilitymeasure', 0);

		// Adding the standard "show quiz progress to students" field.
		$mform->addElement(
			'select',
			'showattemptprogress',
			get_string('modformshowattemptprogress', 'catadaptivequiz'),
			[get_string('no'), get_string('yes')]
		);
		$mform->addHelpButton('showattemptprogress', 'modformshowattemptprogress', 'catadaptivequiz');
		$mform->setDefault('showattemptprogress', 0);




		// Adding the "Test settings" fieldset, where all the common settings are showed.
		$mform->addElement('header', 'testsettingsheader', get_string('testsettingsheader', 'catadaptivequiz'));


		// Adding the standard "select task type" field.
		$mform->addElement('select', 'selecttasktypes', get_string('selecttasktypes', 'catadaptivequiz'),  [get_string('sequential', 'catadaptivequiz'), get_string('random', 'catadaptivequiz')], [get_string('sequential', 'catadaptivequiz'), get_string('random', 'catadaptivequiz')]);
		$mform->addHelpButton('selecttasktypes', 'selecttasktypesDescription', 'catadaptivequiz');

		// Adding the standard "Number of calibration clusters" field.
		$mform->addElement('text', 'numbercalibrationclusters', get_string('numbercalibrationclusters', 'catadaptivequiz')); // should be only intergers
		$mform->setType('numbercalibrationclusters', PARAM_INT);
		$mform->addRule('numbercalibrationclusters', get_string('formelementempty', 'catadaptivequiz'), 'required', null, 'client');
		$mform->addHelpButton('numbercalibrationclusters', 'numbercalibrationclustersDescription', 'catadaptivequiz');

		// Adding the standard "Number of linking clusters" field.
		$mform->addElement('text', 'numberlinkingclusters', get_string('numberlinkingclusters', 'catadaptivequiz')); // should be only intergers
		$mform->setType('numberlinkingclusters', PARAM_INT);
		$mform->addRule('numberlinkingclusters', get_string('formelementempty', 'catadaptivequiz'), 'required', null, 'client');
		$mform->addHelpButton('numberlinkingclusters', 'numberlinkingclustersDescription', 'catadaptivequiz');

		// Adding the standard "Number of adaptive clusters" field.
		$mform->addElement('text', 'numberadaptiveclusters', get_string('numberadaptiveclusters', 'catadaptivequiz')); // should be only intergers
		$mform->setType('numberadaptiveclusters', PARAM_INT);
		$mform->addRule('numberadaptiveclusters', get_string('formelementempty', 'catadaptivequiz'), 'required', null, 'client');
		$mform->addHelpButton('numberadaptiveclusters', 'numberadaptiveclustersDescription', 'catadaptivequiz');

		// Adding the standard "Personal parameter estimation" field.
		$mform->addElement('select', 'personalparameterestimation', get_string('personalparameterestimation', 'catadaptivequiz'),  ["Maximum-A-Posteriori (MAP)", "Expected-A-Posteriori (EAP)", "Weighted Likelihood Estimation (WLE)", "Maximum Likelihood Estimation (ML)"], ["Maximum-A-Posteriori (MAP)", "Expected-A-Posteriori (EAP)", "Weighted Likelihood Estimation (WLE)", "Maximum Likelihood Estimation (ML)"]);
		$mform->addHelpButton('personalparameterestimation', 'personalparameterestimationDescription', 'catadaptivequiz');

		// Adding the standard "Task selection adaptive part" field
		$mform->addElement('select', 'adaptivepart', get_string('adaptivepart', 'catadaptivequiz'),  ["Maximum Information", "Minimum Expected Posterior Variance", "Maximum Expected Information", "Integration-based Kullback-Leibler"], ["Maximum Information", "Minimum Expected Posterior Variance", "Maximum Expected Information", "Integration-based Kullback-Leibler"]);
		$mform->addHelpButton('adaptivepart', 'adaptivepartDescription', 'catadaptivequiz');

		// Adding the standard "Randomesque Exposure Control" checkbox
		$mform->addElement('advcheckbox', 'randomesque_exposure_control', get_string('randomesqueexposurecontrol', 'catadaptivequiz'), 'Randomesque Exposure Control', array('group' => 1), array(0, 1));

		// Adding the standard "Number of best matching tasks from which to choose at random" field
		$mform->addElement('text', 'suitabletasks', get_string('suitabletasks', 'catadaptivequiz')); // should be only intergers
		$mform->setType('suitabletasks', PARAM_INT);
		$mform->setDefault('suitabletasks', 0);
		$mform->hideIf('suitabletasks', 'randomesque_exposure_control', 'notchecked');
		$mform->addHelpButton('suitabletasks', 'suitabletasksdescription', 'catadaptivequiz');

		// // adding standard "message before test" field 
		// $mform->addElement('textarea','messagebeforetest',get_string('messagebeforetest','catadaptivequiz'));
		// $mform->setType('messagebeforetest', PARAM_NOTAGS);
		// $mform->addRule('messagebeforetest', get_string('formtextareaempty', 'catadaptivequiz'), 'required', null, 'client');
		// $mform->addHelpButton('messagebeforetest', 'messagebeforetestDescription', 'catadaptivequiz');

		// adding standard "message on last page of the test" field 
		// $mform->addElement('textarea', 'messageatlastpage', get_string('messageatlastpage', 'catadaptivequiz'));
		// $mform->setType('messageatlastpage', PARAM_NOTAGS);
		// $mform->addRule('messageatlastpage', get_string('formtextareaempty', 'catadaptivequiz'), 'required', null, 'client');
		// $mform->addHelpButton('messageatlastpage', 'messageatlastpageDescription', 'catadaptivequiz');

		// adding checkbox for "User-defined specification of proportions of individual content areas in the overall test?"
		$mform->addElement('advcheckbox', 'contentareas', '', get_string('contentareas', 'catadaptivequiz'), array('group' => 1), array(0, 1));

		// adding content area fields
		$mform->addElement('text', 'contentarea1', get_string('contentareaDistributionDescription', 'catadaptivequiz')); 
		$mform->setType('contentarea1', PARAM_NOTAGS);
		$mform->addHelpButton('contentarea1', 'contentareaDistributionDescription', 'catadaptivequiz');

		// $mform->addElement('text', 'contentarea2', 'Inhaltsbereich2'); 
		// $mform->setType('contentarea2', PARAM_NOTAGS);
		// $mform->addElement('text', 'contentarea3', 'Inhaltsbereich3'); 
		// $mform->setType('contentarea3', PARAM_NOTAGS);
		// $mform->addElement('text', 'contentarea4', 'Inhaltsbereich4'); 
		// $mform->setType('contentarea4', PARAM_NOTAGS);
		$mform->hideIf('contentarea1', 'contentareas', 'notchecked');
		// $mform->hideIf('contentarea2', 'contentareas', 'notchecked');
		// $mform->hideIf('contentarea3', 'contentareas', 'notchecked');
		// $mform->hideIf('contentarea4', 'contentareas', 'notchecked');

		// Adding the "Stopping conditions" fieldset, where all the common settings are showed.
		$mform->addElement('header', 'stopingconditionshdr', get_string('stopingconditionshdr', 'catadaptivequiz'));

		// Adding the standard "minimum number of questions" field.
		$mform->addElement(
			'text',
			'minimumquestions',
			get_string('minimumquestions', 'catadaptivequiz'),
			['size' => '3', 'maxlength' => '3']
		);
		$mform->addHelpButton('minimumquestions', 'minimumquestions', 'catadaptivequiz');
		$mform->addRule('minimumquestions', get_string('formelementempty', 'catadaptivequiz'), 'required', null, 'client');
		$mform->addRule('minimumquestions', get_string('formelementnumeric', 'catadaptivequiz'), 'numeric', null, 'client');
		$mform->setType('minimumquestions', PARAM_INT);
		$mform->setDefault('minimumquestions', $pluginconfig->minimumquestions);

		// Adding the standard "maximum number of questions" field.
		$mform->addElement(
			'text',
			'maximumquestions',
			get_string('maximumquestions', 'catadaptivequiz'),
			['size' => '3', 'maxlength' => '3']
		);
		$mform->addHelpButton('maximumquestions', 'maximumquestions', 'catadaptivequiz');
		$mform->addRule('maximumquestions', get_string('formelementempty', 'catadaptivequiz'), 'required', null, 'client');
		$mform->addRule('maximumquestions', get_string('formelementnumeric', 'catadaptivequiz'), 'numeric', null, 'client');
		$mform->setType('maximumquestions', PARAM_INT);
		$mform->setDefault('maximumquestions', $pluginconfig->maximumquestions);

		// // Adding the standard "test length" field.
		// $mform->addElement('text','testlength',get_string('testlength','catadaptivequiz')); // should be only intergers
		// $mform->setType('testlength', PARAM_INT);
		// $mform->addRule('testlength', get_string('formelementempty', 'catadaptivequiz'), 'required', null, 'client');
		// $mform->addHelpButton('testlength', 'testlengthDescription', 'catadaptivequiz');

		// Adding the standard "test duration in minutes" field.
		$mform->addElement('text', 'testduration', get_string('testduration', 'catadaptivequiz'), 'min'); // should be only intergers
		$mform->setType('testduration', PARAM_INT);
		$mform->addRule('testduration', get_string('formelementempty', 'catadaptivequiz'), 'required', null, 'client');
		$mform->addHelpButton('testduration', 'testdurationDescription', 'catadaptivequiz');


		// // Adding the standard "maximum number of questions" field.
		// $mform->addElement('text', 'maximumquestions', get_string('maximumquestions', 'catadaptivequiz'),
		//     ['size' => '3', 'maxlength' => '3']);
		// $mform->addHelpButton('maximumquestions', 'maximumquestions', 'catadaptivequiz');
		// $mform->addRule('maximumquestions', get_string('formelementempty', 'catadaptivequiz'), 'required', null, 'client');
		// $mform->addRule('maximumquestions', get_string('formelementnumeric', 'catadaptivequiz'), 'numeric', null, 'client');
		// $mform->setType('maximumquestions', PARAM_INT);
		// $mform->setDefault('maximumquestions', $pluginconfig->maximumquestions);

		// Adding the standard "standard error to stop" field.
		$mform->addElement(
			'text',
			'standarderror',
			get_string('standarderror', 'catadaptivequiz'),
			['size' => '10', 'maxlength' => '10']
		);
		$mform->addHelpButton('standarderror', 'standarderror', 'catadaptivequiz');
		$mform->addRule('standarderror', get_string('formelementempty', 'catadaptivequiz'), 'required', null, 'client');
		$mform->addRule('standarderror', get_string('formelementdecimal', 'catadaptivequiz'), 'numeric', null, 'client');
		$mform->setType('standarderror', PARAM_FLOAT);
		$mform->setDefault('standarderror', $pluginconfig->standarderror);


		// Grade settings.
		$this->standard_grading_coursemodule_elements();
		$mform->removeElement('grade');

		// Grading method.
		$mform->addElement(
			'select',
			'grademethod',
			get_string('grademethod', 'catadaptivequiz'),
			adaptivequiz_get_grading_options()
		);
		$mform->addHelpButton('grademethod', 'grademethod', 'catadaptivequiz');
		$mform->setDefault('grademethod', ADAPTIVEQUIZ_GRADEHIGHEST);
		$mform->disabledIf('grademethod', 'attempts', 'eq', 1);

		// Add standard elements, common to all modules.
		$this->standard_coursemodule_elements();

		// Add standard buttons, common to all modules.
		$this->add_action_buttons();
	}

	public function add_completion_rules(): array
	{
		$form = $this->_form;
		$form->addElement(
			'checkbox',
			'completionattemptcompleted',
			' ',
			get_string('completionattemptcompletedform', 'catadaptivequiz')
		);

		return ['completionattemptcompleted'];
	}

	public function completion_rule_enabled($data): bool
	{
		if (!isset($data['completionattemptcompleted'])) {
			return false;
		}

		return $data['completionattemptcompleted'] != 0;
	}

	/**
	 * Perform extra validation. @see validation() in moodleform_mod.php.
	 *
	 * @param array $data Array of submitted form values.
	 * @param array $files Array of file data.
	 * @return array Array of form elements that didn't pass validation.
	 * @throws coding_exception
	 * @throws dml_exception
	 */
	public function validation($data, $files)
	{
		$errors = parent::validation($data, $files);

		if (empty($data['questionpool'])) {
			$errors['questionpool'] = get_string('formquestionpool', 'catadaptivequiz');
		}

		// Validate for positivity.
		if (0 >= $data['minimumquestions']) {
			$errors['minimumquestions'] = get_string('formelementnegative', 'catadaptivequiz');
		}

		if (0 >= $data['maximumquestions']) {
			$errors['maximumquestions'] = get_string('formelementnegative', 'catadaptivequiz');
		}

		// if (0 >= $data['startinglevel']) {
		// 	$errors['startinglevel'] = get_string('formelementnegative', 'catadaptivequiz');
		// }

		// if (0 >= $data['lowestlevel']) {
		// 	$errors['lowestlevel'] = get_string('formelementnegative', 'catadaptivequiz');
		// }

		// if (0 >= $data['highestlevel']) {
		// 	$errors['highestlevel'] = get_string('formelementnegative', 'catadaptivequiz');
		// }

		if ((float) 0 > (float) $data['standarderror'] || (float) 1 <= (float) $data['standarderror']) {
			$errors['standarderror'] = get_string('formstderror', 'catadaptivequiz');
		}

		// Validate higher and lower values.
		if ($data['minimumquestions'] >= $data['maximumquestions']) {
			$errors['minimumquestions'] = get_string('formminquestgreaterthan', 'catadaptivequiz');
		}

		// if ($data['lowestlevel'] >= $data['highestlevel']) {
		// 	$errors['lowestlevel'] = get_string('formlowlevelgreaterthan', 'catadaptivequiz');
		// }

		// if (!($data['startinglevel'] >= $data['lowestlevel'] && $data['startinglevel'] <= $data['highestlevel'])) {
		// 	$errors['startinglevel'] = get_string('formstartleveloutofbounds', 'catadaptivequiz');
		// }

		// if ($questionspoolerrormsg = $this->validate_questions_pool($data['questionpool'], $data['startinglevel'])) {
		// 	$errors['questionpool'] = $questionspoolerrormsg;
		// }

		// if (0 >= $data['testlength']) {
		//     $errors['testlength'] = get_string('formelementnegative', 'catadaptivequiz');
		// }
		if (0 >= $data['testduration']) {
			$errors['testduration'] = get_string('formelementnegative', 'catadaptivequiz');
		}
		if (0 > $data['numbercalibrationclusters']) {
			$errors['numbercalibrationclusters'] = get_string('formelementnegative', 'catadaptivequiz');
		}
		if (0 > $data['numberlinkingclusters']) {
			$errors['numberlinkingclusters'] = get_string('formelementnegative', 'catadaptivequiz');
		}
		if (0 > $data['numberadaptiveclusters']) {
			$errors['numberadaptiveclusters'] = get_string('formelementnegative', 'catadaptivequiz');
		}
		if ($data['randomesque_exposure_control']) {
			if (0 > $data['suitabletasks']) {
				$errors['suitabletasks'] = get_string('formelementnegative', 'catadaptivequiz');
			}
		}
		return $errors;
	}

	/**
	 * @param int[] $qcategoryidlist A list of id of selected questions categories.
	 * @return string An error message if any.
	 * @throws coding_exception
	 */
	private function validate_questions_pool(array $qcategoryidlist, int $startinglevel): string
	{
		return questions_repository::count_adaptive_questions_in_pool_with_level($qcategoryidlist, $startinglevel) > 0
			? ''
			: get_string('questionspoolerrornovalidstartingquestions', 'catadaptivequiz');
	}
}
