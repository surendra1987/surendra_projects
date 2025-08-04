<?php
/**
 * This file is part of Totara Talent Experience Platform
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Sam Hemelryk <sam.hemelryk@totaralearning.com>
 * @package usagedata
 */

namespace core\usagedata;

use tool_usagedata\export;

class plugin_usage implements export {

    /**
     * @throws \coding_exception
     */
    public function get_summary(): string {
        return get_string('plugin_usage_summary');
    }

    /**
     * @inheritDoc
     */
    public function get_type(): int {
        return export::TYPE_ARRAY;
    }

    /**
     * @throws \coding_exception
     */
    public function export(): array {
        $manager = \core_plugin_manager::instance();
        $result = [];
        foreach ($manager->get_plugin_types() as $plugin_type => $plugin_dir) {
            $item = [];
            $data = $manager->get_plugins_of_type($plugin_type);
            if (empty($data)) {
                continue;
            }
            $item['plugin_type'] = $plugin_type;
            $item['plugin_detail'] = array_map(function ($key, $plugin) {
                $plugin->load_db_version();
                return [
                    'name' => $plugin->name,
                    'version' => $plugin->versiondb
                ];
            }, $data, $data);
            $result[] = $item;
        }

        return $result;
    }
}