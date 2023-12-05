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
 * Plugin's settings.
 *
 * @package    mod_adaptivequiz
 * @author		Christoph Stilling / 91interactive
 * @copyright  2023 Vitaly Potenko <potenkov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__). '/classes/form/testsettingsform.php');

$id = required_param('cmid', PARAM_INT);

if (!$cm = get_coursemodule_from_id('adaptivequiz', $id)) {
    throw new moodle_exception('invalidcoursemodule');
}

$PAGE->set_url(new moodle_url('/mod/adaptivequiz/testsettings.php', array('cmid' => $cm->id)));
$PAGE->set_title(get_string('testSettings', 'adaptivequiz'));

$templatecontext = (object)[

	'message' => 'Hello Test Settings'
];

$mform = new mod_testsettingsform_mod_form('testsettingform',$cm->section,$cm,$cm->course);
if ($mform->is_cancelled()) {
    // If there is a cancel element on the form, and it was pressed,
    // then the `is_cancelled()` function will return true.
    // You can handle the cancel operation here.
} else if ($fromform = $mform->get_data()) {
    // When the form is submitted, and the data is successfully validated,
    // the `get_data()` function will return the data posted in the form.
} else {
    // This branch is executed if the form is submitted but the data doesn't
    // validate and the form should be redisplayed or on the first display of the form.

    // Set anydefault data (if any).
    

    // Display the form.
    $mform->display();

}

echo $OUTPUT->header();

echo $OUTPUT->render_from_template('adaptivequiz/testsettings',$templatecontext);

echo $OUTPUT->footer();
