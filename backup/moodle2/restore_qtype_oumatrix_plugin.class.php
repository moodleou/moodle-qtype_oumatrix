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
 * Restore plugin class that provides the necessary information needed to restore one oumatrix qtype plugin.
 *
 * @package qtype_oumatrix
 * @copyright 2023 The Open University
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_qtype_oumatrix_plugin extends restore_qtype_plugin {

    /**
     * Returns the paths to be handled by the plugin at question level.
     *
     * @return array The restore path element array.
     */
    protected function define_question_plugin_structure(): array {

        $paths = [];

        // We used get_recommended_name() so this works.
        $elements = [
            'qtype_oumatrix' => '/oumatrix',
            'qtype_oumatrix_column' => '/columns/column',
            'qtype_oumatrix_row' => '/rows/row',
        ];

        foreach ($elements as $elename => $path) {
            $elepath = $this->get_pathfor($path);
            $paths[] = new restore_path_element($elename, $elepath);
        }

        return $paths; // And we return the interesting paths.
    }
    
    #[\Override]
    public static function convert_backup_to_questiondata(array $backupdata): \stdClass {
        $questiondata = parent::convert_backup_to_questiondata($backupdata);
        $qtype = $questiondata->qtype;
        if (isset($backupdata["plugin_qtype_{$qtype}_question"]['oumatrix'])) {
            $questiondata->options = (object) array_merge(
                (array) $questiondata->options,
                $backupdata["plugin_qtype_{$qtype}_question"]['oumatrix'][0],
            );
        }

        $questiondata->columns = [];
        foreach ($backupdata["plugin_qtype_{$qtype}_question"]['columns']['column'] as $column) {
            $questiondata->columns[] = (object) $column;
        }
        $questiondata->rows = [];
        foreach ($backupdata["plugin_qtype_{$qtype}_question"]['rows']['row'] as $row) {
            $questiondata->rows[] = (object) $row;
        }
        return $questiondata;
    }

    #[\Override]
    protected function define_excluded_identity_hash_fields(): array {
        return [
            'columns/questionid',
            'columns/id',
            'rows/questionid',
            'rows/id',
        ];
    }

    /**
     *
     * Process the qtype_oumatrix element.
     *
     * @param array $data
     */
    public function process_qtype_oumatrix(array $data): void {
        self::process_qtype_oumatrix_data_with_table_name($data, 'qtype_oumatrix_options');
    }

    /**
     *
     * Process the qtype_oumatrix_columns element.
     *
     * @param array $data
     */
    public function process_qtype_oumatrix_column(array $data): void {
        self::process_qtype_oumatrix_data_with_table_name($data, 'qtype_oumatrix_columns');
    }

    /**
     *
     * Process the qtype_oumatrix_rows element.
     *
     * @param array $data
     */
    public function process_qtype_oumatrix_row(array $data): void {
        self::process_qtype_oumatrix_data_with_table_name($data, 'qtype_oumatrix_rows');
    }

    /**
     * Process the qtype oumatrix data with the table name.
     *
     * @param array $data XML data.
     * @param string $tablename Table name
     */
    private function process_qtype_oumatrix_data_with_table_name(array $data, string $tablename): void {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;

        // Detect if the question is created or mapped.
        $questioncreated = $this->get_mappingid('question_created',
                $this->get_old_parentid('question'));

        // If the question has been created by restore, we need to create its question_oumatrix too.
        if ($questioncreated) {
            // Adjust some columns.
            $data->questionid = $this->get_new_parentid('question');
            // Insert record.
            $newitemid = $DB->insert_record($tablename, $data);
            // Create mapping.
            $this->set_mapping($tablename, $oldid, $newitemid);
        }
    }

    /**
     * Return the contents of this qtype to be processed by the links decoder.
     */
    public static function define_decode_contents(): array {
        $contents = [];
        $contents[] = new restore_decode_content('qtype_oumatrix_options',
            ['correctfeedback', 'partiallycorrectfeedback', 'incorrectfeedback'], 'qtype_oumatrix_options');
        $contents[] = new restore_decode_content('qtype_oumatrix_rows',
            ['feedback'], 'qtype_oumatrix_rows');
        return $contents;
    }
}
