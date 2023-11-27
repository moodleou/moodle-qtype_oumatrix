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
use qtype_oumatrix_edit_form;
use qtype_oumatrix_test_helper;

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
class questiontype_test extends \advanced_testcase {
    protected $qtype;

    protected function setUp(): void {
        $this->qtype = new qtype_oumatrix();
    }

    protected function tearDown(): void {
        $this->qtype = null;
    }

    public function test_name(): void {
        $this->assertEquals($this->qtype->name(), 'oumatrix');
    }


    public function test_get_random_guess_score(): void {
        $helper = new qtype_oumatrix_test_helper();

        $qdata = $helper->get_test_question_data('animals_single');
        $this->assertEquals(0.25, $this->qtype->get_random_guess_score($qdata));

        $qdata = $helper->get_test_question_data('food_multiple');
        $this->assertEquals(null, $this->qtype->get_random_guess_score($qdata));
    }

    public function test_get_random_guess_score_broken_question(): void {
        $helper = new qtype_oumatrix_test_helper();
        $q = $helper->get_test_question_data('animals_single');
        $q->columns = [];
        $this->assertNull($this->qtype->get_random_guess_score($q));
    }

    public function get_save_question_which() {
        return [['animals_single'], ['oumatrix_multiple']];
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
