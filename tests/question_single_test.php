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

    public function test_get_expected_data() {
        $question = \test_question_maker::make_question('oumatrix');
        $question->start_attempt(new question_attempt_step(), 1);

        $expected = ['rowanswers0' => PARAM_INT, 'rowanswers1' => PARAM_INT,
                'rowanswers2' => PARAM_INT, 'rowanswers3' => PARAM_INT];
        $this->assertEquals($expected , $question->get_expected_data());
    }

    public function test_is_gradable_response() {
        $question = \test_question_maker::make_question('oumatrix');
        $question->start_attempt(new question_attempt_step(), 1);

        $response = ['rowanswers0' => '1', 'rowanswers1' => '1', 'rowanswers2' => '2', 'rowanswers3' => '2'];
        $this->assertEquals($question->is_gradable_response($response), $question->is_complete_response($response));

        $response = ['rowanswers0' => '1', 'rowanswers1' => '1', 'rowanswers2' => '2'];
        $this->assertEquals($question->is_gradable_response($response), $question->is_complete_response($response));
    }

    public function test_is_same_response() {
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

    public function test_get_correct_response() {
        $question = \test_question_maker::make_question('oumatrix', 'animals_single');
        $question->start_attempt(new question_attempt_step(), 1);
        $correct = ['rowanswers0' => '1', 'rowanswers1' => '2', 'rowanswers2' => '3', 'rowanswers3' => '4'];
        $this->assertEquals($correct, $question->get_correct_response());
    }

    public function test_summarise_response() {
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

    public function test_is_complete_response() {
        $question = \test_question_maker::make_question('oumatrix');
        $question->start_attempt(new question_attempt_step(), 1);

        // Not complete responses
        $this->assertFalse($question->is_complete_response([]));
        $this->assertFalse($question->is_complete_response(['rowanswers0' => '1']));
        $this->assertFalse($question->is_complete_response(['rowanswers0' => '1', 'rowanswers1' => '2']));
        $this->assertFalse($question->is_complete_response(['rowanswers0' => '1', 'rowanswers1' => '2',  'rowanswers1' => '3']));

        // Complete responses.
        $this->assertTrue($question->is_complete_response(
                ['rowanswers0' => '1', 'rowanswers1' => '2', 'rowanswers2' => '3', 'rowanswers3' => '4'])); // Coorrect.
        $this->assertTrue($question->is_complete_response(
                ['rowanswers0' => '1', 'rowanswers1' => '2', 'rowanswers2' => '3', 'rowanswers3' => '1'])); // Partially correct.
        $this->assertTrue($question->is_complete_response(
                ['rowanswers0' => '4', 'rowanswers1' => '3', 'rowanswers2' => '2', 'rowanswers3' => '1'])); // Incorrect.
    }

    public function test_grade_response() {
        $question = \test_question_maker::make_question('oumatrix', 'animals_single');
        $question->start_attempt(new question_attempt_step(), 1);

        $correctresponse = ['rowanswers0' => '1', 'rowanswers1' => '2', 'rowanswers2' => '3', 'rowanswers3' => '4'];
        $this->assertEquals([1, question_state::$gradedright], $question->grade_response($correctresponse));

        $partiallycorrectresponse = ['rowanswers0' => '1', 'rowanswers1' => '2', 'rowanswers2' => '3', 'rowanswers3' => '1'];
        $this->assertEquals([0.75, question_state::$gradedpartial], $question->grade_response($partiallycorrectresponse));

        $partialresponse = ['rowanswers0' => '1', 'rowanswers1' => '2', 'rowanswers2' => '3'];
        $this->assertEquals([0.75, question_state::$gradedpartial], $question->grade_response($partialresponse));

        $wrongresponse = ['rowanswers0' => '4', 'rowanswers1' => '3', 'rowanswers2' => '2', 'rowanswers3' => '1'];
        $this->assertEquals(array(0, question_state::$gradedwrong), $question->grade_response($wrongresponse));
    }

    public function test_get_num_parts_right() {
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
}
