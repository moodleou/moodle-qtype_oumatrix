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
 * Plugin administration pages are defined here.
 *
 * @package     qtype_oumatrix
 * @copyright   2023 The Open University
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $inputtype = [
            'single' => new lang_string('inputtypesingle', 'qtype_oumatrix'),
            'multiple' => new lang_string('inputtypemultiple', 'qtype_oumatrix'),
    ];
    $settings->add(new admin_setting_configselect('qtype_oumatrix/inputtype',
            new lang_string('inputtype', 'qtype_oumatrix'),
            new lang_string('inputtype_desc', 'qtype_oumatrix'), 'single', $inputtype));

    $grademethod = [
            'partial' => new lang_string('gradepartialcredit', 'qtype_oumatrix'),
            'allnone' => new lang_string('gradeallornothing', 'qtype_oumatrix'),
    ];
    $settings->add(new admin_setting_configselect('qtype_oumatrix/grademethod',
            new lang_string('grademethod', 'qtype_oumatrix'),
            new lang_string('grademethod_desc', 'qtype_oumatrix'), 'partial', $grademethod));

    $settings->add(new admin_setting_configcheckbox('qtype_oumatrix/shuffleanswers',
            new lang_string('shuffleanswers', 'qtype_oumatrix'),
            new lang_string('shuffleanswers_desc', 'qtype_oumatrix'), '1'));
}
