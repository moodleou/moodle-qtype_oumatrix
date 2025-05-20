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

use stdClass;
use qtype_oumatrix_test_helper;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');
require_once($CFG->dirroot . '/question/type/oumatrix/tests/helper.php');
require_once($CFG->dirroot . '/question/type/oumatrix/questiontype.php');
require_once($CFG->dirroot . '/question/type/edit_question_form.php');
require_once($CFG->dirroot . '/question/type/oumatrix/edit_oumatrix_form.php');


/**
 * Unit tests for oumatrix question edit form.
 *
 * @package    qtype_oumatrix
 * @copyright  2023 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \qtype_oumatrix_edit_form
 */
final class edit_oumatrix_form_test extends \advanced_testcase {

    /**
     * Helper method.
     *
     * @param string $classname the question form class to instantiate.
     *
     * @return array with two elements:
     *      question_edit_form great a question form instance that can be tested.
     *      stdClass the question category.
     */
    protected function get_form(string $classname): array {
        $this->setAdminUser();
        $this->resetAfterTest();
        if (\core_plugin_manager::instance()->get_plugin_info('mod_qbank')) {
            $course = self::getDataGenerator()->create_course();
            $qbank = self::getDataGenerator()->create_module('qbank', ['course' => $course->id]);
            $context = \context_module::instance($qbank->cmid);
            $category = question_get_default_category($context->id, true);
        } else {
            $context = \context_system::instance();
            $category = question_make_default_categories([$context]);
        }
        $fakequestion = new stdClass();
        $fakequestion->qtype = 'oumatrix';
        $fakequestion->contextid = $context->id;
        $fakequestion->createdby = 2;
        $fakequestion->category = $category->id;
        $fakequestion->questiontext = 'Animal classification. Please answer the sub questions in all 4 rows.';
        $fakequestion->options = new stdClass();
        $fakequestion->options->answers = [];
        $fakequestion->formoptions = new stdClass();
        $fakequestion->formoptions->movecontext = null;
        $fakequestion->formoptions->repeatelements = true;
        $fakequestion->inputs = null;

        $form = new $classname(new \moodle_url('/'), $fakequestion, $category,
                new \core_question\local\bank\question_edit_contexts($context));

        return [$form, $category];
    }

    /**
     * Test the form correctly validates minimum requirements of rows and columns.
     */
    public function test_validation_cols_rows_minimum(): void {
        [$form, $category] = $this->get_form('qtype_oumatrix_edit_form');
        $formdata = (array) \test_question_maker::get_question_form_data('oumatrix', 'animals_single');
        $formdata['category'] = $category->id;

        // Minumum number of columns.
        $testdata = $formdata;
        $testdata['columnname'][1] = '';
        $testdata['columnname'][2] = '';
        $testdata['columnname'][3] = '';
        $expected = get_string('notenoughanswercols', 'qtype_oumatrix', column::MIN_NUMBER_OF_COLUMNS);
        $errors = $form->validation($testdata, []);
        $this->assertArrayNotHasKey('columnname[0]', $errors);
        $this->assertArrayHasKey('columnname[1]', $errors);
        $this->assertArrayNotHasKey('columnname[2]', $errors);
        $this->assertArrayNotHasKey('columnname[3]', $errors);
        $this->assertEquals($expected, $errors['columnname[1]']);

        // Minumum number of rows.
        $testdata = $formdata;
        $testdata['rowname'][1] = '';
        $testdata['rowname'][2] = '';
        $testdata['rowname'][3] = '';
        $errors = $form->validation($testdata, []);
        $expected = get_string('notenoughquestionrows', 'qtype_oumatrix', row::MIN_NUMBER_OF_ROWS);
        $this->assertEquals($expected, $errors['rowname[1]']);
    }

    /**
     * Test the form correctly validates duplicates of rows and columns.
     */
    public function test_validation_cols_rows_duplicates(): void {
        [$form, $category] = $this->get_form('qtype_oumatrix_edit_form');
        $formdata = (array) \test_question_maker::get_question_form_data('oumatrix', 'animals_single');
        $formdata['category'] = $category->id;

        // Duplicate a column name.
        $testdata = $formdata;
        $testdata['columnname'][1] = 'Insects';
        $expected = get_string('duplicates', 'qtype_oumatrix', 'Insects');
        $errors = $form->validation($testdata, []);
        $this->assertArrayNotHasKey('columnname[0]', $errors);
        $this->assertArrayHasKey('columnname[1]', $errors);
        $this->assertArrayNotHasKey('columnname[2]', $errors);
        $this->assertArrayNotHasKey('columnname[3]', $errors);
        $this->assertEquals($expected, $errors['columnname[1]']);

        // Duplicate a column name.
        $testdata = $formdata;
        $testdata['rowname'][1] = 'Bee';
        $errors = $form->validation($testdata, []);
        $expected = get_string('duplicates', 'qtype_oumatrix', 'Bee');
        $this->assertEquals($expected, $errors['rowname[1]']);
    }

    /**
     * Test the form correctly validates if there are empty columns in between.
     */
    public function test_validation_column_names_empty(): void {
        [$form, $category] = $this->get_form('qtype_oumatrix_edit_form');
        $formdata = (array) \test_question_maker::get_question_form_data('oumatrix', 'animals_single');
        $formdata['category'] = $category->id;

        // Empty columns names (second and third columns are empty).
        $testdata = $formdata;
        $testdata['columnname'][1] = '';
        $testdata['columnname'][2] = '';
        $errors = $form->validation($testdata, []);
        $this->assertArrayNotHasKey('columnname[0]', $errors);
        $this->assertArrayHasKey('columnname[1]', $errors);
        $this->assertArrayHasKey('columnname[2]', $errors);
        $this->assertArrayNotHasKey('columnname[3]', $errors);
        $expectedcol1 = $errors['columnname[1]'] = get_string('blankcolumnsnotallowed', 'qtype_oumatrix');
        $expectedcol2 = $errors['columnname[2]'] = get_string('blankcolumnsnotallowed', 'qtype_oumatrix');
        $this->assertEquals($expectedcol1, $errors['columnname[1]']);
        $this->assertEquals($expectedcol2, $errors['columnname[2]']);
    }

    /**
     * Test the form correctly validates if correct answers have been input.
     */
    public function test_validation_rowanswers(): void {
        [$form, $category] = $this->get_form('qtype_oumatrix_edit_form');
        $formdata = (array) \test_question_maker::get_question_form_data('oumatrix', 'animals_single');
        $formdata['category'] = $category->id;

        // Rows without chosen answer(s) are not valid.
        $testdata = $formdata;
        $testdata['rowanswers'][1] = '0';
        $testdata['rowanswers'][2] = '0';
        $errors = $form->validation($testdata, []);
        $expectedanswer1 = $errors['rowoptions[1]'] = get_string('noinputanswer', 'qtype_oumatrix');
        $expectedanswer2 = $errors['rowoptions[2]'] = get_string('noinputanswer', 'qtype_oumatrix');
        $this->assertEquals($expectedanswer1, $errors['rowoptions[1]']);
        $this->assertEquals($expectedanswer2, $errors['rowoptions[2]']);
    }

    /**
     * Test the form correctly validates if correct answers have been input.
     */
    public function test_validation_rowanswers_on_empty_columns(): void {
        [$form, $category] = $this->get_form('qtype_oumatrix_edit_form');

        // Single choice test.
        $formdata = (array) \test_question_maker::get_question_form_data('oumatrix', 'animals_single');
        $formdata['category'] = $category->id;

        // Rows with chosen answer on empty columns are not valid(single choice).
        $testdata = $formdata;
        $colkey = 1;
        $testdata['columnname'][$colkey] = '';
        $testdata['rowanswers'][$colkey] = '3';
        $errors = $form->validation($testdata, []);
        $a = new stdClass();
        $a->answerlabel = get_string('answerlabel', 'qtype_oumatrix',  $colkey + 1);
        $a->answerlabelshort = get_string('answerlabelshort', 'qtype_oumatrix', $colkey + 1);
        $expectedanswer = $errors['rowoptions[1]'] = get_string('correctanswererror', 'qtype_oumatrix', $a);
        $this->assertEquals($expectedanswer, $errors['rowoptions[1]']);

        // Multiple response test.
        $formdata = (array) \test_question_maker::get_question_form_data('oumatrix', 'food_multiple');
        $formdata['category'] = $category->id;

        // Rows with chosen answers on empty columns are not valid(multi response).
        $testdata = $formdata;
        $colkey = 6;
        $testdata['columnname'][$colkey] = '';
        $testdata['rowanswers'][$colkey] = '3';
        $errors = $form->validation($testdata, []);
        $expectedanswer = $errors['rowoptions[1]'] = get_string('correctanswerserror', 'qtype_oumatrix', $a);
        $this->assertEquals($expectedanswer, $errors['rowoptions[1]']);
    }

    /**
     * Test the form correctly validates if illegal html tags are added to the column and or row names.
     */
    public function test_get_illegal_tag_error(): void {
        [$form, $category] = $this->get_form('qtype_oumatrix_edit_form');

        // Single choice test.
        $formdata = (array) \test_question_maker::get_question_form_data('oumatrix', 'animals_single');
        $formdata['category'] = $category->id;
        $testdata = $formdata;

        // Set the allowed tags.
        $a = new \stdClass();
        $a->allowed = '&lt;sub&gt;, &lt;sup&gt;, &lt;i&gt;, &lt;em&gt;, &lt;span&gt;';

        // Add html tags to the column names.
        // Illegal html tag.
        $testdata['columnname'][0] = '<div>Insects</div>';
        $errors = $form->validation($testdata, []);
        $this->assertTrue(array_key_exists('columnname[0]', $errors));
        $this->assertEquals(1, count($errors));
        $a->tag = '&lt;div&gt;';
        $expected = get_string('tagsnotallowed', 'qtype_oumatrix', $a);
        $this->assertEquals($expected, $errors['columnname[0]']);
        $this->assertEquals($expected, $form->get_illegal_tag_error('<div>'));

        // Illegal html tag.
        $testdata['columnname'][1] = '<blink><i>Fish</i></blink>';
        $errors = $form->validation($testdata, []);
        $this->assertTrue(array_key_exists('columnname[1]', $errors));
        $this->assertEquals(2, count($errors));
        $a->tag = '&lt;blink&gt;';
        $expected = get_string('tagsnotallowed', 'qtype_oumatrix', $a);
        $this->assertEquals($expected, $errors['columnname[1]']);
        $this->assertEquals($expected, $form->get_illegal_tag_error('<blink>'));

        // Allowed html tag.
        $testdata['columnname'][2] = '<em>Birds</em>';
        $this->assertFalse(array_key_exists('columnname[2]', $errors));
        $errors = $form->validation($testdata, ['columnname[2]']);
        $this->assertEquals(2, count($errors));
        $a->tag = '&lt;em&gt;';
        $expected = get_string('tagsnotallowed', 'qtype_oumatrix', $a);
        $this->assertNotEquals($expected, $form->get_illegal_tag_error('<em>'));

        // Remove illegal tags from column names to remove errors.
        $testdata['columnname'][0] = 'Insect';
        $testdata['columnname'][1] = '<i>Fish</i>';
        $testdata['columnname'][2] = '<em>Birds</em>';

        // Add html tags to the rowname
        // Illegal html tag.
        $testdata['rowname'][0] = '<div>Bee</div>';
        $errors = $form->validation($testdata, []);
        $this->assertTrue(array_key_exists('rowname[0]', $errors));
        $this->assertEquals(1, count($errors));
        $a->tag = '&lt;div&gt;';
        $expected = get_string('tagsnotallowed', 'qtype_oumatrix', $a);
        $this->assertEquals($expected, $errors['rowname[0]']);
        $this->assertEquals($expected, $form->get_illegal_tag_error('<div>'));

        // Illegal html tag.
        $testdata['rowname'][1] = '<em><blink>Salmon</blink></em>';
        $errors = $form->validation($testdata, []);
        $this->assertTrue(array_key_exists('rowname[1]', $errors));
        $this->assertEquals(2, count($errors));
        $a->tag = '&lt;blink&gt;';
        $expected = get_string('tagsnotallowed', 'qtype_oumatrix', $a);
        $this->assertEquals($expected, $errors['rowname[1]']);
        $this->assertEquals($expected, $form->get_illegal_tag_error('<blink>'));

        // Allowed html tag.
        $testdata['rowname'][2] = '<em>Seagull</em>';
        $this->assertFalse(array_key_exists('rowname[2]', $errors));
        $errors = $form->validation($testdata, ['rowname[2]']);
        $this->assertEquals(2, count($errors));
        $a->tag = '&lt;em&gt;';
        $expected = get_string('tagsnotallowed', 'qtype_oumatrix', $a);
        $this->assertNotEquals($expected, $form->get_illegal_tag_error('<em>'));
    }
}
