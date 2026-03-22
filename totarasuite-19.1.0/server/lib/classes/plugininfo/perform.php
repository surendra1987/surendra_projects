<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package core
 */

namespace core\plugininfo;

use core\orm\query\builder;
use perform_goal\entity\goal as perform_goal_entity;
use perform_goal\settings_helper;

/**
 * Plugin info for Perform plugins.
 */
class perform extends base {
    /**
     * Return the list of enabled Perform plugins
     *
     * @return array
     */
    public static function get_enabled_plugins(): array {
        global $CFG;

        if (empty($CFG->perform_plugins)) {
            return [];
        }

        $enabled = explode(',', $CFG->perform_plugins);
        $plugins = \core_component::get_plugin_list('perform');
        $enabled_list = [];

        foreach ($enabled as $name) {
            $name = trim($name);
            if (empty($name)) {
                continue;
            }
            if (!isset($plugins[$name])) {
                debugging("Cannot find plugin '{$name} of type 'perform'");
                continue;
            }
            $enabled_list[$name] = $name;
        }

        return $enabled_list;
    }

    /**
     * @return string
     */
    public function get_settings_section_name(): string {
        return 'perform';
    }

    /**
     * @inheritDoc
     */
    public function get_usage_for_registration_data() {
        $data = [];
        $data['number_of_totara_goals'] = builder::table(perform_goal_entity::TABLE)->count();
        $data['perform_goals_enabled'] = (int)settings_helper::is_perform_goals_enabled();
        return $data;
    }
}
