<?php
/**
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
 * @author Simon Chester <simon.chester@totara.com>
 * @package auth_ssosaml
 */

namespace auth_ssosaml\model\idp\metadata;

use totara_core\http\client as http_client;
use totara_core\http\clients\curl_client;
use totara_core\http\request;

/**
 * Loader for remote metadata.
 */
class loader {
    /** @var http_client */
    private $http_client;

    /**
     * @param http_client $http_client
     */
    public function __construct(http_client $http_client) {
        $this->http_client = $http_client;
    }

    /**
     * Create a metadata loader instance with the default http_client
     *
     * @return loader
     */
    public static function make(): loader {
        $http_client = new curl_client();
        $http_client->set_connect_timeout(10);
        $http_client->set_timeout(30);
        return new self($http_client);
    }

    /**
     * Load metadata from the provided URL.
     *
     * @param string $url
     * @return string
     */
    public function load_metadata(string $url): string {
        $request = new request($url);
        $response = $this->http_client->execute($request);
        $response->throw_if_error();
        $response_body = $response->get_body();

        return $response_body;
    }
}
