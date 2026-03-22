<?php
/*
 * This file is part of Totara Learn
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Brian Barnea <brian.barnes@totara.com>
 * @package tool_recyclebin
 */

/**
 * Database upgrade script
 *
 * @param integer $oldversion Current (pre-upgrade) local db version timestamp
 * @return bool
 *
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_tool_recyclebin_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2025031000) {

        // Increase the length available for course name
        // Yes the table name looks wrong, but testing proves it's right ...
        $table = new xmldb_table('tool_recyclebin_category');
        $field = new xmldb_field('fullname', XMLDB_TYPE_CHAR, '1333', null, true);
        $dbman->change_field_type($table, $field);

        // Recyclebin savepoint reached.
        upgrade_plugin_savepoint(true, 2025031000, 'tool', 'recyclebin');
    }

    return true;
}