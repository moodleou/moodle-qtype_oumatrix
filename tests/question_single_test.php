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

namespace qtype_oumatrix;

use question_attempt_step;
use question_state;
use question_classified_response;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');
require_once($CFG->dirroot . '/question/type/oumatrix/tests/helper.php');
require_once($CFG->dirroot . '/question/type/oumatrix/question.php');
require_once($CFG->dirroot . '/question/type/oumatrix/questiontype.php');

/**
 * Unit tests for the oumatrix (single) question definition class.
 *
 * @package   qtype_oumatrix
 * @copyright 2023 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \qtype_oumatrix_single
 */
class question_single_test extends \advanced_testcase {

    public function test_get_expected_data(): void {
        $question = \test_question_maker::make_question('oumatrix');
        $question->start_attempt(new question_attempt_step(), 1);

        $expected = [
            'rowanswers0' => PARAM_INT,
            'rowanswers1' => PARAM_INT,
            'rowanswers2' => PARAM_INT,
            'rowanswers3' => PARAM_INT,
        ];
        $this->assertEquals($expected, $question->get_expected_data());
    }

    public function test_is_gradable_response(): void {
        $question = \test_question_maker::make_question('oumatrix');
        $question->start_attempt(new question_attempt_step(), 1);

        $response = ['rowanswers0' => '1', 'rowanswers1' => '1', 'rowanswers2' => '2', 'rowanswers3' => '2'];
        $this->assertTrue($question->is_gradable_response($response));

        $response = ['rowanswers0' => '1', 'rowanswers1' => '1', 'rowanswers2' => '2'];
        $this->assertTrue($question->is_gradable_response($response));

        $response = ['rowanswers0' => '1'];
        $this->assertTrue($question->is_gradable_response($response));

        $response = [];
        $this->assertFalse($question->is_gradable_response($response));
    }

    public function test_classify_response_single(): void {
        $this->resetAfterTest();
        $question = \test_question_maker::make_question('oumatrix', 'animals_single');
        $question->shuffleanswers = 0;
        $question->start_attempt(new question_attempt_step(), 1);

        // All sub-questions are answered correctly.
        $response = $question->prepare_simulated_post_data(
                ['Bee' => 'Insects', 'Salmon' => 'Fish', 'Seagull' => 'Birds', 'Dog' => 'Mammals']);
        $this->assertEquals([
            '1. Bee' => new question_classified_response(1, 'Insects', 1),
            '2. Salmon' => new question_classified_response(2, 'Fish',  1),
            '3. Seagull' => new question_classified_response(3, 'Birds', 1),
            '4. Dog' => new question_classified_response(4, 'Mammals', 1),
        ], $question->classify_response($response));

        // Three sub-questions are answered correctly and one incorrectly.
        $response = $question->prepare_simulated_post_data(
                ['Bee' => 'Insects', 'Salmon' => 'Birds', 'Seagull' => 'Birds', 'Dog' => 'Mammals']);
        $this->assertEquals([
            '1. Bee' => new question_classified_response(1, 'Insects', 1),
            '2. Salmon' => new question_classified_response(3, 'Birds', 0),
            '3. Seagull' => new question_classified_response(3, 'Birds', 1),
            '4. Dog' => new question_classified_response(4, 'Mammals', 1),
        ], $question->classify_response($response));

        // Two sub-questions are answered correctly and two incorrectly.
        $response = $question->prepare_simulated_post_data(
                ['Bee' => 'Insects', 'Salmon' => 'Birds', 'Seagull' => 'Birds', 'Dog' => 'Insects']);
        $this->assertEquals([
            '1. Bee' => new question_classified_response(1, 'Insects', 1),
            '2. Salmon' => new question_classified_response(3, 'Birds', 0),
            '3. Seagull' => new question_classified_response(3, 'Birds', 1),
            '4. Dog' => new question_classified_response(1, 'Insects', 0),
        ], $question->classify_response($response));

        // Two sub-questions are answered correctly, one incorrectly, and the second sub-question is not answered.
        $response = $question->prepare_simulated_post_data(
                ['Bee' => 'Insects', 'Salmon' => '', 'Seagull' => 'Birds', 'Dog' => 'Insects']);
        $this->assertEquals([
            '1. Bee' => new question_classified_response(1, 'Insects', 1),
            '2. Salmon' => question_classified_response::no_response(),
            '3. Seagull' => new question_classified_response(3, 'Birds', 1),
            '4. Dog' => new question_classified_response(1, 'Insects', 0),
        ], $question->classify_response($response));
    }

    public function test_prepare_simulated_post_data_single(): void {
        $this->resetAfterTest();
        $question = \test_question_maker::make_question('oumatrix', 'animals_single');
        $question->shuffleanswers = 0;
        $question->start_attempt(new question_attempt_step(), 1);

        $response = ['Bee' => 'Insects', 'Salmon' => 'Fish', 'Seagull' => 'Birds', 'Dog' => 'Mammals'];
        $expected = ['rowanswers0' => 1, 'rowanswers1' => 2, 'rowanswers2' => 3, 'rowanswers3' => 4];
        $this->assertEquals($expected, $question->prepare_simulated_post_data($response));

        $response = ['Bee' => 'Insects', 'Salmon' => 'Birds', 'Seagull' => 'Birds', 'Dog' => 'Mammals'];
        $expected = ['rowanswers0' => 1, 'rowanswers1' => 3, 'rowanswers2' => 3, 'rowanswers3' => 4];
        $this->assertEquals($expected, $question->prepare_simulated_post_data($response));

        $response = ['Bee' => 'Insects', 'Salmon' => 'Birds', 'Seagull' => 'Birds', 'Dog' => 'Insects'];
        $expected = ['rowanswers0' => 1, 'rowanswers1' => 3, 'rowanswers2' => 3, 'rowanswers3' => 1];
        $this->assertEquals($expected, $question->prepare_simulated_post_data($response));
    }

    public function test_is_same_response(): void {
        $question = \test_question_maker::make_question('oumatrix');
        $question->start_attempt(new question_attempt_step(), 1);

        $this->assertTrue($question->is_same_response(
                ['rowanswers0' => '1', 'rowanswers1' => '1', 'rowanswers2' => '1', 'rowanswers3' => '1'],
                ['rowanswers0' => '1', 'rowanswers1' => '1', 'rowanswers2' => '1', 'rowanswers3' => '1']));
        $this->assertFalse($question->is_same_response(
                ['rowanswers0' => '1', 'rowanswers1' => '1', 'rowanswers2' => '1', 'rowanswers3' => '1'],
                ['rowanswers0' => '1', 'rowanswers1' => '2', 'rowanswers2' => '1', 'rowanswers3' => '1']));
        $this->assertTrue($question->is_same_response(
                ['rowanswers0' => '1', 'rowanswers1' => '2', 'rowanswers2' => '1', 'rowanswers3' => '1'],
                ['rowanswers0' => '1', 'rowanswers1' => '2', 'rowanswers2' => '1', 'rowanswers3' => '1']));
        $this->assertFalse($question->is_same_response(
                ['rowanswers0' => '1', 'rowanswers1' => '2', 'rowanswers2' => '1', 'rowanswers3' => '1'],
                ['rowanswers0' => '1', 'rowanswers1' => '2', 'rowanswers2' => '3', 'rowanswers3' => '1']));
        $this->assertTrue($question->is_same_response(
                ['rowanswers0' => '1', 'rowanswers1' => '2', 'rowanswers2' => '3', 'rowanswers3' => '1'],
                ['rowanswers0' => '1', 'rowanswers1' => '2', 'rowanswers2' => '3', 'rowanswers3' => '1']));
        $this->assertFalse($question->is_same_response(
                ['rowanswers0' => '1', 'rowanswers1' => '2', 'rowanswers2' => '3', 'rowanswers3' => '1'],
                ['rowanswers0' => '1', 'rowanswers1' => '2', 'rowanswers2' => '3', 'rowanswers3' => '4']));
        $this->assertTrue($question->is_same_response(
                ['rowanswers0' => '1', 'rowanswers1' => '2', 'rowanswers2' => '3', 'rowanswers3' => '4'],
                ['rowanswers0' => '1', 'rowanswers1' => '2', 'rowanswers2' => '3', 'rowanswers3' => '4']));
    }

    public function test_get_correct_response(): void {
        $question = \test_question_maker::make_question('oumatrix', 'animals_single');
        $question->start_attempt(new question_attempt_step(), 1);
        $correct = ['rowanswers0' => '1', 'rowanswers1' => '2', 'rowanswers2' => '3', 'rowanswers3' => '4'];
        $this->assertEquals($correct, $question->get_correct_response());
    }

    public function test_summarise_response(): void {
        $question = \test_question_maker::make_question('oumatrix', 'animals_single');
        $question->start_attempt(new question_attempt_step(), 1);

        $actual = $question->summarise_response(
                ['rowanswers0' => '1', 'rowanswers1' => '2']);
        $expected = '/Bee → Insects; Salmon → Fish/';
        $this->assertMatchesRegularExpression($expected, $actual);

        $actual = $question->summarise_response(
                ['rowanswers0' => '1', 'rowanswers1' => '2', 'rowanswers2' => '3']);
        $expected = '/Bee → Insects; Salmon → Fish; Seagull → Birds/';
        $this->assertMatchesRegularExpression($expected, $actual);

        $actual = $question->summarise_response(
                ['rowanswers0' => '1', 'rowanswers1' => '1', 'rowanswers2' => '3', 'rowanswers3' => '4']);
        $expected = '/Bee → Insects; Salmon → Insects; Seagull → Birds; Dog → Mammals/';
        $this->assertMatchesRegularExpression($expected, $actual);

        $actual = $question->summarise_response(
                ['rowanswers0' => '1', 'rowanswers1' => '2', 'rowanswers2' => '3', 'rowanswers3' => '4']);
        $expected = '/Bee → Insects; Salmon → Fish; Seagull → Birds; Dog → Mammals/';
        $this->assertMatchesRegularExpression($expected, $actual);
    }

    public function test_is_complete_response(): void {
        $question = \test_question_maker::make_question('oumatrix');
        $question->start_attempt(new question_attempt_step(), 1);

        // Not complete responses.
        $this->assertFalse($question->is_complete_response([]));
        $this->assertFalse($question->is_complete_response(['rowanswers0' => '1']));
        $this->assertFalse($question->is_complete_response(['rowanswers0' => '1', 'rowanswers1' => '2']));
        $this->assertFalse($question->is_complete_response(['rowanswers0' => '1', 'rowanswers1' => '2',  'rowanswers2' => '3']));

        // Complete responses.
        $this->assertTrue($question->is_complete_response(
                ['rowanswers0' => '1', 'rowanswers1' => '2', 'rowanswers2' => '3', 'rowanswers3' => '4'])); // Coorrect.
        $this->assertTrue($question->is_complete_response(
                ['rowanswers0' => '1', 'rowanswers1' => '2', 'rowanswers2' => '3', 'rowanswers3' => '1'])); // Partially correct.
        $this->assertTrue($question->is_complete_response(
                ['rowanswers0' => '4', 'rowanswers1' => '3', 'rowanswers2' => '2', 'rowanswers3' => '1'])); // Incorrect.
    }

    public function test_grade_response(): void {
        $question = \test_question_maker::make_question('oumatrix', 'animals_single');
        $question->start_attempt(new question_attempt_step(), 1);

        $correctresponse = ['rowanswers0' => '1', 'rowanswers1' => '2', 'rowanswers2' => '3', 'rowanswers3' => '4'];
        $this->assertEquals([1, question_state::$gradedright], $question->grade_response($correctresponse));

        $partiallycorrectresponse = ['rowanswers0' => '1', 'rowanswers1' => '2', 'rowanswers2' => '3', 'rowanswers3' => '1'];
        $this->assertEquals([0.75, question_state::$gradedpartial], $question->grade_response($partiallycorrectresponse));

        $partialresponse = ['rowanswers0' => '1', 'rowanswers1' => '2', 'rowanswers2' => '3'];
        $this->assertEquals([0.75, question_state::$gradedpartial], $question->grade_response($partialresponse));

        $wrongresponse = ['rowanswers0' => '4', 'rowanswers1' => '3', 'rowanswers2' => '2', 'rowanswers3' => '1'];
        $this->assertEquals([0, question_state::$gradedwrong], $question->grade_response($wrongresponse));
    }

    public function test_get_num_parts_right(): void {
        $question = \test_question_maker::make_question('oumatrix');

        $question->start_attempt(new question_attempt_step(), 1);

        $actual = $question->get_num_parts_right(
                ['rowanswers0' => '1', 'rowanswers1' => '1', 'rowanswers2' => '1', 'rowanswers3' => '1']);
        $this->assertEquals([1, 4], $actual);

        $actual = $question->get_num_parts_right(
                ['rowanswers0' => '1', 'rowanswers1' => '2', 'rowanswers2' => '1', 'rowanswers3' => '1']);
        $this->assertEquals([2, 4], $actual);

        $actual = $question->get_num_parts_right(
                ['rowanswers0' => '1', 'rowanswers1' => '2', 'rowanswers2' => '3', 'rowanswers3' => '1']);
        $this->assertEquals([3, 4], $actual);

        $actual = $question->get_num_parts_right(
                ['rowanswers0' => '1', 'rowanswers1' => '2', 'rowanswers2' => '3', 'rowanswers3' => '4']);
        $this->assertEquals([4, 4], $actual);
    }

    public function test_validate_can_regrade_with_other_version_bad_num_rows(): void {
        $q = \test_question_maker::make_question('oumatrix');

        $newq = clone($q);
        $newq->id = 456;
        $newq->rows = [
            21 => new row(21, $newq->id, 1, 'Bee', [1 => '1'],
                    'Fly, Bee and spider are insects.', FORMAT_HTML),
            22 => new row(22, $newq->id, 2, 'Salmon', [2 => '1'],
                    'Cod, Salmon and Trout are fish.', FORMAT_HTML),
        ];

        $this->assertEquals(get_string('regradeissuenumrowschanged', 'qtype_oumatrix'),
                $newq->validate_can_regrade_with_other_version($q));
    }

    public function test_validate_can_regrade_with_other_version_bad_num_columns(): void {
        $q = \test_question_maker::make_question('oumatrix', 'animals_single');

        $newq = clone($q);
        $newq->id = 456;
        $newq->columns = [
            21 => new column($newq->id, 1, 'Insects', 21),
            22 => new column($newq->id, 2, 'Fish', 22),
            23 => new column($newq->id, 3, 'Birds', 23),
        ];

        $this->assertEquals(get_string('regradeissuenumcolumnschanged', 'qtype_oumatrix'),
                $newq->validate_can_regrade_with_other_version($q));
    }

    public function test_validate_can_regrade_with_other_version_ok(): void {
        /** @var \qtype_oumatrix_single $q */
        $q = \test_question_maker::make_question('oumatrix', 'animals_single');

        $newq = clone($q);
        $newq->id = 456;
        $newq->columns = [
            21 => new column($newq->id, 1, 'Insects', 21),
            22 => new column($newq->id, 2, 'Fish', 22),
            23 => new column($newq->id, 3, 'Birds', 23),
            24 => new column($newq->id, 4, 'Mammals', 24),
        ];

        $newq->rows = [
            21 => new row(21, $newq->id, 1, 'Bee', [1 => '1'],
                    'Fly, Bee and spider are insects.', FORMAT_HTML),
            22 => new row(22, $newq->id, 2, 'Salmon', [2 => '1'],
                    'Cod, Salmon and Trout are fish.', FORMAT_HTML),
            23 => new row(23, $newq->id, 3, 'Seagull', [3 => '1'],
                    'Gulls and Owls are birds.', FORMAT_HTML),
            24 => new row(24, $newq->id, 4, 'Dog', [4 => '1'],
                    'Cow, Dog and Horse are mammals.', FORMAT_HTML),
        ];

        $this->assertNull($newq->validate_can_regrade_with_other_version($q));
    }

    public function test_update_attempt_state_date_from_old_version_bad(): void {
        $q = \test_question_maker::make_question('oumatrix', 'animals_single');

        $newq = clone($q);
        $newq->id = 456;
        $newq->columns = [
            1 => new column($newq->id, 1, 'Insects', 21),
            2 => new column($newq->id, 2, 'Fish', 22),
            3 => new column($newq->id, 3, 'Birds', 23),
        ];

        $oldstep = new question_attempt_step();
        $oldstep->set_qt_var('_roworder', '12,23,11,14');
        $this->expectExceptionMessage(get_string('regradeissuenumcolumnschanged', 'qtype_oumatrix'));
        $newq->update_attempt_state_data_for_new_version($oldstep, $q);
    }

    public function test_update_attempt_state_date_from_old_version_ok(): void {
        $q = \test_question_maker::make_question('oumatrix', 'animals_single');

        $newq = clone($q);
        $newq->id = 456;
        $newq->columns = [
            1 => new column($newq->id, 1, 'Insects', 21),
            2 => new column($newq->id, 2, 'Fish', 22),
            3 => new column($newq->id, 3, 'Birds', 23),
            4 => new column($newq->id, 4, 'Mammals', 24),
        ];

        $newq->rows = [
            1 => new row(21, $newq->id, 1, 'Bee', [1 => '1'],
                    'Fly, Bee and spider are insects.', FORMAT_HTML),
            2 => new row(22, $newq->id, 2, 'Salmon', [2 => '1'],
                    'Cod, Salmon and Trout are fish.', FORMAT_HTML),
            3 => new row(23, $newq->id, 3, 'Seagull', [3 => '1'],
                    'Gulls and Owls are birds.', FORMAT_HTML),
            4 => new row(24, $newq->id, 4, 'Dog', [4 => '1'],
                    'Cow, Dog and Horse are mammals.', FORMAT_HTML),
        ];

        $oldstep = new question_attempt_step();
        $oldstep->set_qt_var('_roworder', '2,3,1,4');
        $this->assertEquals(['_roworder' => '2,3,1,4'],
                $newq->update_attempt_state_data_for_new_version($oldstep, $q));
    }

    public function test_has_specific_feedback(): void {
        $q = \test_question_maker::make_question('oumatrix', 'animals_single');

        // This question has speific feedback for all 4  rows.
        $this->assertTrue($q->has_specific_feedback());

        // First row does not have feedback text.
        $q->rows[1]->feedback = '';
        $this->assertTrue($q->has_specific_feedback());

        // First and second rows do not have feedback text.
        $q->rows[2]->feedback = '';
        $this->assertTrue($q->has_specific_feedback());

        // First, second and third rows do not have feedback text.
        $q->rows[3]->feedback = '';
        $this->assertTrue($q->has_specific_feedback());

        // All rows do not have feedback text.
        $q->rows[4]->feedback = '';
        $this->assertFalse($q->has_specific_feedback());
    }
}
