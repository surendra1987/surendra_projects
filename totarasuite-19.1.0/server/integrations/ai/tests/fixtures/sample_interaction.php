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
 */

namespace fixtures;

use core_ai\configuration\config_collection;
use core_ai\feature\feature_base;
use core_ai\feature\generative_prompt\generative_prompt_feature;
use core_ai\feature\generative_prompt\prompt;
use core_ai\feature\generative_prompt\request;
use core_ai\feature\interaction_input_interface;
use core_ai\feature\interaction_output_interface;
use core_ai\feature\response_base;
use core_ai\interaction;
use stdClass;

class sample_interaction extends interaction {

    public static function get_name(): string {
        return "sample interaction";
    }

    public static function get_description(): string {
        return "sample interaction description";
    }

    public static function get_feature_class(): string {
        return generative_prompt_feature::class;
    }

    public static function get_config(): array {
        $config = new stdClass();
        $config->enabled = '1';
        $config->model = 'sample';
        return (array)$config;
    }

    public function run(interaction_input_interface $params): interaction_output_interface {
        $prompt_gen = $this->get_ai_feature();
        $request = new request([
            new prompt("Hello"),
            new prompt("world"),
        ]);
        $request->set_model('sample');
        $response = $prompt_gen->generate($request);

        $output = new class implements interaction_output_interface {
            public response_base $response;
        };
        $output->response = $response;
        return $output;
    }

    protected function get_ai_feature(): feature_base {
        return new sample_connector_feature(new config_collection([]), sample_interaction::class);
    }
}
