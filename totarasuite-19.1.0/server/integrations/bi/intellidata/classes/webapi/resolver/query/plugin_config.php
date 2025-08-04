<?php
/**
 * This file is part of Totara Learn
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
 * @author Qingyang Liu <qingyang.liu@totaralearning.com>
 * @package bi_intellidata
 */

namespace bi_intellidata\webapi\resolver\query;

use core\webapi\middleware\require_plugin_enabled;
use bi_intellidata\helpers\ParamsHelper;
use bi_intellidata\helpers\SettingsHelper;
use bi_intellidata\helpers\TasksHelper;
use context_system;
use core\webapi\execution_context;
use core\webapi\middleware\require_authenticated_user;
use core\webapi\middleware\require_user_capability;
use core\webapi\query_resolver;
use external_api;
use stdClass;

/**
 * Query to get plugin configurations for IB.
 */
class plugin_config extends query_resolver {
    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec) {
        global $CFG;
        require_once($CFG->libdir . '/externallib.php');
        // Ensure the current user is allowed to run this function.
        external_api::validate_context(context_system::instance());

        return [
            'moodleconfig' => ParamsHelper::get_moodle_config(),
            'pluginversion' => ParamsHelper::get_plugin_version(),
            'cronconfig' => TasksHelper::get_tasks_config(),
            'pluginconfig' => self::format_plugin_info(),
        ];

    }

    /**
     * @return array
     */
    public static function format_plugin_info(): array {
        $payload = [];
        $plugin_settings = SettingsHelper::get_plugin_settings();
        foreach ($plugin_settings as $settings) {
            $payload['title'] = $settings['title'];

            foreach ($settings['items'] as $items) {
                $obj = new stdClass();
                foreach ($items['items'] as $item){
                    // Skip sensitive setting value.
                    if (in_array($item['name'], SettingsHelper::SENSITIVE_SETTINGS)) {
                        continue;
                    }

                    if (isset($items['grouptitle'])) {
                        $obj->grouptitle = $items['grouptitle'];
                    }
                    $obj->type = $item['type'];
                    $obj->subtype = $item['subtype'];
                    $obj->title = $item['title'];
                    $obj->name  = $item['name'];

                    if (is_array($item['value'])){
                        $obj->value = json_encode($item['value']);
                    } else if (is_bool($item['value'])) {
                        $obj->value = $item['value'] ? 1 : 0;
                    } else if (is_string($item['value'])) {
                        $obj->value = $item['value'];
                    }

                    $payload['items'][]= (array)$obj;
                }
            }
        }

        return $payload;
    }

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            new require_authenticated_user(),
            new require_plugin_enabled('bi_intellidata'),
            new require_user_capability('bi/intellidata:viewconfig')
        ];
    }
}