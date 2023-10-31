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

    /**
     * Returns the test questions.
     *
     * @return string[]
     */
    public function get_test_questions(): array {
        return [
                'animals_single',
                'food_multiple',
        ];
    }

    /**
     * Get the question data, as it would be loaded by get_question_options.
     *
     * @return object
     */
    public function get_oumatrix_question_data_animals_single(): object {
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
        $qdata->options->shuffleanswers = 0;
        $qdata->options->correctfeedback = test_question_maker::STANDARD_OVERALL_CORRECT_FEEDBACK;
        $qdata->options->correctfeedbackformat = FORMAT_HTML;
        $qdata->options->partiallycorrectfeedback = test_question_maker::STANDARD_OVERALL_PARTIALLYCORRECT_FEEDBACK;
        $qdata->options->partiallycorrectfeedbackformat = FORMAT_HTML;
        $qdata->options->incorrectfeedback = test_question_maker::STANDARD_OVERALL_INCORRECT_FEEDBACK;
        $qdata->options->incorrectfeedbackformat = FORMAT_HTML;
        $qdata->options->shownumcorrect = 1;

        $qdata->columns = [
                11 => (object) [
                        'id' => 11,
                        'number' => 1,
                        'name' => 'Insects',
                ],
                12 => (object) [
                        'id' => 12,
                        'number' => 2,
                        'name' => 'Fish',
                ],
                13 => (object) [
                        'id' => 13,
                        'number' => 3,
                        'name' => 'Birds',
                ],
                14 => (object) [
                        'id' => 13,
                        'number' => 4,
                        'name' => 'Mammals',
                ],
        ];
        $qdata->rows = [
                11 => (object) [
                        'id' => 11,
                        'number' => 1,
                        'name' => 'Bee',
                        'correctanswers' => ['1' => 'a1'],
                        'feedback' => 'Fly, Bee and spider are insects.',
                        'feedbackformat' => FORMAT_HTML,
                ],
                12 => (object) [
                        'id' => 12,
                        'number' => 2,
                        'name' => 'Salmon',
                        'correctanswers' => ['2' => 'a2'],
                        'feedback' => 'Cod, Salmon and Trout are fish.',
                        'feedbackformat' => FORMAT_HTML,
                ],
                13 => (object) [
                        'id' => 13,
                        'number' => 3,
                        'name' => 'Seagull',
                        'correctanswers' => ['3' => 'a3'],
                        'feedback' => 'Gulls and Owls are birds.',
                        'feedbackformat' => FORMAT_HTML,
                ],
                14 => (object) [
                        'id' => 14,
                        'number' => 4,
                        'name' => 'Dog',
                        'correctanswers' => ['4' => 'a4'],
                        'feedback' => 'Cow, Dog and Horse are mammals.',
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
     *
     * @return object
     */
    public static function get_oumatrix_question_form_data_animals_single(): object {
        $qfdata = new stdClass();

        $qfdata->name = 'oumatrix_animals_single01';
        $qfdata->questiontext = [
                'text' => 'Animal classification. Please answer the sub questions in all 4 rows.',
                'format' => FORMAT_HTML,
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
        $qfdata->shuffleanswers = 0;
        $qfdata->correctfeedback = [
                'text' => test_question_maker::STANDARD_OVERALL_CORRECT_FEEDBACK,
                'format' => FORMAT_HTML,
        ];
        $qfdata->partiallycorrectfeedback = [
                'text' => test_question_maker::STANDARD_OVERALL_PARTIALLYCORRECT_FEEDBACK,
                'format' => FORMAT_HTML,
        ];
        $qfdata->shownumcorrect = 1;
        $qfdata->incorrectfeedback = [
                'text' => test_question_maker::STANDARD_OVERALL_INCORRECT_FEEDBACK,
                'format' => FORMAT_HTML,
        ];
        $qfdata->columnname = [
                0 => 'Insects',
                1 => 'Fish',
                2 => 'Birds',
                3 => 'Mammals',
        ];
        $qfdata->rowname = [
                0 => 'Bee',
                1 => 'Salmon',
                2 => 'Seagull',
                3 => 'Dog',
        ];
        $qfdata->rowanswers = [
                0 => "1",
                1 => "2",
                2 => "3",
                3 => "4",
        ];
        $qfdata->feedback = [
                0 => [
                        'text' => 'Fly, Bee and Spider are insects.',
                        'format' => FORMAT_HTML,
                ],
                1 => [
                        'text' => 'Cod, Salmon and Trout are fish.',
                        'format' => FORMAT_HTML,
                ],
                2 => [
                        'text' => 'Gull and Owl are birds.',
                        'format' => FORMAT_HTML,
                ],
                3 => [
                        'text' => 'Cow, Dog and Horse are mammals.',
                        'format' => FORMAT_HTML,
                ],
        ];
        $qfdata->hint = [
                0 => ['text' => 'Hint 1.', 'format' => FORMAT_HTML],
                1 => ['text' => 'Hint 2.', 'format' => FORMAT_HTML],
        ];
        $qfdata->hintshownumbcorrect = [1, 1];
        return $qfdata;
    }

    /**
     * Get the question data, as it would be loaded by get_question_options.
     *
     * @return object
     */
    public function get_oumatrix_question_data_food_multiple(): object {
        global $USER;

        $qdata = new stdClass();

        $qdata->createdby = $USER->id;
        $qdata->modifiedby = $USER->id;
        $qdata->qtype = 'oumatrix';
        $qdata->name = 'oumatrix_food_multiple01';
        $qdata->questiontext = 'Please classify the list of food item as Proteins, Vegetables, Fats.';
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
        $qdata->options->shuffleanswers = 0;
        $qdata->options->correctfeedback = test_question_maker::STANDARD_OVERALL_CORRECT_FEEDBACK;
        $qdata->options->correctfeedbackformat = FORMAT_HTML;
        $qdata->options->partiallycorrectfeedback = test_question_maker::STANDARD_OVERALL_PARTIALLYCORRECT_FEEDBACK;
        $qdata->options->partiallycorrectfeedbackformat = FORMAT_HTML;
        $qdata->options->incorrectfeedback = test_question_maker::STANDARD_OVERALL_INCORRECT_FEEDBACK;
        $qdata->options->incorrectfeedbackformat = FORMAT_HTML;
        $qdata->options->shownumcorrect = 1;

        $qdata->columns = [
                21 => (object) [
                        'id' => 21,
                        'number' => 1,
                        'name' => 'Chicken breast',
                ],
                22 => (object) [
                        'id' => 22,
                        'number' => 1,
                        'name' => 'Carrot',
                ],
                23 => (object) [
                        'id' => 23,
                        'number' => 3,
                        'name' => 'Salmon fillet',
                ],
                24 => (object) [
                        'id' => 24,
                        'number' => 4,
                        'name' => 'Asparagus',
                ],
                25 => (object) [
                        'id' => 25,
                        'number' => 5,
                        'name' => 'Olive oil',
                ],
                26 => (object) [
                        'id' => 26,
                        'number' => 6,
                        'name' => 'Steak',
                ],
                27 => (object) [
                        'id' => 27,
                        'number' => 7,
                        'name' => 'Potato',
                ],
        ];
        $qdata->rows = [
                21 => (object) [
                        'id' => 21,
                        'number' => 1,
                        'name' => 'Proteins',
                        'correctanswers' => [1, 3, 6],
                        'feedback' => 'Chicken, fish and red meat containing proteins.',
                        'feedbackformat' => FORMAT_HTML,
                ],
                22 => (object) [
                        'id' => 22,
                        'number' => 2,
                        'name' => 'Vegetables',
                        'correctanswers' => [2, 4, 7],
                        'feedback' => 'Carrot, Asparagus, Potato are vegetables.',
                        'feedbackformat' => FORMAT_HTML,
                ],
                23 => (object) [
                        'id' => 23,
                        'number' => 3,
                        'name' => 'Fats',
                        'correctanswers' => [5],
                        'feedback' => 'Olive oil contains fat.',
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
     *
     * @return object
     */
    public static function get_oumatrix_question_form_data_food_multiple(): object {
        $qfdata = new stdClass();

        $qfdata->name = 'oumatrix_food_multiple01';
        $qfdata->questiontext = ['text' => 'Please classify the list of food item as Proteins, Vegetables, Fats.',
            'format' => FORMAT_HTML,
        ];
        $qfdata->generalfeedback = ['text' => 'A delicious and healthy meal is a balanced one.', 'format' => FORMAT_HTML];
        $qfdata->defaultmark = 1;
        $qfdata->length = 1;
        $qfdata->penalty = 0.3333333;
        $qfdata->status = \core_question\local\bank\question_version_status::QUESTION_STATUS_READY;
        $qfdata->versionid = 0;
        $qfdata->version = 1;
        $qfdata->questionbankentryid = 0;
        $qfdata->inputtype = 'multiple';
        $qfdata->grademethod = 'partial';
        $qfdata->shuffleanswers = 0;
        $qfdata->correctfeedback = [
                'text' => test_question_maker::STANDARD_OVERALL_CORRECT_FEEDBACK,
                'format' => FORMAT_HTML,
        ];
        $qfdata->partiallycorrectfeedback = [
                'text' => test_question_maker::STANDARD_OVERALL_PARTIALLYCORRECT_FEEDBACK,
                'format' => FORMAT_HTML,
        ];
        $qfdata->shownumcorrect = 1;
        $qfdata->incorrectfeedback = [
                'text' => test_question_maker::STANDARD_OVERALL_INCORRECT_FEEDBACK,
                'format' => FORMAT_HTML,
        ];
        $qfdata->columnname = [
                0 => 'Chicken breast',
                1 => 'Carrot',
                2 => 'Salmon fillet',
                3 => 'Asparagus',
                4 => 'Olive oil',
                5 => 'Steak',
                6 => 'Potato',
        ];
        $qfdata->rowname = [
                0 => 'Proteins',
                1 => 'Vegetables',
                2 => 'Fats',
        ];
        $qfdata->rowanswersa1 = [
                0 => "1",
        ];
        $qfdata->rowanswersa2 = [
                1 => "1",
        ];
        $qfdata->rowanswersa3 = [
                0 => "1",
        ];
        $qfdata->rowanswersa4 = [
                1 => "1",
        ];
        $qfdata->rowanswersa5 = [
                2 => "1",
        ];
        $qfdata->rowanswersa6 = [
                0 => "1",
        ];
        $qfdata->rowanswersa7 = [
                1 => "1",
        ];
        $qfdata->feedback = [
                0 => [
                        'text' => 'Chicken, fish and red meat containing proteins.',
                        'format' => FORMAT_HTML,
                ],
                1 => [
                        'text' => 'Carrot, Asparagus, Potato are vegetables.',
                        'format' => FORMAT_HTML,
                ],
                2 => [
                        'text' => 'Olive oil contains fat.',
                        'format' => FORMAT_HTML,
                ],
        ];
        $qfdata->hint = [
                0 => ['text' => 'Hint 1.', 'format' => FORMAT_HTML],
                1 => ['text' => 'Hint 2.', 'format' => FORMAT_HTML],
        ];
        $qfdata->hintshownumbcorrect = [1, 1];
        return $qfdata;
    }

    public function get_test_question_data(string $which) {
        return \test_question_maker::get_question_data('oumatrix', $which);
    }

    public function get_test_question_form_data(string $which) {
        return (array)\test_question_maker::get_question_form_data('oumatrix', $which);
    }

    public function make_question(string $which) {
        return \test_question_maker::make_question('oumatrix', $which);
    }

    /**
     * Returns a qtype_oumatrix_single question.
     *
     * @return qtype_oumatrix_single
     */
    public function make_oumatrix_question_animals_single(): qtype_oumatrix_single {
        question_bank::load_question_definition_classes('oumatrix');
        $question = new qtype_oumatrix_single();
        $question->name = 'oumatrix_animals_single01';
        $question->questiontext = 'Animal classification. Please answer the sub questions in all 4 rows.';
        $question->generalfeedback = 'We are recognising different type of animals';
        $question->generalfeedbackformat = FORMAT_HTML;
        $question->qtype = question_bank::get_qtype('oumatrix');
        $question->defaultmark = 1;
        $question->length = 1;
        $question->penalty = 0.3333333;
        $question->status = \core_question\local\bank\question_version_status::QUESTION_STATUS_READY;
        $question->versionid = 0;
        $question->version = 1;
        $question->questionbankentryid = 0;
        $question->options = new stdClass();
        $question->options->inputtype = 'single';
        $question->options->grademethod = 'partial';
        $question->options->shuffleanswers = 1;
        $question->options->correctfeedback = test_question_maker::STANDARD_OVERALL_CORRECT_FEEDBACK;
        $question->options->correctfeedbackformat = FORMAT_HTML;
        $question->options->partiallycorrectfeedback = test_question_maker::STANDARD_OVERALL_PARTIALLYCORRECT_FEEDBACK;
        $question->options->partiallycorrectfeedbackformat = FORMAT_HTML;
        $question->options->incorrectfeedback = test_question_maker::STANDARD_OVERALL_INCORRECT_FEEDBACK;
        $question->options->incorrectfeedbackformat = FORMAT_HTML;
        $question->options->shownumcorrect = 1;

        $question->columns = [
                11 => (object) [
                        'id' => 11,
                        'number' => 1,
                        'name' => 'Insects',
                ],
                12 => (object) [
                        'id' => 12,
                        'number' => 2,
                        'name' => 'Fish',
                ],
                13 => (object) [
                        'id' => 13,
                        'number' => 3,
                        'name' => 'Birds',
                ],
                14 => (object) [
                        'id' => 13,
                        'number' => 4,
                        'name' => 'Mammals',
                ],
        ];
        $question->rows = [
                11 => (object) [
                        'id' => 11,
                        'number' => 1,
                        'name' => 'Bee',
                        'correctanswers' => ['1' => 'a1'],
                        'feedback' => 'Fly, Bee and spider are insects.',
                        'feedbackformat' => FORMAT_HTML,
                ],
                12 => (object) [
                        'id' => 12,
                        'number' => 2,
                        'name' => 'Salmon',
                        'correctanswers' => ['2' => 'a2'],
                        'feedback' => 'Cod, Salmon and Trout are fish.',
                        'feedbackformat' => FORMAT_HTML,
                ],
                13 => (object) [
                        'id' => 13,
                        'number' => 3,
                        'name' => 'Seagull',
                        'correctanswers' => ['3' => 'a3'],
                        'feedback' => 'Gulls and Owls are birds.',
                        'feedbackformat' => FORMAT_HTML,
                ],
                14 => (object) [
                        'id' => 14,
                        'number' => 4,
                        'name' => 'Dog',
                        'correctanswers' => ['4' => 'a4'],
                        'feedback' => 'Cow, Dog and Horse are mammals.',
                        'feedbackformat' => FORMAT_HTML,
                ],
        ];
        return $question;
    }
}