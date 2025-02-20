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

namespace mod_catadaptivequiz\local\attempt;

use coding_exception;
use context_module;
use mod_catadaptivequiz\event\attempt_completed;
use question_usage_by_activity;
use stdClass;

/**
 * This class contains information about the attempt parameters
 *
 * @package    mod_catadaptivequiz
 * @copyright  2013 onwards Remote-Learner {@link http://www.remote-learner.ca/}
 * @copyright  2022 onwards Vitaly Potenko <potenkov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class attempt
{

	/**
	 * Database table to store attempt state.
	 */
	private const TABLE = 'catadaptivequiz_attempt';

	/**
	 * The behaviour to use by default.
	 */
	const ATTEMPTBEHAVIOUR = 'deferredfeedback';

	/**
	 * Flag to denote developer debugging is enabled and this class should write message to the debug
	 * wrap on multiple lines
	 * @var bool
	 */
	protected $debugenabled = false;

	/** @var array $debug debugging array of messages */
	protected $debug = array();

	/** @var stdClass $adpqattempt object, properties come from the adaptivequiz_attempt table */
	protected $adpqattempt;

	/** @var int $userid user id */
	protected $userid;

	/** @var array $tags an array of tags that used to identify eligible questions for the attempt */
	protected $tags = array();

	/**
	 * @var stdClass $adaptivequiz Record from the {catadaptivequiz} table.
	 */
	private $adaptivequiz;

	// public static $start_time;
	// public static $end_time;

	/**
	 * The constructor.
	 *
	 * @param stdClass $adaptivequiz A record from the {catadaptivequiz} table.
	 * @param int $userid
	 * @param string[] $tags An array of acceptable tags.
	 */
	private function __construct(stdClass $adaptivequiz, int $userid, array $tags = array())
	{
		$this->adaptivequiz = $adaptivequiz;
		$this->userid = $userid;
		$this->tags = $tags;
		$this->tags[] = ADAPTIVEQUIZ_QUESTION_TAG;

		if (debugging('', DEBUG_DEVELOPER)) {
			$this->debugenabled = true;
		}
	}

	/**
	 * This function returns the debug array
	 * @return array array of debugging messages
	 */
	public function get_debug()
	{
		return $this->debug;
	}

	/**
	 * This function determins whether the user answered the question correctly or incorrectly.
	 * If the answer is partially correct it is seen as correct.
	 * @param question_usage_by_activity $quba an object loaded with the unique id of the attempt
	 * @param int $slotid the slot id of the question
	 * @return float a float representing the user's mark.  Or null if there was no mark
	 */
	public function get_question_mark($quba, $slotid)
	{
		$mark = $quba->get_question_mark($slotid);

		if (is_float($mark)) {
			return $mark;
		}

		$this->print_debug('get_question_mark() - Question mark was not a float slot id: ' . $slotid . '.  Returning zero');

		return 0;
	}

	/**
	 * Sets the appropriate state for the attempt when a question has been answered.
	 *
	 * @param cat_calculation_steps_result $calcstepsresult
	 * @param int $time Current timestamp.
	 * @throws coding_exception
	 */
	public function update_after_question_answered(cat_calculation_steps_result $calcstepsresult, int $time, string $detaildtestresults): void
	{
		if ($this->adpqattempt === null) {
			throw new coding_exception('attempt record must be set already when updating an attempt with any data');
		}

		$record = $this->adpqattempt;

		$record->difficultysum = (float) $record->difficultysum + $calcstepsresult->logit()->as_float();
		$record->questionsattempted = (int) $record->questionsattempted + 1;
		$record->standarderror = $calcstepsresult->standard_error();
		$record->measure = $calcstepsresult->measure();
		$record->detaildtestresults = $detaildtestresults;

		$this->adpqattempt = $record;

		$this->save($time);
	}
	/**
	 * Sets the appropriate state with the data of the R-Server for the attempt when a question has been answered.
	 *
	 * @param cat_calculation_steps_result $calcstepsresult
	 * @param int $time Current timestamp.
	 * @throws coding_exception
	 */
	public function update_after_question_answered_with_r_response(float $difficultysum, float $standarderror, float $measure, int $time, string $detaildtestresults): void
	{
		if ($this->adpqattempt === null) {
			throw new coding_exception('attempt record must be set already when updating an attempt with any data');
		}

		$record = $this->adpqattempt;

		$record->difficultysum = (float) $record->difficultysum + (float) $difficultysum;
		$record->questionsattempted = (int) $record->questionsattempted + 1;
		$record->standarderror = $standarderror;
		$record->measure = $measure;
		$record->detaildtestresults = $detaildtestresults;

		$this->adpqattempt = $record;

		$this->save($time);
	}


	/**
	 * Sets the attempt as complete.
	 *
	 * @param context_module $context
	 * @param float $standarderror
	 * @param string $statusmessage
	 * @param int $time Current timestamp.
	 * @return void
	 */
	public function complete(context_module $context, float $standarderror, string $statusmessage, int $time): void
	{
		// Need to keep the record as it is before triggering the event below.
		$attemptrecordsnapshot = clone $this->adpqattempt;

		$this->adpqattempt->attemptstate = attempt_state::COMPLETED;
		$this->adpqattempt->attemptstopcriteria = $statusmessage;
		$this->adpqattempt->standarderror = $standarderror;

		$this->save($time);

		adaptivequiz_update_grades($this->adaptivequiz, $this->userid);

		$event = attempt_completed::create([
			'objectid' => $this->adpqattempt->id,
			'context' => $context,
			'userid' => $this->adpqattempt->userid
		]);
		$event->add_record_snapshot('catadaptivequiz_attempt', $attemptrecordsnapshot);
		$event->add_record_snapshot('catadaptivequiz', $this->adaptivequiz);
		$event->trigger();
	}

	/**
	 * Sets quba id for the attempt.
	 *
	 * @param int $id
	 */
	public function set_quba_id(int $id): void
	{
		$this->adpqattempt->uniqueid = $id;

		$this->save(time());
	}

	/**
	 * Returns the attempt record from {catadaptivequiz_attempt}.
	 *
	 * @return stdClass {@see self::$adpqattempt}.
	 */
	public function read_attempt_data(): stdClass
	{
		return $this->adpqattempt;
	}

	/**
	 * Checks whether the user has a completed attempt for the specified adaptive quiz instance.
	 *
	 * @param int $adaptivequizid
	 * @param int $userid
	 * @return bool
	 */
	public static function user_has_completed_on_quiz(int $adaptivequizid, int $userid): bool
	{
		global $DB;

		return $DB->record_exists(
			self::TABLE,
			['userid' => $userid, 'instance' => $adaptivequizid, 'attemptstate' => attempt_state::COMPLETED]
		);
	}

	/**
	 * Returns an in-progress attempt for the uer, returns null when no such attempt was found.
	 *
	 * @param stdClass $adaptivequiz
	 * @param int $userid
	 * @return self|null
	 */
	public static function find_in_progress_for_user(stdClass $adaptivequiz, int $userid): ?self
	{
		global $DB;

		$record = $DB->get_record(
			'catadaptivequiz_attempt',
			['instance' => $adaptivequiz->id, 'userid' => $userid, 'attemptstate' => attempt_state::IN_PROGRESS]
		);
		if (!$record) {
			return null;
		}

		$attempt = new self($adaptivequiz, $userid);
		$attempt->adpqattempt = $record;

		return $attempt;
	}


	/**
	 * Created an instance of attempt, saves it in the database and returns as the result.
	 *
	 * @param stdClass $adaptivequiz A record from {catadaptivequiz}.
	 * @param int $userid
	 * @return self
	 */
	public static function create(stdClass $adaptivequiz, int $userid): self
	{
		global $DB;

		$time = time();

		$record = new stdClass();
		$record->instance = $adaptivequiz->id;
		$record->userid = $userid;
		$record->uniqueid = 0;
		$record->attemptstate = attempt_state::IN_PROGRESS;
		$record->attemptstopcriteria = '';
		$record->questionsattempted = 0;
		$record->difficultysum = 0;
		$record->standarderror = 999;
		$record->measure = 0;
		$record->timecreated = $time;
		$record->timemodified = $time;

		$record->id = $DB->insert_record(self::TABLE, $record);

		$attempt = new self($adaptivequiz, $userid);
		$attempt->adpqattempt = $record;

		return $attempt;
	}

	public function call_r_server($data)
	{
		$url = 'http://ikea-m.uni-frankfurt.de/doCAT'; //'https://jsonplaceholder.typicode.com/posts'; //http://ikea-m.uni-frankfurt.de/doCAT
		$decoded_response = NULL;


		$data = array(
			'courseID' => $data->courseID,
			'testID' => $data->testID,
			'itempool' => $data->itempool,
			'settings' => $data->settings,
			'person' => $data->person,
			'test' => $data->test,
		);

		$options = array(
			'http' => array(
				'header' => "Content-type: application/json",
				'method' => 'POST',
				'content' => json_encode(array('data' => $data))
			),
		);

		$context = stream_context_create($options);

		// Führe den HTTP-Request aus
		$response = file_get_contents($url, false, $context);
		// write the $response to a local file with filename of the current timestamp
		$debuggingstuff = array(
			'url' => $url,
			'data' => $data,
			'response' => $response,
		);
		// debugging
		file_put_contents('doCat_response_' . time() . '.json', json_encode($debuggingstuff));
		// Überprüfe auf Fehler
		if ($response === FALSE) {
			echo 'Fehler beim Senden des POST-Requests.';
		} else {
			// debugging('this is r-server-response:' . $response);

			$decoded_response = json_decode($response);
			// if decoded_response is an array, use the first element
			if (is_array($decoded_response)) {
				$decoded_response = json_decode($decoded_response[0]);
				// debugging('this is decoded_responsee:' . json_encode($decoded_response));
			}
			// result:
			// {
			// 	"personID": [
			// 		"2"
			// 	],
			// 	"terminated": [
			// 		false
			// 	],
			// 	"theta": [
			// 		-0.2288
			// 	],
			// 	"SE": [
			// 		0.952
			// 	],
			// 	"nextItem": [
			// 		"sb2erz14"
			// 	]
			// }
			// convert decoded_response to object without the inner arrays
			$decoded_response = (object) array(
				'personID' => $decoded_response->personID[0],
				'terminated' => $decoded_response->terminated[0],
				'theta' => $decoded_response->theta[0],
				'SE' => $decoded_response->SE[0],
				'nextItem' => $decoded_response->nextItem[0],
			);
		}
		return $decoded_response;
	}


	/**
	 * This function adds a message to the debugging array
	 * @param string $message details of the debugging message
	 */
	protected function print_debug($message = '')
	{
		if ($this->debugenabled) {
			$this->debug[] = $message;
		}
	}

	/**
	 * Saves the attempt in its current state to the database.
	 *
	 * @param int $time Current timestamp.
	 * @return void
	 */
	private function save(int $time): void
	{
		global $DB;

		$this->adpqattempt->timemodified = $time;

		$DB->update_record(self::TABLE, $this->adpqattempt);
	}

	/**
	 * distrubutes the tags already used in that attempt
	 *
	 * @return stdClass {@see self::$adpqattempt}.
	 */
	public static function distribute_used_tags($tags, $itemsArray)
	{

		foreach ($tags as $tag => $value) {

			if (str_starts_with($value, "diff_")) {
				$temp1 = str_replace('diff_[', '', $value);
				$temp = str_replace(']', '', $temp1);
				if (strpos($temp, ';') !== false) {
					$numbers = explode(';', $temp);
					foreach ($numbers as $number) {
						array_push($itemsArray->diff, $number);
					}
				} else {
					$number = $temp;
					array_push($itemsArray->diff, $number);
				}
			}

			if (str_starts_with($value, "ca_")) {
				$temp1 = str_replace('ca_[', '', $value);
				$temp = str_replace(']', '', $temp1);
				$categories = explode(';', $temp);
				foreach ($categories as $cat) {
					array_push($itemsArray->content_area, $cat);
				}
			}
			if (str_starts_with($value, "enemy_")) {
				$temp1 = str_replace('enemy_[', '', $value);
				$temp = str_replace(']', '', $temp1);
				if (strpos($temp, ';') !== false) {
					$numbers = explode(';', $temp);
					foreach ($numbers as $number) {
						array_push($itemsArray->enemys, $number);
					}
				} else {
					$number = $temp;
					array_push($itemsArray->enemys, $number);
				}
			}
			if (str_starts_with($value, "disc_")) {
				$temp1 = str_replace('disc_[', '', $value);
				$temp = str_replace(']', '', $temp1);
				if (strpos($temp, ';') !== false) {
					$numbers = explode(';', $temp);
					foreach ($numbers as $number) {
						array_push($itemsArray->disc, $number);
					}
				} else {
					$number = $temp;
					array_push($itemsArray->disc, $number);
				}
			}
			// if (str_starts_with($value, "max_")) {
			// 	$temp1 = str_replace('max_', '', $value);
			// 	$number = $temp1;
			// 	$itemsArray->max = $number;
			// }
			// if (str_starts_with($value, "answer_")) {
			// 	$temp1 = str_replace('answer_[', '', $value);
			// 	$temp = str_replace(']', '', $temp1);
			// 	if (strpos($temp, ';') !== false) {
			// 		$numbers = explode(';', $temp);
			// 		foreach ($numbers as $number) {
			// 			array_push($itemsArray->answer, $number);
			// 		}
			// 	} else {
			// 		$number = $temp;
			// 		array_push($itemsArray->answer, $number);
			// 	}
			// }
			if (str_starts_with($value, "cluster_")) {
				$temp1 = str_replace('cluster_', '', $value);

				$number = $temp1;
				$itemsArray->cluster = $number;
			}
		}
		return $itemsArray;
	}
}
