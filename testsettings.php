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
global $DB;
global $USER;

$id = optional_param('cmid', 0, PARAM_INT);
$downloadusersattempts = optional_param('download', '', PARAM_ALPHA);
$n  = optional_param('n', 0, PARAM_INT);
$resetfilter = optional_param('resetfilter', 0, PARAM_INT);

if ($id) {
    $cm         = get_coursemodule_from_id('adaptivequiz', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $adaptivequiz  = $DB->get_record('adaptivequiz', ['id' => $cm->instance], '*', MUST_EXIST);
} else if ($n) {
    $adaptivequiz  = $DB->get_record('adaptivequiz', ['id' => $n], '*', MUST_EXIST);
    $course     = $DB->get_record('course', ['id' => $adaptivequiz->course], '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('adaptivequiz', $adaptivequiz->id, $course->id, false, MUST_EXIST);
} else {
    throw new moodle_exception('invalidarguments');
}

$context = context_module::instance($cm->id);
$PAGE->set_context($context);
$PAGE->add_body_class('limitedwidth');

$PAGE->set_url(new moodle_url('/mod/adaptivequiz/testsettings.php', array('cmid' => $cm->id)));
$PAGE->set_title(get_string('testSettings', 'adaptivequiz'));
$PAGE->set_cm($cm);
/** @var mod_adaptivequiz_renderer $renderer */
$renderer = $PAGE->get_renderer('mod_adaptivequiz');

$templatecontext = (object)[

	'message' => 'Hello Test Settings'
];
echo $OUTPUT->header();

// $mform = new mod_testsettingsform_mod_form('testsettingform',$cm->section,$cm,$cm->course);
$mform = new mod_testsettingsform_mod_form($PAGE->url->out());
if ($mform->is_cancelled()) {
    // If there is a cancel element on the form, and it was pressed,
    // then the `is_cancelled()` function will return true.
    // You can handle the cancel operation here.
} else if ($fromform = $mform->get_data()) {
    // When the form is submitted, and the data is successfully validated,
    // the `get_data()` function will return the data posted in the form.
	$recordtoinsert = new stdClass();
		
	$recordtoinsert->userid = $USER->id;
	$recordtoinsert->courseid = $course->id;
	$recordtoinsert->testlength = $fromform->testlength;
	$recordtoinsert->testduration = $fromform->testduration;
	$recordtoinsert->selecttasktypes = $fromform->selecttasktypes;
	$recordtoinsert->numbercalibrationclusters = $fromform->numbercalibrationclusters;
	$recordtoinsert->numberlinkingclusters = $fromform->numberlinkingclusters;
	$recordtoinsert->numberadaptivclusters = $fromform->numberadaptivclusters;
	$recordtoinsert->personalparameterestimation = $fromform->personalparameterestimation;
	$recordtoinsert->adaptivepart = $fromform->adaptivepart;
	$recordtoinsert->randomesque_exposure_control = $fromform->randomesque_exposure_control;
	$recordtoinsert->suitabletasks = $fromform->suitabletasks;

	$DB->insert_record('adaptivequiz_testsettings', $recordtoinsert);
	// go back to manage page
	redirect($CFG->wwwroot . '/mod/adaptivequiz/testsettings.php?cmid=' .$cm->id,'Testsettings saved on DB');


} else {
    // This branch is executed if the form is submitted but the data doesn't
    // validate and the form should be redisplayed or on the first display of the form.

    // Set anydefault data (if any).
    

    // Display the form.
    $mform->display();

}

echo $OUTPUT->render_from_template('adaptivequiz/testsettings',$templatecontext);

echo $OUTPUT->footer();
