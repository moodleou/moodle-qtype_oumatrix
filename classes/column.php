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
    public function __construct(int $id, int $questionid = 0, int $number = 0, string $name = '') {
        $this->questionid = $questionid;
        $this->number = $number;
        $this->name = $name;
        $this->id = $id;
    }

    public function populate(stdClass $column) {
        $this->column = $column;
        $this->id = $column->id;
        $this->questionid = $column->questionid;
        $this->number = $column->number;
        $this->name = $column->name;
    }

    /**
     * Return a column object
     *
     * @param int $id
     * @return stdClass
     * @throws \dml_exception
     */
    public function get_a_column_by_id(int $id): ?stdClass {
        global $DB;
        //if ($this->column->id === $id) {
        //    return $this->column;
        //}
        if ($column = $DB->get_record('qtype_oumatrix_columns', ['id' => $id])) {
            return $column;
        }
        return null;
    }

    /**
     * Create a column.
     *
     * @param int $questionid
     * @param int $number
     * @param string $name
     * @return int
     */
    public function create_a_column(int $questionid, int $number, string $name): int {
        global $DB;
        $id = $DB->insert_record('qtype_oumatrix_columns',
                ['questionid' => $questionid, 'number' => $number,'name' => $name ]);
        return $id;
    }

    /**
     * @return int
     */
    public function getQuestionid(): int {
        return $this->questionid;
    }

    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getNumber(): int {
        return $this->number;
    }

    /**
     * Return name
     *
     * @param int $id
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * Delete a column.
     *
     * @param int $id
     * @return void
     */
    public function delete_a_column(int $id) {
        global $DB;
        $DB->delete_records('qtype_oumatrix_columns', ['questionid' => $this->questionid, 'id' => $id]);
    }
}
