<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2021 onwards Totara Learning Solutions LTD
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
 * @author Nathan Lewis <nathan.lewis@totaralearning.com>
 * @package mod_approval
 */

function xmldb_approval_install() {
    global $CFG, $DB;

    // Register all approval built-in notifications.
    require_once("{$CFG->dirroot}/totara/notification/db/upgradelib.php");
    totara_notification_sync_built_in_notification('mod_approval');

    $dbman = $DB->get_manager();

    // Add foreign keys with cascade delete on user_enrolments_application table. Done here because we can't create the fk until approval tables are installed.
    $table = new xmldb_table('user_enrolments_application');
    $key = new xmldb_key('user_enrolments_fk', XMLDB_KEY_FOREIGN_UNIQUE, ['user_enrolments_id'], 'user_enrolments', ['id'], 'cascade');
    if ($dbman->table_exists($table) && !$dbman->key_exists($table, $key)) {
        $DB->get_manager()->add_key($table, $key);
    }
    $key = new xmldb_key('approval_application_fk', XMLDB_KEY_FOREIGN, ['approval_application_id'], 'approval_application', ['id'], 'cascade');
    if ($dbman->table_exists($table) && !$dbman->key_exists($table, $key)) {
        $DB->get_manager()->add_key($table, $key);
    }
}
