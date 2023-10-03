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

/**
 * Provides the information to backup oumatrix questions.
 *
 * @package qtype_oumatrix
 * @copyright 2023 The Open University
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_qtype_oumatrix_plugin extends backup_qtype_plugin {

    /**
     * Returns the qtype information to attach to question element.
     */
    protected function define_question_plugin_structure(): backup_plugin_element {

        // Define the virtual plugin element with the condition to fulfill.
        $plugin = $this->get_plugin_element(null, '../../qtype', 'oumatrix');

        // Create one standard named plugin element (the visible container).
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());

        // Connect the visible container ASAP.
        $plugin->add_child($pluginwrapper);

        // Now create the qtype own structures.
        $matrix = new backup_nested_element('oumatrix', ['id'], ['inputtype', 'grademethod', 'shuffleanswers',
            'correctfeedback', 'correctfeedbackformat', 'partiallycorrectfeedback', 'partiallycorrectfeedbackformat',
            'incorrectfeedback', 'incorrectfeedbackformat', 'shownumcorrect']);
        $pluginwrapper->add_child($matrix);

        // Define the columns.
        $columns = new backup_nested_element('columns');
        $column = new backup_nested_element('column', ['id'], ['number', 'name']);
        $columns->add_child($column);
        // This qtype uses column id's for correct answers in rows,
        // so adding columns to the tree before rows.
        $pluginwrapper->add_child($columns);

        // Define the rows.
        $rows = new backup_nested_element('rows');
        $row = new backup_nested_element('row', ['id'], ['number', 'name', 'feedback', 'feedbackformat', 'correctanswers']);
        $rows->add_child($row);
        $pluginwrapper->add_child($rows);

        // Set source to populate the data.
        $matrix->set_source_table('qtype_oumatrix_options', ['questionid' => backup::VAR_PARENTID]);
        $row->set_source_table('qtype_oumatrix_rows', ['questionid' => backup::VAR_PARENTID]);
        $column->set_source_table('qtype_oumatrix_columns', ['questionid' => backup::VAR_PARENTID]);

        return $plugin;
    }

    public static function get_qtype_fileareas() {
        return [
                'feedback' => 'question_created',
                'correctfeedback' => 'question_created',
                'partiallycorrectfeedback' => 'question_created',
                'incorrectfeedback' => 'question_created',
        ];
    }
}
