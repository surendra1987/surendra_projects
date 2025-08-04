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
 * @author Michael Ivanov <michael.ivanov@totara.com>
 * @package totara_api
 */

namespace totara_api\watcher;

use totara_api\auth\access_token_validator;
use totara_api\global_api_config;
use totara_webapi\graphql;
use totara_webapi\hook\pluginfile_pre_hook;

class pluginfile_pre_watcher {

    /**
     * @param pluginfile_pre_hook $hook
     * @return void
     */
    public static function watch(pluginfile_pre_hook $hook): void {
        $execution_context = $hook->execution_context;
        if ($execution_context->get_type() != graphql::TYPE_EXTERNAL ||
            !empty(global_api_config::get_disable_oauth2_authentication())
        ) {
            return;
        }

        try {
            $request = access_token_validator::validate_access_token();
            $client = access_token_validator::get_client_by_request($request);
            access_token_validator::login_api_user($client);

            // Send the oauth2 request, which includes the oauth_client_id property, through to the resolver.
            $execution_context->set_variable('oauth2_request', $request);

            // Send the client model, through to the resolver.
            $execution_context->set_variable('client', $client);
        } catch (\Exception $exception) {
            $hook->set_exception($exception);
        }
    }
}