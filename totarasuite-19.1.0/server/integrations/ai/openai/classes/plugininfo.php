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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @author Andrew Watkins <andrew.watkins@pixelfusion.co.nz>
 * @package ai_openai
 */

namespace ai_openai;

use admin_setting_configselect;
use admin_setting_configtext;
use admin_setting_encryptedconfig;
use core\plugininfo\ai as ai_plugininfo;
use core_ai\configuration\config_collection;
use core_ai\configuration\option;

/**
 * Plugin information class as required by Totara's plugin ecosystem.
 * This extends the ai plugininfo class in core.
 */
class plugininfo extends ai_plugininfo {

    public const DEFAULT_URL = 'https://api.openai.com/';

    public const DEFAULT_MODEL = 'gpt-3.5-turbo';

    public const DEFAULT_STRUCTURED_MODEL = 'gpt-4o-mini';

    /**
     * Get available OpenAI generative_prompt models
     *
     * @return array Array of model options
     */
    public static function get_available_models(): array {
        return [
            'gpt-4o' => 'GPT-4o',
            'gpt-4o-mini' => 'GPT-4o Mini',
            'gpt-4-turbo-preview' => 'GPT-4 Turbo',
            'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
            'gpt-3.5-turbo-16k' => 'GPT-3.5 Turbo 16K',
            'custom' => get_string('model_custom', 'core_ai'),
        ];
    }

    /**
     * Models with structured output support, necessary for the response corresponding to a JSON schema.
     *
     * @return string[]
     */
    public static function get_available_structured_json_models(): array {
        return [
            'gpt-4o' => 'GPT-4o',
            'gpt-4o-mini' => 'GPT-4o Mini',
        ];
    }

    /**
     * Get available OpenAI generative_image models
     *
     * @return string[]
     */
    public static function get_available_image_models(): array {
        return [
            'dall-e-2' => 'DALL-E 2',
            'dall-e-3' => 'DALL-E 3',
            'gpt-image-1' => 'GPT-Image-1',
        ];
    }

    public function get_config_collection(): config_collection {
        global $CFG;
        require_once $CFG->libdir . '/adminlib.php';

        $options = [];
        $options[] = new option(
            new admin_setting_encryptedconfig(
                "$this->component/api_key",
                get_string('api_key', 'ai_openai'),
                '',
                ''
            ),
            true
        );
        $options[] = new option(
            new admin_setting_configtext(
                "$this->component/base_url",
                get_string('base_url', 'ai_openai'),
                get_string('base_url_desc', 'ai_openai'),
                static::DEFAULT_URL
            )
        );

        // Add model selection dropdown
        $options[] = new option(
            new admin_setting_configselect(
                "$this->component/model_selection",
                get_string('model_selection', 'ai_openai'),
                get_string('model_selection_desc', 'ai_openai'),
                self::DEFAULT_MODEL,
                static::get_available_models()
            )
        );

        // Only show the model_custom config if model_selection is set to 'custom'.
        if (get_config('ai_openai', 'model_selection') == 'custom') {
            $options[] = new option(
                new admin_setting_configtext(
                    "$this->component/model",
                    get_string('model_custom', 'ai_openai'),
                    get_string('model_custom_desc', 'ai_openai'),
                    static::DEFAULT_MODEL,
                )
            );
        }

        $options[] = new option(
            new admin_setting_configtext(
                "$this->component/org",
                get_string('org', 'ai_openai'),
                '',
                '',
            )
        );

        return new config_collection($options);
    }

    /**
     * Get the configured service base URL for OpenAI API endpoints.
     *
     * @return string
     */
    public static function get_base_url(): string {
        $url = get_config('ai_openai', 'base_url');
        return empty($url) ? static::DEFAULT_URL : rtrim($url, '/');
    }
}
