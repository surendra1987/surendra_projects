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

namespace totara_webhook\auth;

use totara_core\http\request;
use totara_oauth2\model\client_provider;

class webhook_hmac_auth implements webhook_auth {

    /**
     * Will sign a request based on the body and hmac
     *
     * @param request $request
     * @param string $auth_config
     * @return request
     * @throws \coding_exception
     */
    public static function authorise_request(request $request, string $auth_config): request {
        $data = $request->get_post_data();
        $signature = hash_hmac('sha256', $data, $auth_config);
        $request->set_header("X-Totara-Signature", $signature);

        return $request;
    }

    /**
     * Will generate a new signing secret
     *
     * @return string
     */
    public static function generate_config(): string {
        return random_string(24);
    }
}
