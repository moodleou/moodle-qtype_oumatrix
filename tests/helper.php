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
 * Unit tests for ou matrix question type.
 *
 * @package     qtype_oumatrix
 * @copyright   2023 The Open University
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class qtype_oumatrix_test_helper extends question_test_helper {

    public function get_test_questions() {
        return [
                'animals_single',
                'oumatrix_multiple'
        ];
    }

    /**
     * Get the question data, as it would be loaded by get_question_options.
     * @return object
     */
    public function get_oumatrix_question_data_animals_single() {
        global $USER;

        $qdata = new stdClass();

        $qdata->createdby = $USER->id;
        $qdata->modifiedby = $USER->id;
        $qdata->qtype = 'oumatrix';
        $qdata->name = 'oumatrix_animals_single01';
        $qdata->questiontext = 'Animal classification. Please answer the sub questions in all 4 rows.';
        $qdata->questiontextformat = FORMAT_HTML;
        $qdata->generalfeedback = 'We are recognising different type of animals';
        $qdata->generalfeedbackformat = FORMAT_HTML;
        $qdata->defaultmark = 1;
        $qdata->length = 1;
        $qdata->penalty = 0.3333333;
        $qdata->status = \core_question\local\bank\question_version_status::QUESTION_STATUS_READY;
        $qdata->versionid = 0;
        $qdata->version = 1;
        $qdata->questionbankentryid = 0;
        $qdata->options = new stdClass();
        $qdata->options->inputtype = 'single';
        $qdata->options->grademethod = 'partial';
        $qdata->options->shuffleanswers = 1;
        $qdata->options->correctfeedback =
                test_question_maker::STANDARD_OVERALL_CORRECT_FEEDBACK;
        $qdata->options->correctfeedbackformat = FORMAT_HTML;
        $qdata->options->partiallycorrectfeedback =
                test_question_maker::STANDARD_OVERALL_PARTIALLYCORRECT_FEEDBACK;
        $qdata->options->partiallycorrectfeedbackformat = FORMAT_HTML;
        $qdata->options->shownumcorrect = 1;
        $qdata->options->incorrectfeedback =
                test_question_maker::STANDARD_OVERALL_INCORRECT_FEEDBACK;
        $qdata->options->incorrectfeedbackformat = FORMAT_HTML;

        $qdata->columns = [
                11 => (object) [
                        'id' => 11,
                        'number' => 0,
                        'name' => 'Insects',
                ],
                12 => (object) [
                        'id' => 12,
                        'number' => 1,
                        'name' => 'Fish',
                ],
                13 => (object) [
                        'id' => 13,
                        'number' => 2,
                        'name' => 'Birdas',
                ],
                14 => (object) [
                        'id' => 13,
                        'number' => 2,
                        'name' => 'Mammals',
                ],
        ];
        $qdata->rows = [
                11 => (object) [
                        'id' => 11,
                        'number' => 0,
                        'name' => 'Fly, bee, spider',
                        'feedback' => 'Flies, bees and spiders are insects.',
                        'feedbackformat' => FORMAT_HTML,
                        'correctanswers' => '{"Insects":"1","Fish":"0","Birds":"0", "mammals":"0"}',
                ],
                12 => (object) [
                        'id' => 12,
                        'number' => 1,
                        'name' => 'Cod, Salmon, Trout',
                        'feedback' => 'Cod, Salmon and Trout are fish.',
                        'feedbackformat' => FORMAT_HTML,
                        'correctanswers' => '{"Insects":"0","Fish":"1","Birds":"0", "mammals":"0"}',
                ],
                13 => (object) [
                        'id' => 13,
                        'number' => 2,
                        'name' => 'Gull, Owl',
                        'feedback' => 'Gull and Owl are birds.',
                        'feedbackformat' => FORMAT_HTML,
                        'correctanswers' => '{"Insects":"0","Fish":"0","Birds":"1", "mammals":"0"}',
                ],
                14 => (object) [
                        'id' => 14,
                        'number' => 3,
                        'name' => 'Cow, Dog, Horse',
                        'feedback' => 'Cow, Dog and  Horse are mammals',
                        'feedbackformat' => FORMAT_HTML,
                        'correctanswers' => '{"Insects":"0","Fish":"0","Birds":"0", "mammals":"1"}',
                ],
        ];

        $qdata->hints = [
                1 => (object) [
                        'hint' => 'Hint 1.',
                        'hintformat' => FORMAT_HTML,
                        'shownumcorrect' => 1,
                        'clearwrong' => 0,
                        'options' => 0,
                ],
                2 => (object) [
                        'hint' => 'Hint 2.',
                        'hintformat' => FORMAT_HTML,
                        'shownumcorrect' => 1,
                        'clearwrong' => 1,
                        'options' => 1,
                ],
        ];
        return $qdata;
    }

    /**
     * Get the question data, as it would be loaded by get_question_options.
     * @return object
     */
    public static function get_oumatrix_question_form_data_animals_single() {
        $qfdata = new stdClass();

        $qfdata->name = 'oumatrix_animals_single01';
        $qfdata->questiontext = [
                'text' => 'Animal classification. Please answer the sub questions in all 4 rows.',
                'format' => FORMAT_HTML
        ];
        $qfdata->generalfeedback = ['text' => 'We are recognising different type of animals.', 'format' => FORMAT_HTML];
        $qfdata->defaultmark = 1;
        $qfdata->length = 1;
        $qfdata->penalty = 0.3333333;
        $qfdata->status = \core_question\local\bank\question_version_status::QUESTION_STATUS_READY;
        $qfdata->versionid = 0;
        $qfdata->version = 1;
        $qfdata->questionbankentryid = 0;
        $qfdata->inputtype = 'single';
        $qfdata->grademethod = 'partial';
        $qfdata->shuffleanswers = 1;
        $qfdata->correctfeedback = [
                'text' => test_question_maker::STANDARD_OVERALL_CORRECT_FEEDBACK,
                'format' => FORMAT_HTML];
        $qfdata->partiallycorrectfeedback = [
                'text' => test_question_maker::STANDARD_OVERALL_PARTIALLYCORRECT_FEEDBACK,
                'format' => FORMAT_HTML];
        $qfdata->shownumcorrect = 1;
        $qfdata->incorrectfeedback = [
                'text' => test_question_maker::STANDARD_OVERALL_INCORRECT_FEEDBACK,
                'format' => FORMAT_HTML];
        $qfdata->columns = [
                11 => [
                        'number' => 0,
                        'name' => 'Insects'
                ],
                12 =>  [
                        'number' => 1,
                        'name' => 'Fish'
                ],
                13 => [
                        'number' => 2,
                        'name' => 'Birdas'
                ],
                14 => [
                        'number' => 2,
                        'name' => 'Mammals'
                ],
        ];
        $qfdata->rows = [
                11 => [
                        'number' => 0,
                        'name' => 'Fly, bee, spider',
                        'correctanswers' => '{"Insects":"1","Fish":"0","Birds":"0", "mammals":"0"}',
                        'feedback' => ['text' => 'Flies, bees and spiders are insects.', 'format' => FORMAT_HTML]
                ],
                12 => [
                        'number' => 0,
                        'name' => 'Cod, Salmon, Trout',
                        'correctanswers' => '{"Insects":"0","Fish":"1","Birds":"0", "mammals":"0"}',
                        'feedback' => ['text' => 'Cod, Salmon and Trout are fish.', 'format' => FORMAT_HTML]
                ],
                13 => [
                        'number' => 0,
                        'name' => 'Gull, Owl',
                        'correctanswers' => '{"Insects":"0","Fish":"0","Birds":"1", "mammals":"0"}',
                        'feedback' => ['text' => 'Gull and Owl are birds.', 'format' => FORMAT_HTML]
                ],
                14 => (object) [
                        'number' => 0,
                        'name' => 'Cow, Dog, Horse',
                        'correctanswers' => '{"Insects":"0","Fish":"0","Birds":"0", "mammals":"1"}',
                        'feedback' => ['text' => 'Cow, Dog and  Horse are mammals', 'format' => FORMAT_HTML]
                ],
        ];

        $qfdata->hints = [
                0 => ['text' => 'Hint 1.', 'format' => FORMAT_HTML],
                1 => ['text' => 'Hint 2.', 'format' => FORMAT_HTML]
            ];
        $qfdata->hintclearwrong = [0, 1];
        $qfdata->hintshownumbcorrect = [1, 1];
        return $qfdata;
    }

    /**
     * Get the question data, as it would be loaded by get_question_options.
     * @return object
     */
    public function get_oumatrix_question_data_food_multiple() {
        global $USER;

        $qdata = new stdClass();

        $qdata->createdby = $USER->id;
        $qdata->modifiedby = $USER->id;
        $qdata->qtype = 'oumatrix';
        $qdata->name = 'oumatrix_multiple01';
        $qdata->questiontext = 'Please classify the list of food item in the follwoing groups:
        Proteins, Vegetables, Fats, and Herbs & Spices';
        $qdata->questiontextformat = FORMAT_HTML;
        $qdata->generalfeedback = 'A delicious and healthy meal is a balanced one.';
        $qdata->generalfeedbackformat = FORMAT_HTML;
        $qdata->defaultmark = 1;
        $qdata->length = 1;
        $qdata->penalty = 0.3333333;
        $qdata->status = \core_question\local\bank\question_version_status::QUESTION_STATUS_READY;
        $qdata->versionid = 0;
        $qdata->version = 1;
        $qdata->questionbankentryid = 0;
        $qdata->options = new stdClass();
        $qdata->options->inputtype = 'multiple';
        $qdata->options->grademethod = 'partial';
        $qdata->options->shuffleanswers = 1;
        $qdata->options->correctfeedback =
                test_question_maker::STANDARD_OVERALL_CORRECT_FEEDBACK;
        $qdata->options->correctfeedbackformat = FORMAT_HTML;
        $qdata->options->partiallycorrectfeedback =
                test_question_maker::STANDARD_OVERALL_PARTIALLYCORRECT_FEEDBACK;
        $qdata->options->partiallycorrectfeedbackformat = FORMAT_HTML;
        $qdata->options->shownumcorrect = 1;
        $qdata->options->incorrectfeedback =
                test_question_maker::STANDARD_OVERALL_INCORRECT_FEEDBACK;
        $qdata->options->incorrectfeedbackformat = FORMAT_HTML;

        $qdata->columns = [
                21 => (object) [
                        'id' => 21,
                        'number' => 0,
                        'name' => 'Chicken breast',
                ],
                22 => (object) [
                        'id' => 22,
                        'number' => 1,
                        'name' => 'carrots',
                ],
                23 => (object) [
                        'id' => 23,
                        'number' => 2,
                        'name' => 'Salmon fillet',
                ],
                24 => (object) [
                        'id' => 24,
                        'number' => 3,
                        'name' => 'Asparagus',
                ],
                25 => (object) [
                        'id' => 25,
                        'number' => 4,
                        'name' => 'Olive oil',
                ],
                26 => (object) [
                        'id' => 26,
                        'numbdr' => 5,
                        'name' => 'Steak',
                ],
                27 => (object) [
                        'id' => 27,
                        'number' => 6,
                        'name' => 'Potatos',
                ],
        ];
        $qdata->rows = [
                21 => (object) [
                        'id' => 21,
                        'numbdr' => 0,
                        'name' => 'Proteins',
                        'correctanswers' => '{"one":"0","two":"1","three":"0"}',
                        'feedback' => 'Chicken, fish and read meat containing proteins.',
                        'feedbackformat' => FORMAT_HTML,
                ],
                22 => (object) [
                        'id' => 22,
                        'numbdr' => 1,
                        'name' => 'Odd numbers',
                        'correctanswers' => '{"one":"1","two":"0","three":"1"}',
                        'feedback' => 'Odd numbers are not evenly divisible by 2 and end in 1, 3, 5, 7, or 9.',
                        'feedbackformat' => FORMAT_HTML,
                ],
                23 => (object) [
                        'id' => 23,
                        'numbdr' => 2,
                        'name' => 'Number less than 3',
                        'correctanswers' => '{"one":"1","two":"1","three":"0"}',
                        'feedback' => 'All numbers smaller than 3.',
                        'feedbackformat' => FORMAT_HTML,
                ],
        ];

        $qdata->hints = [
                1 => (object) [
                        'hint' => 'Hint 1.',
                        'hintformat' => FORMAT_HTML,
                        'shownumcorrect' => 1,
                        'clearwrong' => 0,
                        'options' => 0,
                ],
                2 => (object) [
                        'hint' => 'Hint 2.',
                        'hintformat' => FORMAT_HTML,
                        'shownumcorrect' => 1,
                        'clearwrong' => 1,
                        'options' => 1,
                ],
        ];
        return $qdata;
    }

    /**
     * Get the question data, as it would be loaded by get_question_options.
     * @return object
     */
    public static function get_oumatrix_question_form_data_oumatrix_pultiple() {
        $qfdata = new stdClass();

        $qfdata->name = 'oumatrix_multiple01';
        $qfdata->questiontext = ['text' => 'Please answer the sub questions in each row', 'format' => FORMAT_HTML];
        $qfdata->generalfeedback = ['text' => 'We are dealing with even and odd numbers', 'format' => FORMAT_HTML];
        $qfdata->defaultmark = 1;
        $qfdata->length = 1;
        $qfdata->penalty = 0.3333333;
        $qfdata->status = \core_question\local\bank\question_version_status::QUESTION_STATUS_READY;
        $qfdata->versionid = 0;
        $qfdata->version = 1;
        $qfdata->questionbankentryid = 0;
        $qfdata->inputtype = 'multiple';
        $qfdata->grademethod = 'partial';
        $qfdata->shuffleanswers = 1;
        $qfdata->correctfeedback = [
                'text' => test_question_maker::STANDARD_OVERALL_CORRECT_FEEDBACK,
                'format' => FORMAT_HTML];
        $qfdata->partiallycorrectfeedback = [
                'text' => test_question_maker::STANDARD_OVERALL_PARTIALLYCORRECT_FEEDBACK,
                'format' => FORMAT_HTML];
        $qfdata->shownumcorrect = 1;
        $qfdata->incorrectfeedback = [
                'text' => test_question_maker::STANDARD_OVERALL_INCORRECT_FEEDBACK,
                'format' => FORMAT_HTML];
        $qfdata->columns = [
                21 => [
                        'number' => 0,
                        'name' => 'one'
                ],
                22 =>  [
                        'number' => 1,
                        'name' => 'two'
                ],
                23 => [
                        'number' => 2,
                        'name' => 'three'
                ],
        ];
        $qfdata->rows = [
                21 => [
                        'number' => 0,
                        'name' => 'Even numbers',
                        'correctanswers' => '{"one":"0","two":"1","three":"0"}',
                        'feedback' => ['text' => 'Even numbers are divisible by 2 without remainders.', 'format' => FORMAT_HTML]
                ],
                22 => [
                        'number' => 1,
                        'name' => 'Odd numbers',
                        'correctanswers' => '{"one":"1","two":"0","three":"1"}',
                        'feedback' => ['text' => 'Odd numbers are not evenly divisible by 2 and end in 1, 3, 5, 7, or 9.',
                                'format' => FORMAT_HTML]
                ],
                23 => [
                        'number' => 2,
                        'name' => 'Number less than 3',
                        'correctanswers' => '{"one":"1","two":"1","three":"0"}',
                        'feedback' => ['text' => 'All numbers smaller than 3.', 'format' => FORMAT_HTML]
                ],
        ];

        $qfdata->hints = [
                0 => ['text' => 'Hint 1.', 'format' => FORMAT_HTML],
                1 => ['text' => 'Hint 2.', 'format' => FORMAT_HTML]
        ];
        $qfdata->hintclearwrong = [0, 1];
        $qfdata->hintshownumbcorrect = [1, 1];
        return $qfdata;
    }

    public function get_test_question_data($witch) {
        return \test_question_maker::get_question_data('oumatrix', $witch);
    }
}
