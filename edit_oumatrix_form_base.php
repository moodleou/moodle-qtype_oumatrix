<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Defines the editing form for the OU matrix question type.
 *
 * @package     qtype_oumatrix
 * @copyright   2023 The Open University
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use \qtype_oumatrix\column;
use \qtype_oumatrix\row;

defined('MOODLE_INTERNAL') || die();


/**
 * Base clas oumatrix question type editing form.
 *
 * @copyright  2023 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class qtype_oumatrix_edit_form_base extends question_edit_form {

    /**
     * The default starting number of rows (answers).
     */
    protected const ROW_NUM_START = 4;

    /**
     * The number of rows (answers) that get added at a time.
     */
    protected const ROW_NUM_ADD = 2;

    /**
     * The default starting number of columns (answers).
     */
    protected const COL_NUM_START = 3;

    /**
     * The number of columns (answers) that get added at a time.
     */
    protected const COL_NUM_ADD = 2;

    /**
     * Add a set of form fields, obtained from get_per_column_fields.
     *
     * @param MoodleQuickForm $mform the form being built.
     * @param string $label the label to use for each column.
     * @param int $minoptions the minimum number of column blanks to display. Default COL_NUM_START.
     * @param int $addoptions the number of column blanks to add. Default COL_NUM_ADD.
     */
    protected function add_per_column_fields(MoodleQuickForm $mform, string $label,
            int $minoptions = self::COL_NUM_START, int $addoptions = self::COL_NUM_ADD) {
        $mform->addElement('header', 'columnshdr', get_string('columnshdr', 'qtype_oumatrix'));
        $mform->setExpanded('columnshdr', 1);
        $columns = [];
        $repeatedoptions = [];

        if (isset($this->question->columns)) {
            $repeatsatstart = count($this->question->columns);
        } else {
            $repeatsatstart = $minoptions;
        }

        $this->repeat_elements($this->get_per_column_fields($mform, $label, $repeatedoptions, $columns),
                $repeatsatstart, $repeatedoptions,
                'nocolumns', 'addcolumns', $addoptions,
                $this->get_more_blanks('columns'), true);
    }

    protected function get_per_column_fields($mform, $label, $repeatedoptions, $columns) {
        $repeated = [];
       // $repeated[] = $mform->createElement('editor', 'columnname', $label, ['rows' => 2], $this->editoroptions);
        $repeated[] = $mform->createElement('text', 'columnname', $label, ['size' => 40]);
        $repeatedoptions['column']['type'] = PARAM_RAW;
        $columns['name'] = 'columns';
        return $repeated;
    }

    /**
     * Add a set of form fields, obtained from get_per_row_fields.
     *
     * @param MoodleQuickForm $mform the form being built.
     * @param string $label the label to use for each row.
     * @param int $minoptions the minimum number of row blanks to display. Default COL_NUM_START.
     * @param int $addoptions the number of row blanks to add. Default COL_NUM_ADD.
     */
    protected function add_per_row_fields(MoodleQuickForm $mform, string $label,
            int $minoptions = self::ROW_NUM_START, int $addoptions = self::ROW_NUM_ADD) {
        $mform->addElement('header', 'rowshdr', get_string('rowshdr', 'qtype_oumatrix'));
        $mform->setExpanded('rowshdr', 1);
        $rows = [];
        $repeatedoptions = [];

        if (isset($this->question->rows)) {
            $repeatsatstart = count($this->question->rows);
        } else {
            $repeatsatstart = $minoptions;
        }

        $this->repeat_elements($this->get_per_row_fields($mform, $label, $repeatedoptions, $rows), $repeatsatstart, $repeatedoptions,
                'norows', 'addrows', $addoptions,
                $this->get_more_blanks('rows'), true);
    }

    /**
     *
     * @param $mform
     * @param $label
     * @param $repeatedoptions
     * @param $rows
     * @return array
     */
    protected function get_per_row_fields($mform, $label, $repeatedoptions, $rows) {
        $numberofcolumns = 5;
        $repeated = [];
        $rowoptions = [];
       // $rowoptions[] = $mform->createElement('editor', 'rowname', $label, ['rows' => 2], $this->editoroptions);
        $rowoptions[] = $mform->createElement('text', 'rowname', 'Name', ['size' => 40]);
        for ($i = 1; $i <= $numberofcolumns; $i++) {
            $anslabel = get_string('a', 'qtype_oumatrix', '{no}');
            $rowoptions[] = $mform->createElement('checkbox', "a$i", $anslabel);
        }

        $repeated[] = $mform->createElement('group', 'rowoptions',
                $label, $rowoptions, null, false);
        //$rowoptions[] = $mform->createElement('editor', 'feedback',
        //        get_string('feedback', 'question'), array('rows' => 2"a$i"), $this->editoroptions);
        $repeated[] = $mform->createElement('text', 'feedback',
                get_string('feedback', 'question'), ['rows' => 2]);
        $rows['name'] = 'rows';
        $repeatedoptions['row']['type'] = PARAM_RAW;
        return $repeated;
    }


    /**
     * Language string to use for 'Add {no} more {rows or columns}'.
     */
    protected function get_more_blanks(string $string) {
        return get_string('addmoreblanks', 'qtype_oumatrix', $string);
    }

}

