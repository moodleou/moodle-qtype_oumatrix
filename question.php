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

    /** @var columns[] The columns (answers) object. */
    public $columns;

    /** @var rows[] The rows (subquestions) object. */
    public $rows;

    /** @var int The number of columns. */
    public $numcolumns;

    /** @var int The number of rows. */
    public $numrows;

    //public abstract function get_response(question_attempt $qa);
    public abstract function is_choice_selected($colname, $response, $rowkey, $colkey);

    public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) {
        return parent::check_file_access($qa, $options, $component, $filearea, $args, $forcedownload);
    }

    public function is_same_response(array $prevresponse, array $newresponse): bool {
        // TODO:
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
        return 'sub' . $rowkey;
    }

    public function get_correct_response(): ?array {
        $response = [];
        for ($i = 0; $i < count($this->answers); $i++) {
            $response[$this->field($i)] = $this->answers[$i]->answer;
        }
        return $response;
    }

    public function summarise_response(array $response): ?string {
        return "from base";
    }

    public function is_complete_response(array $response): bool {
    }

    public function is_gradable_response(array $response): bool {

    }

    public function get_validation_error(array $response): string {
        if ($this->is_complete_response($response)) {
            return '';
        }
        return get_string('pleaseananswerallparts', 'qtype_crossword');
    }

    public function grade_response(array $response): array {
        // Retrieve a number of right answers and total answers.
        //[$numrightparts, $total] = $this->get_num_parts_right($response);
        //// Retrieve a number of wrong accent numbers.
        //$numpartialparts = $this->get_num_parts_partial($response);
        //// Calculate fraction.
        //$fraction = ($numrightparts + $numpartialparts - $numpartialparts * $this->accentpenalty)
        //        / $total;
        //
        //return [$fraction, question_state::graded_state_for_fraction($fraction)];
        return [];
    }

    public function get_num_parts_right(array $response): array {
        $numright = 0;
        foreach ($this->answers as $key => $answer) {
            if ($this->is_full_fraction($answer, $response[$this->field($key)])) {
                $numright++;
            }
        }
        return [$numright, count($this->answers)];
    }

    /**
     * Get number of words in the response which are not right, but are if you ignore accents.
     *
     * @param array $response The answer list.
     * @return int The number of partial answers.
     */
    public function get_num_parts_partial(array $response): int {
        $numpartial = 0;
        foreach ($this->answers as $key => $answer) {
            if ($this->is_partial_fraction($answer, $response[$this->field($key)])) {
                $numpartial++;
            }
        }

        return $numpartial;
    }

    public function clear_wrong_from_response(array $response): array {
        foreach ($this->answers as $key => $answer) {
            if (isset($response[$this->field($key)]) && !$this->is_full_fraction($answer, $response[$this->field($key)])) {
                $response[$this->field($key)] = '';
            }
        }
        return $response;
    }

    /**
     * Verify if the response to one clue should receive full marks.
     *
     * The answer must satisfy at least one of two conditions: Either
     * Condition 1 - the answer is completely correct, including accent characters; or
     * Condition 2 - the answer has the same letter characters but incorrect accent characters
     * and the accent grading type of the question is disregarded.
     *
     * @param qtype_crossword\answer $answer The answer object.
     * @param string $responseword The inputanswer need to calculate.
     * @return bool The result of answer. True if it's correct.
     */
    public function is_full_fraction(qtype_crossword\answer $answer, string $responseword): bool {
        return $answer->is_correct($responseword) || ($this->accentgradingtype === \qtype_crossword::ACCENT_GRADING_IGNORE &&
                        $answer->is_wrong_accents($responseword));
    }

    /**
     * Verify if if the response to one clue should receive partial marks.
     *
     * The answer must satisfy two conditions: Both
     * Condition 1 - the answer is wrong accent only; and
     * Condition 2 - the accent grading type of the question is penalty.
     *
     * @param qtype_crossword\answer $answer The answer object.
     * @param string $responseword The inputanswer need to calculate.
     * @return bool The result of answer. True if it's partial correct.
     */
    public function is_partial_fraction(qtype_crossword\answer $answer, string $responseword): bool {
        return $this->accentgradingtype === \qtype_crossword::ACCENT_GRADING_PENALTY &&
                $answer->is_wrong_accents($responseword);
    }

    /**
     * Calculate fraction of answer.
     *
     * @param answer $answer One of the clues.
     * @param string $responseword The the response given to that clue.
     * @return float the fraction for that word.
     */
    public function calculate_fraction_for_answer(answer $answer, string $responseword): float {

        if ($this->is_full_fraction($answer, $responseword)) {
            return 1;
        } else {
            if ($this->is_partial_fraction($answer, $responseword)) {
                return 1 - $this->accentpenalty;
            } else {
                return 0;
            }
        }
    }

    public function validate_can_regrade_with_other_version(question_definition $otherversion): ?string {
        $basemessage = parent::validate_can_regrade_with_other_version($otherversion);
        if ($basemessage) {
            return $basemessage;
        }
        // TODO: this and other two folloing function are taken from multianswer. To be sorted

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


    public function apply_attempt_state(question_attempt_step $step) {
        //foreach ($this->subquestions as $i => $subq) {
        //    $subq->apply_attempt_state($this->get_substep($step, $i));
        //}
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
            if ($row->correctanswers != '') {
                foreach($this->columns as $column) {
                    $expected[$this->field($row->number, $column->number)] = PARAM_RAW;
                }
            }
        }
        return $expected;
    }

    public function is_choice_selected($colname, $response, $rowkey, $colkey) {
        if($response) {
            return (string) $response[$this->field($rowkey)] === $colname;
        }
    }

    /*public function prepare_simulated_post_data($simulatedresponse) {
        print_object("simulated response&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&");
        print_object($simulatedresponse);
        return $simulatedresponse;
    }*/

    public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) {
        return parent::check_file_access($qa, $options, $component, $filearea, $args, $forcedownload);
    }

    public function is_same_response(array $prevresponse, array $newresponse): bool {
        return  parent::is_same_response($prevresponse, $newresponse);
    }

    /**
     * Answer field name.
     *
     * @param int $rowkey The row key number.
     * @param int $columnkey The column key number.
     * @return string The answer key name.
     */
    protected function field(int $rowkey, int $columnkey = 0): string {
        return 'rowanswers' . $rowkey;
    }

    public function get_correct_response(): ?array {
        $response = [];
        foreach ($this->rows as $row) {
            if ($row->correctanswers != '') {
                $response[$this->field($row->number)] = $this->columns[array_key_first($row->correctanswers)]->name;
            }
        }
        return $response;
    }

    public function summarise_response(array $response): ?string {
        $responsewords = [];
        foreach ($this->rows as $row) {
            $fieldname = $this->field($row->number);
            if (array_key_exists($fieldname, $response) && $response[$fieldname]) {
                $responsewords[] = $row->name . " => " . $response[$fieldname];
            }
        }
        return implode('; ', $responsewords);
    }

    public function is_complete_response(array $response): bool {
        return !empty($response);
    }

    public function is_gradable_response(array $response): bool {
        return true;
    }

    public function get_validation_error(array $response): string {
        if ($this->is_complete_response($response)) {
            return '';
        }
        return get_string('pleaseananswerallparts', 'qtype_crossword');
    }

    public function grade_response(array $response): array {
        $fraction = 1;
        return [$fraction, question_state::graded_state_for_fraction($fraction)];
    }

    public function get_num_parts_right(array $response): array {

        $numright = 0;
        foreach ($this->answers as $key => $answer) {
            if ($this->is_full_fraction($answer, $response[$this->field($key)])) {
                $numright++;
            }
        }
        return [$numright, count($this->answers)];
    }

    /**
     * Get number of words in the response which are not right, but are if you ignore accents.
     *
     * @param array $response The answer list.
     * @return int The number of partial answers.
     */
    public function get_num_parts_partial(array $response): int {
        $numpartial = 0;
        foreach ($this->answers as $key => $answer) {
            if ($this->is_partial_fraction($answer, $response[$this->field($key)])) {
                $numpartial++;
            }
        }

        return $numpartial;
    }

    public function clear_wrong_from_response(array $response): array {
        foreach ($this->answers as $key => $answer) {
            if (isset($response[$this->field($key)]) && !$this->is_full_fraction($answer, $response[$this->field($key)])) {
                $response[$this->field($key)] = '';
            }
        }
        return $response;
    }

    /**
     * Verify if the response to one clue should receive full marks.
     *
     * The answer must satisfy at least one of two conditions: Either
     * Condition 1 - the answer is completely correct, including accent characters; or
     * Condition 2 - the answer has the same letter characters but incorrect accent characters
     * and the accent grading type of the question is disregarded.
     *
     * @param qtype_crossword\answer $answer The answer object.
     * @param string $responseword The inputanswer need to calculate.
     * @return bool The result of answer. True if it's correct.
     */
    public function is_full_fraction(qtype_crossword\answer $answer, string $responseword): bool {
        return $answer->is_correct($responseword) || ($this->accentgradingtype === \qtype_crossword::ACCENT_GRADING_IGNORE &&
                        $answer->is_wrong_accents($responseword));
    }

    /**
     * Verify if if the response to one clue should receive partial marks.
     *
     * The answer must satisfy two conditions: Both
     * Condition 1 - the answer is wrong accent only; and
     * Condition 2 - the accent grading type of the question is penalty.
     *
     * @param qtype_crossword\answer $answer The answer object.
     * @param string $responseword The inputanswer need to calculate.
     * @return bool The result of answer. True if it's partial correct.
     */
    public function is_partial_fraction(qtype_crossword\answer $answer, string $responseword): bool {
        return $this->accentgradingtype === \qtype_crossword::ACCENT_GRADING_PENALTY &&
                $answer->is_wrong_accents($responseword);
    }

    /**
     * Calculate fraction of answer.
     *
     * @param answer $answer One of the clues.
     * @param string $responseword The the response given to that clue.
     * @return float the fraction for that word.
     */
    public function calculate_fraction_for_answer(answer $answer, string $responseword): float {

        if ($this->is_full_fraction($answer, $responseword)) {
            return 1;
        } else {
            if ($this->is_partial_fraction($answer, $responseword)) {
                return 1 - $this->accentpenalty;
            } else {
                return 0;
            }
        }
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
                foreach($this->columns as $column) {
                    $expected[$this->field($row->number, $column->number)] = PARAM_RAW;
                }
            }
        }
        return $expected;

    }

    public function is_choice_selected($colname, $response, $rowkey, $colkey) {
        if($response) {
            $fieldname = $this->field($rowkey, $colkey);
            if($response[$fieldname] == "1" || $response[$fieldname] == $colname) {
                return true;
            }
            return false;
        }
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
        print_object("simulated response&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&");
        print_object($simulatedresponse);
        return $simulatedresponse;
    }

    public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) {
        return parent::check_file_access($qa, $options, $component, $filearea, $args, $forcedownload);
    }

    public function is_same_response(array $prevresponse, array $newresponse): bool {
        return  parent::is_same_response($prevresponse, $newresponse);
        /*if (!$this->is_complete_response($prevresponse)) {
            $prevresponse = [];
        }
        if (!$this->is_complete_response($newresponse)) {
            $newresponse = [];
        }
        foreach ($this->rows as $k => $row) {
            print_object('$row -------------');
            print_object($row);
            // TODO:
            foreach ($row->correctanswers as $key =>$value) {
                $fieldname = $this->field($key);
                if (!question_utils::arrays_same_at_key(
                        $prevresponse, $newresponse, $fieldname)) {
                    return false;
                }
                return question_utils::arrays_same_at_key($prevresponse, $newresponse, 'answer');
            }
        }
        return true;*/
    }

    public function get_correct_response(): ?array {
        print_object("get_correct_response");
        print_object($this);
        $response = [];
        foreach ($this->rows as $row) {
            if ($row->correctanswers != '') {
                foreach ($row->correctanswers as $colkey => $answer) {
                    $response[$this->field($row->number, $colkey)] = $answer;
                }
            }
        }
        print_object("=====================================");
        print_object($response);
        return $response;
    }

    public function summarise_response(array $response): ?string {
        $responsewords = [];
        print_object("summarise_response");
        print_object($response);
        print_object($this);

        foreach ($this->rows as $row) {
            $rowresponse = $row->name . " => ";
            $answers = [];
            foreach ($this->columns as $col) {
                $fieldname = $this->field($row->number, $col->number);
                if (array_key_exists($fieldname, $response) && $response[$fieldname]) {
                    $answers[] =  $col->name;
                }
            }
            $rowresponse = $rowresponse . implode(', ', $answers);
            $responsewords[] = $rowresponse;
        }
        return implode('; ', $responsewords);
    }

    public function is_complete_response(array $response): bool {
        foreach ($this->rows as $row) {
            foreach ($this->columns as $col) {
                $fieldname = $this->field($row->number, $col->number);
                if (!empty($response[$fieldname] && $response[$fieldname] != "0")) {
                    return true;
                }
            }
        }
        return false;
    }

    public function is_gradable_response(array $response): bool {
       return true;
    }

    public function get_validation_error(array $response): string {
        if ($this->is_complete_response($response)) {
            return '';
        }
        return get_string('pleaseananswerallrows', 'qtype_oumatrix');
    }

    public function grade_response(array $response): array {
        // Retrieve a number of right answers and total answers.
        //[$numrightparts, $total] = $this->get_num_parts_right($response);
        //// Retrieve a number of wrong accent numbers.
        //$numpartialparts = $this->get_num_parts_partial($response);
        //// Calculate fraction.
        //$fraction = ($numrightparts + $numpartialparts - $numpartialparts * $this->accentpenalty)
        //        / $total;
        //
        //return [$fraction, question_state::graded_state_for_fraction($fraction)];
        print_object("grade_response££££££££££££££££££££££££££££");
        print_object($response);
        $fraction = 1;
        return [$fraction, question_state::graded_state_for_fraction($fraction)];
    }

    public function get_num_parts_right(array $response): array {
        $numright = 0;
        foreach ($this->answers as $key => $answer) {
            if ($this->is_full_fraction($answer, $response[$this->field($key)])) {
                $numright++;
            }
        }
        return [$numright, count($this->answers)];
    }

    /**
     * Get number of words in the response which are not right, but are if you ignore accents.
     *
     * @param array $response The answer list.
     * @return int The number of partial answers.
     */
    public function get_num_parts_partial(array $response): int {
        $numpartial = 0;
        foreach ($this->answers as $key => $answer) {
            if ($this->is_partial_fraction($answer, $response[$this->field($key)])) {
                $numpartial++;
            }
        }

        return $numpartial;
    }

    public function clear_wrong_from_response(array $response): array {
        foreach ($this->answers as $key => $answer) {
            if (isset($response[$this->field($key)]) && !$this->is_full_fraction($answer, $response[$this->field($key)])) {
                $response[$this->field($key)] = '';
            }
        }
        return $response;
    }

    /**
     * Verify if the response to one clue should receive full marks.
     *
     * The answer must satisfy at least one of two conditions: Either
     * Condition 1 - the answer is completely correct, including accent characters; or
     * Condition 2 - the answer has the same letter characters but incorrect accent characters
     * and the accent grading type of the question is disregarded.
     *
     * @param qtype_crossword\answer $answer The answer object.
     * @param string $responseword The inputanswer need to calculate.
     * @return bool The result of answer. True if it's correct.
     */
    public function is_full_fraction(qtype_crossword\answer $answer, string $responseword): bool {
        return $answer->is_correct($responseword) || ($this->accentgradingtype === \qtype_crossword::ACCENT_GRADING_IGNORE &&
                        $answer->is_wrong_accents($responseword));
    }

    /**
     * Verify if if the response to one clue should receive partial marks.
     *
     * The answer must satisfy two conditions: Both
     * Condition 1 - the answer is wrong accent only; and
     * Condition 2 - the accent grading type of the question is penalty.
     *
     * @param qtype_crossword\answer $answer The answer object.
     * @param string $responseword The inputanswer need to calculate.
     * @return bool The result of answer. True if it's partial correct.
     */
    public function is_partial_fraction(qtype_crossword\answer $answer, string $responseword): bool {
        return $this->accentgradingtype === \qtype_crossword::ACCENT_GRADING_PENALTY &&
                $answer->is_wrong_accents($responseword);
    }
}
