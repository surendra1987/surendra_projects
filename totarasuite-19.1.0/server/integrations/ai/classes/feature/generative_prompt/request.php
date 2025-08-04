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

namespace core_ai\feature\generative_prompt;

use core_ai\feature\request_base;

/**
 * A generative_prompt request is a collection of prompts to pass to the plugin.
 *
 * Each prompt is a role (user or system (or assistant?)) and a message.
 *
 */
class request extends request_base {

    private array $prompts;

    public function __construct(array $prompts) {
        foreach ($prompts as $prompt) {
            if (!$prompt instanceof prompt) {
                throw new \coding_exception("only prompts allowed");
            }
        }
        $this->prompts = $prompts;
    }

    public function get_prompts(): array {
        return $this->prompts;
    }

    public function to_array(): array {
        return [
            'prompts' => $this->get_prompts(),
            'model' => $this->get_model()
        ];
    }
}
