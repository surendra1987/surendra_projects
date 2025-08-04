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

use core_ai\feature\response_base;

/**
 * A generative_prompt request is a collection of prompts to pass to the plugin.
 *
 * Each prompt is a role -- always 'assistant' in the response -- and a message.
 *
 */
class response extends response_base {

    private array $prompts;

    private ?string $error;

    public function __construct(array $prompts = [], ?string $error = null) {
        $this->prompts = $prompts;
        $this->error = clean_string($error);
    }

    public function get_error(): ?string {
        return $this->error;
    }

    public function to_array(): array {
        return $this->prompts;
    }
}
