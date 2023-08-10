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

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/upgradelib.php');

/**
 * Execute qtype_oumatrix upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_qtype_oumatrix_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();
    if ($oldversion < 2023080300) {

        $table = new xmldb_table('qtype_oumatrix_rows');
        $field = new xmldb_field('correctanswers', XMLDB_TYPE_TEXT, 'small', null, false, false);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Data savepoint reached.
        upgrade_plugin_savepoint(true, 2023080300, 'qtype', 'oumatrix');
    }

    if ($oldversion < 2023081400) {

        // Changing type of field name on table qtype_oumatrix_columns to text.
        $table = new xmldb_table('qtype_oumatrix_columns');
        $field = new xmldb_field('name', XMLDB_TYPE_TEXT, 'smal', null, XMLDB_NOTNULL, null, null, 'number');

        // Launch change of type for field name.
        $dbman->change_field_type($table, $field);

        // Changing type of field name on table qtype_oumatrix_rows to text.
        $table = new xmldb_table('qtype_oumatrix_rows');
        $field = new xmldb_field('name', XMLDB_TYPE_TEXT, 'smal', null, XMLDB_NOTNULL, null, null, 'number');

        // Launch change of type for field name.
        $dbman->change_field_type($table, $field);

        // Oumatrix savepoint reached.
        upgrade_plugin_savepoint(true, 2023081400, 'qtype', 'oumatrix');
    }


    return true;
}
