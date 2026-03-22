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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package tool_usagedata
 */

namespace tool_usagedata\api;

use Exception;
use tool_usagedata\config;
use tool_usagedata\exception\signing_service_api_exception;

class signing_service_api extends api {

    /**
     * Requests and returns a signed URL for exporting usagedata
     * @return string
     * @throws signing_service_api_exception
     */
    public function retrieve_signed_url(): string {
        $base_url = rtrim(config::get_signing_service_base_url(), '/');
        $url = $base_url . config::get_signing_service_endpoint();

        $hash = config::get_registration_hash();
        if ($hash === "") {
            throw new signing_service_api_exception('The registration code should not be empty.');
        }

        try {
            $resp = $this->get(
                $url,
                [
                    'site_id' => config::get_site_identifier(),
                    'site_identifier' => get_site_identifier(),
                    'registration_hash' => $hash,
                ]
            );
            $resp->throw_if_error();

            $data = json_decode(
                $resp->get_body(),
                true,
                512,
                JSON_THROW_ON_ERROR
            )['data'];
        } catch (Exception $e) {
            throw new signing_service_api_exception('Could not get signed URL: ' . $e->getMessage(), 0, $e);
        }

        if (empty($data['signed_url'])) {
            throw new signing_service_api_exception('Could not get signed URL: Response is not as expected, response given: ' . json_encode($data));
        }

        return $data['signed_url'];
    }
}
