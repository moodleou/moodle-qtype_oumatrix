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
 * Plugin upgrade steps are defined here.
 *
 * @package    qtype_oumatrix
 * @copyright  2023 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Execute qtype_oumatrix upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_qtype_oumatrix_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();
    // Put any upgrade step following this.
    if ($oldversion < 2025090200) {
        // Define field questionnumbering to be added to qtype_oumatrix_options.
        $table = new xmldb_table('qtype_oumatrix_options');
        $field = new xmldb_field('questionnumbering', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, 'none', 'shownumcorrect');

        // Conditionally launch add field questionnumbering.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // OU matrix savepoint reached.
        upgrade_plugin_savepoint(true, 2025090200, 'qtype', 'oumatrix');
    }

    return true;
}
