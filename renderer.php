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
 * Base class for generating the bits of output common to oumatrix
 * single choice and multiple response questions.
 *
 * @copyright 2023 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class qtype_oumatrix_renderer_base extends qtype_with_combined_feedback_renderer {

    abstract protected function get_input_type();

    abstract protected function get_input_name(question_attempt $qa, $value, $columnnumber);

    abstract protected function get_input_value($value);

    abstract protected function get_input_id(question_attempt $qa, $value, $columnnumber);

    /**
     * Whether a choice should be considered right or wrong.
     *
     * @param question_definition $question the question
     * @param int $rowkey representing the row.
     * @param int $columnkey representing the column.
     * @return float 1.0, 0.0 or something in between, respectively.
     */
    protected function is_right(question_definition $question, $rowkey, $columnkey) {
        $row = $question->rows[$rowkey];
        foreach ($question->columns as $column) {
            if ($column->number == $columnkey && array_key_exists($column->number, $row->correctanswers)) {
                return 1;
            }
        }
    }

    protected function feedback_class($fraction) {
        return question_state::graded_state_for_fraction($fraction)->get_feedback_class();
    }

    /**
     * Return an appropriate icon (green tick, red cross, etc.) for a grade.
     * @param float $fraction grade on a scale 0..1.
     * @param bool $selected whether to show a big or small icon. (Deprecated)
     * @return string html fragment.
     */
    protected function feedback_image($fraction, $selected = true) {
        $feedbackclass = question_state::graded_state_for_fraction($fraction)->get_feedback_class();

        return $this->output->pix_icon('i/grade_' . $feedbackclass, get_string($feedbackclass, 'question'));
    }

    public function formulation_and_controls(question_attempt $qa,
            question_display_options $options) {
        $question = $qa->get_question();
        $result = '';

        $result .= html_writer::tag('div', $question->format_questiontext($qa), ['class' => 'qtext']);
        $result .= html_writer::start_tag('fieldset', ['class' => 'ablock no-overflow visual-scroll-x']);

        $result .= html_writer::start_tag('div', ['class' => 'answer']);

        // Display the matrix.
        $result .= $this->matrix_table($qa, $options);

        $result .= html_writer::end_tag('div');
        $result .= html_writer::end_tag('fieldset');

        if ($qa->get_state() == question_state::$invalid) {
            $result .= html_writer::nonempty_tag('div',
                $question->get_validation_error($qa->get_last_qt_data()), ['class' => 'validationerror']);
        }

        return $result;
    }

    public function matrix_table(question_attempt $qa, question_display_options $options) {

        $question = $qa->get_question();
        $response = $qa->get_last_qt_data();
        $caption = $options->add_question_identifier_to_label(get_string('answer'), false, true);

        // Create table and caption.
        $table = html_writer::start_tag('table', ['class' => 'generaltable']);
        $table .= html_writer::tag('caption', $caption, ['class' => 'sr-only']);

        // Creating the matrix column headers.
        $table .= html_writer::start_tag('tr');
        $table .= html_writer::tag('th', '', ['scope' => 'col']);
        $index = 0;
        foreach ($question->columns as $value) {
            $colname[$index] = $value->name;
            $table .= html_writer::tag('th', html_writer::span($colname[$index], 'answer_col', ['id' => 'col' . $index]),
                ['scope' => 'col', 'class' => 'align-middle']);
            $index += 1;
        }
        // Add feedback header.
        if ($options->feedback) {
            $table .= html_writer::tag('th', html_writer::span(get_string('feedback', 'question'),
                'answer_col', ['id' => 'col' . $index]), ['scope' => 'col', 'class' => 'rowfeedback align-middle']);
        }
        $table .= html_writer::end_tag('tr');

        if ($options->readonly) {
            $inputattributes['disabled'] = 'disabled';
        }
        // Set the input attribute based on the single or multiple answer mode.
        $inputattributes['type'] = $this->get_input_type();

        // Adding table rows for the sub-questions.
        foreach ($question->get_order($qa) as $rowkey => $rowid) {
            $row = $question->rows[$rowid];
            $rowname = $row->name;
            $rownewid = 'row'. $rowkey;
            $feedback = '';

            $table .= html_writer::start_tag('tr');
            $table .= html_writer::tag('th', html_writer::span($rowname, '', ['id' => $rownewid]),
                ['class' => 'align-middle', 'scope' => 'row']);

            for ($c = 1; $c <= count($colname); $c++) {
                $inputattributes['name'] = $this->get_input_name($qa, $rowkey, $c);
                $inputattributes['value'] = $this->get_input_value($c);
                $inputattributes['id'] = $this->get_input_id($qa, $rowkey, $c);
                $inputattributes['aria-labelledby'] = 'col' . ($c - 1). ' ' . $rownewid;

                $isselected = $question->is_choice_selected($response, $rowkey, $c);

                // Get the row per feedback.
                if ($options->feedback && $feedback == '' &&
                        $isselected && trim($row->feedback)) {
                    $feedback = html_writer::tag('div',
                        $question->make_html_inline($question->format_text($row->feedback, $row->feedbackformat,
                            $qa, 'qtype_oumatrix', 'feedback', $row->id)),
                        ['class' => 'specificfeedback']);
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
                $answered = html_writer::tag('label', $button . $feedbackimg, ['class' => "answerlabel $class"]);

                $table .= html_writer::tag('td', $answered, ['class' => "matrixanswer align-middle"]);
            }
            if ($options->feedback) {
                $table .= html_writer::tag('td', $feedback);
            }
            $table .= html_writer::end_tag('tr');
        }

        $table .= html_writer::end_tag('table');
        return $table;
    }

    public function specific_feedback(question_attempt $qa) {
        return $this->combined_feedback($qa);
    }

    /**
     * Function returns string based on number of correct answers
     * @param array $right An Array of correct responses to the current question
     * @return string based on number of correct responses
     */
    protected function correct_choices(array $right) {
        // Return appropriate string for single/multiple correct answer(s).
        $right = array_merge(["<br>"], $right);
        if (count($right) == 1) {
            return get_string('correctansweris', 'qtype_oumatrix',
                    implode("<br>", $right));
        } else if (count($right) > 1) {
            return get_string('correctanswersare', 'qtype_oumatrix',
                    implode("<br>", $right));
        } else {
            return "";
        }
    }
}

/**
 * Subclass for generating the bits of output specific to oumatrix
 * single choice questions.
 *
 * @copyright  2023 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_oumatrix_single_renderer extends qtype_oumatrix_renderer_base {
    protected function get_input_type() {
        return 'radio';
    }

    protected function get_input_name(question_attempt $qa, $value, $columnnumber) {
        return $qa->get_qt_field_name('rowanswers' . $value);
    }

    protected function get_input_value($value) {
        return $value;
    }

    protected function get_input_id(question_attempt $qa, $value, $columnnumber) {
        return $qa->get_qt_field_name('rowanswers' . $value . '_' . $columnnumber);
    }

    public function correct_response(question_attempt $qa) {
        $question = $qa->get_question();
        $right = [];
        foreach ($question->rows as $row) {
            $right[] = $row->name . ' → ' . $question->columns[array_key_first($row->correctanswers)]->name;
        }
        return $this->correct_choices($right);
    }
}

/**
 * Subclass for generating the bits of output specific to oumatrix
 * multiple choice questions.
 *
 * @copyright  2023 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_oumatrix_multiple_renderer extends qtype_oumatrix_renderer_base {
    protected function get_input_type() {
        return 'checkbox';
    }

    protected function get_input_name(question_attempt $qa, $value, $columnnumber) {
        return $qa->get_qt_field_name('rowanswers' . $value . '_' . $columnnumber);
    }

    protected function get_input_value($value) {
        return 1;
    }

    protected function get_input_id(question_attempt $qa, $value, $columnnumber) {
        return $this->get_input_name($qa, $value, $columnnumber);
    }

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

    protected function num_parts_correct(question_attempt $qa): string {
        if ($qa->get_question()->get_num_selected_choices($qa->get_last_qt_data()) >
                $qa->get_question()->get_num_correct_choices()) {
            return get_string('toomanyselected', 'qtype_oumatrix');
        }

        $a = new stdClass();
        if ($qa->get_question()->grademethod == 'allnone') {
            list($a->num, $a->outof) = $qa->get_question()->get_num_grade_allornone($qa->get_last_qt_data());
            if (is_null($a->outof)) {
                return '';
            }
            if ($a->num == 1) {
                return get_string('yougot1rightsubquestion', 'qtype_oumatrix');
            }
            $f = new NumberFormatter(current_language(), NumberFormatter::SPELLOUT);
            $a->num = $f->format($a->num);
            return get_string('yougotnrightsubquestion', 'qtype_oumatrix', $a);
        } else {
            list($a->num, $a->outof) = $qa->get_question()->get_num_parts_grade_partial($qa->get_last_qt_data());
            if (is_null($a->outof)) {
                return '';
            }
            if ($a->num == 1) {
                return get_string('yougot1right', 'qtype_oumatrix');
            }
            $f = new NumberFormatter(current_language(), NumberFormatter::SPELLOUT);
            $a->num = $f->format($a->num);
            return get_string('yougotnright', 'qtype_oumatrix', $a);
        }
    }
}
