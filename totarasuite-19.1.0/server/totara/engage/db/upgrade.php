<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2020 onwards Totara Learning Solutions LTD
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
 * @author Johannes Cilliers <johannes.cilliers@totaralearning.com>
 * @package totara_engage
 */

/**
 * Local database upgrade script
 *
 * @param   integer $oldversion Current (pre-upgrade) local db version timestamp
 * @return  boolean $result
 */
function xmldb_totara_engage_upgrade($oldversion) {
    global $DB;
    require_once __DIR__ . '/upgradelib.php';

    $dbman = $DB->get_manager();

    if ($oldversion < 2020102700) {
        $DB->execute('update {engage_share_recipient} set area = lower(area)');
        upgrade_plugin_savepoint(true, 2020102700, 'totara', 'engage');
    }

    if ($oldversion < 2020110601) {

        totara_engage_set_context_id_for_resource();

        upgrade_plugin_savepoint(true, 2020110601, 'totara', 'engage');
    }

    if ($oldversion < 2023122200) {
        // Remove the component in the existing topic collection (if it exists) so that it works like other tag collections.
        $DB->set_field('tag_coll', 'component', null, ['component' => 'totara_topic']);

        upgrade_plugin_savepoint(true, 2023122200, 'totara', 'engage');
    }

    if ($oldversion < 2024080800) {

        totara_engage_upgrade_allow_view_user_profiles_setting();

        upgrade_plugin_savepoint(true, 2024080800, 'totara', 'engage');
    }

    return true;
}