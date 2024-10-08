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
 * A class to display a table with user's own attempts on the activity's view page.
 *
 * @copyright  2022 onwards Vitaly Potenko <potenkov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_catadaptivequiz\output;

use renderable;
use stdClass;

final class ability_measure implements renderable
{

	/**
	 * @var float $abilitymeasure
	 */
	public $measurevalue;
	/**
	 * @var float $standarderror
	 */
	public $standarderror;
	/**
	 * @var int $lowestquestiondifficulty
	 */
	public $lowestquestiondifficulty;
	/**
	 * @var int $highestquestiondifficulty
	 */
	public $highestquestiondifficulty;

	/**
	 * A convenience method to convert the object to what {@link mod_catadaptivequiz_renderer::format_measure()} expects
	 * to produce a formatted ability measure.
	 */
	public function as_object_to_format(): stdClass
	{
		$return = new stdClass();
		$return->measure = $this->measurevalue;
		$return->standarderror = $this->standarderror;
		$return->lowestlevel = $this->lowestquestiondifficulty;
		$return->highestlevel = $this->highestquestiondifficulty;

		return $return;
	}

	/**
	 * A named constructor to set up the object and increase code readability.
	 *
	 * @param stdClass $adaptivequiz A record from {catadaptivequiz}. lowestlevel and highestlevel are the expected fields.
	 * @param float $measurevalue
	 * @param float $standarderror
	 * @return self
	 */
	public static function of_attempt_on_adaptive_quiz(stdClass $adaptivequiz, float $measurevalue, float $standarderror): self
	{
		$return = new self();
		$return->lowestquestiondifficulty = !empty($adaptivequiz->lowestlevel) ? $adaptivequiz->lowestlevel : 0;
		$return->highestquestiondifficulty = !empty($adaptivequiz->highestlevel) ? $adaptivequiz->highestlevel : 0;
		$return->measurevalue = $measurevalue;
		$return->standarderror = $standarderror;

		return $return;
	}
}
