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
 * Class that represents different types of oumatrix question.
 *
 * @package   qtype_oumatrix
 * @copyright 2023 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use qtype_oumatrix\column;
use qtype_oumatrix\row;

defined('MOODLE_INTERNAL') || die();

/**
 * Class that represents an oumatrix question.
 *
 * @copyright   2023 The Open University
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class qtype_oumatrix_base extends question_graded_automatically {
    /** @var bool whether the rows should be shuffled. */
    public bool $shuffleanswers;

    /** @var string 'All or none' or 'partial' grading method for multiple response. */
    public string $grademethod;

    /** @var string feedback for any correct response. */
    public string $correctfeedback;

    /** @var int format of $correctfeedback. */
    public int $correctfeedbackformat;

    /** @var string feedback for any partially correct response. */
    public string $partiallycorrectfeedback;

    /** @var int format of $partiallycorrectfeedback. */
    public int $partiallycorrectfeedbackformat;

    /** @var string feedback for any incorrect response. */
    public string $incorrectfeedback;

    /** @var int format of $incorrectfeedback. */
    public int $incorrectfeedbackformat;

    /** @var column[] The columns (answers) object, indexed by number. */
    public array $columns;

    /** @var row[] The rows (subquestions) object, indexed by number. */
    public array $rows;

    /** @var array The order of the rows, key => row number. */
    protected ?array $roworder = null;

    /**
     * Returns true if the response has been selected for that row and column.
     *
     * @param array $response the response data.
     * @param int $rowkey The row key.
     * @param int $colnumber The column key.
     * @return bool
     */
    abstract public function is_choice_selected(array $response, int $rowkey, int $colnumber): bool;

    #[\Override]
    abstract public function is_same_response(array $prevresponse, array $newresponse);

    #[\Override]
    abstract public function grade_response(array $response);

    #[\Override]
    public function start_attempt(question_attempt_step $step, $variant) {
        $this->roworder = array_keys($this->rows);
        if ($this->shuffleanswers) {
            shuffle($this->roworder);
        }
        $step->set_qt_var('_roworder', implode(',', $this->roworder));
    }

    #[\Override]
    public function apply_attempt_state(question_attempt_step $step) {
        $this->roworder = explode(',', $step->get_qt_var('_roworder'));
    }

    /**
     * Returns the roworder of the question being displayed.
     *
     * @param question_attempt $qa
     * @return array|null
     */
    public function get_order(question_attempt $qa): ?array {
        $this->init_roworder($qa);
        return $this->roworder;
    }

    /**
     * Sets the roworder from the question_attempt.
     *
     * @param question_attempt $qa
     */
    protected function init_roworder(question_attempt $qa) {
        if (is_null($this->roworder)) {
            $this->roworder = explode(',', $qa->get_step(0)->get_qt_var('_roworder'));
        }
    }

    #[\Override]
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

    #[\Override]
    public function get_validation_error(array $response): string {
        if ($this->is_complete_response($response)) {
            return '';
        }
        return get_string('pleaseananswerallparts', 'qtype_oumatrix');
    }

    #[\Override]
    public function validate_can_regrade_with_other_version(question_definition $otherversion): ?string {
        $basemessage = parent::validate_can_regrade_with_other_version($otherversion);
        if ($basemessage) {
            return $basemessage;
        }

        if (count($this->columns) != count($otherversion->columns)) {
            return get_string('regradeissuenumcolumnschanged', 'qtype_oumatrix');
        }

        if (count($this->rows) != count($otherversion->rows)) {
            return get_string('regradeissuenumrowschanged', 'qtype_oumatrix');
        }

        return null;
    }

    /**
     * Check whether question has any specific feedback.
     *
     * @return bool
     */
    public function has_specific_feedback(): bool {
        foreach ($this->rows as $row) {
            if (trim($row->feedback) !== '') {
                return true;
            }
        }
        return false;
    }
}

/**
 * Class that represents an oumatrix question for single choice.
 */
class qtype_oumatrix_single extends qtype_oumatrix_base {

    #[\Override]
    public function get_renderer(moodle_page $page) {
        return $page->get_renderer('qtype_oumatrix', 'single');
    }

    #[\Override]
    public function get_expected_data(): array {
        $expected = [];
        foreach ($this->rows as $row) {
            $expected[$this->field($row->number - 1)] = PARAM_INT;
        }
        return $expected;
    }

    #[\Override]
    public function is_choice_selected(array $response, int $rowkey, int $colnumber): bool {
        $responsekey = $this->field($rowkey);
        if (array_key_exists($responsekey, $response)) {
            return ((string) $response[$responsekey]) == $colnumber;
        }
        return false;
    }

    #[\Override]
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
     * @param int $rowkey The row key.
     * @return string The answer key name.
     */
    protected function field(int $rowkey): string {
        return 'rowanswers' . $rowkey;
    }

    #[\Override]
    public function get_correct_response(): array {
        $response = [];
        foreach ($this->roworder as $key => $rownumber) {
            $row = $this->rows[$rownumber];
            foreach ($row->correctanswers as $colnum => $notused) {
                // Get the corresponding column number associated with the column key.
                $response[$this->field($key)] = $colnum;
            }
        }
        return $response;
    }

    #[\Override]
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

    #[\Override]
    public function is_complete_response(array $response): bool {
        foreach ($this->roworder as $key => $rownumber) {
            $fieldname = $this->field($key);
            if (!array_key_exists($fieldname, $response)) {
                return false;
            }
        }
        return true;
    }

    #[\Override]
    public function is_gradable_response(array $response): bool {
        foreach ($this->roworder as $key => $rownumber) {
            $fieldname = $this->field($key);
            if (array_key_exists($fieldname, $response)) {
                return true;
            }
        }

        return false;
    }

    #[\Override]
    public function classify_response(array $response): array {
        $classifiedresponse = [];
        foreach ($this->roworder as $key => $rownumber) {
            $row = $this->rows[$rownumber];
            $partname = format_string($row->name);
            if (!array_key_exists($this->field($key), $response)) {
                $classifiedresponse[$partname] = question_classified_response::no_response();
                continue;
            }

            $selectedcolumn = $this->columns[$response[$this->field($key)]];
            $classifiedresponse[$partname] = new question_classified_response(
                $selectedcolumn->number,
                format_string($selectedcolumn->name),
                (int) array_key_exists($response[$this->field($key)], $row->correctanswers),
            );
        }

        return $classifiedresponse;
    }

    #[\Override]
    public function prepare_simulated_post_data($simulatedresponse): array {
        // Expected structure of $simulatedresponse is Row field name => Col name.
        // Each row must be present, in order.
        $postdata = [];
        $subquestions = array_keys($simulatedresponse);
        $answers = array_values($simulatedresponse);

        foreach ($this->roworder as $key => $rownumber) {
            $row = $this->rows[$rownumber];
            if ($row->name !== $subquestions[$key]) {
                continue;
            }
            if ($key === ($row->number - 1) && $row->name === $subquestions[$key]) {
                foreach ($this->columns as $column) {
                    if ($column->name !== $answers[$key]) {
                        continue;
                    }
                    $postdata[$this->field($key)] = $column->number;
                }
            }
        }
        return $postdata;
    }

    #[\Override]
    public function grade_response(array $response): array {
        // Retrieve the number of right responses and the total number of responses.
        [$numrightparts, $total] = $this->get_num_parts_right($response);
        $fraction = $numrightparts / $total;
        return [$fraction, question_state::graded_state_for_fraction($fraction)];
    }

    #[\Override]
    public function get_num_parts_right(array $response): array {
        $numright = 0;
        foreach ($this->roworder as $key => $rownumber) {
            $row = $this->rows[$rownumber];
            if (array_key_exists($this->field($key), $response) &&
                    array_key_exists($response[$this->field($key)], $row->correctanswers)) {
                $numright++;
            }
        }
        return [$numright, count($this->rows)];
    }
}

    /**
     * Class that represents a oumatrix question for multiple response.
     */
class qtype_oumatrix_multiple extends qtype_oumatrix_base {

    #[\Override]
    public function get_renderer(moodle_page $page) {
        return $page->get_renderer('qtype_oumatrix', 'multiple');
    }

    #[\Override]
    public function get_expected_data(): array {
        $expected = [];
        foreach ($this->rows as $row) {
            foreach ($this->columns as $column) {
                $expected[$this->field($row->number - 1, $column->number)] = PARAM_INT;
            }
        }
        return $expected;
    }

    #[\Override]
    public function is_choice_selected(array $response, int $rowkey, int $colnumber): bool {
        $responsekey = $this->field($rowkey, $colnumber);
        if (array_key_exists($responsekey, $response)) {
            return ((string) $response[$responsekey]) == 1;
        }
        return false;
    }

    /**
     * Answer field name.
     *
     * @param int $rowkey The row key.
     * @param int $columnnumber The column number.
     * @return string The answer key name.
     */
    protected function field(int $rowkey, int $columnnumber): string {
        return 'rowanswers' . $rowkey . '_' . $columnnumber;
    }

    #[\Override]
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

    #[\Override]
    public function get_correct_response(): ?array {
        $response = [];
        foreach ($this->roworder as $key => $rownumber) {
            $row = $this->rows[$rownumber];
            foreach ($row->correctanswers as $colnum => $answer) {
                // Get the corresponding column number associated with the column key.
                $response[$this->field($key, $colnum)] = $answer;
            }
        }
        return $response;
    }

    #[\Override]
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

    #[\Override]
    public function is_complete_response(array $response): bool {
        foreach ($this->roworder as $key => $rownumber) {
            $inputresponse = false;
            foreach ($this->columns as $column) {
                $fieldname = $this->field($key, $column->number);
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

    #[\Override]
    public function is_gradable_response(array $response): bool {
        foreach ($this->roworder as $key => $rownumber) {
            foreach ($this->columns as $column) {
                $fieldname = $this->field($key, $column->number);
                if (array_key_exists($fieldname, $response)) {
                    return true;
                }
            }
        }
        return false;
    }

    #[\Override]
    public function classify_response(array $response) {
        $classifiedresponse = [];
        foreach ($this->roworder as $rowkey => $rownumber) {
            $row = $this->rows[$rownumber];
            $rowname = format_string($row->name);

            foreach ($this->columns as $column) {
                if ($this->is_choice_selected($response, $rowkey, $column->number)) {
                    $classifiedresponse[$rowname . ': ' . format_string($column->name)] =
                        new question_classified_response(
                            1,
                            get_string('selected', 'qtype_oumatrix'),
                            array_key_exists($column->number, $row->correctanswers) / count($row->correctanswers),
                        );
                }
            }
        }

        return $classifiedresponse;
    }

    #[\Override]
    public function prepare_simulated_post_data($simulatedresponse): array {
        $postdata = [];
        $subquestions = array_keys($simulatedresponse);
        $answers = array_values($simulatedresponse);
        foreach ($this->roworder as $key => $rowid) {
            $row = $this->rows[$rowid];
            $rowanswers = $answers[$key];
            if ($key === ($row->number - 1) && $row->name === $subquestions[$key]) {
                foreach ($this->columns as $colid => $column) {
                    // Set the field to '0' initially.
                    $postdata[$this->field($key, $column->number)] = '0';
                    foreach ($rowanswers as $colnumber => $colname) {
                        if ($row->name === $subquestions[$key] &&
                                $column->number === $colnumber && $column->name === $colname) {
                            // Set the field to '1' if it has been ticked..
                            $postdata[$this->field($key, $column->number)] = '1';
                        }
                    }
                }
            }
        }
        return $postdata;
    }

    #[\Override]
    public function grade_response(array $response): array {
        // Retrieve the number of right responses and the total number of responses.
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
            foreach ($this->columns as $column) {
                $reponsekey = $this->field($rowkey, $column->number);
                if (array_key_exists($reponsekey, $response)) {
                    if (array_key_exists($column->number, $row->correctanswers)) {
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
            foreach ($this->columns as $column) {
                $reponsekey = $this->field($rowkey, $column->number);
                if (array_key_exists($reponsekey, $response) && array_key_exists($column->number, $row->correctanswers)) {
                    $rightresponse++;
                }
            }
            $numcorrectanswers += count($row->correctanswers);
        }
        return [$rightresponse, $numcorrectanswers];
    }

    /**
     * Get the number of selected choices in a response.
     *
     * @param array $response responses, as returned by
     *      {@link question_attempt_step::get_qt_data()}.
     * @return int the number of choices that were selected in this response.
     */
    public function get_num_selected_choices(array $response): int {
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
     * Returns the count of correct answers for the question.
     *
     * @return int the number of choices that are correct.
     */
    public function get_num_correct_choices(): int {
        $numcorrect = 0;
        foreach ($this->rows as $row) {
            $numcorrect += count($row->correctanswers);
        }
        return $numcorrect;
    }
}
