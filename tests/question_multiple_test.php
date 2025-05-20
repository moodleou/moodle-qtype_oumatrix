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
 * Unit tests for the matching question definition class.
 *
 * @package   qtype_oumatrix
 * @copyright 2023 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \qtype_oumatrix_multiple
 */
final class question_multiple_test extends \advanced_testcase {

    public function test_get_expected_data(): void {
        $question = \test_question_maker::make_question('oumatrix', 'food_multiple');
        $question->start_attempt(new question_attempt_step(), 1);

        $expected = [];
        foreach ($question->rows as $row) {
            foreach ($question->columns as $column) {
                $expected['rowanswers' . ($row->number - 1) . '_' . $column->number] = PARAM_INT;
            }
        }
        $this->assertEquals($expected , $question->get_expected_data());
    }

    public function test_is_complete_response(): void {
        $question = \test_question_maker::make_question('oumatrix', 'food_multiple');
        $question->start_attempt(new question_attempt_step(), 1);

        // For complete response, each row needs to have at least one response.

        // No response at all.
        $response = [];
        $this->assertFalse($question->is_complete_response($response));

        // No response for second row.
        $response = ['rowanswers0_1' => '1'];
        $this->assertFalse($question->is_complete_response($response));

        // No response for third row.
        $response = ['rowanswers0_1' => '1', 'rowanswers1_2' => '1'];
        $this->assertFalse($question->is_complete_response($response));

        // Each row has at least one response.
        $response = ['rowanswers0_1' => '1', 'rowanswers1_1' => '1', 'rowanswers2_1' => '1'];
        $this->assertTrue($question->is_complete_response($response));

        // Each row has one or more responses (they are actually correct responses).
        $response = [
                'rowanswers0_1' => '1',
                'rowanswers0_3' => '1',
                'rowanswers0_6' => '1',
                'rowanswers1_2' => '1',
                'rowanswers1_4' => '1',
                'rowanswers1_7' => '1',
                'rowanswers2_5' => '1',
        ];
        $this->assertTrue($question->is_complete_response($response));
    }

    public function test_is_gradable_response(): void {
        $question = \test_question_maker::make_question('oumatrix', 'food_multiple');
        $question->start_attempt(new question_attempt_step(), 1);

        // If all rows (sub-questions) are answered then the response is gradable.
        $response = ['rowanswers0_1' => '1'];
        $this->assertTrue($question->is_gradable_response($response));

        $response = ['rowanswers0_1' => '1', 'rowanswers1_2' => '1'];
        $this->assertTrue($question->is_gradable_response($response));

        $response = ['rowanswers0_1' => '1', 'rowanswers1_1' => '1', 'rowanswers2_1' => '1'];
        $this->assertTrue($question->is_gradable_response($response));

        $response = [];
        $this->assertFalse($question->is_gradable_response($response));
    }

    public function test_classify_response_multiple(): void {
        $this->resetAfterTest();
        $question = \test_question_maker::make_question('oumatrix', 'food_multiple');
        $question->shuffleanswers = 0;
        $question->start_attempt(new question_attempt_step(), 1);

        // Test a correct response.
        $response = $question->prepare_simulated_post_data([
                'Proteins' => [1 => 'Chicken breast', 3 => 'Salmon fillet', 6 => 'Steak'],
                'Vegetables' => [2 => 'Carrot', 4 => 'Asparagus', 7 => 'Potato'],
                'Fats' => [5 => 'Olive oil']]);
        $this->assertEquals([
               "1. Proteins: Chicken breast" => new question_classified_response(1, 'Selected', 1 / 3),
               "1. Proteins: Salmon fillet" => new question_classified_response(1, 'Selected', 1 / 3),
               "1. Proteins: Steak" => new question_classified_response(1, 'Selected', 1 / 3),
               "2. Vegetables: Carrot" => new question_classified_response(1, 'Selected', 1 / 3),
               "2. Vegetables: Asparagus" => new question_classified_response(1, 'Selected', 1 / 3),
               "2. Vegetables: Potato" => new question_classified_response(1, 'Selected', 1 / 3),
               "3. Fats: Olive oil" => new question_classified_response(1, 'Selected', 1),
        ], $question->classify_response($response));

        // Test a partial response.
        $response = $question->prepare_simulated_post_data([
                'Proteins' => [1 => 'Chicken breast', 4 => 'Asparagus'],
                'Vegetables' => [2 => 'Carrot', 1 => 'Chicken breast'],
                'Fats' => [5 => 'Olive oil']]);
        $this->assertEquals([
               "1. Proteins: Chicken breast" => new question_classified_response(1, 'Selected', 1 / 3),
               "1. Proteins: Asparagus" => new question_classified_response(1, 'Selected', 0),
               "2. Vegetables: Chicken breast" => new question_classified_response(1, 'Selected', 0),
               "2. Vegetables: Carrot" => new question_classified_response(1, 'Selected', 1 / 3),
               "3. Fats: Olive oil" => new question_classified_response(1, 'Selected', 1),
        ], $question->classify_response($response));
    }

    public function test_prepare_simulated_post_data_multiple(): void {
        $this->resetAfterTest();
        $question = \test_question_maker::make_question('oumatrix', 'food_multiple');
        $question->shuffleanswers = 0;
        $question->start_attempt(new question_attempt_step(), 1);

        $response = ['Proteins' => [1 => 'Chicken breast', 3 => 'Salmon fillet', 6 => 'Steak'],
                'Vegetables' => [2 => 'Carrot', 4 => 'Asparagus', 7 => 'Potato'], 'Fats' => [5 => 'Olive oil']];

        $expected = [
                'rowanswers0_1' => '1',
                'rowanswers0_2' => '0',
                'rowanswers0_3' => '1',
                'rowanswers0_4' => '0',
                'rowanswers0_5' => '0',
                'rowanswers0_6' => '1',
                'rowanswers0_7' => '0',

                'rowanswers1_1' => '0',
                'rowanswers1_2' => '1',
                'rowanswers1_3' => '0',
                'rowanswers1_4' => '1',
                'rowanswers1_5' => '0',
                'rowanswers1_6' => '0',
                'rowanswers1_7' => '1',

                'rowanswers2_1' => '0',
                'rowanswers2_2' => '0',
                'rowanswers2_3' => '0',
                'rowanswers2_4' => '0',
                'rowanswers2_5' => '1',
                'rowanswers2_6' => '0',
                'rowanswers2_7' => '0',
        ];
        $this->assertEquals($expected, $question->prepare_simulated_post_data($response));
    }


    public function test_is_same_response(): void {
        $question = \test_question_maker::make_question('oumatrix', 'food_multiple');
        $question->start_attempt(new question_attempt_step(), 1);

        $this->assertTrue($question->is_same_response(
                ['rowanswers0_1' => '1', 'rowanswers1_2' => '1', 'rowanswers2_3' => '1'],
                ['rowanswers0_1' => '1', 'rowanswers1_2' => '1', 'rowanswers2_3' => '1']));
        $this->assertFalse($question->is_same_response(
                ['rowanswers0_1' => '1', 'rowanswers1_2' => '1', 'rowanswers2_3' => '1'],
                ['rowanswers0_1' => '1', 'rowanswers1_2' => '1', 'rowanswers2_1' => '1']));
        $this->assertTrue($question->is_same_response(
                ['rowanswers0_1' => '1', 'rowanswers0_2' => '1', 'rowanswers0_3' => '1'],
                ['rowanswers0_1' => '1', 'rowanswers0_2' => '1', 'rowanswers0_3' => '1']));
        $this->assertFalse($question->is_same_response(
                ['rowanswers0_2' => '1', 'rowanswers1_3' => '1', 'rowanswers2_4' => '1'],
                ['rowanswers0_2' => '1', 'rowanswers1_3' => '1', 'rowanswers2_3' => '1']));
        $this->assertTrue($question->is_same_response(
                ['rowanswers0' => '1', 'rowanswers1' => '2', 'rowanswers2' => '3', 'rowanswers3' => '1'],
                ['rowanswers0' => '1', 'rowanswers1' => '2', 'rowanswers2' => '3', 'rowanswers3' => '1']));

        $correctresponse = [
                'rowanswers0_1' => '1',
                'rowanswers0_3' => '1',
                'rowanswers0_6' => '1',
                'rowanswers1_2' => '1',
                'rowanswers1_4' => '1',
                'rowanswers1_7' => '1',
                'rowanswers2_5' => '1',
        ];
        $this->assertFalse($question->is_same_response($correctresponse, [
                'rowanswers0_1' => '1',
                'rowanswers0_3' => '1',
                'rowanswers0_6' => '1',
                'rowanswers1_2' => '1',
                'rowanswers1_4' => '1',
                'rowanswers1_7' => '1',
        ]));
        $this->assertTrue($question->is_same_response($correctresponse, [
                'rowanswers0_1' => '1',
                'rowanswers0_3' => '1',
                'rowanswers0_6' => '1',
                'rowanswers1_2' => '1',
                'rowanswers1_4' => '1',
                'rowanswers1_7' => '1',
                'rowanswers2_5' => '1',
        ]));
    }

    public function test_get_correct_response(): void {
        $question = \test_question_maker::make_question('oumatrix', 'food_multiple');
        $question->start_attempt(new question_attempt_step(), 1);

        // Here are correct responses.
        $expectedcorrect = [
                'rowanswers0_1' => '1',
                'rowanswers0_3' => '1',
                'rowanswers0_6' => '1',
                'rowanswers1_2' => '1',
                'rowanswers1_4' => '1',
                'rowanswers1_7' => '1',
                'rowanswers2_5' => '1',
        ];
        $this->assertEquals($expectedcorrect, $question->get_correct_response());

        // Here are some random responses.
        $expectedincorrect = [
                'rowanswers0_1' => '1',
                'rowanswers0_2' => '1',
                'rowanswers0_3' => '1',
                'rowanswers1_1' => '1',
                'rowanswers1_2' => '1',
                'rowanswers1_3' => '1',
                'rowanswers2_1' => '1',
        ];
        $this->assertNotEquals($expectedincorrect, $question->get_correct_response());
    }

    public function test_summarise_response(): void {
        $question = \test_question_maker::make_question('oumatrix', 'food_multiple');
        $question->start_attempt(new question_attempt_step(), 1);

        $expected = '/Proteins → Chicken breast, Salmon fillet, Steak/';
        $this->assertMatchesRegularExpression($expected, $question->summarise_response(
                ['rowanswers0_1' => '1', 'rowanswers0_3' => '1', 'rowanswers0_6' => '1']));

        $expected = '/Proteins → Chicken breast, Salmon fillet, Steak; Vegetables → Carrot, Asparagus, Potato/';
        $this->assertMatchesRegularExpression($expected, $question->summarise_response([
                'rowanswers0_1' => '1',
                'rowanswers0_3' => '1',
                'rowanswers0_6' => '1',
                'rowanswers1_2' => '1',
                'rowanswers1_4' => '1',
                'rowanswers1_7' => '1',
        ]));

        $expected = '/Proteins → Chicken breast, Salmon fillet, Steak; Vegetables → Carrot, Asparagus, Potato; Fats → Olive oil/';
        $this->assertMatchesRegularExpression($expected, $question->summarise_response([
                'rowanswers0_1' => '1',
                'rowanswers0_3' => '1',
                'rowanswers0_6' => '1',
                'rowanswers1_2' => '1',
                'rowanswers1_4' => '1',
                'rowanswers1_7' => '1',
                'rowanswers2_5' => '1',
        ]));

        $expected = '/Proteins → Chicken breast; Vegetables → Carrot; Fats → Salmon fillet/';
        $this->assertMatchesRegularExpression($expected, $question->summarise_response(
                ['rowanswers0_1' => '1', 'rowanswers1_2' => '1', 'rowanswers2_3' => '1']));
    }

    public function test_grade_response(): void {
        $question = \test_question_maker::make_question('oumatrix', 'food_multiple');
        $question->start_attempt(new question_attempt_step(), 1);

        $expectedcorrectresponse = [
                'rowanswers0_1' => '1',
                'rowanswers0_3' => '1',
                'rowanswers0_6' => '1',
                'rowanswers1_2' => '1',
                'rowanswers1_4' => '1',
                'rowanswers1_7' => '1',
                'rowanswers2_5' => '1',
        ];
        $this->assertEquals([1, question_state::$gradedright], $question->grade_response($expectedcorrectresponse));

        $expectedpartiallycorrectresponse = [
                'rowanswers0_1' => '1',
                'rowanswers0_3' => '1',
                'rowanswers0_6' => '1',
                'rowanswers1_2' => '1',
                'rowanswers2_4' => '1',
                'rowanswers2_7' => '1',
                'rowanswers2_5' => '1',
        ];
        $this->assertEqualsWithDelta([0.7142857, question_state::$gradedpartial],
                $question->grade_response($expectedpartiallycorrectresponse), 0.0000001);

        $expectedpartialresponse = [
                'rowanswers0_1' => '1',
                'rowanswers0_3' => '1',
                'rowanswers0_6' => '1',
                'rowanswers1_1' => '1',
                'rowanswers2_2' => '1',
        ];
        $this->assertEqualsWithDelta([0.4285714, question_state::$gradedpartial],
                $question->grade_response($expectedpartialresponse), 0.0000001);

        $expectedwrongresponse = [
                'rowanswers0_2' => '1',
                'rowanswers0_4' => '1',
                'rowanswers0_7' => '1',
                'rowanswers1_6' => '1',
                'rowanswers1_5' => '1',
                'rowanswers1_3' => '1',
                'rowanswers2_2' => '1',
        ];
        $this->assertEquals([0, question_state::$gradedwrong], $question->grade_response($expectedwrongresponse));
    }

    public function test_get_num_grade_allornone(): void {
        $question = \test_question_maker::make_question('oumatrix', 'food_multiple');
        $question->start_attempt(new question_attempt_step(), 1);

        // All three rows have correct responses.
        $allrowsarecorrect = [
                'rowanswers0_1' => '1',
                'rowanswers0_3' => '1',
                'rowanswers0_6' => '1',
                'rowanswers1_2' => '1',
                'rowanswers1_4' => '1',
                'rowanswers1_7' => '1',
                'rowanswers2_5' => '1',
        ];
        $this->assertEquals([3, 3], $question->get_num_grade_allornone($allrowsarecorrect));

        // First and second rows are correct, and third row is incorrect.
        $rowsoneandtwoarecorrect = [
                'rowanswers0_1' => '1',
                'rowanswers0_3' => '1',
                'rowanswers0_6' => '1',
                'rowanswers1_2' => '1',
                'rowanswers1_4' => '1',
                'rowanswers1_7' => '1',
                'rowanswers2_1' => '1',
        ];
        $this->assertEquals([2, 3], $question->get_num_grade_allornone($rowsoneandtwoarecorrect));

        // First row is correct, second row does not have all responses and third row is incorrect.
        $rowoneiscorrect = [
                'rowanswers0_1' => '1',
                'rowanswers0_3' => '1',
                'rowanswers0_6' => '1',
                'rowanswers1_2' => '1',
                'rowanswers1_4' => '1',
                'rowanswers1_1' => '1',
                'rowanswers2_1' => '1',
        ];
        $this->assertEquals([1, 3], $question->get_num_grade_allornone($rowoneiscorrect));

        // Second row is correct, first has not been answered and third row is incorrect.
        $rowtwoiscorrect = [
                'rowanswers1_2' => '1',
                'rowanswers1_4' => '1',
                'rowanswers1_7' => '1',
                'rowanswers2_1' => '1',
        ];
        $this->assertEquals([1, 3], $question->get_num_grade_allornone($rowtwoiscorrect));
    }

    public function test_get_num_parts_grade_partial(): void {
        $question = \test_question_maker::make_question('oumatrix', 'food_multiple');
        $question->start_attempt(new question_attempt_step(), 1);

        // Seven responses are given and all of them are correct.
        $this->assertEquals([7, 7], $question->get_num_parts_grade_partial([
                'rowanswers0_1' => '1',
                'rowanswers0_3' => '1',
                'rowanswers0_6' => '1',
                'rowanswers1_2' => '1',
                'rowanswers1_4' => '1',
                'rowanswers1_7' => '1',
                'rowanswers2_5' => '1',
        ]));

        // Seven responses are given and one response is wrong.
        $this->assertEquals([6, 7], $question->get_num_parts_grade_partial([
                'rowanswers0_1' => '1',
                'rowanswers0_3' => '1',
                'rowanswers0_6' => '1',
                'rowanswers1_2' => '1',
                'rowanswers1_4' => '1',
                'rowanswers1_7' => '1',
        ]));

        // Five responsess are given and all are correct.
        $this->assertEquals([5, 7], $question->get_num_parts_grade_partial([
                'rowanswers0_1' => '1',
                'rowanswers0_3' => '1',
                'rowanswers0_6' => '1',
                'rowanswers1_2' => '1',
                'rowanswers2_5' => '1',
        ]));

        // Three responsess are given and all are correct.
        $this->assertEquals([3, 7], $question->get_num_parts_grade_partial(
                ['rowanswers0_1' => '1', 'rowanswers1_2' => '1', 'rowanswers2_5' => '1']));

        // Two responsess are given and both are correct (Third row has not been answered).
        $this->assertEquals([2, 7], $question->get_num_parts_grade_partial(['rowanswers0_1' => '1', 'rowanswers1_2' => '1']));

        // Two responsess are given and both are correct (Second and third row have not been answered).
        $this->assertEquals([2, 7], $question->get_num_parts_grade_partial(['rowanswers0_1' => '1', 'rowanswers0_3' => '1']));
    }

    public function test_get_num_correct_choices(): void {
        $question = \test_question_maker::make_question('oumatrix', 'food_multiple');

        // Correct number of choices are expected.
        $this->assertEquals(7, $question->get_num_correct_choices());
    }
}
