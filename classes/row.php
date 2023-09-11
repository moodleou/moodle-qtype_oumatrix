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
 * Get information about a row (answer) a given class.
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
class row {
    /** @var int The id of the question. */
    public $questionid;

    /** @var int The row id. */
    public $id;

    /** @var int The row number. */
    public $number;

    /** @var string The row name. */
    public $name;

    /** @var array The list of correct answers, A json-encoded list of correct answerids for a given row. */
    public $correctanswers = [];

    /** @var string The row specific feedback. */
    public $feedback;

    /** @var int The row feedback format. E.g: FORMAT_HTML, FORMAT_PLAIN */
    public $feedbackformat;

    /**
     * Construct the matrix object to be used by rows and colums objects.
     *
     * @param int $id
     * @param int $questionid
     * @param int $numberofrows
     * @param int $numberofcolumns
     */
    public function __construct(int $id = 0, int $questionid = 0, int $number = 0, string $name = '', array $correctanswers = [],
            string $feedback = '', int $feedbackformat = 0) {
        $this->questionid = $questionid;
        $this->number = $number;
        $this->name = $name;
        $this->correctanswers = $correctanswers;
        $this->feedback = $feedback;
        $this->feedbackformat = $feedbackformat;
        $this->id = $id;
    }
}

