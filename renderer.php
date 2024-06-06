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
 * OU matrix question renderer classes.
 *
 * @package   qtype_oumatrix
 * @copyright 2023 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use qtype_oumatrix\column;

defined('MOODLE_INTERNAL') || die();

/**
 * Base class for generating the bits of output common to oumatrix single choice and multiple response questions.
 *
 * @copyright 2023 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class qtype_oumatrix_renderer_base extends qtype_with_combined_feedback_renderer {

    /**
     * Returns the value as radio/checkbox based on the single choice or multiple response question.
     *
     * @return string
     */
    abstract protected function get_input_type(): string;

    /**
     * Returns the name for the row.
     *
     * @param question_attempt The question attempt
     * @param int The row key
     * @param int The column number
     * @return string
     */
    abstract protected function get_input_name(question_attempt $qa, int $rowkey, int $columnnumber): string;

    /**
     * Returns the value for the radio/checkbox.
     *
     * @param int The column number
     * @return string
     */
    abstract protected function get_input_value(int $value): string;

    /**
     * Returns the id's attribute for the radio/checkbox.
     *
     * @param question_attempt The question attempt
     * @param int The row key
     * @param int The column number
     * @return string
     */
    abstract protected function get_input_id(question_attempt $qa, int $rowkey, int $columnnumber): string;

    /**
     * Whether a choice should be considered right or wrong.
     *
     * @param question_definition $question the question
     * @param int $rowkey representing the row.
     * @param int $columnnumber representing the column.
     * @return int returns 1 or 0.
     */
    protected function is_right(question_definition $question, int $rowkey, int $columnnumber): int {
        $row = $question->rows[$rowkey];
        foreach ($question->columns as $column) {
            if ($column->number == $columnnumber && array_key_exists($column->number, $row->correctanswers)) {
                return 1;
            }
        }
        return 0;
    }

    #[\Override]
    protected function feedback_class($fraction) {
        return question_state::graded_state_for_fraction($fraction)->get_feedback_class();
    }

    #[\Override]
    protected function feedback_image($fraction, $selected = true) {
        $feedbackclass = question_state::graded_state_for_fraction($fraction)->get_feedback_class();

        // We have to add position-absolute to the class attribute to keep checkboxes/radio buttons aligned
        // when the feedback icon is displayed.
        return $this->output->pix_icon('i/grade_' . $feedbackclass, get_string($feedbackclass, 'question'), '',
            ['class' => 'position-absolute ml-1 mt-1']);
    }

    #[\Override]
    public function formulation_and_controls(question_attempt $qa,
            question_display_options $options) {
        $question = $qa->get_question();
        $result = '';

        $result .= html_writer::tag('div', $question->format_questiontext($qa), ['class' => 'qtext']);

        // Display the matrix.
        $result .= $this->matrix_table($qa, $options);

        if ($qa->get_state() == question_state::$invalid) {
            $result .= html_writer::nonempty_tag('div',
                $question->get_validation_error($qa->get_last_qt_data()), ['class' => 'validationerror']);
        }

        return $result;
    }

    /**
     * Returns the matrix question for displaying in the table format.
     *
     * @param question_attempt $qa
     * @param question_display_options $options
     * @return string
     */
    public function matrix_table(question_attempt $qa, question_display_options $options): string {

        $question = $qa->get_question();
        $response = $qa->get_last_qt_data();
        $caption = $options->add_question_identifier_to_label(get_string('answer'), false, true);

        $table = html_writer::start_tag('fieldset', ['class' => 'ablock no-overflow']);
        $table .= html_writer::tag('legend', $caption, ['class' => 'sr-only']);
        $table .= html_writer::start_tag('div', ['class' => 'answer']);

        // Create table and caption.
        $table .= html_writer::start_tag('table', ['class' => 'generaltable']);
        $table .= html_writer::tag('caption', $caption, ['class' => 'sr-only']);

        // Creating the matrix column headers.
        $table .= html_writer::start_tag('thead');
        $table .= html_writer::start_tag('tr');
        $table .= html_writer::tag('th', '', ['scope' => 'col', 'class' => 'subquestion']);
        $index = 0;
        foreach ($question->columns as $value) {
            $table .= html_writer::tag('th', html_writer::span(format_string($value->name), 'answer_col', ['id' => 'col' . $index]),
                ['scope' => 'col', 'class' => 'align-middle text-center']);
            $index += 1;
        }
        // Add feedback header only when specific feedback is set to be displayed and provided at least for one row.
        if ($options->feedback && $question->has_specific_feedback()) {
            $table .= html_writer::tag('th', html_writer::span(get_string('feedback', 'question'),
                'answer_col', ['id' => 'col' . $index]), ['scope' => 'col', 'class' => 'rowfeedback align-middle']);
        }
        $table .= html_writer::end_tag('tr');
        $table .= html_writer::end_tag('thead');

        if ($options->readonly) {
            $inputattributes['disabled'] = 'disabled';
        }
        // Set the input attribute based on the single or multiple answer mode.
        $inputattributes['type'] = $this->get_input_type();

        // Adding table rows for the sub-questions.
        foreach ($question->get_order($qa) as $rowkey => $rowid) {

            $row = $question->rows[$rowid];
            $rownewid = 'row'. $rowkey;
            $feedback = '';

            $table .= html_writer::start_tag('tr');
            $table .= html_writer::tag('th', html_writer::span(format_string($row->name), '', ['id' => $rownewid]),
                ['class' => 'subquestion align-middle', 'scope' => 'row']);

            for ($c = 1; $c <= count($question->columns); $c++) {
                $inputattributes['name'] = $this->get_input_name($qa, $rowkey, $c);
                $inputattributes['value'] = $this->get_input_value($c);
                $inputattributes['id'] = $this->get_input_id($qa, $rowkey, $c);
                $inputattributes['aria-labelledby'] = 'col' . ($c - 1). ' ' . $rownewid;
                $inputattributes['class'] = 'align-middle';
                $isselected = $question->is_choice_selected($response, $rowkey, $c);

                // Get the row per feedback.
                if ($options->feedback && $feedback == '' &&
                        $isselected && trim($row->feedback)) {
                    $feedback = html_writer::tag('div',
                        $question->make_html_inline($question->format_text($row->feedback, $row->feedbackformat,
                            $qa, 'qtype_oumatrix', 'feedback', $row->id)),
                        ['class' => 'specificfeedback text-left px-2 m-0']);
                }

                $class = '';
                $feedbackimg = '';

                // Select the radio button or checkbox and display feedback image.
                if ($isselected) {
                    $inputattributes['checked'] = 'checked';
                    if ($options->correctness) {
                        $feedbackimg = html_writer::span($this->feedback_image($this->is_right($question, $rowid, $c)));
                        $class .= ' ' . $this->feedback_class($this->is_right($question, $rowid, $c));
                    }
                } else {
                    unset($inputattributes['checked']);
                }

                // Write row and its attributes.
                $button = html_writer::empty_tag('input', $inputattributes);
                $answered = html_writer::tag('label', $button . $feedbackimg,
                    ['class' => "position-relative d-inline-block w-100 m-0"]);

                $table .= html_writer::tag('td', $answered, ['class' => "$class matrixanswer align-middle text-center"]);
            }
            if ($options->feedback && $question->has_specific_feedback()) {
                $table .= html_writer::tag('td', $feedback);
            }
            $table .= html_writer::end_tag('tr');
        }

        $table .= html_writer::end_tag('table');
        $table .= html_writer::end_tag('div');
        $table .= html_writer::end_tag('fieldset');

        return $table;
    }

    #[\Override]
    public function specific_feedback(question_attempt $qa) {
        return $this->combined_feedback($qa);
    }

    /**
     * Function returns string based on number of correct answers
     *
     * @param array $right An Array of correct responses to the current question
     * @return string based on number of correct responses
     */
    protected function correct_choices(array $right): string {
        // Return appropriate string for single/multiple correct answer(s).
        $correctanswers = "<br>" . implode("<br>", $right);
        if (count($right) == 1) {
            return get_string('correctansweris', 'qtype_oumatrix', $correctanswers);
        } else if (count($right) > 2) {
            return get_string('correctanswersare', 'qtype_oumatrix', $correctanswers);
        } else {
            return "";
        }
    }
}

/**
 * Subclass for generating the bits of output specific to oumatrix single choice questions.
 *
 * @copyright  2023 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_oumatrix_single_renderer extends qtype_oumatrix_renderer_base {

    #[\Override]
    protected function get_input_type(): string {
        return 'radio';
    }

    #[\Override]
    protected function get_input_name(question_attempt $qa, int $rowkey, int $columnnumber): string {
        return $qa->get_qt_field_name('rowanswers' . $rowkey);
    }

    #[\Override]
    protected function get_input_value(int $value): string {
        return $value;
    }

    #[\Override]
    protected function get_input_id(question_attempt $qa, int $rowkey, int $columnnumber): string {
        return $qa->get_qt_field_name('rowanswers' . $rowkey . '_' . $columnnumber);
    }

    #[\Override]
    public function correct_response(question_attempt $qa) {
        $question = $qa->get_question();
        $right = [];
        foreach ($question->rows as $row) {
            $right[] = $row->name . ' → ' . $question->columns[array_key_first($row->correctanswers)]->name;
        }
        return $this->correct_choices($right);
    }

    #[\Override]
    protected function num_parts_correct(question_attempt $qa): string {
        $a = new stdClass();
        list($a->num, $a->outof) = $qa->get_question()->get_num_parts_right(
                $qa->get_last_qt_data());
        if (is_null($a->outof)) {
            return '';
        } else if ($a->num == 1) {
            return html_writer::tag('p', get_string('yougot1rightsubquestion', 'qtype_oumatrix'));
        } else {
            $f = new NumberFormatter(current_language(), NumberFormatter::SPELLOUT);
            $a->num = $f->format($a->num);
            return html_writer::tag('p', get_string('yougotnrightsubquestion', 'qtype_oumatrix', $a));
        }
    }
}

/**
 * Subclass for generating the bits of output specific to oumatrix multiple choice questions.
 *
 * @copyright  2023 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_oumatrix_multiple_renderer extends qtype_oumatrix_renderer_base {

    #[\Override]
    protected function get_input_type(): string {
        return 'checkbox';
    }

    #[\Override]
    protected function get_input_name(question_attempt $qa, int $rowkey, int $columnnumber): string {
        return $qa->get_qt_field_name('rowanswers' . $rowkey . '_' . $columnnumber);
    }

    #[\Override]
    protected function get_input_value(int $value): string {
        return "1";
    }

    #[\Override]
    protected function get_input_id(question_attempt $qa, int $rowkey, int $columnnumber): string {
        return $this->get_input_name($qa, $rowkey, $columnnumber);
    }

    #[\Override]
    public function correct_response(question_attempt $qa) {
        $question = $qa->get_question();
        foreach ($question->rows as $row) {
            // Get the correct row.
            $rowanswer = $row->name . ' → ';
            $answers = [];
            if ($row->correctanswers != '') {
                foreach ($row->correctanswers as $columnnumber => $notused) {
                    $answers[] = $question->columns[$columnnumber]->name;
                }
                $rowanswer .= implode(', ', $answers);
                $rightanswers[] = $rowanswer;
            }
        }
        return $this->correct_choices($rightanswers);
    }

    #[\Override]
    protected function num_parts_correct(question_attempt $qa): string {
        if ($qa->get_question()->get_num_selected_choices($qa->get_last_qt_data()) >
                $qa->get_question()->get_num_correct_choices()) {
            return html_writer::tag('p', get_string('toomanyselected', 'qtype_oumatrix'));
        }

        $a = new stdClass();
        if ($qa->get_question()->grademethod == 'allnone') {
            list($a->num, $a->outof) = $qa->get_question()->get_num_grade_allornone($qa->get_last_qt_data());
            if (is_null($a->outof)) {
                return '';
            }
            if ($a->num == 1) {
                return html_writer::tag('p', get_string('yougot1rightsubquestion', 'qtype_oumatrix'));
            }
            $f = new NumberFormatter(current_language(), NumberFormatter::SPELLOUT);
            $a->num = $f->format($a->num);
            return html_writer::tag('p', get_string('yougotnrightsubquestion', 'qtype_oumatrix', $a));
        } else {
            list($a->num, $a->outof) = $qa->get_question()->get_num_parts_grade_partial($qa->get_last_qt_data());
            if (is_null($a->outof)) {
                return '';
            }
            if ($a->num == 1) {
                return html_writer::tag('p', get_string('yougot1right', 'qtype_oumatrix'));
            }
            $f = new NumberFormatter(current_language(), NumberFormatter::SPELLOUT);
            $a->num = $f->format($a->num);
            return html_writer::tag('p', get_string('yougotnright', 'qtype_oumatrix', $a));
        }
    }
}
