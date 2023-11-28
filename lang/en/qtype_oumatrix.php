<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     qtype_oumatrix
 * @category    string
 * @copyright   2023 The Open University
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
$string['answerlabel'] = 'Column {$a}';
$string['answerlabelshort'] = 'A{$a}';
$string['addmoreblanks'] = 'Blanks for {no} more {$a}';
$string['blankcolumnsnotallowed'] = 'Empty column name is not allowed.';
$string['columnshdr'] = 'Matrix columns (answers)';
$string['correctanswer'] = 'Correct answer';
$string['correctanswererror'] = '\'{$a->answerlabel}\' is empty so \'{$a->answerlabelshort}\' cannot be chosen as the correct answer.';
$string['correctansweris'] = 'The correct answer is: {$a}';
$string['correctanswersare'] = 'The correct answers are: {$a}';
$string['correctanswers'] = 'Correct answers';
$string['correctanswerserror'] = '\'{$a->answerlabel}\' is empty so \'{$a->answerlabelshort}\' cannot be chosen as a correct answer.';
$string['duplicates'] = 'Duplicate answer ({$a}) is not allowed.';
$string['grademethod'] = 'Grading type';
$string['grademethod_desc'] = 'Give partial credit (default): each correct response in the body cells are worth one point, so students score a percentage of the total correct responses.
All or nothing: students must get every response correct, otherwise they score zero.';
$string['grademethod_help'] = 'Give partial credit (default): each correct response in the body cells are worth one point, so students score a percentage of the total correct responses.

All or nothing: students must get every response correct, otherwise they score zero.';
$string['gradepartialcredit'] = 'Give partial credit';
$string['gradeallornothing'] = 'All-or-nothing';
$string['inputtype'] = 'One or multiple answers?';
$string['inputtype_desc'] = 'One or multiple answers? can be either \'Single choice\' or \'Multiple response\' for each row in the matrix table.';
$string['inputtypemultiple'] = 'Multiple response';
$string['inputtypesingle'] = 'Single choice';
$string['noinputanswer'] = 'Each sub-question should have at least one correct answer.';
$string['notenoughanswercols'] = 'You must have at least {$a} answer columns.';
$string['notenoughquestionrows'] = 'You must have at least {$a} sub-questions.';
$string['pleaseananswerallparts'] = 'Please answer all parts of the question.';
$string['pluginname'] = 'Matrix';
$string['pluginname_help'] = 'Creating a matrix question requires you to specify column headings (answers) and row headings (sub-questions). For example, you might use Single Choice mode to ask students to categorise various sub-question items as being either an animal, vegetable, or mineral. You can use the Multiple Response mode where several answers may apply to a sub-question, e.g. if the item is a conductor, insulator, and/or magnetic.';
$string['pluginnameadding'] = 'Adding a Matrix question';
$string['pluginnameediting'] = 'Editing a Matrix question';
$string['pluginnamesummary'] = 'A multi-row table that can use single choice or multiple response inputs.';
$string['privacy:metadata'] = 'Matrix question type plugin allows question authors to set default options as user preferences.';
$string['privacy:preference:defaultmark'] = 'The default mark set for a given question.';
$string['privacy:preference:penalty'] = 'The penalty for each incorrect try when questions are run using the \'Interactive with multiple tries\' or \'Adaptive mode\' behaviour.';
$string['privacy:preference:inputtype'] = 'Whether the \'One or multiple answers?\' should be \'Single choice\' or \'Multiple response\'.';
$string['privacy:preference:grademethod'] = 'Whether the Grading type should be \'All-or-nothing\' or \'Give partial credit\' for \'Multiple response\'. This option is diabled for \'Single choice\'.';
$string['privacy:preference:shuffleanswers'] = 'Whether the answers should be automatically shuffled.';
$string['regradeissuenumcolumnschanged'] = 'The number of columns has changed.';
$string['regradeissuenumrowschanged'] = 'The number of rows has changed.';
$string['row'] = 'Row {$a}';
$string['rowshdr'] = 'Matrix rows (sub-questions)';
$string['rowanswerlist'] = 'Select answers';
$string['rowx'] = 'Row{$a})';
$string['shuffleanswers'] = 'Shuffle the items?';
$string['shuffleanswers_desc'] = 'Whether options should be randomly shuffled for each attempt by default.';
$string['shuffleanswers_help'] = 'If enabled, the order of the row items is randomly shuffled for each attempt, provided that "Shuffle within questions" in the activity settings is also enabled.';
$string['toomanyanswercols'] = 'You can have maximum {$a} answer columns.';
$string['toomanyquestionrows'] = 'You can have maximum {$a} sub-questions.';
$string['toomanyselected'] = 'You have selected too many options.';
$string['updateform'] = 'Update the response matrix';
$string['yougot1right'] = 'You have correctly selected one option.';
$string['yougotnright'] = 'You have correctly selected {$a->num} options.';
$string['yougot1rightsubquestion'] = 'You have correctly selected one row.';
$string['yougotnrightsubquestion'] = 'You have correctly selected {$a->num} rows.';
