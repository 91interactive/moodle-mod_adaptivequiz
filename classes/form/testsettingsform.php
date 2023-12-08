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

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/adaptivequiz/locallib.php');
require_once($CFG->libdir.'/formslib.php');


/**
 * Class testsettingsform
 *
 * @package    mod_adaptivequiz
 * @copyright  2023 Christoph Stilling <c.stilling@91interactive.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


class mod_testsettingsform_mod_form  extends moodleform {
	public function definition(){
		global $PAGE;
		$mform = $this->_form;
		// 
		$mform->addElement('header', 'testsettingsheader', get_string('testsettingsheader', 'adaptivequiz'));

		$mform->addElement('text','testlength',get_string('testlength','adaptivequiz')); // should be only intergers
		$mform->setType('testlength', PARAM_INT);
		$mform->setDefault('testlength',0); // TODO Default value from DB?
		
		$mform->addElement('text','testduration',get_string('testduration','adaptivequiz'),'min'); // should be only intergers
		$mform->setType('testduration', PARAM_INT);
		$mform->setDefault("testduration",0); // TODO Default value from DB?


		$mform->addElement('header', 'notadaptivepartheader', get_string('notadaptivepartheader', 'adaptivequiz'));
		$mform->addElement('static', 'adaptiveSettingsDescription',get_string('description'),get_string('adaptiveSettingsDescription', 'adaptivequiz'));
		$mform->addElement('select', 'selectTaskTypes', get_string('selectTaskTypes', 'adaptivequiz'),  ["Sequentiell","Zufällig"],["sequentiell","zufällig"]);


		// $mform->addElement('header', 'numbercalibrationclusters', get_string('numbercalibrationclusters', 'adaptivequiz'));

		$mform->addElement('text','numbercalibrationclusters',get_string('numbercalibrationclusters','adaptivequiz')); // should be only intergers
		$mform->setType('numbercalibrationclusters', PARAM_INT);
		$mform->setDefault("numbercalibrationclusters",0); // TODO Default value from DB?

		$mform->addElement('text','numberlinkingclusters',get_string('numberlinkingclusters','adaptivequiz')); // should be only intergers
		$mform->setType('numberlinkingclusters', PARAM_INT);
		$mform->setDefault("numberlinkingclusters",0);

		$mform->addElement('text','numberadaptivclusters',get_string('numberadaptivclusters','adaptivequiz')); // should be only intergers
		$mform->setType('numberadaptivclusters', PARAM_INT);
		$mform->setDefault("numberadaptivclusters",0);


		$mform->addElement('static', 'personalparameterestimationDescription',get_string('description'),get_string('personalparameterestimationDescription', 'adaptivequiz'));
		$mform->addElement('select', 'personalparameterestimation', get_string('personalparameterestimation', 'adaptivequiz'),  ["Maximum-A-Posteriori (MAP)","Expected-A-Posteriori (EAP)","Weighted Likelihood Estimation (WLE)","Maximum Likelihood Estimation (MLE)"],["Maximum-A-Posteriori (MAP)","Expected-A-Posteriori (EAP)","Weighted Likelihood Estimation (WLE)","Maximum Likelihood Estimation (MLE)"]);

		$mform->addElement('static', 'adaptivepartheaderDescription',get_string('description'),get_string('adaptivepartheaderDescription', 'adaptivequiz'));
		$mform->addElement('select', 'adaptivepart', get_string('adaptivepart', 'adaptivequiz'),  ["Maximum Information","Minimum Expected Posterior Variance","Maximum Expected Information","Integration-based Kullback-Leibler"],["Maximum Information","Minimum Expected Posterior Variance","Maximum Expected Information","Integration-based Kullback-Leibler"]);


		$mform->addElement('advcheckbox', 'randomesque_exposure_control', '', 'Randomesque Exposure Control', array('group' => 1), array(0, 1));
		
		$mform->addElement('text','suitableTasks',get_string('suitableTasks','adaptivequiz')); // should be only intergers
		$mform->setType('suitableTasks', PARAM_INT);
		$mform->disabledIf('suitableTasks', 'advcheckbox', 'unchecked');
	
		$PAGE->requires->js_init_code("
		document.getElementById('id_suitableTasks').disabled = true;
			document.getElementById('id_randomesque_exposure_control').addEventListener('change', function() {
				document.getElementById('id_suitableTasks').disabled = !this.checked;
			});"
		);

		$this->add_action_buttons(true, get_string('save'));

	}

	// Custom validation should be added here.
	function validation($data, $files) {
        return [];
    }
}
