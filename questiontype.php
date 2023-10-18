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

use qtype_oumatrix\column;
use qtype_oumatrix\row;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/questionlib.php');

/**
 * Class that represents the oumatrix question type.
 *
 * The class loads, saves and deletes questions of the type oumatrix
 * to and from the database and provides methods to help with editing questions
 * of this type. It can also provide the implementation for import and export
 * in various formats.
 *
 * @package   qtype_oumatrix
 * @copyright 2023 The Open University
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_oumatrix extends question_type {
    public function get_question_options($question) {
        global $DB, $OUTPUT;
        parent::get_question_options($question);
        if (!$question->options = $DB->get_record('qtype_oumatrix_options', ['questionid' => $question->id])) {
            echo $OUTPUT->notification('Error: Missing matrix question options!');
            return false;
        }
        if (!$question->columns = $DB->get_records('qtype_oumatrix_columns', ['questionid' => $question->id])) {
            echo $OUTPUT->notification('Error: Missing question columns!');
            return false;
        }
        if (!$question->rows = $DB->get_records('qtype_oumatrix_rows', ['questionid' => $question->id])) {
            echo $OUTPUT->notification('Error: Missing question rows!');
            return false;
        }
        return true;
    }

    public function save_defaults_for_new_questions(stdClass $fromform): void {
        parent::save_defaults_for_new_questions($fromform);
        $this->set_default_value('inputtype', $fromform->inputtype);
        $this->set_default_value('grademethod', $fromform->grademethod);
        $this->set_default_value('shuffleanswers', $fromform->shuffleanswers);
        $this->set_default_value('shownumcorrect', $fromform->shownumcorrect);
    }

    public function save_question_options($question) {
        global $DB;
        $context = $question->context;
        $options = $DB->get_record('qtype_oumatrix_options', ['questionid' => $question->id]);
        if (!$options) {
            $options = new stdClass();
            $options->questionid = $question->id;
            $options->inputtype = '';
            $options->grademethod = '';
            $options->shuffleanswers = 0;
            $options->correctfeedback = '';
            $options->partiallycorrectfeedback = '';
            $options->incorrectfeedback = '';
            $options->shownumcorrect = 0;
            $options->id = $DB->insert_record('qtype_oumatrix_options', $options);
        }
        $options->questionid = $question->id;
        $options->inputtype = $question->inputtype;
        $options->grademethod = $question->grademethod;
        $options->shuffleanswers = $question->shuffleanswers;
        $options = $this->save_combined_feedback_helper($options, $question, $context, true);
        $DB->update_record('qtype_oumatrix_options', $options);

        $columnslist = $this->save_columns($question);
        $this->save_rows($question, $columnslist);
        $this->save_hints($question, true);
    }

    /**
     * Save the question columns and return a list of columns to be used in the save_rows function.
     *
     * @param stdClass $question This holds the information from the editing form.
     * @return array The list of columns created.
     */
    public function save_columns(object $question): array {
        global $DB;
        $numcolumns = count($question->columnname);
        $columnslist = [];

        // Insert column input data.
        for ($i = 0; $i < $numcolumns; $i++) {
            if (trim(($question->columnname[$i]) ?? '') === '') {
                continue;
            }
            $column = new stdClass();
            $column->questionid = $question->id;
            $column->number = $i;
            $column->name = $question->columnname[$i];
            $column->id = $DB->insert_record('qtype_oumatrix_columns', $column);
            $columnslist[] = $column;
        }
        return $columnslist;
    }

    /**
     * Save the question rows.
     *
     * @param stdClass $question This holds the information from the editing form
     * @param array $columnslist
     */
    public function save_rows(object $question, array $columnslist) {
        global $DB;
        $context = $question->context;
        $numrows = count($question->rowname);

        // Insert row input data.
        for ($i = 0; $i < $numrows; $i++) {
            $answerslist = [];
            if (trim($question->rowname[$i] ?? '') === '') {
                continue;
            }
            $questionrow = new stdClass();
            $questionrow->questionid = $question->id;
            $questionrow->number = $i;
            $questionrow->name = $question->rowname[$i];
            // Prepare correct answers.
            for ($c = 0; $c < count($columnslist); $c++) {
                if ($question->inputtype == 'single') {
                    $columnindex = preg_replace("/[^0-9]/", "", $question->rowanswers[$i]);
                    $answerslist[$columnslist[$columnindex - 1]->id] = "1";
                } else {
                    $rowanswerslabel = "rowanswers" . 'a' . ($c + 1);
                    if (!isset($question->$rowanswerslabel) || !array_key_exists($i, $question->$rowanswerslabel)) {
                        continue;
                    }
                    $answerslist[$columnslist[$c]->id] = $question->{$rowanswerslabel}[$i];
                }
            }
            $questionrow->correctanswers = json_encode($answerslist);
            $questionrow->feedback = $question->feedback[$i]['text'];
            $questionrow->feedbackformat = FORMAT_HTML;
            $questionrow->id = $DB->insert_record('qtype_oumatrix_rows', $questionrow);

            if ($question->feedback[$i]['text'] != '') {
                $questionrow->feedback = $this->import_or_save_files($question->feedback[$i],
                    $context, 'qtype_oumatrix', 'feedback', $questionrow->id);
                $questionrow->feedbackformat = $question->feedback[$i]['format'];

                $DB->update_record('qtype_oumatrix_rows', $questionrow);
            }
        }
    }

    protected function make_question_instance($questiondata) {
        question_bank::load_question_definition_classes($this->name());
        if ($questiondata->options->inputtype === 'single') {
            $class = 'qtype_oumatrix_single';
        } else {
            $class = 'qtype_oumatrix_multiple';
        }
        return new $class();
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        $question->grademethod = $questiondata->options->grademethod;
        $question->shuffleanswers = $questiondata->options->shuffleanswers;
        $this->initialise_question_columns($question, $questiondata);
        $this->initialise_question_rows($question, $questiondata);
        $this->initialise_combined_feedback($question, $questiondata, true);
    }

    /**
     * Initialise the question columns.
     *
     * @param question_definition $question the question_definition we are creating.
     * @param stdClass $questiondata the question data loaded from the database.
     */
    protected function initialise_question_columns(question_definition $question, stdClass $questiondata): void {
        foreach ($questiondata->columns as $index => $column) {
            $question->columns[$index] = $this->make_column($column);
        }
    }

    /**
     * Make a column from raw data from the DB.
     *
     * @param stdClass $columndata
     * @return column
     */
    protected function make_column(stdClass $columndata): column {
        return new column($columndata->questionid, $columndata->number, $columndata->name, $columndata->id);
    }

    /**
     * Initialise the question rows.
     *
     * @param question_definition $question the question_definition we are creating.
     * @param stdClass $questiondata the question data loaded from the database.
     */
    protected function initialise_question_rows(question_definition $question, stdClass $questiondata): void {
        foreach ($questiondata->rows as $index => $row) {
            $newrow = $this->make_row($row);
            $correctanswers = [];
            $decodedanswers = json_decode($newrow->correctanswers, true);
            foreach ($questiondata->columns as $column) {
                if ($decodedanswers != null && array_key_exists($column->id, $decodedanswers)) {
                    if ($questiondata->options->inputtype == 'single') {
                        $anslabel = 'a' . ($column->number + 1);
                        $correctanswers[$column->id] = $anslabel;
                    } else {
                        $correctanswers[$column->id] = $decodedanswers[$column->id];
                    }
                }
            }
            $newrow->correctanswers = $correctanswers;
            $question->rows[$index] = $newrow;
        }
    }

    /**
     * Make a row from raw data from the DB.
     *
     * @param stdClass $rowdata
     * @return row
     */
    public function make_row(stdClass $rowdata): row {
        return new row($rowdata->id, $rowdata->questionid, $rowdata->number, $rowdata->name,
            $rowdata->correctanswers, $rowdata->feedback, $rowdata->feedbackformat);
    }

    public function delete_question($questionid, $contextid) {
        global $DB;
        $DB->delete_records('qtype_oumatrix_options', ['questionid' => $questionid]);
        $DB->delete_records('qtype_oumatrix_columns', ['questionid' => $questionid]);
        $DB->delete_records('qtype_oumatrix_rows', ['questionid' => $questionid]);
        parent::delete_question($questionid, $contextid);
    }

    public function get_random_guess_score($questiondata) {
        // We compute the randome guess score here on the assumption we are using
        // the deferred feedback behaviour, and the question text tells the
        // student how many of the responses are correct.
        // Amazingly, the forumla for this works out to be
        // # correct choices / total # choices in all cases.

        if ($this->get_total_number_of_choices($questiondata) === null) {
            return null;
        }
        return $this->get_num_correct_choices($questiondata) / $this->get_total_number_of_choices($questiondata);
    }

    /**
     * Return total number if choices for both (single, multiple) matrix choices.
     * @param stdClass $questiondata
     * @return int
     */
    public function get_total_number_of_choices(object $questiondata):? int {
        // if rows or columns are not set return null;
        if (sizeof($questiondata->columns) === 0 || sizeof($questiondata->rows) === 0)  {
            return null;
        }
        // Total number of choices for each row is the number of columns,
        // therefore the total number of choices for the question is
        return count($questiondata->columns) * count($questiondata->rows);
    }

    public function get_num_correct_choices($questiondata) {
        $numright = 0;
        foreach ($questiondata->rows as $row) {
            $rowanwers = json_decode($row->correctanswers);
            $numright += count((array)$rowanwers);
        }
        return $numright;
    }

    public function get_possible_responses($questiondata) {
        if ($questiondata->options->single) {
            $responses = array();

            // TODO: Sort out this funtion to work with rows and columns, etc.
            foreach ($questiondata->options->answers as $aid => $answer) {
                $responses[$aid] = new question_possible_response(
                        question_utils::to_plain_text($answer->answer, $answer->answerformat),
                        $answer->fraction);
            }

            $responses[null] = question_possible_response::no_response();
            return array($questiondata->id => $responses);
        } else {
            $parts = array();

            foreach ($questiondata->options->answers as $aid => $answer) {
                $parts[$aid] = array($aid => new question_possible_response(
                        question_utils::to_plain_text($answer->answer, $answer->answerformat),
                        $answer->fraction));
            }

            return $parts;
        }
    }

    public function import_from_xml($data, $question, qformat_xml $format, $extra = null) {
        if (!isset($data['@']['type']) || $data['@']['type'] != 'oumatrix') {
            return false;
        }

        $question = $format->import_headers($data);
        $question->qtype = 'oumatrix';

        $question->inputtype = $format->import_text(
            $format->getpath($data, ['#', 'inputtype'], 'single'));
        $question->grademethod = $format->import_text(
            $format->getpath($data, ['#', 'grademethod'], 'partial'));
        $question->shuffleanswers = $format->trans_single(
            $format->getpath($data, ['#', 'shuffleanswers', 0, '#'], 1));

        $columns = $format->getpath($data, ['#', 'columns', 0, '#', 'column'], false);
        if ($columns) {
            $this->import_columns($format, $question, $columns);
        } else {
            $question->columns = [];
        }
        $rows = $format->getpath($data, ['#', 'rows', 0, '#', 'row'], false);
        if ($rows) {
            $this->import_rows($format, $question, $rows);
        } else {
            $question->rows = [];
        }
        $format->import_combined_feedback($question, $data, true);
        $format->import_hints($question, $data, true, true,
                $format->get_format($question->questiontextformat));

        // Get extra choicefeedback setting from each hint.
        if (!empty($question->hintoptions)) {
            foreach ($question->hintoptions as $key => $options) {
                $question->hintshowrowfeedback[$key] = !empty($options);
            }
        }

        return $question;
    }

    public function import_columns(qformat_xml $format, stdClass $question, array $columns) {
        foreach ($columns as $column) {
            static $indexno = 0;
            $question->columns[$indexno]['name'] =
                    $format->import_text($format->getpath($column, ['#', 'text'], ''));
            $question->columns[$indexno]['id'] = $format->getpath($column, ['@', 'key'], $indexno);

            $question->columnname[$indexno] =
                    $format->import_text($format->getpath($column, ['#', 'text'], ''));
            $indexno++;
        }
    }

    public function import_rows(qformat_xml $format, stdClass $question, array $rows) {
        foreach ($rows as $row) {
            static $indexno = 0;
            $question->rows[$indexno]['id'] = $format->getpath($row, ['@', 'key'], $indexno);
            $question->rows[$indexno]['name'] =
                    $format->import_text($format->getpath($row, ['#', 'name', 0, '#', 'text'], ''));

            $correctanswer = $format->getpath($row, ['#', 'correctanswers', 0, '#', 'text', 0, '#'], '');
            $decodedanswers = json_decode($correctanswer, true);
            foreach ($question->columns as $colindex => $col) {
                if (array_key_exists($col['id'], $decodedanswers)) {
                    // Import correct answers for single choice.
                    if ($question->inputtype == 'single') {
                        // Assigning $colindex + 1 as answers are stored as 1,2 and so on.
                        $question->rowanswers[$indexno] = ($colindex + 1);
                        break;
                    } else {
                        // Import correct answers for multiple choice.
                        $rowanswerslabel = "rowanswers" . 'a' . ($colindex + 1);
                        if (array_key_exists($col['id'], $decodedanswers)) {
                            $question->{$rowanswerslabel}[$indexno] = "1";
                        }
                    }
                }
            }
            $question->rowname[$indexno] =
                $format->import_text($format->getpath($row, ['#', 'name', 0, '#', 'text'], ''));
            $question->feedback[$indexno] = $format->import_text_with_files($row, ['#', 'feedback', 0], '', 'html');
            $indexno++;
        }
    }

    public function export_to_xml($question, qformat_xml $format, $extra = null) {
        $output = '';

        $output .= '    <inputtype>' . $format->xml_escape($question->options->inputtype)
                . "</inputtype>\n";
        $output .= '    <grademethod>' . $format->xml_escape($question->options->grademethod)
                . "</grademethod>\n";
        $output .= "    <shuffleanswers>" . $format->get_single(
                        $question->options->shuffleanswers) . "</shuffleanswers>\n";

        // Export columns data.
        $output .= "    <columns>\n";
        ksort($question->columns);
        foreach ($question->columns as $columnkey => $column) {
            $output .= "      <column key=\"{$columnkey}\">\n";
            $output .= $format->writetext($column->name, 4);
            $output .= "      </column>\n";
        }
        $output .= "    </columns>\n";

        // Export rows data.
        $fs = get_file_storage();
        $output .= "    <rows>\n";
        ksort($question->rows);
        $indent = 5;
        foreach ($question->rows as $rowkey => $row) {
            $output .= "      <row key=\"{$rowkey}\">\n";
            $output .= "        <name>\n";
            $output .= $format->writetext($row->name, $indent);
            $output .= "        </name>\n";
            $output .= "        <correctanswers>\n";
            $output .= $format->writetext($row->correctanswers, $indent);
            $output .= "        </correctanswers>\n";
            if (($row->feedback ?? '') != '') {
                $output .= '        <feedback ' . $format->format($row->feedbackformat) . ">\n";
                $output .= $format->writetext($row->feedback, $indent);
                $files = $fs->get_area_files($question->contextid, 'qtype_oumatrix', 'feedback', $row->id);
                $output .= $format->write_files($files);
                $output .= "        </feedback>\n";
            }
            $output .= "      </row>\n";
        }
        $output .= "    </rows>\n";
        $output .= $format->write_combined_feedback($question->options,
                $question->id,
                $question->contextid);
        return $output;
    }

    public function move_files($questionid, $oldcontextid, $newcontextid) {
        $fs = get_file_storage();

        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_row_feedback($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_hints($questionid, $oldcontextid, $newcontextid);

        $fs->move_area_files_to_new_context($oldcontextid,
            $newcontextid, 'question', 'correctfeedback', $questionid);
        $fs->move_area_files_to_new_context($oldcontextid,
            $newcontextid, 'question', 'partiallycorrectfeedback', $questionid);
        $fs->move_area_files_to_new_context($oldcontextid,
            $newcontextid, 'question', 'incorrectfeedback', $questionid);
    }

    /**
     * Move all the feedback files belonging to each sub-question
     * when the question is moved from one context to another.
     *
     * @param int $questionid the question being moved.
     * @param int $oldcontextid the context it is moving from.
     * @param int $newcontextid the context it is moving to.
     */
    protected function move_files_in_row_feedback(int $questionid, int $oldcontextid, int $newcontextid) {
        global $DB;
        $fs = get_file_storage();

        $rowids = $DB->get_records_menu('qtype_oumatrix_rows',
            ['questionid' => $questionid], 'id', 'id,1');
        foreach ($rowids as $rowid => $notused) {
            $fs->move_area_files_to_new_context($oldcontextid,
                $newcontextid, 'qtype_oumatrix', 'feedback', $rowid);
        }
    }

    protected function delete_files($questionid, $contextid) {
        $fs = get_file_storage();

        parent::delete_files($questionid, $contextid);
        $this->delete_files_in_row_feedback($questionid, $contextid);
        $this->delete_files_in_hints($questionid, $contextid);
        $fs->delete_area_files($contextid, 'question', 'correctfeedback', $questionid);
        $fs->delete_area_files($contextid, 'question', 'partiallycorrectfeedback', $questionid);
        $fs->delete_area_files($contextid, 'question', 'incorrectfeedback', $questionid);
    }

    /**
     * Delete all the files belonging to this question's row feedbacks.
     *
     * @param int $questionid the question being deleted.
     * @param int $contextid the context the question is in.
     */
    protected function delete_files_in_row_feedback(int $questionid, int $contextid) {
        global $DB;
        $fs = get_file_storage();

        $rowids = $DB->get_records_menu('qtype_oumatrix_rows',
                ['questionid' => $questionid], 'id', 'id,1');
        foreach ($rowids as $rowid => $notused) {
            $fs->delete_area_files($contextid, 'qtype_oumatrix', 'feedback', $rowid);
        }
    }
}
