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
 * This file manipulate data when an upgrade of the plug-in has been detected.
 *
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Upgrade code according to the evolution of the database.
 * This method is automagically called by Moodle.
 * @param int $oldversion number of the old version
 * @return bool
 */
function xmldb_tool_attestoodle_upgrade($oldversion) {
    // Update this function in need of DB upgrade while installing new version.
    global $DB;
    $dbman = $DB->get_manager();
    // Add columns grpcriteria1 and grpcriteria2 to attestoodle_train_template.
    if ($oldversion < 2018101001) {
        $table = new xmldb_table('attestoodle_train_template');
        $field = new xmldb_field('grpcriteria1', XMLDB_TYPE_CHAR, '35', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('grpcriteria2', XMLDB_TYPE_CHAR, '35', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2018101001, 'tool', 'attestoodle');
    }
    if ($oldversion < 2018101611) {
        $table = new xmldb_table('attestoodle_value_log');
        $field = new xmldb_field('milestone', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'certificateid');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'moduleid');
            upgrade_plugin_savepoint(true, 2018101611, 'tool', 'attestoodle');
        }
    }
    if ($oldversion < 2018101705) {
        $table = new xmldb_table('attestoodle_milestone');
        $field = new xmldb_field('milestone', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'id');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'creditedtime');
            upgrade_plugin_savepoint(true, 2018101705, 'tool', 'attestoodle');
        }
    }

    if ($oldversion < 2018120501) {
        // Define table to be renamed.
        $table = new xmldb_table('attestoodle_train_template');
        if ($dbman->table_exists($table)) {
            // Rename the table to use the correct Moodle naming convention.
            $dbman->rename_table($table, 'tool_attestoodle_train_style');
        }

        $table = new xmldb_table('attestoodle_template_detail');
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'tool_attestoodle_tpl_detail');
        }
        $table = new xmldb_table('attestoodle_template');
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'tool_attestoodle_template');
        }
        $table = new xmldb_table('attestoodle_value_log');
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'tool_attestoodle_value_log');
        }
        $table = new xmldb_table('attestoodle_certif_log');
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'tool_attestoodle_certif_log');
        }
        $table = new xmldb_table('attestoodle_launch_log');
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'tool_attestoodle_launch_log');
        }
        $table = new xmldb_table('attestoodle_milestone');
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'tool_attestoodle_milestone');
        }
        $table = new xmldb_table('attestoodle_training');
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'tool_attestoodle_training');
        }

        upgrade_plugin_savepoint(true, 2018120501, 'tool', 'attestoodle');
    }

    // Create temporary table.
    if ($oldversion < 2018122601) {
        // Define table to be created.
        $table = new xmldb_table('tool_attestoodle_tmp');
        // Adding fields to table.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('trainingid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('fileinfo', XMLDB_TYPE_TEXT, 'medium', null, null, null, null);
        $table->add_field('pdfinfo', XMLDB_TYPE_TEXT, 'medium', null, null, null, null);
        $table->add_field('learnerid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        // Adding keys to table.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        // Create table.
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
        $dbman->create_table($table);
        upgrade_plugin_savepoint(true, 2018122601, 'tool', 'attestoodle');
    }

    // Add information on milestone.
    if ($oldversion < 2019021911) {
        $table = new xmldb_table('tool_attestoodle_milestone');
        $field = new xmldb_field('trainingid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('course', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('name', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Update records.
        $items = $DB->get_records('tool_attestoodle_milestone');
        foreach ($items as $item) {
            if ($item->trainingid != null) {
                continue;
            }
            $item->timemodified = \time();
            $rec = $DB->get_record('course_modules', array('id' => $item->moduleid));
            if (!isset($rec->course)) {
                continue;
            }
            $item->course = $rec->course;

            $table = $DB->get_field('modules', 'name', array('id' => $rec->module));
            $infocm = $DB->get_record($table, array('id' => $rec->instance));
            $item->name = $infocm->name;

            $categ = $DB->get_field('course', 'category', array('id' => $rec->course));
            $training = $DB->get_field('tool_attestoodle_training', 'id', array('categoryid' => $categ));

            $notfind = ($training == 0);
            while ($notfind) {
                $categ = $DB->get_field('course_categories', 'parent', array('id' => $categ));
                if ($categ != 0) {
                    $training = $DB->get_field('tool_attestoodle_training', 'id', array('categoryid' => $categ));
                    $notfind = ($training == 0);
                } else {
                    $notfind = false;
                }
            }

            $item->trainingid = $training;
            $DB->update_record("tool_attestoodle_milestone", $item);
        }
        upgrade_plugin_savepoint(true, 2019021911, 'tool', 'attestoodle');
    }

    return true;
}
