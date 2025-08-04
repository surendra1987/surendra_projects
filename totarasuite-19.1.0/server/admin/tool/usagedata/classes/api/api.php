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

use coding_exception;
use totara_core\http\clients\curl_client;
use totara_core\http\request;
use totara_core\http\response;

abstract class api {

    protected curl_client $client;

    public function __construct() {
        $this->client = new curl_client();
    }

    /**
     * Send get request to URL
     *
     * @param string $url
     * @param array $params
     * @return response
     * @throws coding_exception
     */
    protected function get(string $url, array $params = []): response {
        $request_url = new \moodle_url($url, $params);
        $request = request::get($request_url);

        return $this->client->execute($request);
    }

    /**
     * Send put request to URL
     *
     * @param string $url
     * @param array $data The post payload
     * @return response
     * @throws coding_exception
     */
    protected function put(string $url, array $data = []): response {
        $request_url = new \moodle_url($url);
        $request = request::put($request_url, $data);

        return $this->client->execute($request);
    }
}