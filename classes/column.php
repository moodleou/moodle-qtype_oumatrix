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
 * Get information about a columns for the matrix.
 *
 * @package     qtype_oumatrix
 * @copyright   2023 The Open University
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qtype_oumatrix;
use stdClass;

/**
 * Helper used by the testcases in this file.
 *
 * @copyright  2008 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class column {
    const MIN_NUMBER_OF_COLUMNS = 2;

    /** @var int The id of the question. */
    public $questionid;

    /** @var int The column id. */
    public $id;

    /** @var int The column number. */
    public $number;

    /** @var string The column name. */
    public $name;

    /**
     * Construct the column object.
     *
     * @param int $id
     * @param int $questionid
     * @param int $number
     * @param string $name
     */
    public function __construct(int $questionid, int $number = 0, string $name = '', int $id = 0) {
        $this->questionid = $questionid;
        $this->number = $number;
        $this->name = $name;
        $this->id = $id;
    }
}
