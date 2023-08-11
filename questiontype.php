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
 * Question type class for oumatrix is defined here.
 *
 * @package     qtype_oumatrix
 * @copyright   2023 The Open University
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use \qtype_oumatrix\row;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/questionlib.php');

/**
 * Class that represents a oumatrix question type.
 *
 * The class loads, saves and deletes questions of the type oumatrix
 * to and from the database and provides methods to help with editing questions
 * of this type. It can also provide the implementation for import and export
 * in various formats.
 */
class qtype_oumatrix extends question_type {

    public function get_question_options($question) {
        global $DB;
        $question->options = $DB->get_record('qtype_oumatrix_options', ['questionid' => $question->id]);
        if ($question->options === false) {
           // If this has happened, then we have a problem.
           // For the user to be able to edit or delete this question, we need options.
           debugging("Question ID {$question->id} was missing an options record. Using default.", DEBUG_DEVELOPER);
        }
        $question->options = $this->create_default_options($question);
        $question->options->columns = $DB->get_records('qtype_oumatrix_columns', ['questionid' => $question->id]);
        $question->options->rows = $DB->get_records('qtype_oumatrix_rows', ['questionid' => $question->id]);
        parent::get_question_options($question);
    }

    /**
     * Create a default options object for the provided question.
     *
     * @param object $question The queston we are working with.
     * @return object The options object.
     */
    protected function create_default_options($question) {
        // Create a default question options record.
        $config = get_config('qtype_oumatrix');
        $options = new stdClass();
        $options->questionid = $question->id;
        $options->inputtype = $config->inputtype;
        $options->grademethod = $config->grademethod;
        $options->shuffleanswers = $config->shuffleanswers ?? 0;
        $options->shownumcorrect = 1;

        // Get the default strings and just set the format.
        $options->correctfeedback = get_string('correctfeedbackdefault', 'question');
        $options->correctfeedbackformat = FORMAT_HTML;
        $options->partiallycorrectfeedback = get_string('partiallycorrectfeedbackdefault', 'question');;
        $options->partiallycorrectfeedbackformat = FORMAT_HTML;
        $options->incorrectfeedback = get_string('incorrectfeedbackdefault', 'question');
        $options->incorrectfeedbackformat = FORMAT_HTML;
        return $options;
    }

    public function save_defaults_for_new_questions(stdClass $fromform): void {
        parent::save_defaults_for_new_questions($fromform);
        $this->set_default_value('inputtype', $fromform->inputtype);
        $this->set_default_value('grademethod', $fromform->grademethod);
        $this->set_default_value('shuffleanswers', $fromform->shuffleanswers);
        $this->set_default_value('shownumcorrect', $fromform->shownumcorrect);
    }

    public function save_question($question, $form) {
        $question = parent::save_question($question, $form);


        return $question;
    }

    public function save_question_options($question) {
        global $DB;
        $context = $question->context;

        $options = $DB->get_record('qtype_oumatrix_options',['questionid' => $question->id]);
        if (!$options) {
            $options = new stdClass();
            $options->questionid = $question->id;
            $options->correctfeedback = '';
            $options->partiallycorrectfeedback = '';
            $options->incorrectfeedback = '';
            $options->id = $DB->insert_record('qtype_oumatrix_options', $options);
        }
        $options->inputtype = $question->inputtype;
        $options->grademethod = $question->grademethod;
        $options->shuffleanswers = $question->shuffleanswers;
        $options = $this->save_combined_feedback_helper($options, $question, $context, true);
        $DB->update_record('qtype_oumatrix_options', $options);

        $this->save_rows($question);
        $this->save_columns($question);
        $this->save_hints($question, true);
    }

    public function save_columns($formdata) {
        global $DB;
        $context = $formdata->context;
        $result = new stdClass();
        $oldcolumns = $DB->get_records('qtype_oumatrix_columns', ['questionid' => $formdata->id], 'id ASC');
        $numcolumns = count($formdata->columnname);

        // Check if the question has at least one column.
        $answercount = 0;
        for ($i = 0; $i < $numcolumns; $i++) {
            if ($formdata->columnname[$i] !== '') {
                $answercount++;
            }
        }
        if ($answercount < 1) {
            $result->error = get_string('notenoughquestions', 'qtype_oumatrix', '1');
            return $result;
        }

        // Insert all the new words.
        for ($i = 0; $i < $numcolumns; $i++) {
            if (trim($formdata->columnname[$i]) === '') {
                continue;
            }
            // Update an existing word if possible.
            $questioncolumn = array_shift($oldcolumns);
            if (!$questioncolumn) {
                $questioncolumn = new stdClass();
                $questioncolumn->questionid = $formdata->id;
                $questioncolumn->number = $i;
                $questioncolumn->name = $formdata->columnname[$i];
                $questioncolumn->id = $DB->insert_record('qtype_oumatrix_columns', $questioncolumn);
            }

            // Remove old columns.
            if ($oldcolumns) {
                $ids = array_map(function($question) {
                    return $question->id;
                }, $oldcolumns);
                list($idssql, $idsparams) = $DB->get_in_or_equal($ids, SQL_PARAMS_QM);
                //$fs->delete_area_files_select($context->id, 'qtype_crossword', 'feedback', "id $idssql", $idsparams);
                //$fs->delete_area_files_select($context->id, 'qtype_crossword', 'clue', "id $idssql", $idsparams);
                $DB->delete_records_select('qtype_oumatrix_columns', "id $idssql", $idsparams);
            }
        }
    }
    public function save_rows($formdata) {
        global $DB;
        print_object("11111111111111111111111111111111111111111111111");
        print_object($formdata);
        $context = $formdata->context;
        $result = new stdClass();
        // Old records.
        $oldrowquestions = $DB->get_records('qtype_oumatrix_rows',
                ['questionid' => $formdata->id], 'id ASC');

        $numquestions = count($formdata->rowname);

        // Insert all the new words.
        for ($i = 0; $i < $numquestions; $i++) {
            if (trim($formdata->rowname[$i] ?? '') === '') {
                continue;
            }
            // Update an existing word if possible.
            $questionrow = array_shift($oldrowquestions);
            if (!$questionrow) {
                $questionrow = new stdClass();
                $questionrow->questionid = $formdata->id;
                $questionrow->number = $i;
                $questionrow->name = $formdata->rowname[$i];
                // Prepare correct answers.
                $json = [];
                //$json['answertext'] = $formdata->columnname[$i];
                //$questionrow->correctanswers = json_encode($json);
                $questionrow->correctanswers = $formdata->rowanswers[$i] ?? '';
                //$questionrow->correctanswers = ''; //implode(', ', $formdata->a[$i]);
                $questionrow->feedback = $formdata->feedback[$i]['text'];
                $questionrow->feedbackitemid = $formdata->feedback[$i]['itemid']; // TODO: Is this actually needed?
                $questionrow->feedbackformat = FORMAT_HTML;
                $questionrow->id = $DB->insert_record('qtype_oumatrix_rows', $questionrow);
            }
        }
        // Remove remain words.
        $fs = get_file_storage();
        if ($oldrowquestions) {
            $ids = array_map(function($question){
                return $question->id;
            }, $oldrowquestions);
            list($idssql, $idsparams) = $DB->get_in_or_equal($ids, SQL_PARAMS_QM);
            //$fs->delete_area_files_select($context->id, 'qtype_crossword', 'feedback', "id $idssql", $idsparams);
            //$fs->delete_area_files_select($context->id, 'qtype_crossword', 'clue', "id $idssql", $idsparams);
            $DB->delete_records_select('qtype_oumatrix_rows', "id $idssql", $idsparams);
        }
    }

   public function save_hints($formdata, $withparts = false) {
        global $DB;
        $context = $formdata->context;

        $oldhints = $DB->get_records('question_hints', ['questionid' => $formdata->id], 'id ASC');

        if (!empty($formdata->hint)) {
            $numhints = max(array_keys($formdata->hint)) + 1;
        } else {
            $numhints = 0;
        }

        if ($withparts) {
            if (!empty($formdata->hintclearwrong)) {
                $numclears = max(array_keys($formdata->hintclearwrong)) + 1;
            } else {
                $numclears = 0;
            }
            if (!empty($formdata->hintshownumcorrect)) {
                $numshows = max(array_keys($formdata->hintshownumcorrect)) + 1;
            } else {
                $numshows = 0;
            }
            $numhints = max($numhints, $numclears, $numshows);
        }

        if (!empty($formdata->hintshowrowfeedback)) {
            $numshowfeedbacks = max(array_keys($formdata->hintshowrowfeedback)) + 1;
        } else {
            $numshowfeedbacks = 0;
        }
        $numhints = max($numhints, $numshowfeedbacks);

        for ($i = 0; $i < $numhints; $i += 1) {
            if (html_is_blank($formdata->hint[$i]['text'])) {
                $formdata->hint[$i]['text'] = '';
            }

            if ($withparts) {
                $clearwrong = !empty($formdata->hintclearwrong[$i]);
                $shownumcorrect = !empty($formdata->hintshownumcorrect[$i]);
            }

            $showrowfeedback = !empty($formdata->hintshowrowfeedback[$i]);

            if (empty($formdata->hint[$i]['text']) && empty($clearwrong) &&
                    empty($shownumcorrect) && empty($showrowfeedback)) {
                continue;
            }

            // Update an existing hint if possible.
            $hint = array_shift($oldhints);
            if (!$hint) {
                $hint = new stdClass();
                $hint->questionid = $formdata->id;
                $hint->hint = '';
                $hint->id = $DB->insert_record('question_hints', $hint);
            }

            $hint->hint = $this->import_or_save_files($formdata->hint[$i],
                    $context, 'question', 'hint', $hint->id);
            $hint->hintformat = $formdata->hint[$i]['format'];
            if ($withparts) {
                $hint->clearwrong = $clearwrong;
                $hint->shownumcorrect = $shownumcorrect;
            }
            $hint->options = $showrowfeedback;
            $DB->update_record('question_hints', $hint);
        }

        // Delete any remaining old hints.
        $fs = get_file_storage();
        foreach ($oldhints as $oldhint) {
            $fs->delete_area_files($context->id, 'question', 'hint', $oldhint->id);
            $DB->delete_records('question_hints', array('id' => $oldhint->id));
        }
    }

    protected function make_question_instance($questiondata) {
        question_bank::load_question_definition_classes($this->name());
        if ($questiondata->options->inputtype) {
            $class = 'qtype_oumatrix_single';
        } else {
            $class = 'qtype_oumatrix_multiple';
        }
        return new $class();
    }

    protected function make_hint($hint) {
        return qtype_oumatrix_hint::load_from_record($hint);
    }

    // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
    public function make_answer($answer) {
        // Overridden just so we can make it public for use by question.php.
        return parent::make_answer($answer);
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        $question->inputtype = $questiondata->options->inputtype;
        $question->grademethod = $questiondata->options->grademethod;
        $question->shuffleanswers = $questiondata->options->shuffleanswers;
        $this->initialise_question_rows($question, $questiondata);
        $this->initialise_question_columns($question, $questiondata);
        $this->initialise_combined_feedback($question, $questiondata, true);
        $this->initialise_question_answers($question, $questiondata, false);
    }

    public function delete_question($questionid, $contextid) {
        global $DB;
        $DB->delete_records('qtype_oumatrix_options', ['questionid' => $questionid]);
        $DB->delete_records('qtype_oumatrix_rows', ['questionid' => $questionid]);
        $DB->delete_records('qtype_oumatrix_columns', ['questionid' => $questionid]);
        parent::delete_question($questionid, $contextid);
    }

    protected function get_num_correct_choices($questiondata) {
        $numright = 0;
        // TODO: To be done correctly
        //foreach ($questiondata->options->answers as $answer) {
        //    if (!question_state::graded_state_for_fraction($answer->fraction)->is_incorrect()) {
        //        $numright += 1;
        //    }
        //}
        return $numright;
    }

    public function get_random_guess_score($questiondata) {
        // We compute the randome guess score here on the assumption we are using
        // the deferred feedback behaviour, and the question text tells the
        // student how many of the responses are correct.
        // Amazingly, the forumla for this works out to be
        // # correct choices / total # choices in all cases.

        //TODO: improve this is a correct way if we are not using the answers table, etc.
        return $this->get_num_correct_choices($questiondata) /
                count($questiondata->options->answers);
    }

    public function get_possible_responses($questiondata) {
        $numright = $this->get_num_correct_choices($questiondata);
        $parts = [];

        // TODO: To be done correctly
        //foreach ($questiondata->options->answers as $aid => $answer) {
        //    $parts[$aid] = array($aid =>
        //            new question_possible_response($answer->answer, $answer->fraction / $numright));
        //}

        return $parts;
    }

    /**
     * Initialise the question rows.
     * @param question_definition $question the question_definition we are creating.
     * @param object $questiondata the question data loaded from the database.
     */
    protected function initialise_question_rows(question_definition $question,
            $questiondata) {
        if (!empty($questiondata->options->rows)) {
            foreach ($questiondata->options->rows as $row) {
                $newrow  = $this->make_row($row);
                $question->rows[] = $newrow;
            }
        }
    }

    /**
     * Initialise the question columns.
     * @param question_definition $question the question_definition we are creating.
     * @param object $questiondata the question data loaded from the database.
     */
    protected function initialise_question_columns(question_definition $question,
            $questiondata) {
        if (!empty($questiondata->options->columns)) {
            foreach ($questiondata->options->columns as $column) {
                //$newcolumn  = $this->make_column($column);
                $question->columns[] = $this->make_column($column);
            }
        }
    }

    protected function make_column($columndata) {
        return new \qtype_oumatrix\column($columndata->id, $columndata->questionid, $columndata->number, $columndata->name);
    }

    protected function make_row($rowdata) {
        return new row($rowdata->id, $rowdata->questionid, $rowdata->number, $rowdata->name,
            explode(',',$rowdata->correctanswers), $rowdata->feedback, $rowdata->feedbackformat);
    }

    public function import_from_xml($data, $question, qformat_xml $format, $extra=null) {
        if (!isset($data['@']['type']) || $data['@']['type'] != 'oumultiresponse') {
            return false;
        }

        $question = $format->import_headers($data);
        $question->qtype = 'oumultiresponse';

        $question->shuffleanswers = $format->trans_single(
                $format->getpath($data, array('#', 'shuffleanswers', 0, '#'), 1));
        $question->answernumbering = $format->getpath($data,
                array('#', 'answernumbering', 0, '#'), 'abc');
        $question->showstandardinstruction = $format->getpath($data,
                array('#', 'showstandardinstruction', 0, '#'), 1);

        $format->import_combined_feedback($question, $data, true);

        // TODO: To be done correctly
        // Run through the answers.
        $answers = $data['#']['answer'];
        foreach ($answers as $answer) {
            $ans = $format->import_answer($answer, true,
                    $format->get_format($question->questiontextformat));
            $question->answer[] = $ans->answer;
            $question->correctanswer[] = !empty($ans->fraction);
            $question->feedback[] = $ans->feedback;

            // Backwards compatibility.
            if (array_key_exists('correctanswer', $answer['#'])) {
                $keys = array_keys($question->correctanswer);
                $question->correctanswer[end($keys)] = $format->getpath($answer,
                        array('#', 'correctanswer', 0, '#'), 0);
            }
        }

        $format->import_hints($question, $data, true, true,
                $format->get_format($question->questiontextformat));

        // Get extra choicefeedback setting from each hint.
        if (!empty($question->hintoptions)) {
            foreach ($question->hintoptions as $key => $options) {
                $question->hintshowrowfeedback[$key] = !empty($options);
            }
        }

        return $question;
    }

    public function export_to_xml($question, qformat_xml $format, $extra = null) {
        print_object($question);
        $output = '';

        $output .= "    <shuffleanswers>" . $format->get_single(
                        $question->options->shuffleanswers) . "</shuffleanswers>\n";
        //$output .= "    <answernumbering>{$question->options->answernumbering}</answernumbering>\n";
        $output .= "    <showstandardinstruction>{$question->options->showstandardinstruction}</showstandardinstruction>\n";
        $output .= '    <grademethod>' . $format->xml_escape($question->options->grademethod)
                . "</grademethod>\n";
        foreach ($question->rows as $word => $value) {
            $expout .= "    <word>\n";
            foreach (self::WORD_FIELDS as $xmlfield) {
                if ($xmlfield === 'clue' || $xmlfield === 'feedback') {
                    if (!isset($value->{$xmlfield})) {
                        $value->{$xmlfield} = '';
                    }
                    $formatfield = $xmlfield . 'format';
                    if (!isset($value->{$formatfield})) {
                        $value->{$formatfield} = FORMAT_HTML;
                    }
                    $files = $fs->get_area_files($question->contextid, 'question', $xmlfield, $value->id);
                    $expout .= "      <{$xmlfield} {$format->format($value->{$formatfield})}>\n";
                    $expout .= '        ' . $format->writetext($value->{$xmlfield});
                    $expout .= $format->write_files($files);
                    $expout .= "      </{$xmlfield}>\n";
                } else {
                    $exportedvalue = $format->xml_escape($value->{$xmlfield});
                    $expout .= "      <$xmlfield>{$exportedvalue}</$xmlfield>\n";
                }
            }
            $expout .= "    </word>\n";
        }
        $output .= $format->write_combined_feedback($question->options,
                $question->id,
                $question->contextid);
        $output .= $format->write_answers($question->rows->correctanswers);

        return $output;
    }

    public function move_files($questionid, $oldcontextid, $newcontextid) {
        $fs = get_file_storage();

        parent::move_files($questionid, $oldcontextid, $newcontextid);
        //TODO: replace the commented line below if needed.
        //$this->move_files_in_answers($questionid, $oldcontextid, $newcontextid, true);
        $this->move_files_in_hints($questionid, $oldcontextid, $newcontextid);

        $fs->move_area_files_to_new_context($oldcontextid,
                $newcontextid, 'question', 'correctfeedback', $questionid);
        $fs->move_area_files_to_new_context($oldcontextid,
                $newcontextid, 'question', 'partiallycorrectfeedback', $questionid);
        $fs->move_area_files_to_new_context($oldcontextid,
                $newcontextid, 'question', 'incorrectfeedback', $questionid);
    }

    protected function delete_files($questionid, $contextid) {
        $fs = get_file_storage();

        parent::delete_files($questionid, $contextid);
        $this->delete_files_in_answers($questionid, $contextid, true);
        $this->delete_files_in_hints($questionid, $contextid);
        $fs->delete_area_files($contextid, 'question', 'correctfeedback', $questionid);
        $fs->delete_area_files($contextid, 'question', 'partiallycorrectfeedback', $questionid);
        $fs->delete_area_files($contextid, 'question', 'incorrectfeedback', $questionid);
    }
}


/**
 * An extension of {@link question_hint_with_parts} for qtype_oumatrix questions
 * with an extra option for whether to show the feedback for each row.
 *
 * @copyright  2023 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_oumatrix_hint extends question_hint_with_parts {
    /** @var boolean whether to show the feedback for each row. */
    public $showrowfeedback;

    /**
     * Constructor.
     * @param string $hint The hint text
     * @param bool $shownumcorrect whether the number of right parts should be shown
     * @param bool $clearwrong whether the wrong parts should be reset.
     * @param bool $showrowfeedback whether to show the feedback for each row.
     */
    public function __construct($id, $hint, $hintformat, $shownumcorrect,
            $clearwrong, $showrowfeedback) {
        parent::__construct($id, $hint, $hintformat, $shownumcorrect, $clearwrong);
        $this->showrowfeedback = $showrowfeedback;
    }

    /**
     * Create a basic hint from a row loaded from the question_hints table in the database.
     * @param object $row with $row->hint, ->shownumcorrect and ->clearwrong set.
     * @return question_hint_with_parts
     */
    public static function load_from_record($row) {
        return new qtype_oumatrix_hint($row->id, $row->hint, $row->hintformat,
                $row->shownumcorrect, $row->clearwrong, !empty($row->options));
    }

    public function adjust_display_options(question_display_options $options) {
        parent::adjust_display_options($options);
        $options->suppressrowfeedback = !$this->showrowfeedback;
    }
}

















