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
 */

namespace fixtures;

use core_ai\configuration\config_collection;
use core_ai\feature;
use core_ai\feature\generative_prompt;
use core_ai\feature\generative_prompt\prompt;
use core_ai\feature\generative_prompt\request;
use core_ai\interaction;

class sample_interaction extends interaction {

    public static function get_name(): string {
        return "sample interaction";
    }

    public static function get_description(): string {
        return "sample interaction description";
    }

    public function run(array $params): array {
        $prompt_gen = $this->get_ai_feature(generative_prompt::class);
        $response = $prompt_gen->generate(new request([
            new prompt("Hello"),
            new prompt("world"),
        ]));

        return [
            "response" => $response,
        ];
    }

    protected function get_ai_feature(string $feature_class): feature {
        return new sample_feature(new config_collection([]), sample_interaction::class);
    }
}
