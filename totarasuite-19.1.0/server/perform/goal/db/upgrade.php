<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package perform_goal
 */

/**
 * Local database upgrade script
 *
 * @param   integer $oldversion Current (pre-upgrade) local db version timestamp
 * @return  boolean $result
 */
function xmldb_perform_goal_upgrade($oldversion) {
    global $DB;

    require_once(__DIR__ . '/upgradelib.php');

    $dbman = $DB->get_manager();

    if ($oldversion < 2024020700) {

        // Define table perform_goal_task to be created.
        $table = new xmldb_table('perform_goal_task');

        // Adding fields to table perform_goal_task.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('goal_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('completed_at', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('created_at', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('updated_at', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Adding keys to table perform_goal_task.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('goal_id_fk', XMLDB_KEY_FOREIGN, array('goal_id'), 'perform_goal', array('id'), 'cascade');

        // Conditionally launch create table for perform_goal_task.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table perform_goal_task_resource to be created.
        $table = new xmldb_table('perform_goal_task_resource');

        // Adding fields to table perform_goal_task_resource.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('task_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('resource_id', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('resource_type', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, null);
        $table->add_field('created_at', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table perform_goal_task_resource.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('task_id_fk', XMLDB_KEY_FOREIGN, array('task_id'), 'perform_goal_task', array('id'), 'cascade');

        // Conditionally launch create table for perform_goal_task_resource.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Goal savepoint reached.
        upgrade_plugin_savepoint(true, 2024020700, 'perform', 'goal');
    }

    if ($oldversion < 2025032701) {

        perform_goal_fix_goal_settings_for_non_perform_sites();

        // Perform goal savepoint reached.
        upgrade_plugin_savepoint(true, 2025032701, 'perform', 'goal');
    }

    return true;
}
