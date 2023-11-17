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

use question_bank;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
require_once($CFG->dirroot . '/course/externallib.php');
require_once($CFG->libdir . "/phpunit/classes/restore_date_testcase.php");

/**
 * Tests for oumatrix question type backup and restore.
 *
 * @package   qtype_oumatrix
 * @copyright 2023 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */
class backup_and_restore_test extends \restore_date_testcase {

    /**
     * Duplicate quiz with an oumatrix question for testing backup and restore.
     * @covers \backup_qtype_oumatrix_plugin
     */
    public function test_duplicate_oumatrix_question_single(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $coregenerator = $this->getDataGenerator();
        $questiongenerator = $coregenerator->get_plugin_generator('core_question');

        // Create a course, a quiz and a question of type oumatrix single.
        $course = $coregenerator->create_course();
        $quiz = $coregenerator->create_module('quiz', ['course' => $course->id]);
        $quizcontext = \context_module::instance($quiz->cmid);

        $cat = $questiongenerator->create_question_category(['contextid' => $quizcontext->id]);
        $questiongenerator->create_question('oumatrix', 'animals_single', ['category' => $cat->id]);

        // Store some counts.
        $numquizzes = count(get_fast_modinfo($course)->instances['quiz']);
        $numoumatrixquestions = $DB->count_records('question', ['qtype' => 'oumatrix']);

        // Duplicate the page.
        duplicate_module($course, get_fast_modinfo($course)->get_cm($quiz->cmid));

        // Verify the copied quiz exists.
        $this->assertCount($numquizzes + 1, get_fast_modinfo($course)->instances['quiz']);

        // Verify the copied question.
        $this->assertEquals($numoumatrixquestions + 1, $DB->count_records('question', ['qtype' => 'oumatrix']));

        $newoumatrixid = $DB->get_field_sql("
                SELECT MAX(id)
                  FROM {question}
                 WHERE qtype = ?
                ", ['oumatrix']);
        $questionata = question_bank::load_question_data($newoumatrixid);
        $this->assertSame('single', $questionata->options->inputtype);
        $this->assertSame('0', $questionata->options->shuffleanswers);

        $questioncolumns = array_values((array) $questionata->columns);
        $this->assertSame('1', $questioncolumns[0]->number);
        $this->assertSame('Insects', $questioncolumns[0]->name);

        $this->assertSame('2', $questioncolumns[1]->number);
        $this->assertSame('Fish', $questioncolumns[1]->name);

        $this->assertSame('3', $questioncolumns[2]->number);
        $this->assertSame('Birds', $questioncolumns[2]->name);

        $this->assertSame('4', $questioncolumns[3]->number);
        $this->assertSame('Mammals', $questioncolumns[3]->name);

        $questionrows = array_values((array) $questionata->rows);
        $this->assertSame('1', $questionrows[0]->number);
        $this->assertSame('Bee', $questionrows[0]->name);
        $this->assertSame('1', $questionrows[0]->correctanswers);
        $this->assertSame('Flies and Bees are insects.', $questionrows[0]->feedback);
        $this->assertSame(FORMAT_HTML, $questionrows[0]->feedbackformat);

        $this->assertSame('2', $questionrows[1]->number);
        $this->assertSame('Salmon', $questionrows[1]->name);
        $this->assertSame('2', $questionrows[1]->correctanswers);
        $this->assertSame('Cod, Salmon and Trout are fish.', $questionrows[1]->feedback);
        $this->assertSame(FORMAT_HTML, $questionrows[1]->feedbackformat);

        $this->assertSame('3', $questionrows[2]->number);
        $this->assertSame('Seagull', $questionrows[2]->name);
        $this->assertSame('3', $questionrows[2]->correctanswers);
        $this->assertSame('Gulls and Owls are birds.', $questionrows[2]->feedback);
        $this->assertSame(FORMAT_HTML, $questionrows[2]->feedbackformat);

        $this->assertSame('4', $questionrows[3]->number);
        $this->assertSame('Dog', $questionrows[3]->name);
        $this->assertSame('4', $questionrows[3]->correctanswers);
        $this->assertSame('Cows, Dogs and Horses are mammals.', $questionrows[3]->feedback);
        $this->assertSame(FORMAT_HTML, $questionrows[3]->feedbackformat);
    }

    /**
     * Backup and restore the course containing an oumatrix question for testing oumatrix backup and restore.
     * @covers \restore_qtype_oumatrix_plugin
     */
    public function test_restore_create_qtype_oumatrix_multiple(): void {
        global $DB;

        // Create a course with one oumatrix question in its question bank.
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $contexts = new \core_question\local\bank\question_edit_contexts(\context_course::instance($course->id));
        $category = question_make_default_categories($contexts->all());
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $oumatrix = $questiongenerator->create_question('oumatrix', 'food_multiple', ['category' => $category->id]);

        // Do backup and restore the course.
        $newcourseid = $this->backup_and_restore($course);

        // Verify that the restored question has the extra data such as options, columns, rows.
        $contexts = new \core_question\local\bank\question_edit_contexts(\context_course::instance($newcourseid));
        $newcategory = question_make_default_categories($contexts->all());
        $newoumatrix = $DB->get_record_sql('SELECT q.*
                                              FROM {question} q
                                              JOIN {question_versions} qv ON qv.questionid = q.id
                                              JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
                                             WHERE qbe.questioncategoryid = ?
                                               AND q.qtype = ?', [$newcategory->id, 'oumatrix']);

        $this->assertSame($newcourseid, $course->id + 1);
        $this->assertSame((int)$newoumatrix->id, $oumatrix->id + 1);

        $this->assertTrue($DB->record_exists('question', ['id' => $newoumatrix->id]));
        $this->assertTrue($DB->record_exists('qtype_oumatrix_options', ['questionid' => $newoumatrix->id]));
        $this->assertTrue($DB->record_exists('qtype_oumatrix_columns', ['questionid' => $newoumatrix->id]));
        $this->assertTrue($DB->record_exists('qtype_oumatrix_rows', ['questionid' => $newoumatrix->id]));

        $questionata = question_bank::load_question_data($newoumatrix->id);

        $this->assertSame('multiple', $questionata->options->inputtype);
        $this->assertSame('0', $questionata->options->shuffleanswers);

        $questioncolumns = array_values((array) $questionata->columns);
        $this->assertSame('1', $questioncolumns[0]->number);
        $this->assertSame('Chicken breast', $questioncolumns[0]->name);

        $this->assertSame('2', $questioncolumns[1]->number);
        $this->assertSame('Carrot', $questioncolumns[1]->name);

        $this->assertSame('3', $questioncolumns[2]->number);
        $this->assertSame('Salmon fillet', $questioncolumns[2]->name);

        $this->assertSame('4', $questioncolumns[3]->number);
        $this->assertSame('Asparagus', $questioncolumns[3]->name);

        $this->assertSame('5', $questioncolumns[4]->number);
        $this->assertSame('Olive oil', $questioncolumns[4]->name);

        $this->assertSame('6', $questioncolumns[5]->number);
        $this->assertSame('Steak', $questioncolumns[5]->name);

        $this->assertSame('7', $questioncolumns[6]->number);
        $this->assertSame('Potato', $questioncolumns[6]->name);

        $questionrows = array_values((array) $questionata->rows);
        $this->assertSame('1', $questionrows[0]->number);
        $this->assertSame('Proteins', $questionrows[0]->name);
        $this->assertSame('1,3,6', $questionrows[0]->correctanswers);
        $this->assertSame('Chicken, fish and red meat containing proteins.', $questionrows[0]->feedback);
        $this->assertSame(FORMAT_HTML, $questionrows[0]->feedbackformat);

        $this->assertSame('2', $questionrows[1]->number);
        $this->assertSame('Vegetables', $questionrows[1]->name);
        $this->assertSame('2,4,7', $questionrows[1]->correctanswers);
        $this->assertSame('Carrot, Asparagus, Potato are vegetables.', $questionrows[1]->feedback);
        $this->assertSame(FORMAT_HTML, $questionrows[1]->feedbackformat);

        $this->assertSame('3', $questionrows[2]->number);
        $this->assertSame('Fats', $questionrows[2]->name);
        $this->assertSame('5', $questionrows[2]->correctanswers);
        $this->assertSame('Olive oil contains fat.', $questionrows[2]->feedback);
        $this->assertSame(FORMAT_HTML, $questionrows[2]->feedbackformat);
    }
}
