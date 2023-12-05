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

/**
 * Class testsettingsform
 *
 * @package    mod_adaptivequiz
 * @copyright  2023 Christoph Stilling <c.stilling@91interactive.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_testsettingsform_mod_form  extends moodleform_mod {
	public function definition(){
		$mform = $this->_form;

		$mform->addElement('text','testlength',get_string('testlength','adaptivequiz')); // should be only intergers
		$mform->setType('testlength', PARAM_NOTAGS);
		$mform->setDefault('testlength',get_string('testlength','adaptivequiz'));

	}

	// Custom validation should be added here.
	function validation($data, $files) {
        return [];
    }
}
