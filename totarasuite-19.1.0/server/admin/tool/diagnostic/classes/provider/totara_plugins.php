<?php
/**
* This file is part of Totara Learn
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
* @author  Johannes Cilliers <johannes.cilliers@totaralearning.com>
* @package tool_diagnostic
*/

namespace tool_diagnostic\provider;

use core\plugininfo\base as plugininfo_base;
use core_plugin_manager;
use tool_diagnostic\content\content;

class totara_plugins extends base {

    /** @var int */
    protected static $order = 40;

    /**
     * @inheritDoc
     */
    public static function get_id(): string {
        return 'totara_plugins';
    }

    /**
     * @inheritDoc
     */
    public function get_content(): content {
        $content = new content();
        $content->set_id(static::get_id());

        $plugins_data = core_plugin_manager::instance()->get_plugins();

        $result_data = [];
        foreach ($plugins_data as $top_plugin => $top_plugin_data) {
            $result_data[$top_plugin] = [];
            /** @var plugininfo_base $plugin_object */
            foreach ($top_plugin_data as $sub_plugin => $plugin_object) {
                $result_data[$top_plugin][$sub_plugin] = [
                    'type' => $plugin_object->type,
                    'name' => $plugin_object->name,
                    'displayname' => $plugin_object->displayname,
                    'source' => $plugin_object->source,
                    'version' => $plugin_object->versiondb,
                    'enabled' => $plugin_object->is_enabled(),
                ];
            }
        }

        $content->set_data($this->json_encode($result_data));
        $content->set_data_type(content::TYPE_JSON);

        return $content;
    }

}
