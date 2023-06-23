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
 * Defines the editing form for the OU matrix question type.
 *
 * @package     qtype_oumatrix
 * @copyright   2023 The Open University
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Editing form for the oumatrix question type.
 *
 * @copyright  2023 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_oumatrix_edit_form extends question_edit_form {

    /** @var int Number of rows. */
    protected $numrows;
    /** @var int Number of columns. */
    protected $numcolumns;
    /** @var array The grid options. */
    protected $gridoptions;

    protected $grademethod = ['partialcredit', 'allornothing'];

    /**
     * Add question-type specific form fields.
     *
     * @param object $mform the form being built.
     */
    protected function definition_inner($mform) {

        $qtype = 'qtype_oumatrix';
        $answermodemenu = array(
            get_string('answersingleno', $qtype),
            get_string('answersingleyes', $qtype),
        );
        $mform->addElement('select', 'single',
            get_string('answermode', $qtype), $answermodemenu);
        $mform->setDefault('single', $this->get_default_value('single',
            get_config($qtype, 'answermode')));

        $mform->addElement('selectyesno', 'shuffleanswers', get_string('shuffleanswers', $qtype), '');
        $mform->addHelpButton('shuffleanswers', 'shuffleanswers', $qtype);
        $mform->setDefault('shuffleanswers', $this->get_default_value('shuffleanswers', 0));

        $mform->addElement('select', 'grademethod',
            get_string('grademethod', $qtype), self::get_grading_modes());

        $mform->addHelpButton('grademethod', 'grademethod', $qtype);
        $mform->setDefault('grademethod', $this->get_default_value('grademethod',
            get_config($qtype, 'grademethod')));

        // Set matrix table options.
        $this->gridoptions = range(1, 12);
        // Add number of matrix rows.
        $mform->addElement('select', 'numrows',
            get_string('numberofrows', $qtype), $this->gridoptions, null);
        $mform->addRule('numrows', null, 'required', null, 'client');
        $mform->setDefault('numrows', 2);

        // Add number of matrix columns.
        $mform->addElement('select', 'numcolumns',
            get_string('numberofcolumns', $qtype), $this->gridoptions, null);
        $mform->addRule('numcolumns', null, 'required', null, 'client');
        $mform->setDefault('numcolumns', 4);
        // Add update field.
        $mform->addElement('submit', 'updateform', get_string('updateform', $qtype));
        $mform->registerNoSubmitButton('updateform');

        $this->set_current_rowcolumn_setting();

        $this->add_combined_feedback_fields(true);
        $mform->disabledIf('shownumcorrect', 'single', 'eq', 1);

        $this->add_interactive_settings(true, true);

    }

    /**
     * Set the matrix grid size.
     *
     * @return void
     */
    protected function set_current_rowcolumn_setting(): void {
        $numrowsindex = optional_param('numrows', -1, PARAM_INT);
        $numcolumnsindex = optional_param('numcolumns', -1, PARAM_INT);

        if ($numrowsindex < 0) {
            $numrowsindex = $this->question->options->numrows ?? 2;
        }

        if ($numcolumnsindex < 0) {
            $numcolumnsindex = $this->question->options->numcolumns ?? 2;
        }

        $this->numrows = $this->gridoptions[$numrowsindex] ?? 2;
        $this->numcolumns = $this->gridoptions[$numcolumnsindex] ?? 2;
    }

    /**
     * @return array supported grading methods.
     */
    public static function get_grading_modes(): array {
        return [
            'partialcredit' => get_string('gradepartialcredit', 'qtype_oumatrix'),
            'allornothing' => get_string('gradeallornothing', 'qtype_oumatrix')
        ];
    }

    protected function add_question_section(): void {

    }
    /**
     * Returns the question type name.
     *
     * @return string The question type name.
     */
    public function qtype() {
        return 'oumatrix';
    }
}
