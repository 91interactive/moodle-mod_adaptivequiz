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
 * Contains function with the definition of upgrade steps for the plugin.
 *
 * @package   mod_catadaptivequiz
 * @copyright 2013 Remote-Learner {@link http://www.remote-learner.ca/}
 * @copyright 2022 onwards Vitaly Potenko <potenkov@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Defines upgrade steps for the plugin.
 *
 * @param mixed $oldversion
 */
function xmldb_catadaptivequiz_upgrade($oldversion)
{
	global $CFG, $DB;

	$dbman = $DB->get_manager();

	if ($oldversion < 2014020400) {
		// Define field grademethod.
		$table = new xmldb_table('catadaptivequiz');
		$field = new xmldb_field('grademethod', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, 1, 'startinglevel');

		// Conditionally add field grademethod.
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}

		// Quiz savepoint reached.
		upgrade_mod_savepoint(true, 2014020400, 'catadaptivequiz');
	}

	if ($oldversion < 2022012600) {
		$table = new xmldb_table('catadaptivequiz');
		$field = new xmldb_field(
			'showabilitymeasure',
			XMLDB_TYPE_INTEGER,
			'1',
			null,
			XMLDB_NOTNULL,
			false,
			'0',
			'attemptfeedbackformat'
		);

		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}

		upgrade_mod_savepoint(true, 2022012600, 'catadaptivequiz');
	}

	if ($oldversion < 2022092600) {
		$table = new xmldb_table('catadaptivequiz');
		$field = new xmldb_field('completionattemptcompleted', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, false, 0);

		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}

		upgrade_mod_savepoint(true, 2022092600, 'catadaptivequiz');
	}

	if ($oldversion < 2022110200) {
		$table = new xmldb_table('catadaptivequiz');
		$field = new xmldb_field(
			'showattemptprogress',
			XMLDB_TYPE_INTEGER,
			'1',
			null,
			XMLDB_NOTNULL,
			null,
			0,
			'showabilitymeasure'
		);

		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}

		upgrade_mod_savepoint(true, 2022110200, 'catadaptivequiz');
	}

	if ($oldversion < 2023021500) {
		$table = new xmldb_table('catadaptivequiz_attempt');
		$field = new xmldb_field(
			'detaildtestresults',
			XMLDB_TYPE_TEXT,
			'255',
			null,
			null,
			null,
			null,
			'questionsattempted'
		);

		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}

		upgrade_mod_savepoint(true, 2023021500, 'catadaptivequiz');
	}

	if ($oldversion < 2024062406) {
		// delete fields 
	}

	if ($oldversion < 2024071114) {
		// change length of detaildtestresults field in  catadaptivequiz_attempt table to max length
		$table = new xmldb_table('catadaptivequiz_attempt');
		// $field = new xmldb_field( 'detaildtestresults', XMLDB_TYPE_TEXT, 'max', null, null, null, null, 'questionsattempted');
		// Define the field with a new type but without length
		$field = new xmldb_field('detaildtestresults', XMLDB_TYPE_TEXT, "medium", null, null, null, null, 'questionsattempted');


		if ($dbman->field_exists($table, $field)) {
			$dbman->change_field_type($table, $field);
		}

		upgrade_mod_savepoint(true, 2024071114, 'catadaptivequiz');
	}
	if ($oldversion < 2024100133) {
		// change length of detaildtestresults field in  catadaptivequiz_attempt table to max length
		$table = new xmldb_table('catadaptivequiz_attempt');
		// $field = new xmldb_field( 'detaildtestresults', XMLDB_TYPE_TEXT, 'max', null, null, null, null, 'questionsattempted');
		// Define the field with a new type but without length
		$field = new xmldb_field('detaildtestresults', XMLDB_TYPE_TEXT, "long", null, null, null, null, 'questionsattempted');


		if ($dbman->field_exists($table, $field)) {
			$dbman->change_field_type($table, $field);
		}

		upgrade_mod_savepoint(true, 2024100133, 'catadaptivequiz');
	}
	
	if ($oldversion < 2024120905) {
		$table = new xmldb_table('catadaptivequiz');
		$field = new xmldb_field('testlength');

		if ($dbman->field_exists($table, $field)) {
			$dbman->drop_field($table, $field);
		}

		upgrade_mod_savepoint(true, 2024120905, 'catadaptivequiz');
	}

	return true;
}
