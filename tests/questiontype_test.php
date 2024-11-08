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

use qtype_oumatrix;
use qtype_oumatrix_test_helper;
use question_bank;
use question_possible_response;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/question/engine/tests/helpers.php');
require_once($CFG->dirroot . '/question/type/oumatrix/tests/helper.php');
require_once($CFG->dirroot . '/question/type/oumatrix/questiontype.php');
require_once($CFG->dirroot . '/question/type/edit_question_form.php');
require_once($CFG->dirroot . '/question/type/oumatrix/edit_oumatrix_form.php');

/**
 * Unit tests for the OU matrix question definition class.
 *
 * @package   qtype_oumatrix
 * @copyright 2023 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers    \qtype_oumatrix
 */
final class questiontype_test extends \advanced_testcase {
    /** @var qtype_oumatrix|null instance of the question type to use in tests. */
    protected ?qtype_oumatrix $qtype;

    #[\Override]
    protected function setUp(): void {
        parent::setUp();
        $this->qtype = new qtype_oumatrix();
    }

    #[\Override]
    protected function tearDown(): void {
        $this->qtype = null;
        parent::tearDown();
    }

    public function test_name(): void {
        $this->assertEquals($this->qtype->name(), 'oumatrix');
    }

    public function test_get_random_guess_score(): void {

        $qdata = \test_question_maker::get_question_data('oumatrix', 'animals_single');
        $this->assertEquals(0.25, $this->qtype->get_random_guess_score($qdata));

        $qdata = \test_question_maker::get_question_data('oumatrix', 'food_multiple');
        $this->assertEquals(null, $this->qtype->get_random_guess_score($qdata));
    }

    public function test_get_random_guess_score_broken_question(): void {
        $q = \test_question_maker::get_question_data('oumatrix', 'animals_single');
        $q->columns = [];
        $this->assertNull($this->qtype->get_random_guess_score($q));
    }

    public function test_get_possible_responses_single(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator()->get_plugin_generator('core_question');

        $category = $generator->create_question_category([]);
        $createdquestion = $generator->create_question('oumatrix', 'animals_single',
                ['category' => $category->id, 'name' => 'Test question']);
        $q = question_bank::load_question_data($createdquestion->id);

        $expected = [
            '1. Bee' => [
                1 => new question_possible_response('Insects', 1),
                2 => new question_possible_response('Fish', 0),
                3 => new question_possible_response('Birds', 0),
                4 => new question_possible_response('Mammals', 0),
                null => question_possible_response::no_response(),
            ],
            '2. Salmon' => [
                1 => new question_possible_response('Insects', 0),
                2 => new question_possible_response('Fish', 1),
                3 => new question_possible_response('Birds', 0),
                4 => new question_possible_response('Mammals', 0),
                null => question_possible_response::no_response(),
            ],
            '3. Seagull' => [
                1 => new question_possible_response('Insects', 0),
                2 => new question_possible_response('Fish', 0),
                3 => new question_possible_response('Birds', 1),
                4 => new question_possible_response('Mammals', 0),
                null => question_possible_response::no_response(),
            ],
            '4. Dog' => [
                1 => new question_possible_response('Insects', 0),
                2 => new question_possible_response('Fish', 0),
                3 => new question_possible_response('Birds', 0),
                4 => new question_possible_response('Mammals', 1),
                null => question_possible_response::no_response(),
            ],
        ];
        $this->assertEquals($expected, $this->qtype->get_possible_responses($q));
    }

    public function test_get_possible_responses_multiple(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $category = $generator->create_question_category([]);
        $createdquestion = $generator->create_question('oumatrix', 'food_multiple',
                ['category' => $category->id, 'name' => 'Test question']);
        $q = question_bank::load_question_data($createdquestion->id);
        $expected = [
            '1. Proteins: Chicken breast' => [1 => new question_possible_response('Selected', 1 / 3)],
            '1. Proteins: Carrot' => [1 => new question_possible_response('Selected', 0)],
            '1. Proteins: Salmon fillet' => [1 => new question_possible_response('Selected', 1 / 3)],
            '1. Proteins: Asparagus' => [1 => new question_possible_response('Selected', 0)],
            '1. Proteins: Olive oil' => [1 => new question_possible_response('Selected', 0)],
            '1. Proteins: Steak' => [1 => new question_possible_response('Selected', 1 / 3)],
            '1. Proteins: Potato' => [1 => new question_possible_response('Selected', 0)],
            '2. Vegetables: Chicken breast' => [1 => new question_possible_response('Selected', 0)],
            '2. Vegetables: Carrot' => [1 => new question_possible_response('Selected', 1 / 3)],
            '2. Vegetables: Salmon fillet' => [1 => new question_possible_response('Selected', 0)],
            '2. Vegetables: Asparagus' => [1 => new question_possible_response('Selected', 1 / 3)],
            '2. Vegetables: Olive oil' => [1 => new question_possible_response('Selected', 0)],
            '2. Vegetables: Steak' => [1 => new question_possible_response('Selected', 0)],
            '2. Vegetables: Potato' => [1 => new question_possible_response('Selected', 1 / 3)],
            '3. Fats: Chicken breast' => [1 => new question_possible_response('Selected', 0)],
            '3. Fats: Carrot' => [1 => new question_possible_response('Selected', 0)],
            '3. Fats: Salmon fillet' => [1 => new question_possible_response('Selected', 0)],
            '3. Fats: Asparagus' => [1 => new question_possible_response('Selected', 0)],
            '3. Fats: Olive oil' => [1 => new question_possible_response('Selected', 1)],
            '3. Fats: Steak' => [1 => new question_possible_response('Selected', 0)],
            '3. Fats: Potato' => [1 => new question_possible_response('Selected', 0)],
        ];
        $this->assertEquals($expected, $this->qtype->get_possible_responses($q));
    }

    public function test_load_question(): void {
        $this->resetAfterTest();

        /** @var \core_question_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $category = $generator->create_question_category([]);
        $createdquestion = $generator->create_question('oumatrix', null,
            ['category' => $category->id, 'name' => 'Test question']);

        $question = \question_bank::load_question_data($createdquestion->id);
        $this->assertEquals($createdquestion->id, $question->id);
    }
}
