<?php
/**
 * This file is part of Totara Core
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package totara_topic
 */

use totara_topic\topic_helper;

/**
 * Local database upgrade script
 *
 * @param   integer $oldversion Current (pre-upgrade) local db version timestamp
 * @return  boolean $result
 */
function xmldb_totara_topic_upgrade($oldversion) {
    global $DB;

    if ($oldversion < 2023122200) {
        $is_default = $DB->get_field('tag_coll', 'isdefault', ['id' => topic_helper::get_engage_tag_collection_id(), 'name' => 'Topics']);

        if ($is_default !== false && $is_default == 1) {
            $DB->set_field('tag_coll', 'isdefault', 0, ['id' => topic_helper::get_engage_tag_collection_id()]);
        }

        upgrade_plugin_savepoint(true, 2023122200, 'totara', 'topic');
    }

    return true;
}