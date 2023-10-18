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
    const MIN_NUMBER_OF_ROWS = 2;

    public $id;

    /** @var int The id of the question. */
    public $questionid;

    /** @var int The row id. */
    /** @var int The row number. */
    public $number;

    /** @var string The row name. */
    public $name;

    /** @var array The string of json encoded correct answers. */
    public $correctanswers;

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
    public function __construct(int $id = 0, int $questionid = 0, int $number = 0, string $name = '', string $correctanswers = '',
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

