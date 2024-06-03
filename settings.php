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
 * @package    mod_catadaptivequiz
 * @copyright  2023 Vitaly Potenko <potenkov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading(
        'catadaptivequizdefaultsettingsheading',
        get_string('settingsdefaultsettingsheading', 'catadaptivequiz'),
        get_string('settingsdefaultsettingsheadinginfo', 'catadaptivequiz')
    ));

    $settings->add(new admin_setting_configtext(
        'catadaptivequiz/startinglevel',
        get_string('startinglevel', 'catadaptivequiz'),
        get_string('startinglevel_help', 'catadaptivequiz'),
        1,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'catadaptivequiz/lowestlevel',
        get_string('lowestlevel', 'catadaptivequiz'),
        get_string('lowestlevel_help', 'catadaptivequiz'),
        1,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'catadaptivequiz/highestlevel',
        get_string('highestlevel', 'catadaptivequiz'),
        get_string('highestlevel_help', 'catadaptivequiz'),
        100,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'catadaptivequiz/minimumquestions',
        get_string('minimumquestions', 'catadaptivequiz'),
        get_string('minimumquestions_help', 'catadaptivequiz'),
        1,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'catadaptivequiz/maximumquestions',
        get_string('maximumquestions', 'catadaptivequiz'),
        get_string('maximumquestions_help', 'catadaptivequiz'),
        1000,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'catadaptivequiz/standarderror',
        get_string('standarderror', 'catadaptivequiz'),
        get_string('standarderror_help', 'catadaptivequiz'),
        5,
        PARAM_FLOAT
    ));
}
