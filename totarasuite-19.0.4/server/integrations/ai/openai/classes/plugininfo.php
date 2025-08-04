<?php
/*
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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 */

namespace ai_openai;

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

    public const DEFAULT_MODEL = 'gpt-3.5-turbo';

    public function get_config_collection(): config_collection {
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
                "$this->component/model",
                get_string('model', 'ai_openai'),
                '',
                self::DEFAULT_MODEL,
            )
        );

        return new config_collection($options);
    }
}
