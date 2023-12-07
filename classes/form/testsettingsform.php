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

		$this->add_action_buttons(true, get_string('save'));

	}

	// Custom validation should be added here.
	function validation($data, $files) {
        return [];
    }
}
