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
 * Contains definition of the renderer class for the plugin.
 *
 * @package   mod_catadaptivequiz
 * @copyright 2013 Remote-Learner {@link http://www.remote-learner.ca/}
 * @copyright 2022 onwards Vitaly Potenko <potenkov@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use core\output\notification;
use mod_catadaptivequiz\form\requiredpassword;
use mod_catadaptivequiz\local\attempt\attempt_state;
use mod_catadaptivequiz\local\catalgorithm\catalgo;
use mod_catadaptivequiz\output\ability_measure;
use mod_catadaptivequiz\output\attempt_progress;
use mod_catadaptivequiz\output\report\individual_user_attempts\individual_user_attempt_action;
use mod_catadaptivequiz\output\report\individual_user_attempts\individual_user_attempt_actions;
use mod_catadaptivequiz\output\user_attempt_summary;

/**
 * The renderer class for the plugin.
 *
 * @package mod_catadaptivequiz
 */
class mod_catadaptivequiz_renderer extends plugin_renderer_base {
    /** @var string $sortdir the sorting direction being used */
    protected $sortdir = '';
    /** @var moodle_url $sorturl the current base url used for keeping the table sorted */
    protected $sorturl = '';
    /** @var int $groupid variable used to reference the groupid that is currently being used to filter by */
    public $groupid = 0;
    /** @var array options that should be used for opening the secure popup. */
    protected static $popupoptions = array(
        'left' => 0,
        'top' => 0,
        'fullscreen' => true,
        'scrollbars' => false,
        'resizeable' => false,
        'directories' => false,
        'toolbar' => false,
        'titlebar' => false,
        'location' => false,
        'status' => false,
        'menubar' => false
    );

    /**
     * Displays either the button to start the quiz or a notification if an attempt cannot be started/continued.
     */
    public function attempt_controls_or_notification(
        int $cmid,
        bool $attemptallowed,
        string $activityavailabilitynotification,
        bool $browsersecurityenabled
    ): string {
        if ($activityavailabilitynotification) {
            return $this->notification($activityavailabilitynotification, notification::NOTIFY_WARNING);
        }

        if (!$attemptallowed) {
            return html_writer::div(get_string('noattemptsallowed', 'catadaptivequiz'), 'alert alert-info text-center');
        }

        if ($browsersecurityenabled) {
            return $this->display_start_attempt_form_secured($cmid);
        }

        return $this->render(new single_button(
            new moodle_url('/mod/catadaptivequiz/attempt.php', ['cmid' => $cmid, 'sesskey' => sesskey()]),
            get_string('startattemptbtn', 'catadaptivequiz'),
            'post',
            single_button::BUTTON_PRIMARY
        ));
    }

    /**
     * This function sets up the javascript required by the page
     * @return array a standard jsmodule structure.
     */
    public function adaptivequiz_get_js_module() {
        return array(
            'name' => 'mod_catadaptivequiz',
            'fullpath' => '/mod/catadaptivequiz/module.js',
            'requires' => array('base', 'dom', 'event-delegate', 'event-key', 'core_question_engine',
                'moodle-core-formchangechecker'),
            'strings' => array(array('cancel', 'moodle'), array('changesmadereallygoaway', 'moodle'),
                array('functiondisabledbysecuremode', 'catadaptivequiz'))
        );
    }

    /**
     * This function generates the HTML markup to render the submission form.
     *
     * @param int $cmid
     * @param question_usage_by_activity $quba
     * @param int $slot Slot number of the question to be displayed.
     * @param int $level Difficulty level of question.
     * @param int $questionnumber The order number of question in the quiz.
     */
    public function question_submit_form($cmid, $quba, $slot, $level, int $questionnumber): string {
        $output = '';

        $processurl = new moodle_url('/mod/catadaptivequiz/attempt.php');

        // Start the form.
        $attr = array('action' => $processurl, 'method' => 'post', 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8',
            'id' => 'responseform');
        $output .= html_writer::start_tag('form', $attr);
        $output .= html_writer::start_tag('div');

        // Print the question.
        $options = new question_display_options();
        $options->hide_all_feedback();
        $options->flags = question_display_options::HIDDEN;
        $options->marks = question_display_options::HIDDEN;

        $output .= $quba->render_question($slot, $options, $questionnumber);

        $output .= html_writer::start_tag('div', ['class' => 'submitbtns adaptivequizbtn mdl-align']);
        $output .= html_writer::empty_tag('input', ['type' => 'submit', 'name' => 'submitanswer',
            'value' => get_string('submitanswer', 'mod_catadaptivequiz'), 'class' => 'btn btn-primary']);
        $output .= html_writer::end_tag('div');

        // Some hidden fields to track what is going on.
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'cmid', 'value' => $cmid));

        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'uniqueid', 'value' => $quba->get_id()));

        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));

        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'slots', 'value' => $slot));

        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'dl', 'value' => $level));

        // Finish the form.
        $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('form');

        return $output;
    }

    /**
     * This function initializing the metadata that needs to be included in the page header
     * before the page is rendered.
     * @param question_usage_by_activity $quba a question usage by activity object
     * @param int|array $slots slot number of the question to be displayed or an array of slot numbers
     * @return string HTML header information for displaying the question
     */
    public function init_metadata($quba, $slots) {
        $meta = '';

        if (is_array($slots)) {
            foreach ($slots as $slot) {
                $meta .= $quba->render_question_head_html($slot);
            }
        } else {
            $meta .= $quba->render_question_head_html($slots);
        }

        $meta .= question_engine::initialise_js();
        return $meta;
    }

    /**
     * @throws coding_exception
     */
    public function attempt_feedback(string $attemptfeedback, int $cmid, ?ability_measure $abilitymeasure,
        bool $popup = false): string {

        $output = html_writer::start_div('text-center');

        $url = new moodle_url('/mod/catadaptivequiz/view.php');
        $attr = ['action' => $url, 'method' => 'post', 'id' => 'attemptfeedback'];
        $output .= html_writer::start_tag('form', $attr);

        if (empty(trim($attemptfeedback))) {
            $attemptfeedback = get_string('attemptfeedbackdefaulttext', 'catadaptivequiz');
        }
        $output .= html_writer::tag('p', s($attemptfeedback), ['class' => 'submitbtns adaptivequizfeedback']);

        if ($abilitymeasure) {
            $output .= $this->render($abilitymeasure);
        }

        if (empty($popup)) {
            $attr = ['type' => 'submit', 'name' => 'attemptfinished', 'value' => get_string('continue'),
                'class' => 'btn btn-primary'];
            $output .= html_writer::empty_tag('input', $attr);
        } else {
            // In a 'secure' popup window.
            $this->page->requires->js_init_call('M.mod_catadaptivequiz.secure_window.init_close_button', [$url],
                $this->adaptivequiz_get_js_module());
            $output .= html_writer::empty_tag('input', ['type' => 'button', 'value' => get_string('continue'),
                'id' => 'secureclosebutton', 'class' => 'btn btn-primary']);
        }

        $output .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $cmid]);
        $output .= html_writer::end_tag('form');

        $output .= html_writer::end_div();

        return $output;
    }

    /**
     * Output a page with an optional message, and JavaScript code to close the
     * current window and redirect the parent window to a new URL.
     * @param moodle_url $url the URL to redirect the parent window to.
     * @param string $message message to display before closing the window. (optional)
     * @return string HTML to output.
     */
    public function close_attempt_popup($url, $message = '') {
        $output = '';
        $output .= $this->header();
        $output .= $this->box_start();

        if ($message) {
            $output .= html_writer::tag('p', $message);
            $output .= html_writer::tag('p', get_string('windowclosing', 'quiz'));
            $delay = 5;
        } else {
            $output .= html_writer::tag('p', get_string('pleaseclose', 'quiz'));
            $delay = 0;
        }
        $this->page->requires->js_init_call('M.mod_quiz.secure_window.close',
                array($url, $delay), false, adaptivequiz_get_js_module());

        $output .= $this->box_end();
        $output .= $this->footer();
        return $output;
    }

    /**
     * This function returns page header information to be printed to the page
     * @return string HTML markup for header inforation
     */
    public function print_header() {
        return $this->header();
    }

    /**
     * This function returns page footer information to be printed to the page
     * @return string HTML markup for footer inforation
     */
    public function print_footer() {
        return $this->footer();
    }

    /**
     * This function creates the table header links that will be used to allow instructor to sort the data
     * @param stdClass $cm a course module object set to the instance of the activity
     * @param string $sort the column the the table is to be sorted by
     * @param string $sortdir the direction of the sort
     * @return array an array of column headers (firstname / lastname, number of attempts, standard error)
     */
    public function format_report_table_headers($cm, $sort, $sortdir) {
        $firstname = '';
        $lastname = '';
        $email = '';
        $numofattempts = '';
        $measure = '';
        $standarderror = '';
        $timemodified = '';

        /* Determine the next sorting direction and icon to display */
        switch ($sortdir) {
            case 'ASC':
                $imageparam = array('src' => $this->image_url('t/down'), 'alt' => '');
                $columnicon = html_writer::empty_tag('img', $imageparam);
                $newsortdir = 'DESC';
                break;
            default:
                $imageparam = array('src' => $this->image_url('t/up'), 'alt' => '');
                $columnicon = html_writer::empty_tag('img', $imageparam);
                $newsortdir = 'ASC';
                break;
        }

        /* Set the sort direction class variable */
        $this->sortdir = $sortdir;

        /* Create header links */
        $param = array('cmid' => $cm->id, 'sort' => 'firstname', 'sortdir' => 'ASC', 'group' => $this->groupid);
        $firstnameurl = new moodle_url('/mod/catadaptivequiz/viewreport.php', $param);
        $param['sort'] = 'lastname';
        $lastnameurl = new moodle_url('/mod/catadaptivequiz/viewreport.php', $param);
        $param['sort'] = 'email';
        $emailurl = new moodle_url('/mod/catadaptivequiz/viewreport.php', $param);
        $param['sort'] = 'attempts';
        $numofattemptsurl = new moodle_url('/mod/catadaptivequiz/viewreport.php', $param);
        $param['sort'] = 'measure';
        $measureurl = new moodle_url('/mod/catadaptivequiz/viewreport.php', $param);
        $param['sort'] = 'stderror';
        $standarderrorurl = new moodle_url('/mod/catadaptivequiz/viewreport.php', $param);
        $param['sort'] = 'timemodified';
        $timemodifiedurl = new moodle_url('/mod/catadaptivequiz/viewreport.php', $param);

        /* Update column header links with a sorting directional icon */
        switch ($sort) {
            case 'firstname':
                $firstnameurl->params(array('sortdir' => $newsortdir));
                $this->sorturl = $firstnameurl;
                $firstname .= '&nbsp;'.$columnicon;
                break;
            case 'lastname':
                $lastnameurl->params(array('sortdir' => $newsortdir));
                $this->sorturl = $lastnameurl;
                $lastname .= '&nbsp;'.$columnicon;
                break;
            case 'email':
                $emailurl->params(array('sortdir' => $newsortdir));
                $this->sorturl = $emailurl;
                $email .= '&nbsp;'.$columnicon;
                break;
            case 'attempts':
                $numofattemptsurl->params(array('sortdir' => $newsortdir));
                $this->sorturl = $numofattemptsurl;
                $numofattempts .= '&nbsp;'.$columnicon;
                break;
            case 'measure':
                $measureurl->params(array('sortdir' => $newsortdir));
                $this->sorturl = $measureurl;
                $measure .= '&nbsp;'.$columnicon;
                break;
            case 'stderror':
                $standarderrorurl->params(array('sortdir' => $newsortdir));
                $this->sorturl = $standarderrorurl;
                $standarderror .= '&nbsp;'.$columnicon;
                break;
            case 'timemodified':
                $timemodifiedurl->params(array('sortdir' => $newsortdir));
                $this->sorturl = $timemodifiedurl;
                $timemodified .= '&nbsp;'.$columnicon;
                break;
        }

        // Create header HTML markup.
        $firstname = html_writer::link($firstnameurl, get_string('firstname')).$firstname;
        $lastname = html_writer::link($lastnameurl, get_string('lastname')).$lastname;
        $email = html_writer::link($emailurl, get_string('email')).$email;
        $numofattempts = html_writer::link($numofattemptsurl, get_string('numofattemptshdr', 'catadaptivequiz')).$numofattempts;
        $measure = html_writer::link($measureurl, get_string('theta', 'catadaptivequiz')).$measure;
        $standarderror = html_writer::link($standarderrorurl, get_string('bestscorestderror', 'catadaptivequiz')).$standarderror;
        $timemodified = html_writer::link($timemodifiedurl, get_string('attemptfinishedtimestamp', 'catadaptivequiz')).$timemodified;

        return array($firstname.' / '.$lastname, $email, $numofattempts, $measure, $standarderror, $timemodified);
    }

    /**
     * This function adds rows to the html_table object
     * @param stdClass $records adaptivequiz_attempt records
     * @param stdClass $cm course module object set to the instance of the activity
     * @param html_table $table an instance of the html_table class
     */
    protected function get_report_table_rows($records, $cm, $table) {
        foreach ($records as $record) {
            $attemptlink = new moodle_url('/mod/catadaptivequiz/viewattemptreport.php',
                array('userid' => $record->id, 'cmid' => $cm->id));
            $link = html_writer::link($attemptlink, $record->attempts);
            $measure = $this->format_measure($record);
            if ($record->uniqueid) {
                $attemptlink = new moodle_url('/mod/catadaptivequiz/reviewattempt.php',
                    array('userid' => $record->id, 'uniqueid' => $record->uniqueid, 'cmid' => $cm->id));
                $measure = html_writer::link($attemptlink, $measure);
            }
            $stderror = $this->format_standard_error($record);
            if (intval($record->timemodified)) {
                $timemodified = userdate(intval($record->timemodified));
            } else {
                $timemodified = get_string('na', 'catadaptivequiz');
            }
            $profileurl = new moodle_url('/user/profile.php', array('id' => $record->id));
            $name = $record->firstname.' '.$record->lastname;
            $namelink = html_writer::link($profileurl, $name);
            $emaillink = html_writer::link('mailto:'.$record->email, $record->email);
            $row = array($namelink, $emaillink, $link, $measure, $stderror, $timemodified);
            $table->data[] = $row;
            $table->rowclasses[] = 'studentattempt';
        }
    }

    /**
     * This function prints paging information
     * @param int $totalrecords the total number of records returned
     * @param int $page the current page the user is on
     * @param int $perpage the number of records displayed on one page
     * @return string HTML markup
     */
    public function print_paging_bar($totalrecords, $page, $perpage) {
        $baseurl = $this->sorturl;
        /* Set the currently set group filter and sort dir */
        $baseurl->params(array('group' => $this->groupid, 'sortdir' => $this->sortdir));

        $output = '';
        $output .= $this->paging_bar($totalrecords, $page, $perpage, $baseurl);
        return $output;
    }

    /**
     * This function prints a grouping selector
     * @param stdClass $cm course module object set to the instance of the activity
     * @param stdClass $course a data record for the current course
     * @param stdClass $context the context instance for the activity
     * @param int $userid the current user id
     * @return string HTML markup
     */
    public function print_groups_selector($cm, $course, $context, $userid) {
        $output = '';
        $groupmode = groups_get_activity_groupmode($cm, $course);

        if (0 != $groupmode) {
            $baseurl = new moodle_url('/mod/catadaptivequiz/viewreport.php', array('cmid' => $cm->id));
            $output = groups_print_activity_menu($cm, $baseurl, true);
        }

        return $output;
    }

    /**
     * Initialize secure browsing mode.
     */
    public function init_browser_security($disablejsfeatures = true) {
        $this->page->set_popup_notification_allowed(false); // Prevent message notifications.
        $this->page->set_cacheable(false);
        $this->page->set_pagelayout('popup');

        if ($disablejsfeatures) {
            $this->page->add_body_class('quiz-secure-window');
            $this->page->requires->js_init_call('M.mod_catadaptivequiz.secure_window.init',
                null, false, $this->adaptivequiz_get_js_module());
        }
    }

    /**
     * This function displays a form for users to enter a password before entering the attempt
     * @param int $cmid course module id
     * @return requiredpassword instance of a formslib object
     */
    public function display_password_form($cmid): requiredpassword {
        $url = new moodle_url('/mod/catadaptivequiz/attempt.php');
        return new requiredpassword($url->out_omit_querystring(),
            array('hidden' => array('cmid' => $cmid, 'uniqueid' => 0)));
    }

    /**
     * This function prints a form and a button that is centered on the page, then the user clicks on the button the user is taken
     * to the url
     * @param moodle_url $url a url
     * @param string $buttontext button caption
     * @return string - HTML markup displaying the description and form with a submit button
     */
    public function print_form_and_button($url, $buttontext) {
        $html = '';

        $attributes = array('method' => 'POST', 'action' => $url);

        $html .= html_writer::start_tag('form', $attributes);
        $html .= html_writer::empty_tag('br');
        $html .= html_writer::empty_tag('br');
        $html .= html_writer::start_tag('center');

        $params = array('type' => 'submit', 'value' => $buttontext, 'class' => 'submitbtns adaptivequizbtn');
        $html .= html_writer::empty_tag('input', $params);
        $html .= html_writer::end_tag('center');
        $html .= html_writer::end_tag('form');

        return $html;
    }

    /**
     * This function formats the ability measure into a user friendly format
     * @param stdClass an object with the following properties: measure, highestlevel, lowestlevel and stderror.  The values must
     *      come from the activty instance and the user's
     * attempt record
     * @return string a user friendly format of the ability measure.  Ability measure is rounded to the nearest decimal.
     */
    public function format_measure($record) {
        if (is_null($record->measure)) {
            return 'n/a';
        }
		return round($record->measure, 4);
        // return round(catalgo::map_logit_to_scale($record->measure, $record->highestlevel, $record->lowestlevel), 1);
    }

    /**
     * This function formats the standard error into a user friendly format
     * @param stdClass an object with the following properties: measure, highestlevel, lowestlevel and stderror.  The values must
     *      come from the activty instance and the user's
     * attempt record
     * @return string a user friendly format of the standard error. Standard error is
     * rounded to the nearest one hundredth then multiplied by 100
     */
    public function format_standard_error($record) {
        if (is_null($record->stderror) || $record->stderror == 0.0) {
            return 'n/a';
        }
		return round($record->stderror,3);
        // $percent = round(catalgo::convert_logit_to_percent($record->stderror), 2) * 100;
        // return '&plusmn; '.$percent.'%';
    }

    /**
     * This function formats the standard error and ability measure into a user friendly format
     * @param stdClass an object with the following properties: measure, highestlevel, lowestlevel and stderror.  The values must
     *      come from the activty instance and the user's
     * attempt record
     * @return string a user friendly format of the ability measure and standard error.  Ability measure is rounded to the nearest
     *      decimal.  Standard error is rounded to the
     * nearest one hundredth then multiplied by 100
     */
    protected function format_measure_and_standard_error($record) {
        if (is_null($record->measure) || is_null($record->stderror) || $record->stderror == 0.0) {
            return 'n/a';
        }
        $measure = round(catalgo::map_logit_to_scale($record->measure, $record->highestlevel, $record->lowestlevel), 1);
        $percent = round(catalgo::convert_logit_to_percent($record->stderror), 2) * 100;
        $format = 'Theta:'.round($record->measure,4).' || SE: '.$record->stderror;
        return $format;
    }

    /**
     * Answer the summery information about an attempt
     *
     * @param stdClass $adaptivequiz See {@link mod_catadaptivequiz_renderer::attempt_report_page_by_tab()}.
     * @param stdClass $attempt See {@link mod_catadaptivequiz_renderer::attempt_report_page_by_tab()}.
     * @param stdClass $user The user who took the quiz that created the attempt.
     * @return string
     * @throws coding_exception
     */
    public function attempt_summary_listing(stdClass $adaptivequiz, stdClass $attempt, stdClass $user): string {
        $table = new html_table();
        $table->attributes['class'] = 'generaltable attemptsummarytable';

        $row = new html_table_row();

        $headercell = new html_table_cell(get_string('attempt_user', 'catadaptivequiz'));
        $headercell->header = true;

        $datacell = new html_table_cell(fullname($user) . ' (' . $user->email . ')');

        $row->cells = [$headercell, $datacell];
        $table->data[] = $row;

        $row = new html_table_row();

        $headercell = new html_table_cell(get_string('attempt_state', 'catadaptivequiz'));
        $headercell->header = true;

        $datacell = new html_table_cell($attempt->attemptstate);

        $row->cells = [$headercell, $datacell];
        $table->data[] = $row;

        $row = new html_table_row();

        $headercell = new html_table_cell(get_string('score', 'catadaptivequiz'));
        $headercell->header = true;

        $abilityfraction = 1 / ( 1 + exp( (-1 * $attempt->measure) ) );
        $ability = (($adaptivequiz->highestlevel - $adaptivequiz->lowestlevel) * $abilityfraction) + $adaptivequiz->lowestlevel;
		$ability = round($attempt->measure, 4);
        $stderror = $attempt->standarderror;//catalgo::convert_logit_to_percent($attempt->standarderror);
        $score = ($stderror > 0)
            ? "Theta: " . $ability . " &nbsp; | SE: ".round($stderror, 3)
            : 'n/a';
        $datacell = new html_table_cell($score);

        $row->cells = [$headercell, $datacell];
        $table->data[] = $row;

        $row = new html_table_row();

        $headercell = new html_table_cell(get_string('attemptstarttime', 'catadaptivequiz'));
        $headercell->header = true;

        $datacell = new html_table_cell(userdate($attempt->timecreated));

        $row->cells = [$headercell, $datacell];
        $table->data[] = $row;

        $row = new html_table_row();

        $headercell = new html_table_cell(get_string('attemptfinishedtimestamp', 'catadaptivequiz'));
        $headercell->header = true;

        $datacell = new html_table_cell(userdate($attempt->timemodified));

        $row->cells = [$headercell, $datacell];
        $table->data[] = $row;

        $row = new html_table_row();

        $headercell = new html_table_cell(get_string('attempttotaltime', 'catadaptivequiz'));
        $headercell->header = true;

        $totaltime = $attempt->timemodified - $attempt->timecreated;
        $hours = floor($totaltime / 3600);
        $remainder = $totaltime - ($hours * 3600);
        $minutes = floor($remainder / 60);
        $seconds = $remainder - ($minutes * 60);
        $cellcontent = sprintf('%02d', $hours).":".sprintf('%02d', $minutes).":".sprintf('%02d', $seconds);
        $datacell = new html_table_cell($cellcontent);

        $row->cells = [$headercell, $datacell];
        $table->data[] = $row;

        $row = new html_table_row();

        $headercell = new html_table_cell(get_string('attemptstopcriteria', 'catadaptivequiz'));
        $headercell->header = true;

        $datacell = new html_table_cell($attempt->attemptstopcriteria);

        $row->cells = [$headercell, $datacell];
        $table->data[] = $row;

        return html_writer::table($table);
    }

    public function attempt_review_tabs(moodle_url $pageurl, string $selected): string {
        $tabs = [];

        $attemptsummarytaburl = clone($pageurl);
        $attemptsummarytaburl->param('tab', 'attemptsummary');
        $tabs[] = new tabobject('attemptsummary', $attemptsummarytaburl,
            get_string('reportattemptsummarytab', 'catadaptivequiz'));

        $attemptgraphtaburl = clone($pageurl);
        $attemptgraphtaburl->param('tab', 'attemptgraph');
        $tabs[] = new tabobject('attemptgraph', $attemptgraphtaburl,
            get_string('reportattemptgraphtab', 'catadaptivequiz'));

        // $answerdistributiontaburl = clone($pageurl);
        // $answerdistributiontaburl->param('tab', 'answerdistribution');
        // $tabs[] = new tabobject('answerdistribution', $answerdistributiontaburl,
        //     get_string('reportattemptanswerdistributiontab', 'catadaptivequiz'));

        $questionsdetailstaburl = clone($pageurl);
        $questionsdetailstaburl->param('tab', 'questionsdetails');
        $tabs[] = new tabobject('questionsdetails', $questionsdetailstaburl,
            get_string('reportattemptquestionsdetailstab', 'catadaptivequiz'));

        return $this->tabtree($tabs, $selected);
    }

    /**
     * @param string $tabid
     * @param stdClass $adaptivequiz A record from {catadaptivequiz}. The expected fields are 'lowestlevel' and 'highestlevel'.
     * @param stdClass $attempt A record from {catadaptivequiz_attempt}. The expected fields are: 'attemptstate', 'measure',
     *        'highestlevel', 'lowestlevel', 'standarderror', 'timecreated', 'timemodified', 'attemptstopcriteria' and 'uniqueid'.
     * @param stdClass $user A record from {user}. The expected fields are: 'id', 'email' and those required for {@link fullname()}
     *        call.
     * @param question_usage_by_activity $quba
     * @param int $cmid
     * @param moodle_url $pageurl
     * @param int $page
     * @return string
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function attempt_report_page_by_tab(
        string $tabid,
        stdClass $adaptivequiz,
        stdClass $attempt,
        stdClass $user,
        question_usage_by_activity $quba,
        int $cmid,
        moodle_url $pageurl,
        int $page
    ): string {
        if ($tabid == 'attemptgraph') {
            // $return = $this->attempt_graph($attempt->uniqueid, $cmid, $user->id);
            // $return .= html_writer::empty_tag('br');
            $return = $this->attempt_scoring_table($adaptivequiz, $quba, $attempt);

            return $return;
        }
        if ($tabid == 'answerdistribution') {
            $return = $this->attempt_answer_distribution_graph($attempt->uniqueid, $cmid, $user->id);
            $return .= html_writer::empty_tag('br');
            $return .= $this->attempt_answer_distribution_table($adaptivequiz, $quba);

            return $return;
        }
        if ($tabid == 'questionsdetails') {
            return $this->attempt_questions_review($quba, $pageurl, $page);
        }

        return $this->attempt_summary_listing($adaptivequiz, $attempt, $user);
    }

    public function reset_users_attempts_filter_action(moodle_url $url): string {
        return html_writer::link($url, get_string('reportattemptsresetfilter', 'catadaptivequiz'));
    }

    /**
     * A wrapper method to call rendering of attempt progress, accepts minimum parameters to base rendering on.
     */
    public function attempt_progress(string $questionsanswered, string $maximumquestions): string {
        return $this->render_attempt_progress(attempt_progress::with_defaults($questionsanswered, $maximumquestions));
    }

    /**
     * Renders an attempt progress object, to be overridden by a theme if required.
     */
    protected function render_attempt_progress(attempt_progress $progress): string {
        $progress = $progress->with_help_icon_content($this->help_icon('attemptquestionsprogress', 'catadaptivequiz'));

        return $this->render_from_template('mod_catadaptivequiz/attempt_progress', $progress->export_for_template($this));
    }

    /**
     * Outputs available action links for an attempt in the user's attempts report.
     *
     * @param stdClass $attempt A record from {catadaptivequiz_attempt}.
     */
    public function individual_user_attempt_actions(stdClass $attempt): string {
        $actions = new individual_user_attempt_actions();

        $actions->add(
            new individual_user_attempt_action(
                new moodle_url('/mod/catadaptivequiz/reviewattempt.php', ['attempt' => $attempt->id]),
                new pix_icon('i/search', ''),
                get_string('reviewattempt', 'catadaptivequiz')
            )
        );

        if ($attempt->attemptstate !== attempt_state::COMPLETED) {
            $actions->add(
                new individual_user_attempt_action(
                    new moodle_url('/mod/catadaptivequiz/closeattempt.php', ['attempt' => $attempt->id]),
                    new pix_icon('t/stop', ''),
                    get_string('closeattempt', 'catadaptivequiz')
                )
            );
        }

        $actions->add(
            new individual_user_attempt_action(
                new moodle_url('/mod/catadaptivequiz/delattempt.php', ['attempt' => $attempt->id]),
                new pix_icon('t/delete', ''),
                get_string('deleteattemp', 'catadaptivequiz')
            )
        );

        return $this->render($actions);
    }

    /**
     * Renders the renderable actions object, intended to be overridden by the theme if needed.
     *
     * @param individual_user_attempt_actions $actions
     */
    protected function render_individual_user_attempt_actions(individual_user_attempt_actions $actions): string {
        return $this->render_from_template(
            'mod_catadaptivequiz/report/individual_user_attempt_actions',
            $actions->export_for_template($this)
        );
    }

    /**
     *
     * @param int $uniqueid See {@link mod_catadaptivequiz_renderer::attempt_report_page_by_tab()}.
     * @param int $cmid
     * @param int $userid
     * @return string
     * @throws moodle_exception
     */
    protected function attempt_graph(int $uniqueid, int $cmid, int $userid): string {
        $graphurl = new moodle_url('/mod/catadaptivequiz/attemptgraph.php',
            ['uniqueid' => $uniqueid, 'cmid' => $cmid, 'userid' => $userid]);
        $params = ['src' => $graphurl, 'class' => 'catadaptivequiz-attemptgraph'];

        $output = html_writer::start_div('mdl-align');
        $output .= html_writer::empty_tag('img', $params);
        $output .= html_writer::end_div('mdl-align');

        return $output;
    }

    /**
     * Produces a table of the question difficulties and the intermediate scores throughout the attempt.
     *
     * @param stdClass $adaptivequiz See {@link mod_catadaptivequiz_renderer::attempt_report_page_by_tab()}.
     * @param question_usage_by_activity $quba The questions used in this attempt.
     * @return string
     * @throws coding_exception
     */
    protected function attempt_scoring_table(stdClass $adaptivequiz, question_usage_by_activity $quba, stdClass $attempt): string {
        $table = new html_table();

        $num = get_string('attemptquestion_num', 'catadaptivequiz');
        $level = get_string('attemptquestion_level', 'catadaptivequiz');
        $rightwrong = get_string('attemptquestion_rightwrong', 'catadaptivequiz');
        $ability = get_string('attemptquestion_ability', 'catadaptivequiz');
        $error = get_string('attemptquestion_error', 'catadaptivequiz');

        $table->head = [$num,/*$level,*/ $rightwrong, $ability, $error];
        $table->align = ['center', 'center', 'center', 'center', 'center'];
        $table->size = ['', '', '', '', '', ''];
        $table->data = [];

		// get values from $attempt->detaildtestresults object/associative array
		$answered_questions = [];
		if ($attempt->detaildtestresults) $answered_questions = array_values(json_decode($attempt->detaildtestresults, true))[0]; 
		// else debugging('no detaildtestresults : ' . json_encode($attempt));
        
		foreach ($quba->get_slots() as $slot) {
			$question = $quba->get_question($slot);
			$tags = core_tag_tag::get_item_tags_array('core_question', 'question', $question->id);
			// get element from $answered_questions array where object value questionId matches $question->id
			$theta = 0;
			$stderror = 1;
			// check if $answered_questions is not empty
			if (!empty($answered_questions)) 
			{
				$answer = array_filter($answered_questions, function ($obj) use ($question) {
						return $obj["questionId"] == $question->id;
					});

				// Since array_filter returns an array, get the first element if you expect only one match
				$answer = reset($answer); // This will get the first matching object, or false if none found
				// Now, $answer contains the object where questionId matches $question->id, or false if no match was found.
				if ($answer) {
					$theta = $answer["theta"];
					$stderror = $answer["standarderror"];
				}
			}
            $qdifficulty = adaptivequiz_get_difficulty_from_tags($tags);
          
            $table->data[] = [$question->name, /*$qdifficulty,*/ round($quba->get_question_mark($slot) ?? 0, 0), round($theta ?? 0, 4),
                round($stderror ?? 0 , 3)];
        }

        $return = $this->heading(get_string('reportattemptgraphtabletitle', 'catadaptivequiz'), '4', 'mdl-align');
        $return .= html_writer::table($table);

        return $return;
    }

    /**
     * @param int $uniqueid Attempt unique id.
     * @param int $cmid
     * @param int $userid
     * @return string
     * @throws moodle_exception
     */
    protected function attempt_answer_distribution_graph(int $uniqueid, int $cmid, int $userid): string {
        $graphurl = new moodle_url('/mod/catadaptivequiz/answerdistributiongraph.php',
            ['uniqueid' => $uniqueid, 'cmid' => $cmid, 'userid' => $userid]);
        $params = ['src' => $graphurl, 'class' => 'catadaptivequiz-answerdistributiongraph'];

        $output = html_writer::start_div('mdl-align');
        $output .= html_writer::empty_tag('img', $params);
        $output .= html_writer::end_div('mdl-align');

        return $output;
    }

    /**
     * Produces a table of the question difficulties and the number of questions answered right and wrong for each
     * difficulty.
     *
     * @param stdClass $adaptivequiz See {@link mod_catadaptivequiz_renderer::attempt_report_page_by_tab()}.
     * @param question_usage_by_activity $quba The questions used in this attempt.
     * @return string
     * @throws coding_exception
     */
    protected function attempt_answer_distribution_table(
        stdClass $adaptivequiz,
        question_usage_by_activity $quba
    ): string {
        $table = new html_table();

        $level = get_string('attemptquestion_level', 'catadaptivequiz');
        $numright = get_string('numright', 'catadaptivequiz');
        $numwrong = get_string('numwrong', 'catadaptivequiz');

        $table->head = [$level, $numright, $numwrong];
        $table->align = ['center', 'center', 'center'];
        $table->size = ['', '', '' ];
        $table->data = [];

        // Set up our data arrays.
        $qdifficulties = [];
        $rightanswers = [];
        $wronganswers = [];

        for ($i = $adaptivequiz->lowestlevel; $i <= $adaptivequiz->highestlevel; $i++) {
            $qdifficulties[] = intval($i);
            $rightanswers[] = 0;
            $wronganswers[] = 0;
        }

        foreach ($quba->get_slots() as $i => $slot) {
            $question = $quba->get_question($slot);
            $tags = core_tag_tag::get_item_tags_array('core_question', 'question', $question->id);
            $qdifficulty = adaptivequiz_get_difficulty_from_tags($tags);
            $correct = ($quba->get_question_mark($slot) > 0);

            $position = array_search($qdifficulty, $qdifficulties);
            if ($correct) {
                $rightanswers[$position]++;
            } else {
                $wronganswers[$position]++;
            }
        }

        foreach ($qdifficulties as $key => $val) {
            $table->data[] = [
                $val,
                $rightanswers[$key],
                $wronganswers[$key],
            ];
        }

        $return = $this->heading(get_string('reportattemptanswerdistributiontabletitle', 'catadaptivequiz'), '4',
            'mdl-align');
        $return .= html_writer::table($table);

        return $return;
    }

    /**
     * This function returns HTML markup of questions and student's responses.
     * See {@link mod_catadaptivequiz_renderer::attempt_report_page_by_tab} for partial parameters description.
     *
     * @param moodle_url $pageurl
     * @param question_usage_by_activity $quba
     * @param int $offset An offset used to determine which question to start processing from.
     * @return string
     * @throws coding_exception
     * @throws moodle_exception
     */
    protected function attempt_questions_review(
        question_usage_by_activity $quba,
        moodle_url $pageurl,
        int $offset
    ): string {
        $pager = $this->attempt_questions_review_pager($quba, $pageurl, $offset);

        $questslots = $quba->get_slots();
        $attr = ['class' => 'questiontags'];
        $offset *= ADAPTIVEQUIZ_REV_QUEST_PER_PAGE;

        $output = $pager;

        // Take a portion of the array of question slots for display.
        $pageqslots = array_slice($questslots, $offset, ADAPTIVEQUIZ_REV_QUEST_PER_PAGE);

        // Setup display options.
        $options = new question_display_options();
        $options->readonly = true;
        $options->flags = question_display_options::HIDDEN;
        $options->marks = question_display_options::MAX_ONLY;
        $options->rightanswer = question_display_options::VISIBLE;
        $options->correctness = question_display_options::VISIBLE;
        $options->numpartscorrect = question_display_options::VISIBLE;

        // Setup quesiton header metadata.
        $output .= $this->init_metadata($quba, $pageqslots);

        foreach ($pageqslots as $slot) {
			// Retrieve question attempt object.
            $questattempt = $quba->get_question_attempt($slot);
            // Get question definition object.
            $questdef = $questattempt->get_question();

            $label = html_writer::tag('label', get_string('questionnumber', 'catadaptivequiz'));
            $output .= html_writer::tag('div', $label.': '.format_string($questdef->name));

            
            // Retrieve the tags associated with this question.
            $qtags = core_tag_tag::get_item_tags_array('core_question', 'question', $questdef->id);

            $label = html_writer::tag('label', get_string('attemptquestion_level', 'catadaptivequiz'));
            $output .= html_writer::tag('div', $label.': '.format_string(adaptivequiz_get_difficulty_from_tags($qtags)));

            $label = html_writer::tag('label', get_string('tags'));
            $output .= html_writer::tag('div', $label.': '.format_string(implode(' ', $qtags)), $attr);

            $output .= $quba->render_question($slot, $options);
            $output .= html_writer::empty_tag('hr');
        }

        $output .= html_writer::empty_tag('br');
        $output .= $pager;

        return $output;
    }

    /**
     * This function prints a paging link for the attempt review page.
     * See {@link mod_catadaptivequiz_renderer::attempt_questions_review()} for parameters description.
     *
     * @throws moodle_exception
     */
    protected function attempt_questions_review_pager(
        question_usage_by_activity $quba,
        moodle_url $pageurl,
        int $page
    ): string {
        $questslots = $quba->get_slots();
        $output = '';
        $attr = ['class' => 'viewattemptreportpages'];
        $pages = ceil(count($questslots) / ADAPTIVEQUIZ_REV_QUEST_PER_PAGE);

        // Don't print anything if there is only one page.
        if (1 == $pages) {
            return '';
        }

        // Print all of the page links.
        $output .= html_writer::start_tag('center');
        for ($i = 0; $i < $pages; $i++) {
            // If we are currently on this page, then don't make it an anchor tag.
            if ($i == $page) {
                $output .= '&nbsp'.html_writer::tag('span', $i + 1, $attr).'&nbsp';
                continue;
            }

            $pageurl->params(['page' => $i]);
            $output .= '&nbsp'.html_writer::link($pageurl, $i + 1, $attr).'&nbsp';
        }
        $output .= html_writer::end_tag('center');

        return $output;
    }

    protected function render_ability_measure(ability_measure $measure): string {
        $output = html_writer::start_div('box py-3');
		$se = round($measure->standarderror, 3);
        $abilitymeasurecontents = get_string('abilityestimated', 'catadaptivequiz') . ': ' .
            html_writer::tag('strong', $this->format_measure($measure->as_object_to_format())) /*. ' / ' .
            $measure->lowestquestiondifficulty . ' - ' . $measure->highestquestiondifficulty .
            $this->help_icon('abilityestimated', 'catadaptivequiz')*/
			. '('. $se .')';
			;
        $output .= $this->heading($abilitymeasurecontents, 3);

        $output .= html_writer::end_div();

        return $output;
    }

    protected function render_user_attempt_summary(user_attempt_summary $summary): string {
        $table = new html_table();
        $table->attributes['class'] = 'generaltable attemptsummarytable';

        $row = new html_table_row();

        $headercell = new html_table_cell(get_string('attempt_state', 'catadaptivequiz'));
        $headercell->header = true;

        $datacell = new html_table_cell(get_string('recent' . $summary->attemptstate, 'catadaptivequiz'));
        $datacell->id = 'attemptstatecell';

        $row->cells = [$headercell, $datacell];
        $table->data[] = $row;

        $row = new html_table_row();

        $headercell = new html_table_cell(get_string('attemptfinishedtimestamp', 'catadaptivequiz'));
        $headercell->header = true;

        $datacell = ($summary->attemptstate == attempt_state::COMPLETED)
            ? userdate($summary->timefinished)
            : '-';

        $row->cells = [$headercell, $datacell];
        $table->data[] = $row;

        if (!empty($summary->abilitymeasure)) {
            $row = new html_table_row();

            $headercell = new html_table_cell(get_string('attemptquestion_ability', 'catadaptivequiz') .
                $this->help_icon('abilityestimated', 'catadaptivequiz'));
            $headercell->header = true;

            $formatmeasure = new stdClass();
            $formatmeasure->measure = $summary->abilitymeasure;
            $formatmeasure->lowestlevel = $summary->lowestquestiondifficulty;
            $formatmeasure->highestlevel = $summary->highestquestiondifficulty;

            $datacell = new html_table_cell(html_writer::tag('strong', $this->format_measure($formatmeasure)));
            $datacell->id = 'abilitymeasurecell';

            $row->cells = [$headercell, $datacell];
            $table->data[] = $row;
        }

        return html_writer::table($table);
    }

    /**
     * This functions prints the start attempt button to start a secured browser attempt.
     */
    private function display_start_attempt_form_secured(int $cmid): string {
        $url = new moodle_url('/mod/catadaptivequiz/attempt.php', ['cmid' => $cmid]);

        $button = new single_button($url, get_string('startattemptbtn', 'catadaptivequiz'), 'post', single_button::BUTTON_PRIMARY);

        $this->page->requires->js_module($this->adaptivequiz_get_js_module());
        $this->page->requires->js('/mod/catadaptivequiz/module.js');

        $popupaction = new popup_action('click', $url, 'catadaptivequizpopup', self::$popupoptions);
        $button->add_action(new component_action('click',
            'M.mod_catadaptivequiz.secure_window.start_attempt_action', [
                'url' => $url->out(false),
                'windowname' => 'catadaptivequizpopup',
                'options' => $popupaction->get_js_options(),
                'fullscreen' => true,
                'startattemptwarning' => '',
            ]));

        $warning = html_writer::tag('noscript', $this->heading(get_string('noscript', 'quiz')));

        return $this->render($button).$warning;
    }
}

/**
 * A substitute renderer class that outputs CSV results instead of HTML.
 */
class mod_catadaptivequiz_csv_renderer extends mod_catadaptivequiz_renderer {
    /**
     * This function returns page header information to be printed to the page
     * @return string HTML markup for header inforation
     */
    public function print_header() {
        header('Content-type: text/csv');
        $filename = $this->page->title;
        $filename = preg_replace('/[^a-z0-9_-]/i', '_', $filename);
        $filename = preg_replace('/_{2,}/', '_', $filename);
        $filename = $filename.'.csv';
        header("Content-Disposition: attachment; filename=$filename");
    }

    /**
     * This function returns page footer information to be printed to the page
     * @return string HTML markup for footer inforation
     */
    public function print_footer() {
        // Do nothing.
    }

    /**
     * This function prints paging information
     * @param int $totalrecords the total number of records returned
     * @param int $page the current page the user is on
     * @param int $perpage the number of records displayed on one page
     * @return string HTML markup
     */
    public function print_paging_bar($totalrecords, $page, $perpage) {
        // Do nothing.
    }

    /**
     * This function returns the HTML markup to display a table of the attempts taken at the activity
     * @param stdClass $records attempt records from adaptivequiz_attempt table
     * @param stdClass $cm course module object set to the instance of the activity
     * @param string $sort the column the the table is to be sorted by
     * @param string $sortdir the direction of the sort
     * @return string HTML markup
     */
    public function print_report_table($records, $cm, $sort, $sortdir) {
        ob_start();
        $output = fopen('php://output', 'w');

        $headers = array(
            get_string('firstname'),
            get_string('lastname'),
            get_string('email'),
            get_string('numofattemptshdr', 'catadaptivequiz'),
            get_string('theta', 'catadaptivequiz'),
            get_string('bestscorestderror', 'catadaptivequiz'),
            get_string('attemptfinishedtimestamp', 'catadaptivequiz'),
        );
        fputcsv($output, $headers);

        foreach ($records as $record) {
            if (intval($record->timemodified)) {
                $timemodified = date('c', intval($record->timemodified));
            } else {
                $timemodified = get_string('na', 'catadaptivequiz');
            }

            $row = array(
                $record->firstname,
                $record->lastname,
                $record->email,
                $record->attempts,
                $this->format_measure($record),
                $this->format_standard_error($record),
                $timemodified,
            );

            fputcsv($output, $row);
        }

        return ob_get_clean();
    }

    /**
     * This function formats the ability measure into a user friendly format
     * @param stdClass an object with the following properties: measure, highestlevel, lowestlevel and stderror.  The values must
     *      come from the activty instance and the user's
     * attempt record
     * @return string a user friendly format of the ability measure.  Ability measure is rounded to the nearest decimal.
     */
    public function format_measure($record) {
        if (is_null($record->measure)) {
            return 'n/a';
        }
		return round($record->measure, 4);
        // return round(catalgo::map_logit_to_scale($record->measure, $record->highestlevel, $record->lowestlevel), 2);
    }

    /**
     * This function formats the standard error into a user friendly format
     * @param stdClass an object with the following properties: measure, highestlevel, lowestlevel and stderror.  The values must
     *      come from the activty instance and the user's
     * attempt record
     * @return string a user friendly format of the standard error. Standard error is
     * rounded to the nearest one hundredth then multiplied by 100
     */
    public function format_standard_error($record) {
        if (is_null($record->stderror) || $record->stderror == 0.0) {
            return 'n/a';
        }
		return round($record->stderror,3);
        // $percent = round(catalgo::convert_logit_to_percent($record->stderror), 2) * 100;
        // return $percent.'%';
    }
}
