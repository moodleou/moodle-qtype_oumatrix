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
    private $question;

    /** @var int The row id. */
    private $id;

    /** @var int The row number. */
    private $number;

    /** @var string The row name. */
    private $name;

    /** @var array The list of correct answers A json-encoded list of correct answerids for a given row. */
    private $correctanswers = [];

    /** @var string The row specific feedback. */
    private $feedback;

    /** @var int The row feedback format. E.g: FORMAT_HTML, FORMAT_PLAIN */
    private $feedbackformat;

    /** @var stdClass the rows of the current question */
    private $row;

    /** @var int the number of rows (question options) used in the current question */
    private $numberofrows = 0;

    /**
     * Construct the matrix object to be used by rows and colums objects.
     *
     * @param object $question
     * @param int $numberofrows
     * @param int $numberofcolumns
     */
    public function __construct($question, int $number, string $name, array $correctanswers = [], string $feedback = '', int $feedbackformat = 1) {
        $this->questionid = $questionid;
        $this->number = $number;
        $this->name = $name;
        $this->correctanswers = $correctanswers;
        $this->feedback = $feedback;
        $this->feedbackformat = $feedbackformat;
    }

    public function get_correct_answers($rownumber) {
        return
    }
    public function create_default_row(int $questionid,  int $number = 1, string $name = 'row', string $feedback = '', int $feedbackformat = 2) {
        global $DB;
        $row = new stdClass();
        $row->questionid = $questionid;
        $row->number = $number;
        $row->name = $name;
        $row->feedback = $feedback;
        $row->feedbackformat = $feedbackformat;
        return $row;
    }

    public function create_row(int $questionid,  int $number = 1, string $name = 'row', string $feedback = '', int $feedbackformat = 2) {
        $row = new stdClass();
        $row->questionid = $questionid;
        $row->number = $number;
        $row->name = $name;
        $row->feedback = $feedbackformat;
        $row->feedbackformat = $feedbackformat;
        return $row;
    }

    /**
     * Create default rows.
     *
     * @param int $questionid
     * @param int $rowstart
     * @param int $numberofrows
     * @return void
     * @throws \dml_exception
     */
    public function create_default_rows(int $questionid, int $rowstart = 1, int $numberofrows = 0) {
        global $DB;
        if ($numberofrows === 0) {
            $numberofrows = $this->numberofrows;
        }
        for ($r = $rowstart; $r <= $numberofrows; $r++) {
            $row = $this->create_a_default_row($questionid, $r, 'r'.$r, );
            $row->id = $DB->insert_record('qtype_oumatrix_rows', $row);
            $this->rows[] = $row;
        }
    }

    /**
     * Retunr a row.
     *
     * @param int $rownumber
     * @return stdClass
     * @throws \dml_exception
     */
    public function get_a_row(int $rownumber): ?stdClass {
        global $DB;
        if ($row = $DB->get_record('qtype_oumatrix_rows', ['questionid' => $this->questionid, 'number' => $rownumber])) {
            return $row;
        }
        return null;
    }

    /**
     * Delete a row.
     *
     * @param int $rownumber
     * @return void
     * @throws \dml_exception
     */
    public function delete_a_row(int $rownumber) {
        global $DB;
        $DB->delete_records('qtype_oumatrix_rows', ['questionid' => $this->questionid, 'number' => $rownumber]);
    }

    /**
     * Return an array of rows.
     *
     * @param int $numberofrows
     * @return array|null
     * @throws \dml_exception
     */
    public function get_rows(int $numberofrows = 0): ?array {
        $rows = [];
        // If there is no changes in number of rows
        if ($this->numberofrows === $numberofrows) {
            for ($r = 1; $r <= $numberofrows; $r++) {
                $rows[] = $this->get_a_row($r);
            }
            return $rows;
        } else {
            return $this->create_default_rows($this->questionid, 1);
        }
    }

    /**
     * Retun number of row.
     *
     * @return int
     */
    public function get_number_of_rows() {
        return $this->numberofrows;
    }

}
