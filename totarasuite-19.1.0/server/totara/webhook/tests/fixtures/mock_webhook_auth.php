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
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package totara_webhook
 */

namespace totara_webapi\fixtures;

use totara_core\http\request;
use totara_webhook\auth\webhook_auth;

/**
 * Class to help with testing webhook authentication functionality
 */
class mock_webhook_auth implements webhook_auth {

    public static request $request;
    public static string $auth_config = '';
    public static string $generated_config = 'mock_generated';

    public static bool $authorise_request_called = false;
    public static bool $generate_config_called = false;

    /**
     * @param request $request
     * @param string $auth_config
     * @return request
     */
    public static function authorise_request(request $request, string $auth_config): request {
        static::$request = $request;
        static::$auth_config = $auth_config;
        static::$authorise_request_called = true;

        return static::$request;
    }

    /**
     * @return string
     */
    public static function generate_config(): string {
        static::$generate_config_called = true;
        return static::$generated_config;
    }
}
