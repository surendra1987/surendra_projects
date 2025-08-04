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
 * @package core_ai
 */

namespace core_ai;

use core_ai\configuration\config_collection;
use core_ai\feature\request;
use core_ai\feature\response;
use core_ai\model\interaction_log;

abstract class feature {

    protected config_collection $config;

    protected string $interaction_class_name;

    public function __construct(config_collection $config, string $interaction_class_name) {
        $this->config = $config;
        $this->interaction_class_name = $interaction_class_name;
    }

    abstract public static function get_name(): string;

    public function generate(request $request): response {
        // log request
        $interaction_log = interaction_log::create($request, $this);

        $response = $this->call_api($request);
        $interaction_log->log_response($response);

        return $response;
    }

    public function get_interaction_class_name(): string {
        return $this->interaction_class_name;
    }

    public function get_config(): config_collection {
        return $this->config;
    }

    /**
     * With the provided request, make a request to the AI's API
     * It is on the developer to ensure the request and response from the API gets sanitized.
     *
     * @param request $request
     * @return response
     */
    abstract protected function call_api(request $request): response;
}
