<?php
/**
 * This file is part of Totara Core
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package ai_noai
 */

namespace ai_noai;

use admin_setting_configselect;
use core\plugininfo\ai as ai_plugininfo;
use core_ai\configuration\config_collection;
use core_ai\configuration\option;

/**
 * Plugin information class as required by Totara's plugin ecosystem.
 * This extends the ai plugininfo class in core.
 */
class plugininfo extends ai_plugininfo {

    public const DEFAULT_MODEL = 'echo';

    /**
     * Get available OpenAI generative_prompt models
     *
     * @return array Array of model options
     */
    public static function get_available_models(): array {
        return [
            'echo' => 'Return the prompt',
        ];
    }

    /**
     * Get available OpenAI generative_image models
     *
     * @return string[]
     */
    public static function get_available_image_models(): array {
        return [
            'wikimedia' => 'Wikimedia',
        ];
    }

    public function get_config_collection(): config_collection {
        $options = [];

        // Add model selection dropdown
        $options[] = new option(
            new admin_setting_configselect(
                "$this->component/model_selection",
                get_string('model_selection', 'ai_noai'),
                get_string('model_selection_desc', 'ai_noai'),
                self::DEFAULT_MODEL,
                self::get_available_models()
            )
        );

        return new config_collection($options);
    }

}
