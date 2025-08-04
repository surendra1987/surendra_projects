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

namespace core_ai\feature\generative_prompt;

class request extends \core_ai\feature\request {

    private array $prompts;

    public function __construct(array $prompts) {
        foreach ($prompts as $prompt) {
            if (!$prompt instanceof prompt) {
                throw new \coding_exception("only prompts allowed");
            }
        }
        $this->prompts = $prompts;
    }

    public function get_data(): array {
        return $this->prompts;
    }

    public function jsonSerialize(): array {
        return $this->prompts;
    }
}
