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
 * @package core_ai
 */

namespace core_ai\feature;

use core_ai\configuration\config_collection;
use core_ai\model\interaction_log;
use curl;

/**
 * Base class for Totara AI features. A feature is a generic version of an AI interaction that is
 * supported by one or more AI connector plugins.
 *
 * Each feature implements an abstract subclass of this base class, leaving the call_api() method
 * abstract. When that feature is implemented as a concrete feature by the connector plugin, the
 * plugin will define the call_api() method.
 *
 */
abstract class feature_base {

    protected config_collection $config;

    protected string $interaction_class_name;

    public function __construct(config_collection $config, string $interaction_class_name) {
        $this->config = $config;
        $this->interaction_class_name = $interaction_class_name;
    }

    /**
     * Feature abstract subclass should implement the feature name.
     * @return string
     */
    abstract public static function get_name(): string;

    /**
     * Takes a feature request, asks the implementing connector to generate a feature response.
     *
     * Adds both request and response to the system interaction log.
     *
     * @param request_base $request
     * @return response_base
     */
    public function generate(request_base $request): response_base {
        // log request
        $interaction_log = interaction_log::create($request, $this);

        $response = $this->call_api($request);
        $interaction_log->log_response($response);

        return $response;
    }

    /**
     * Getter for interaction class name.
     *
     * @return string
     */
    public function get_interaction_class_name(): string {
        return $this->interaction_class_name;
    }

    /**
     * Getter for configuration, which is a collection of admin options.
     *
     * @return config_collection
     */
    public function get_config(): config_collection {
        return $this->config;
    }

    /**
     * The AI connector plugin implementing a feature must implement this method, to take the
     * provided request, and use it to generate a response.
     *
     * Note that the AI connector plugin is responsible for correctly validating and sanitising input
     * and output.
     *
     * @param request_base $request
     * @return response_base
     */
    abstract protected function call_api(request_base $request): response_base;

    /**
     * Creates a curl HTTPS client to use for API requests.
     *
     * @return curl
     */
    protected static function new_https_client(): curl {
        $curl = new curl();
        // Don't time out during HTTP transfer, but do time out if no connection.
        $curl->setopt([
            'CURLOPT_TIMEOUT' => 0,
            'CURLOPT_CONNECTTIMEOUT' => 5,
            'CURLOPT_PROTOCOLS' => CURLPROTO_HTTPS
        ]);
        return $curl;
    }
}
