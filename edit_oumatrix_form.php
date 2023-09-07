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
use \qtype_oumatirx\row;
use \qtype_oumatirx\column;

/**
 * Editing form for the oumatrix question type.
 *
 * @copyright  2023 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_oumatrix_edit_form extends question_edit_form {

    /** The default starting number of columns (answers). */
    private const COL_NUM_START = 3;

    /** The number of columns (answers) that get added at a time. */
    private const COL_NUM_ADD = 2;

    /**The default starting number of rows (question row). */
    private const ROW_NUM_START = 4;

    /** The number of rows (question row) that get added at a time.*/
    private const ROW_NUM_ADD = 2;

    /** @var int Number of columns. */
    private $numcolumns;

    /** @var int Number of rows. */
    private $numrows;

    /** @var string answermode of rows. */
    private $inputtype;

    /** @var string grading method of rows. */
    private $grademethod;

    /**
     * Add question-type specific form fields.
     *
     * @param object $mform the form being built.
     */
    protected function definition_inner($mform) {

        // Sett the number of columns and rows.self::COL_NUM_START;
        $this->numcolumns = $this->numcolumns ?? self::COL_NUM_START;
        $this->numrows = $this->numrows ?? self::ROW_NUM_START;

        $answermodemenu = [
                'single' => get_string('answermodesingle', 'qtype_oumatrix'),
                'multiple' => get_string('answermodemultiple', 'qtype_oumatrix')
        ];
        $mform->addElement('select', 'inputtype', get_string('answermode', 'qtype_oumatrix'), $answermodemenu);
        $mform->setDefault('inputtype', $this->get_default_value('single',
                get_config('qtype_oumatrix', 'inputtype')));

        $grademethod = [
                'partial' => get_string('gradepartialcredit', 'qtype_oumatrix'),
                'allnone' => get_string('gradeallornothing', 'qtype_oumatrix')
        ];
        $mform->addElement('select', 'grademethod', get_string('grademethod', 'qtype_oumatrix'), $grademethod);
        $mform->addHelpButton('grademethod', 'grademethod', 'qtype_oumatrix');
        $mform->setDefault('grademethod', $this->get_default_value(
                'grademethod', get_config('qtype_oumatrix', 'grademethod')));

        $mform->addElement('selectyesno', 'shuffleanswers', get_string('shuffleanswers', 'qtype_oumatrix'));
        $mform->addHelpButton('shuffleanswers', 'shuffleanswers', 'qtype_oumatrix');
        $mform->setDefault('shuffleanswers', $this->get_default_value(
                'shuffleanswers', get_config('qtype_oumatrix', 'shuffleanswers')));

        // Add update field.
        $mform->addElement('submit', 'updateform', get_string('updateform', 'qtype_oumatrix'));
        $mform->registerNoSubmitButton('updateform');

        $this->set_current_settings();

        $this->add_per_column_fields($mform, get_string('a', 'qtype_oumatrix', '{no}'), $this->numcolumns);
        $this->add_per_row_fields($mform, get_string('r', 'qtype_oumatrix', '{no}'), $this->numrows);

        $this->add_combined_feedback_fields(true);

        ///$mform->disabledIf('shownumcorrect', 'single', 'eq', 1);

        $this->add_interactive_settings(true, true);

    }

    /**
     * Set the inputtype and grading method.
     *
     * @return void
     */
    protected function set_current_settings(): void {
        $inputtype = optional_param('inputtype', '', PARAM_TEXT);
        $grademethod = optional_param('grademethod', '', PARAM_TEXT);

        get_config('', 'inputtype');
        if ($inputtype == '') {
            $inputtype = $this->question->options->inputtype ?? 'single';
        }

        if ($grademethod == '') {
            $grademethod = $this->question->options->grademethod ?? 'partial';
        }

        $this->inputtype = $inputtype;
        $this->grademethod = $grademethod;

        $columns = optional_param_array('columnname', '', PARAM_TEXT);
        $this->numcolumns = $columns ? count($columns) : self::COL_NUM_START;
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
        $question = $this->data_preprocessing_combined_feedback($question, true);
        $question = $this->data_preprocessing_hints($question, true, true);
        $question = $this->data_preprocessing_options($question,);
        //print_object('data_preprocessing() 1111111111111111111111');
        //print_object('data_preprocessing() 222222222222222222222');return $question;
        //print_object($question);
        //print_object('data_preprocessing() 222222222222222222222');return $question;
    }

     function data_preprocessing_options($question) {
        if (empty($question->options)) {
            return $question;
        }
        $question->inputtype = $question->options->inputtype;
        $question->grademethod = $question->options->grademethod;
        $question->shuffleanswers = $question->options->shuffleanswers;
        $question->shownumcorrect = $question->options->shownumcorrect;

        // Preprocess columns.
         if (empty($question->options->columns)) {
             return $question;
         }
         $question->columnname = [];
         foreach ($question->options->columns as $column) {
             if (trim($column->name ?? '') === '') {
                 continue;
             }
             $question->columnname[] = $column->name;
         }
         $this->numcolumns = count($question->columnname);

         // preprocess rows.
         if (empty($question->options->rows)) {
             return $question;
         }
         $key = 0;
         $question->rowname = [];
         foreach ($question->options->rows as $index => $row) {
             $question->rowname[] = $row->name;
             if ($question->options->inputtype == 'single') {
                 $question->rowanswers[] = $row->correctanswers;
             } else {
                 $this->format_correct_answers_multiple($row->number, $row->correctanswers, $question);
             }
             $itemid = (int)$row->id ?? null;

             // Prepare the feedback editor to display files in draft area.
             $feedback = [];
             $feedbackdraftitemid = file_get_submitted_draft_itemid('feedback['.$key.']');
             $feedback[$key]['text'] = file_prepare_draft_area(
                     $feedbackdraftitemid,
                     $this->context->id,
                     'qtype_oumatrix',
                     'feedback',
                     $itemid,
                     $this->fileoptions,
                     $row->feedback
             );
             $feedback[$key]['itemid'] = $feedbackdraftitemid;
             $feedback[$key]['format'] = $row->feedbackformat ?? FORMAT_HTML;
             $question->options->rows[$index]->feedbackformat = $feedback[$key]['format'];
             $question->options->rows[$index]->feedback = $feedback[$key]['text'];
             $key++;
         }
         $question->feedback = $feedback;

         //$this->data_preprocessing_columns($question);
         //$this->data_preprocessing_rows($question);
         return $question;
    }

    /**
     * Perform the necessary preprocessing for columns (answers) fields.
     *
     * @param object $question The data being passed to the form.
     * @return object The modified data.
     */
    private function data_preprocessing_columns($question) {
        if (empty($question->options->columns)) {
            return $question;
        }
        $question->columnname = [];
        foreach ($question->options->columns as $column) {
            if (trim($column->name ?? '') === '') {
                continue;
            }
            $question->columnname[] = $column->name;
        }
        $this->numcolumns = count($question->columnname);
        return $question;
    }

    /**
     * Perform the necessary preprocessing for rows (subquestions) fields.
     *
     * @param object $question The data being passed to the form.
     * @return object The modified data.
     */
    private function data_preprocessing_rows($question) {
        if (empty($question->options->rows)) {
            return $question;
        }
        $key = 0;
        $question->rowname = [];
        foreach ($question->options->rows as $index => $row) {
            $question->rowname[] = $row->name;
            if ($question->options->inputtype == 'single') {
                $question->rowanswers[] = $row->correctanswers;
            } else {
                $this->format_correct_answers_multiple($row->number, $row->correctanswers, $question);
            }
            $itemid = (int)$row->id ?? null;

            // Prepare the feedback editor to display files in draft area.
            $feedback = [];
            $feedbackdraftitemid = file_get_submitted_draft_itemid('feedback['.$key.']');
            $feedback[$key]['text'] = file_prepare_draft_area(
                    $feedbackdraftitemid,
                    $this->context->id,
                    'qtype_oumatrix',
                    'feedback',
                    $itemid,
                    $this->fileoptions,
                    $row->feedback
            );
            $feedback[$key]['itemid'] = $feedbackdraftitemid;
            $feedback[$key]['format'] = $row->feedbackformat ?? FORMAT_HTML;
            $question->options->rows[$index]->feedbackformat = $feedback[$key]['format'];
            $question->options->rows[$index]->feedback = $feedback[$key]['text'];
            $key++;
        }
        $question->feedback = $feedback;
        return $question;
    }

    protected function format_correct_answers_multiple($rownumber, $answers, $question) {
        $decodedanswers = json_decode($answers, true);
        foreach ($question->options->columns as $key => $column) {
            $anslabel = get_string('a', 'qtype_oumatrix', $column->number + 1);
            $rowanswerslabel = "rowanswers".$anslabel;
            $question->$rowanswerslabel[$rownumber] = $decodedanswers[$column->name];
        }
    }

    protected function get_hint_fields($withclearwrong = false, $withshownumpartscorrect = false) {
        [$repeated, $repeatedoptions] = parent::get_hint_fields($withclearwrong, $withshownumpartscorrect);
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
    protected function data_preprocessing_hints($question, $withclearwrong = false, $withshownumpartscorrect = false) {
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
        $repeated[] = $mform->createElement('text', 'columnname', $label, ['size' => 40]);
        $mform->setType('columnname', PARAM_RAW);
        $repeatedoptions['column']['type'] = PARAM_RAW;
        $columns = 'columns';
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
        $repeatedoptions = [];
        $rows = [];

        if (isset($this->question->options->rows)) {
            $repeatsatstart = count($this->question->options->rows);
        } else {
            $repeatsatstart = $minoptions;
        }

        $this->repeat_elements($this->get_per_row_fields($mform, $label, $repeatedoptions, $rows),
                $repeatsatstart, $repeatedoptions,
                'norows', 'addrows', $addoptions,
                $this->get_more_blanks('rows'), true);
    }

    /**
     * @param MoodleQuickForm $mform
     * @param string $label
     * @param array $repeatedoptions
     * @param array $rows
     * @return array
     */
    protected function get_per_row_fields(MoodleQuickForm $mform, string $label, array $repeatedoptions, array $rows): array {
        $repeated = [];
        $rowoptions = [];
        $rowoptions[] = $mform->createElement('text', 'rowname', 'Name', ['size' => 40]);

        // Get the list answer input type (radio buttons or checkboxes).
        for ($i = 0; $i < $this->numcolumns; $i++) {
            $anslabel = get_string('a', 'qtype_oumatrix', $i+1);
            if ($this->inputtype === 'single') {
                $rowoptions[] = $mform->createElement('radio', 'rowanswers', '', $anslabel, $anslabel);
            } else {
                $rowoptions[] = $mform->createElement('checkbox', "rowanswers$anslabel", '', $anslabel);
            }
        }
        $rowoptions[] = $mform->createElement('editor', 'feedback',
                get_string('feedback', 'question'), ['rows' => 2], $this->editoroptions);
        $repeated[] = $mform->createElement('group', 'rowoptions', $label, $rowoptions, null, false);
        $mform->setType('rowname', PARAM_RAW);
        $repeatedoptions['row']['type'] = PARAM_RAW;
        $rows = 'rows';
        return $repeated;
    }

    /**
     * Language string to use for 'Add {no} more {rows or columns}'.
     */
    protected function get_more_blanks(string $string) {
        return get_string('addmoreblanks', 'qtype_oumatrix', $string);
    }

}
