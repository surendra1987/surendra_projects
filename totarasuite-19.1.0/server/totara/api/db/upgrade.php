<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2022 onwards Totara Learning Solutions LTD
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author  Michael Ivanov <michael.ivanov@totaralearning.com>
 * @package totara_api
 */

defined('MOODLE_INTERNAL') || die();

use core\orm\query\builder;

/**
 * @param int $old_version
 * @return bool
 */
function xmldb_totara_api_upgrade(int $old_version): bool {
    global $DB;
    $db_manager = $DB->get_manager();

    if ($old_version < 2022111202) {
        // Define table totara_api_client_settings
        $table = new xmldb_table('totara_api_client_settings');

        // Define fields
        $field = new xmldb_field('response_debug', XMLDB_TYPE_INTEGER, '1', null, null, null, null, 'default_token_expiry_time');

        if (!$db_manager->field_exists($table, $field)) {
            $db_manager->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2022111202, 'totara', 'api');
    }

    if ($old_version < 2024022300) {
        // add the new field for introspection per client
        $table = new xmldb_table('totara_api_client_settings');
        $per_client_introspection_field = new xmldb_field('enable_introspection', XMLDB_TYPE_INTEGER, '1');
        if (!$db_manager->field_exists($table, $per_client_introspection_field)) {
            $db_manager->add_field($table, $per_client_introspection_field);
        }

        // update the existing api clients to have introspection matching the previous site wide setting
        $previously_enabled = get_config('totara_api', 'enable_introspection') ? 1 : 0;
        $sql = "UPDATE {totara_api_client_settings} SET enable_introspection = $previously_enabled";
        $DB->execute($sql);

        upgrade_plugin_savepoint(true, 2024022300, 'totara', 'api');
    }

    if ($old_version < 2025022500) {
        $table = new xmldb_table('totara_api_client_settings');
        $allowed_ip_list_field = new xmldb_field('allowed_ip_list', XMLDB_TYPE_TEXT, null, null, null, null, null, null);

        if (!$db_manager->field_exists($table, $allowed_ip_list_field)) {
            $db_manager->add_field($table, $allowed_ip_list_field);
        }

        upgrade_plugin_savepoint(true, 2025022500, 'totara', 'api');
    }
    return true;
}