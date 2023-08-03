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

//namespace gtype_oumatirx;
use \qtype_oumatirx\row_info;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/oumatrix/edit_oumatrix_form_base.php');

/**
 * Editing form for the oumatrix question type.
 *
 * @copyright  2023 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_oumatrix_edit_form extends qtype_oumatrix_edit_form_base {

    /** @var int Number of rows. */
    protected $numrows;

    /** @var int Number of columns. */
    protected $numcolumns;

    /** @var array The grid options. */
    protected $gridoptions;

    /** @var object The matrix row object. */
    protected $rowinfo = null;

    /** @var object The matrix column (answer) object. */
    protected $columninfo = null;

    /**
     * Add question-type specific form fields.
     *
     * @param object $mform the form being built.
     */
    protected function definition_inner($mform) {

        $qtype = 'qtype_oumatrix';
        $answermodemenu = [
                'single' => get_string('answermodesingle', $qtype),
                'multiple' => get_string('answermodemultiple', $qtype)
        ];
        $grademethod = [
                'partial' => get_string('gradepartialcredit', $qtype),
                'allnone' => get_string('gradeallornothing', $qtype)
        ];
        $mform->addElement('select', 'inputtype', get_string('answermode', $qtype), $answermodemenu);
        $mform->setDefault('single', $this->get_default_value('single', get_config($qtype, 'inputtype')));

        $mform->addElement('selectyesno', 'shuffleanswers', get_string('shuffleanswers', $qtype));
        $mform->addHelpButton('shuffleanswers', 'shuffleanswers', $qtype);
        $mform->setDefault('shuffleanswers', $this->get_default_value(
                'shuffleanswers', get_config($qtype, 'shuffleanswers')));

        $mform->addElement('select', 'grademethod', get_string('grademethod', $qtype), $grademethod);
        $mform->addHelpButton('grademethod', 'grademethod', $qtype);
        $mform->setDefault('grademethod', $this->get_default_value(
                'grademethod', get_config($qtype, 'grademethod')));

        // Set matrix table options.
        $this->gridoptions = range(1, 12);
        // Add number of matrix rows.
        $mform->addElement('select', 'numrows',
            get_string('numberofrows', $qtype), $this->gridoptions, null);
        $mform->addRule('numrows', null, 'required', null, 'client');
        $mform->setDefault('numrows', 2);

        // Add number of matrix columns.
        $mform->addElement('select', 'numcolumns',
            get_string('numberofcolumns', $qtype), $this->gridoptions, null);
        $mform->addRule('numcolumns', null, 'required', null, 'client');
        $mform->setDefault('numcolumns', 4);

        // Add update field.
        $mform->addElement('submit', 'updateform', get_string('updateform', $qtype));
        $mform->registerNoSubmitButton('updateform');
        //print_object($mform);

        $this->set_current_rowcolumn_setting($mform);

        $this->add_combined_feedback_fields(true);
        $mform->disabledIf('shownumcorrect', 'single', 'eq', 1);

        $this->add_interactive_settings(true, true);

    }

    /**
     * Set the matrix grid size.
     *
     * @return array
     */
    protected function set_current_rowcolumn_setting($mform) {
        $numrowsindex = optional_param('numrows', -1, PARAM_INT);
        $numcolumnsindex = optional_param('numcolumns', -1, PARAM_INT);

        if ($numrowsindex < 0) {
            $numrowsindex = $this->question->options->numrows ?? 2;
        }

        if ($numcolumnsindex < 0) {
            $numcolumnsindex = $this->question->options->numcolumns ?? 4;
        }
        $this->numrows = $this->gridoptions[$numrowsindex] ?? 2;
        $this->numcolumns = $this->gridoptions[$numcolumnsindex] ?? 2;

        $this->add_per_column_fields($mform, get_string('a', 'qtype_oumatrix', '{no}'), $this->numcolumns);
        $this->add_per_row_fields($mform, get_string('r', 'qtype_oumatrix', '{no}'), $this->numrows);


        //$this->add_row_fields($mform);
    }

    protected function xxxadd_column_fields(&$mform) {
        $mform->addElement('header', 'columnshdr', get_string('columnshdr', 'qtype_oumatrix'));
        $mform->setExpanded('columnshdr', 1);
        $columns = $this->columninfo->get_columns($this->numrows);
        foreach($columns as $column) {
            $label = 'Ans' . $column->number;
            $element = $mform->addElement('editor', $label, $label, ['size' => 50, 'rows' => 2]);
            $mform->setType($label, PARAM_RAW);
            $element->setValue(['text' => $column->name]);
        }
    }

    protected function add_row_fields(&$mform) {
        //$mform = $this->_form;
        $mform->addElement('header', 'rowshdr', get_string('rowshdr', 'qtype_oumatrix'));
        $mform->setExpanded('rowshdr', 1);
        $rows = $this->rowinfo->get_rows($this->numrows);
        foreach($rows as $row) {
            $label = 'Row' . $row->number;
            $element = $mform->addElement('editor', $label, $label, ['size' => 50, 'rows' => 2]);
            $mform->setType($label, PARAM_RAW);
            $element->setValue(['text' => $row->name]);

            // Answers to be chosen for a row.
            $columns = $this->columninfo->get_columns($this->numrows);
            $answernames = [];
            foreach($columns as $column) {
                $answernames[$column->number] = $column->name;
            }
            $mform->addElement('select', 'rowanswerlist',
                    get_string('rowanswerlist', 'qtype_oumatrix'), $answernames);

            $label = 'FB' . $row->number;
            $element = $mform->addElement('editor', $label, $label, ['size' => 50, 'rows' => 2], $this->editoroptions);
            $mform->setType($label, PARAM_RAW);
            $element->setValue(['text' => $row->feedback]);
        }
        $this->rowinfo->get_matrix();
    }

    //protected function add_column_to_select_in_row(&$mform) {
    //    $columns = $this->columninfo->get_columns($this->numrows);
    //    $answernames = [];
    //    foreach($columns as $column) {
    //        $answernames[$column->number] = $column->name;
    //    }
    //    $mform->addElement('select', 'answer', get_string('answer', $qtype), $answernames);
    //}




    protected function add_question_section(): void {

    }

    /**
     * Returns the question type name.
     *
     * @return string The question type name.
     */
    public function qtype() {
        return 'oumatrix';
    }

    public function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        if (!empty($question->options)) {
            $question->inputtype = $question->options->inputtype;
            $question->grademethod = $question->options->grademethod;
            $question->shuffleanswers = $question->options->shuffleanswers;
            $question->shuffleanswers = $question->options->shuffleanswers;
        }
        // Get question rows and columns.
        //$question->rows = $this->rowinfo->get_rows();
        //$question->columns = $this->columninfo->get_columns();

        $question = $this->data_preprocessing_combined_feedback($question, true);
        $question = $this->data_preprocessing_hints($question, true, true);
        //print_object($question);
        return $question;
    }

    protected function get_hint_fields($withclearwrong = false, $withshownumpartscorrect = false) {
        list($repeated, $repeatedoptions) = parent::get_hint_fields($withclearwrong, $withshownumpartscorrect);
        $repeatedoptions['hintclearwrong']['disabledif'] = ['single', 'eq', 1];
        $repeatedoptions['hintshownumcorrect']['disabledif'] = ['single', 'eq', 1];
        return [$repeated, $repeatedoptions];
    }

    /**
     * Perform the necessary preprocessing for the hint fields.
     *
     * @param object $question The data being passed to the form.
     * @param bool $withclearwrong Clear wrong hints.
     * @param bool $withshownumpartscorrect Show number correct.
     * @return object The modified data.
     */
    protected function data_preprocessing_hints($question, $withclearwrong = false,
            $withshownumpartscorrect = false) {
        if (empty($question->hints)) {
            return $question;
        }
        parent::data_preprocessing_hints($question, $withclearwrong, $withshownumpartscorrect);

        $question->hintoptions = [];
        foreach ($question->hints as $hint) {
            $question->hintoptions[] = $hint->options;
        }
        return $question;
    }
}

