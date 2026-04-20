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
     * @param question_attempt $qa The question attempt
     * @param int $rowkey The row key
     * @param int $columnnumber The column number
     * @return string
     */
    abstract protected function get_input_name(question_attempt $qa, int $rowkey, int $columnnumber): string;

    /**
     * Returns the value for the radio/checkbox.
     *
     * @param int $value The column number
     * @return string
     */
    abstract protected function get_input_value(int $value): string;

    /**
     * Returns the id's attribute for the radio/checkbox.
     *
     * @param question_attempt $qa The question attempt
     * @param int $rowkey The row key
     * @param int $columnnumber The column number
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
        return $this->output->pix_icon(
            'i/grade_' . $feedbackclass,
            get_string($feedbackclass, 'question'),
            '',
            ['class' => 'position-absolute ml-1 mt-1']
        );
    }

    #[\Override]
    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {
        $question = $qa->get_question();

        $result = '';
        $result .= html_writer::tag('div', $question->format_questiontext($qa), ['class' => 'qtext']);

        // Display the matrix.
        $result .= $this->matrix_table($qa, $options);

        if ($qa->get_state() == question_state::$invalid) {
            $result .= html_writer::nonempty_tag(
                'div',
                $question->get_validation_error($qa->get_last_qt_data()),
                ['class' => 'validationerror']
            );
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
        foreach ($question->get_colorder($qa) as $colkey => $colid) {
            $column = $question->columns[$colid];
            $table .= html_writer::tag(
                'th',
                html_writer::span(
                    question_utils::format_question_fragment($column->name, $this->page->context),
                    'answer_col',
                    ['id' => 'col' . $index]
                ),
                ['scope' => 'col', 'class' => 'align-middle text-center']
            );
            $index += 1;
        }
        // Add feedback header only when specific feedback is set to be displayed and provided at least for one row.
        if ($options->feedback && $question->has_specific_feedback()) {
            $table .= html_writer::tag(
                'th',
                html_writer::span(
                    get_string('feedback', 'question'),
                    'answer_col',
                    ['id' => 'col' . $index]
                ),
                ['scope' => 'col', 'class' => 'rowfeedback align-middle']
            );
        }
        $table .= html_writer::end_tag('tr');
        $table .= html_writer::end_tag('thead');

        if ($options->readonly) {
            $inputattributes['disabled'] = 'disabled';
        }
        // Set the input attribute based on the single or multiple answer mode.
        $inputattributes['type'] = $this->get_input_type();

        // Adding table rows for the sub-questions.
        $columncount = 0;
        foreach ($question->get_roworder($qa) as $rowkey => $rowid) {
            $row = $question->rows[$rowid];
            $rownewid = 'row' . $rowkey;
            $feedback = '';

            $table .= html_writer::start_tag('tr');
            $table .= html_writer::tag(
                'th',
                html_writer::span(
                    $this->number_in_style(
                        $columncount,
                        $question->questionnumbering
                    ) . question_utils::format_question_fragment(
                        $row->name,
                        $this->page->context
                    ),
                    '',
                    ['id' => $rownewid]
                ),
                ['class' => 'subquestion align-middle', 'scope' => 'row']
            );

            foreach ($question->get_colorder($qa) as $colkey => $colid) {
                $column = $question->columns[$colid];
                $inputattributes['name'] = $this->get_input_name($qa, $rowkey, $column->number);
                $inputattributes['value'] = $this->get_input_value($column->number);
                $inputattributes['id'] = $this->get_input_id($qa, $rowkey, $column->number);
                $inputattributes['aria-labelledby'] = 'col' . $colkey . ' ' . $rownewid;
                $inputattributes['class'] = 'align-middle';
                $isselected = $question->is_choice_selected($response, $rowkey, $column->number);

                // Get the row per feedback.
                if ($options->feedback && $feedback == '' && $isselected && trim($row->feedback)) {
                    $feedback = html_writer::tag(
                        'div',
                        $question->make_html_inline(
                            $question->format_text(
                                $row->feedback,
                                $row->feedbackformat,
                                $qa,
                                'qtype_oumatrix',
                                'feedback',
                                $row->id
                            )
                        ),
                        ['class' => 'specificfeedback text-left px-2 m-0']
                    );
                }

                $class = '';
                $feedbackimg = '';

                // Select the radio button or checkbox and display feedback image.
                if ($isselected) {
                    $inputattributes['checked'] = 'checked';
                    if ($options->correctness) {
                        $feedbackimg = html_writer::span(
                            $this->feedback_image(
                                $this->is_right($question, $rowid, $column->number)
                            )
                        );
                        $class .= ' ' . $this->feedback_class($this->is_right($question, $rowid, $column->number));
                    }
                } else {
                    unset($inputattributes['checked']);
                }

                // Write row and its attributes.
                $button = html_writer::empty_tag('input', $inputattributes);
                $answered = html_writer::tag(
                    'label',
                    $button . $feedbackimg,
                    ['class' => "position-relative d-inline-block w-100 m-0"]
                );

                $table .= html_writer::tag('td', $answered, ['class' => "$class matrixanswer align-middle text-center"]);
            }
            if ($options->feedback && $question->has_specific_feedback()) {
                $table .= html_writer::tag('td', $feedback);
            }
            $table .= html_writer::end_tag('tr');
            $columncount++;
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

    /**
     * Renders a number in a particular style.
     *
     * @param int $num The number, starting at 0.
     * @param string $style The style to render the number in.
     * @return string the number $num in the requested style.
     */
    protected function number_in_style(int $num, string $style): string {
        switch ($style) {
            case 'abc':
                $number = chr(ord('a') + $num);
                break;
            case 'ABCD':
                $number = chr(ord('A') + $num);
                break;
            case '123':
                $number = $num + 1;
                break;
            case 'iii':
                $number = question_utils::int_to_roman($num + 1);
                break;
            case 'IIII':
                $number = strtoupper(question_utils::int_to_roman($num + 1));
                break;
            case 'none':
                return '';
            default:
                return 'ERR';
        }
        return $this->number_html($number);
    }

    /**
     * Returns the HTML to display before the number in a question.
     *
     * @param string $qnum The question number, in whatever format is required.
     * @return string HTML to display before the question number.
     */
    protected function number_html(string $qnum): string {
        return $qnum . '. ';
    }
}

/**
 * Subclass for generating the bits of output specific to oumatrix single choice questions.
 *
 * @copyright  2023 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses,moodle.Commenting.MissingDocblock.Class
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
            $columnname = null;
            if (!empty($row->correctanswers) && is_array($row->correctanswers)) {
                $key = array_key_first($row->correctanswers);
                if (
                    $key !== null &&
                    isset($question->columns[$key]) &&
                    !empty($question->columns[$key]->name)
                ) {
                    $columnname = $question->columns[$key]->name;
                }
            }
            $right[] = $row->name . ' → ' . ($columnname ?? get_string('none', 'qtype_oumatrix'));
        }
        return $this->correct_choices($right);
    }

    #[\Override]
    protected function num_parts_correct(question_attempt $qa): string {
        $a = new stdClass();
        [$a->num, $a->outof] = $qa->get_question()->get_num_parts_right($qa->get_last_qt_data());
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
// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses,moodle.Commenting.MissingDocblock.Class
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
                if ($answers) {
                    $rowanswer .= implode(', ', $answers);
                } else {
                    $rowanswer .= get_string('none', 'qtype_oumatrix');
                }
                $rightanswers[] = $rowanswer;
            }
        }
        return $this->correct_choices($rightanswers);
    }

    #[\Override]
    protected function num_parts_correct(question_attempt $qa): string {
        $numchoices = $qa->get_question()->get_num_selected_choices($qa->get_last_qt_data());
        if ($numchoices > $qa->get_question()->get_num_correct_choices()) {
            return html_writer::tag('p', get_string('toomanyselected', 'qtype_oumatrix'));
        }

        $a = new stdClass();
        if ($qa->get_question()->grademethod == 'allnone') {
            [$a->num, $a->outof] = $qa->get_question()->get_num_grade_allornone($qa->get_last_qt_data());
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
            [$a->num, $a->outof] = $qa->get_question()->get_num_parts_grade_partial($qa->get_last_qt_data());
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
