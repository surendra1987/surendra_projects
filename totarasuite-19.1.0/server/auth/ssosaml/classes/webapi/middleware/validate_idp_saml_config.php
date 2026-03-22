<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package auth_ssosaml
 */

namespace auth_ssosaml\webapi\middleware;

use auth_ssosaml\exception\invalid_payload;
use auth_ssosaml\model\idp\config;
use Closure;
use core\webapi\middleware;
use core\webapi\resolver\payload;
use core\webapi\resolver\result;

/**
 * Middleware to validate the IdP input for SAML config.
 *
 */
class validate_idp_saml_config implements middleware {
    /**
     * Execute the middleware, and if the capability check fails throw an exception.
     *
     * @param payload $payload
     * @param Closure $next
     * @return result
     */
    public function handle(payload $payload, Closure $next): result {
        $input = $payload->get_variable('input');
        if (empty($input)) {
            throw new invalid_payload('input');
        }
        if (!isset($input['saml_config'])) {
            throw new invalid_payload('input.saml_config');
        }

        $saml_config = $input['saml_config'];
        $defaults = config::get_idp_config_defaults();

        // Cast to bools
        $bool_fields = ['sign_metadata', 'wants_assertions_signed', 'authnrequests_signed'];

        // Blanks must be null
        $null_fields = ['entity_id'];

        foreach ($saml_config as $field => $value) {
            if (!array_key_exists($field, $defaults)) {
                throw new \coding_exception('Cannot update saml field ' . $field);
            }

            if (in_array($field, $bool_fields, true)) {
                $saml_config[$field] = boolval($value);
            }

            // Some fields must have a null over an empty value
            if (in_array($field, $null_fields, true)) {
                $value = trim((string) $value); // Null fields don't accept blank values like spaces
                $saml_config[$field] = empty($value) ? null : $value;
            }
        }

        $input['saml_config'] = $saml_config;
        $payload->set_variable('input', $input);

        return $next($payload);
    }
}