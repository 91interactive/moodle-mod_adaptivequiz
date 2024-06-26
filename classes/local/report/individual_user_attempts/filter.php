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
 * @copyright  2022 onwards Vitaly Potenko <potenkov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_catadaptivequiz\local\report\individual_user_attempts;

final class filter {

    /**
     * @var int $adaptivequizid
     */
    public $adaptivequizid;

    /**
     * @var int $userid
     */
    public $userid;

    public static function from_vars(int $adaptivequizid, int $userid): self {
        $return = new self();
        $return->adaptivequizid = $adaptivequizid;
        $return->userid = $userid;

        return $return;
    }
}
