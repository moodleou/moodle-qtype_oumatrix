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
 * Get information about a column (answer) in a given question.
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
class column extends oumatrix {
    public function __construct(object $question, int $numberofrows, int $numberofcolumns) {
        parent::__construct($question, $numberofrows, $numberofcolumns);
    }

    public function create_default_columns(int $questionid, int $numberofcolumns = 0) {
        global $DB;
        if ($numberofcolumns === 0) {
            $numberofcolumns = $this->numberofcolumns;
        }
        for ($c = 1; $c <= $numberofcolumns; $c++) {
            $column = new stdClass();
            $column->questionid = $questionid;
            $column->number = $c;
            $column->name = 'Answer' . $c;
            $column->id = $DB->insert_record('qtype_oumatrix_columns', $column);
            $this->column[] = $column;
        }
    }

    /**
     * Retunr an array of rows.
     *
     * @return array
     */
    public function get_columns(): ?array {
        global $DB;
        if ($columns = $DB->get_records('qtype_oumatrix_columns', ['questionid' => $this->questionid],'number ASC')) {
            return $columns;
        } else {
            return $this->create_default_columns($this->questionid, $this->numberofcolumns);
        }
        // TODO; throw an exception here.
        return null;
    }

    public function get_number_of_columns() {
        return $this->numberofcolumns;
    }
}
