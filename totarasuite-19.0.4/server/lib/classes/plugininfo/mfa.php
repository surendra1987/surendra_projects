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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package core
 */

namespace core\plugininfo;

/**
 * Plugin info for MFA.
 */
class mfa extends base {
    /**
     * @return bool
     */
    public function is_uninstall_allowed(): bool {
        return false;
    }

    /**
     * Return the list of enabled MFA plugins
     *
     * @return array
     */
    public static function get_enabled_plugins(): array {
        global $CFG;

        if (empty($CFG->mfa_plugins)) {
            return [];
        }

        $enabled = explode(',', $CFG->mfa_plugins);
        $plugins = \core_component::get_plugin_list('mfa');
        $enabled_list = [];

        foreach ($enabled as $name) {
            $name = trim($name);
            if (empty($name)) {
                continue;
            }
            if (!isset($plugins[$name])) {
                debugging("Cannot find plugin '{$name} of type 'mfa'");
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
        return 'mfa';
    }
}
