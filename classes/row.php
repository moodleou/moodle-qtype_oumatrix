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

namespace qtype_oumatrix;

/**
 * Represents a row (answer) of a matrix question.
 *
 * @package   qtype_oumatrix
 * @copyright 2023 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class row {
    /** @var int Minimum number of rows. */
    const MIN_NUMBER_OF_ROWS = 2;

    /** @var int The id of the row */
    public $id;

    /** @var int The id of the question. */
    public $questionid;

    /** @var int The row number. */
    public $number;

    /** @var string The row name. */
    public $name;

    /** @var array The array of correct answers. */
    public $correctanswers;

    /** @var string The row specific feedback. */
    public $feedback;

    /** @var int The row feedback format. E.g: FORMAT_HTML, FORMAT_PLAIN */
    public $feedbackformat;

    /**
     * Construct the matrix object to be used by rows.
     *
     * @param int $id the row id
     * @param int $questionid the questionid
     * @param int $number the row number
     * @param string $name the row name
     * @param array $correctanswers the list of correct answers
     * @param string $feedback the row feedback
     * @param string $feedbackformat the row feedback format
     */
    public function __construct(int $id, int $questionid, int $number, string $name, array $correctanswers,
            string $feedback = '', string $feedbackformat = FORMAT_HTML) {
        $this->questionid = $questionid;
        $this->number = $number;
        $this->name = $name;
        $this->correctanswers = $correctanswers;
        $this->feedback = $feedback;
        $this->feedbackformat = $feedbackformat;
        $this->id = $id;
    }
}

