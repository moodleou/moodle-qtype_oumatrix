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
 * Question definition class for oumatrix.
 *
 * @package     qtype_oumatrix
 * @copyright   2023 The Open University
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// For a complete list of base question classes please examine the file
// /question/type/questionbase.php.
//
// Make sure to implement all the abstract methods of the base class.

/**
 * Class that represents a oumatrix question.
 */
abstract class qtype_oumatrix_base extends question_graded_automatically {
    public $shuffleanswers;

    public $correctfeedback;
    public $correctfeedbackformat;
    public $partiallycorrectfeedback;
    public $partiallycorrectfeedbackformat;
    public $incorrectfeedback;
    public $incorrectfeedbackformat;

    /** @var array The columns (answers) object. */
    public $columns;

    /** @var array The rows (subquestions) object. */
    public $rows;

    /** @var int The number of columns. */
    public $numcolumns;

    /** @var int The number of rows. */
    public $numrows;

    /** @var array The order of the rows. */
    protected $roworder = null;

    /** @var array The order of the rows. */
    protected $columnorder = null;

    /** @var string Single choice or multiple response question type. */
    public $inputtype;

    /** @var string 'All or none' or 'partial' grading method for multiple response. */
    public $grademethod;

    abstract public function is_choice_selected($response, $rowkey, $colkey);

    abstract public function is_same_response(array $prevresponse, array $newresponse);

    public function start_attempt(question_attempt_step $step, $variant) {
        $this->roworder = array_keys($this->rows);
        if ($this->shuffleanswers) {
            shuffle($this->roworder);
        }
        $step->set_qt_var('_roworder', implode(',', $this->roworder));
    }

    public function apply_attempt_state(question_attempt_step $step) {
        $this->roworder = explode(',', $step->get_qt_var('_roworder'));
    }

    public function get_order(question_attempt $qa) {
        $this->init_roworder($qa);
        return $this->roworder;
    }

    protected function init_roworder(question_attempt $qa) {
        if (is_null($this->roworder)) {
            $this->roworder = explode(',', $qa->get_step(0)->get_qt_var('_roworder'));
        }
    }

    public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) {
        if ($component == 'question' && in_array($filearea,
                        ['correctfeedback', 'partiallycorrectfeedback', 'incorrectfeedback'])) {
            return $this->check_combined_feedback_file_access($qa, $options, $filearea, $args);

        } else if ($component == 'qtype_oumatrix' && $filearea == 'feedback') {
            return $options->feedback;

        } else if ($component == 'question' && $filearea == 'hint') {
            return $this->check_hint_file_access($qa, $options, $args);

        } else {
            return parent::check_file_access($qa, $options, $component, $filearea,
                    $args, $forcedownload);
        }
    }

    public function get_validation_error(array $response): string {
        if ($this->is_complete_response($response)) {
            return '';
        }
        return get_string('pleaseananswerallparts', 'qtype_oumatrix');
    }

    abstract public function grade_response(array $response);

    public function validate_can_regrade_with_other_version(question_definition $otherversion): ?string {
        $basemessage = parent::validate_can_regrade_with_other_version($otherversion);
        if ($basemessage) {
            return $basemessage;
        }
        // TODO: this and other two folloing function are taken from multianswer. To be sorted.

        if (count($this->subquestions) != count($otherversion->subquestions)) {
            return get_string('regradeissuenumsubquestionschanged', 'qtype_multianswer');
        }

        foreach ($this->subquestions as $i => $subq) {
            $subqmessage = $subq->validate_can_regrade_with_other_version($otherversion->subquestions[$i]);
            if ($subqmessage) {
                return $subqmessage;
            }
        }
        return null;
    }

    public function update_attempt_state_data_for_new_version(
            question_attempt_step $oldstep, question_definition $oldquestion) {
        parent::update_attempt_state_data_for_new_version($oldstep, $oldquestion);

        $result = [];
        foreach ($this->subquestions as $i => $subq) {
            $substep = $this->get_substep($oldstep, $i);
            $statedata = $subq->update_attempt_state_data_for_new_version(
                    $substep, $oldquestion->subquestions[$i]);
            foreach ($statedata as $name => $value) {
                $result[$substep->add_prefix($name)] = $value;
            }
        }

        return $result;
    }
}

    /**
     * Class that represents a oumatrix question.
     */
class qtype_oumatrix_single extends qtype_oumatrix_base {

    public function get_renderer(moodle_page $page) {
        return $page->get_renderer('qtype_oumatrix', 'single');
    }

    public function get_expected_data(): array {
        $expected = [];
        foreach ($this->rows as $row) {
            $expected[$this->field($row->number)] = PARAM_INT;
        }
        return $expected;
    }

    public function is_choice_selected($response, $rowkey, $colkey) {
        $responsekey = $this->field($rowkey);
        if ($response && array_key_exists($responsekey, $response)) {
            return (string) $response[$responsekey] == $colkey;
        }
        return false;
    }

    public function prepare_simulated_post_data($simulatedresponse) {
        return $simulatedresponse;
    }

    public function is_same_response(array $prevresponse, array $newresponse): bool {
        foreach ($this->roworder as $key => $notused) {
            $fieldname = $this->field($key);
            if (!question_utils::arrays_same_at_key_missing_is_blank($prevresponse, $newresponse, $fieldname)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Answer field name.
     *
     * @param int $rowkey The row key number.
     * @return string The answer key name.
     */
    protected function field(int $rowkey): string {
        return 'rowanswers' . $rowkey;
    }

    public function get_correct_response(): ?array {
        $response = [];
        foreach ($this->roworder as $key => $rownumber) {
            $row = $this->rows[$rownumber];
            if ($row->correctanswers != '') {
                $response[$this->field($key)] = $this->columns[array_key_first($row->correctanswers)]->number;
            }
        }
        return $response;
    }

    public function summarise_response(array $response): ?string {
        $responsewords = [];
        foreach ($this->roworder as $key => $rownumber) {
            // Get the correct row.
            $row = $this->rows[$rownumber];

            $fieldname = $this->field($key);
            if (array_key_exists($fieldname, $response)) {
                foreach ($this->columns as $column) {
                    if ($response[$fieldname] == $column->number) {
                        $responsewords[] = $row->name . ' → ' . $column->name;
                    }
                }
            }
        }
        return implode('; ', $responsewords);
    }

    public function is_complete_response(array $response): bool {
        foreach ($this->rows as $row) {
            $fieldname = $this->field($row->number);
            if (!array_key_exists($fieldname, $response)) {
                return false;
            }
        }
        return true;
    }

    public function is_gradable_response(array $response): bool {
        return true;
    }

    public function get_validation_error(array $response): string {
        if ($this->is_complete_response($response)) {
            return '';
        }
        return get_string('pleaseananswerallparts', 'qtype_oumatrix');
    }

    public function grade_response(array $response): array {
        // Retrieve the number of right and total answers.
        [$numrightparts, $total] = $this->get_num_parts_right($response);
        $fraction = $numrightparts / $total;
        return [$fraction, question_state::graded_state_for_fraction($fraction)];
    }

    public function get_num_parts_right(array $response): array {
        $numright = 0;
        foreach ($this->roworder as $key => $rownumber) {
            $row = $this->rows[$rownumber];
            if (array_key_exists($this->field($key), $response) && $row->correctanswers != '' &&
                $response[$this->field($key)] == $this->columns[array_key_first($row->correctanswers)]->number) {
                $numright++;
            }
        }
        return [$numright, count($this->rows)];
    }
}

    /**
     * Class that represents a oumatrix question.
     */
class qtype_oumatrix_multiple extends qtype_oumatrix_base {

    public function get_renderer(moodle_page $page) {
        return $page->get_renderer('qtype_oumatrix', 'multiple');
    }


    public function get_expected_data(): array {
        $expected = [];
        foreach ($this->rows as $row) {
            if ($row->correctanswers != '') {
                foreach ($this->columns as $column) {
                    $expected[$this->field($row->number, $column->number)] = PARAM_INT;
                }
            }
        }
        return $expected;
    }

    public function is_choice_selected($response, $rowkey, $colkey) {
        $responsekey = $this->field($rowkey, $colkey);
        if ($response && array_key_exists($responsekey, $response)) {
            return (string) $response[$responsekey] == 1;
        }
        return false;
    }

    /**
     * Answer field name.
     *
     * @param int $rowkey The row key number.
     * @param int $columnkey The column key number.
     * @return string The answer key name.
     */
    protected function field(int $rowkey, int $columnkey): string {
        return 'rowanswers' . $rowkey . '_' . $columnkey;
    }

    public function prepare_simulated_post_data($simulatedresponse) {
        return $simulatedresponse;
    }

    public function is_same_response(array $prevresponse, array $newresponse): bool {
        foreach ($this->roworder as $key => $notused) {
            foreach ($this->columns as $column) {
                $fieldname = $this->field($key, $column->number);
                if (!question_utils::arrays_same_at_key_integer($prevresponse, $newresponse, $fieldname)) {
                    return false;
                }
            }
        }
        return true;
    }

    public function get_correct_response(): ?array {
        $answers = [];
        foreach ($this->roworder as $key => $rownumber) {
            $row = $this->rows[$rownumber];
            if ($row->correctanswers != '') {
                foreach ($row->correctanswers as $colkey => $answer) {
                    // Get the corresponding column object associated with the column key.
                    $column = $this->columns[$colkey];
                    $answers[$this->field($key, $column->number)] = $answer;
                }
            }
        }
        return $answers;
    }

    public function summarise_response(array $response): ?string {
        $responsewords = [];
        foreach ($this->roworder as $key => $rownumber) {
            // Get the correct row.
            $row = $this->rows[$rownumber];
            $rowresponse = $row->name . ' → ';
            $answers = [];
            foreach ($this->columns as $column) {
                $fieldname = $this->field($key, $column->number);
                if (array_key_exists($fieldname, $response)) {
                    $answers[] = $column->name;
                }
            }
            if (count($answers) > 0) {
                $rowresponse = $rowresponse . implode(', ', $answers);
                $responsewords[] = $rowresponse;
            }
        }
        return implode('; ', $responsewords);
    }

    public function is_complete_response(array $response): bool {
        foreach ($this->rows as $row) {
            $inputresponse = false;
            foreach ($this->columns as $col) {
                $fieldname = $this->field($row->number, $col->number);
                if (array_key_exists($fieldname, $response)) {
                    $inputresponse = true;
                }
            }
            if (!$inputresponse) {
                return $inputresponse;
            }
        }
        return true;
    }

    public function is_gradable_response(array $response): bool {
        return true;
    }

    public function get_validation_error(array $response): string {
        if ($this->is_complete_response($response)) {
            return '';
        }
        return get_string('pleaseananswerallparts', 'qtype_oumatrix');
    }

    public function grade_response(array $response): array {
        // Retrieve a number of right answers and total answers.
        if ($this->grademethod == 'allnone') {
            [$numrightparts, $total] = $this->get_num_grade_allornone($response);
            $fraction = $numrightparts / $total;
        } else {
            [$numrightparts, $total] = $this->get_num_parts_grade_partial($response);
            $numwrong = $this->get_num_selected_choices($response) - $total;
            $fraction = max(min($numrightparts, $total - $numwrong), 0) / $total;
        }
        return [$fraction, question_state::graded_state_for_fraction($fraction)];
    }

    /**
     * Get the number of correct choices selected in the response, for All-or-nothing grade method.
     *
     * @param array $response The response list.
     * @return array The array of number of correct response and the total rows.
     */
    public function get_num_grade_allornone(array $response): array {
        $numright = 0;
        // Use the shuffled order.
        foreach ($this->roworder as $rowkey => $rownumber) {
            $row = $this->rows[$rownumber];
            $rowrightresponse = 0;
            if (isset($row->correctanswers)) {
                foreach ($this->columns as $column) {
                    $reponsekey = $this->field($rowkey, $column->number);
                    if (array_key_exists($reponsekey, $response)) {
                        if (array_key_exists($column->id, $row->correctanswers)) {
                            // Add to the count of correct responses.
                            $rowrightresponse++;
                        } else {
                            // Check if there are too many responses selected.
                            // Then set it to -1 so marks are not allotted for it.
                            $rowrightresponse = -1;
                            break;
                        }
                    }
                }
                // Check if the row has the correct response.
                if ($rowrightresponse == count($row->correctanswers)) {
                    $numright++;
                }
            }
        }
        return [$numright, count($this->rows)];
    }

    /**
     * Get the number of correct choices selected in the response, for Partial grade method.
     *
     * @param array $response The response list.
     * @return array The array of number of correct response and the total correct answers.
     */
    public function get_num_parts_grade_partial(array $response): array {
        $numcorrectanswers = 0;
        $rightresponse = 0;
        foreach ($this->roworder as $rowkey => $rownumber) {
            $row = $this->rows[$rownumber];
            if ($row->correctanswers != '' ) {
                foreach ($this->columns as $column) {
                    $reponsekey = $this->field($rowkey, $column->number);
                    if (array_key_exists($reponsekey, $response) && array_key_exists($column->id, $row->correctanswers)) {
                        $rightresponse++;
                    }
                }
                $numcorrectanswers += count($row->correctanswers);
            }
        }
        return [$rightresponse, $numcorrectanswers];
    }

    /**
     * @param array $response responses, as returned by
     *      {@link question_attempt_step::get_qt_data()}.
     * @return int the number of choices that were selected in this response.
     */
    public function get_num_selected_choices(array $response) {
        $numselected = 0;
        foreach ($response as $key => $value) {
            // Response keys starting with _ are internal values like _order, so ignore them.
            if (!empty($value) && $key[0] != '_') {
                $numselected += 1;
            }
        }
        return $numselected;
    }

    /**
     * @return int the number of choices that are correct.
     */
    public function get_num_correct_choices() {
        $numcorrect = 0;
        foreach ($this->rows as $row) {
            if ($row->correctanswers != '') {
                $numcorrect += count($row->correctanswers);
            }
        }
        return $numcorrect;
    }
}
