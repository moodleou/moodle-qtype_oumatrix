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
use question_possible_response;
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

    public function test_name() {
        $this->assertEquals($this->qtype->name(), 'oumatrix');
    }

    protected function get_test_question_data() {
        return \test_question_maker::get_question_data('oumatrix', 'four_by_four_single');
    }

    public function test_initialise_question_instance() {
        $h = new qtype_oumatrix_test_helper();
        //$qsingle = $h->get_oumatrix_question_data_four_by_four_single();
        //$this->assertEquals(0.5, $this->qtype->get_random_guess_score($qsingle));

        $qmultiple = $h->get_oumatrix_question_data_three_by_three_multiple();
        //print_object($qmultiple);
        $qdata = $this->get_test_question_data();

        $expected = \test_question_maker::make_question('ddwtos');
        $expected->stamp = $qdata->stamp;
        $expected->idnumber = null;

        $q = $this->qtype->make_question($qdata);

        $this->assertEquals($expected, $q);
    }

    public function test_get_random_guess_score() {
        $h = new qtype_oumatrix_test_helper();

        $qsingle = $h->get_oumatrix_question_data_four_by_four_single();
        $this->assertEquals(0.5, $this->qtype->get_random_guess_score($qsingle));

        $qmultiple = $h->get_oumatrix_question_data_three_by_three_multiple();
        print_object('$qsingle ------------------------------------------------');
        print_object($qmultiple);
        return;
        $this->assertEquals(0.5, $this->qtype->get_random_guess_score($qmultiple));
    }

    public function test_get_random_guess_score_broken_question() {
        $q = $this->get_test_question_data();
        $q->options->rows= [];
        $this->assertNull($this->qtype->get_random_guess_score($q));
    }

    public function test_get_possible_responses_multi() {
        $q = $this->get_test_question_data();
        $q->options->single = false;

        $this->assertEquals(array(
            1 => array(1 => new question_possible_response('frog', 1)),
            2 => array(2 => new question_possible_response('toad', 0)),
        ), $this->qtype->get_possible_responses($q));
    }

    public function get_question_saving_which() {
        return [['four_by_four_songle'], ['three_by_three_multiple']];
    }

    /**
     * @dataProvider get_question_saving
     */
    public function test_question_saving($which) {
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $questiondata = \test_question_maker::get_question_data('oumatrix', $which);
        $formdata = \test_question_maker::get_question_form_data('oumatrix', $which);

        $generator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $generator->create_question_category(array());

        $formdata->category = "{$cat->id},{$cat->contextid}";
        qtype_oumatrix_edit_form::mock_submit((array)$formdata);

        $form = \qtype_oumatrix_test_helper::get_question_editing_form($cat, $questiondata);

        $this->assertTrue($form->is_validated());

        $fromform = $form->get_data();

        $returnedfromsave = $this->qtype->save_question($questiondata, $fromform);
        $actualquestionsdata = question_load_questions([$returnedfromsave->id], 'qbe.idnumber');
        $actualquestiondata = end($actualquestionsdata);

        foreach ($questiondata as $property => $value) {
            if (!in_array($property, ['id', 'timemodified', 'timecreated', 'options', 'hints', 'stamp',
                'versionid', 'questionbankentryid'])) {
                $this->assertEquals($value, $actualquestiondata->$property);
            }
        }

        foreach ($questiondata->options as $optionname => $value) {
            if ($optionname === 'columns' || $optionname === 'rows') {
                continue;
            }
            $this->assertEquals($value, $actualquestiondata->options->$optionname);
        }

        foreach ($questiondata->options->columns as $id => $column) {
            $actualcolumn = array_shift($actualquestiondata->options->columns);
            // TODO: finsih this.
        }

        foreach ($questiondata->options->rows as $id => $row) {
            $actualrow = array_shift($actualquestiondata->options->rows);
            // TODO: finsih this.
        }

        foreach ($questiondata->hints as $hint) {
            $actualhint = array_shift($actualquestiondata->hints);
            foreach ($hint as $property => $value) {
                if (!in_array($property, array('id', 'questionid', 'options'))) {
                    $this->assertEquals($value, $actualhint->$property);
                }
            }
        }
    }

    /**
     * Test to make sure that loading of question options works, including in an error case.
     */
    public function test_get_question_options() {
        global $DB;

        $this->resetAfterTest(true);
        $this->setAdminUser();

        // Create a complete, in DB question to use.
        $questiondata = \test_question_maker::get_question_data('oumatrix', 'four_by_four_single');
        $formdata = \test_question_maker::get_question_form_data('oumatrix', 'four_by_four_single');

        $generator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $generator->create_question_category(array());

        $formdata->category = "{$cat->id},{$cat->contextid}";
        qtype_oumatrix_edit_form::mock_submit((array)$formdata);

        $form = \qtype_oumatrix_test_helper::get_question_editing_form($cat, $questiondata);

        $this->assertTrue($form->is_validated());

        $fromform = $form->get_data();

        $returnedfromsave = $this->qtype->save_question($questiondata, $fromform);

        // Now get just the raw DB record.
        $question = $DB->get_record('question', ['id' => $returnedfromsave->id], '*', MUST_EXIST);

        // Load it.
        $this->qtype->get_question_options($question);
        $this->assertDebuggingNotCalled();
        $this->assertInstanceOf(\stdClass::class, $question->options);

        $options = $question->options;
        $this->assertEquals($question->id, $options->questionid);
        $this->assertEquals(0, $options->single);

        $this->assertCount(4, $options->answers);

        // Now we are going to delete the options record.
        $DB->delete_records('qtype_oumatrix_options', ['questionid' => $question->id]);

        // Now see what happens.
        $question = $DB->get_record('question', ['id' => $returnedfromsave->id], '*', MUST_EXIST);
        $this->qtype->get_question_options($question);

        $this->assertDebuggingCalled('Question ID '.$question->id.' was missing an options record. Using default.');
        $this->assertInstanceOf(\stdClass::class, $question->options);
        $options = $question->options;
        $this->assertEquals($question->id, $options->questionid);
        $this->assertCount(4, $options->answers);

        $this->assertEquals(get_string('correctfeedbackdefault', 'question'), $options->correctfeedback);
        $this->assertEquals(FORMAT_HTML, $options->correctfeedbackformat);

        // We no longer know how many answers, so it just has to guess with the default value.
        $this->assertEquals(get_config('qtype_oumatrix', 'answerhowmany'), $options->single);

        // And finally we try again with no answer either.
        $DB->delete_records('question_answers', ['question' => $question->id]);

        $question = $DB->get_record('question', ['id' => $returnedfromsave->id], '*', MUST_EXIST);
        $this->qtype->get_question_options($question);

        $this->assertDebuggingCalled('Question ID '.$question->id.' was missing an options record. Using default.');
        $this->assertInstanceOf(\stdClass::class, $question->options);
        $options = $question->options;
        $this->assertEquals($question->id, $options->questionid);
        $this->assertCount(0, $options->answers);
    }
}
