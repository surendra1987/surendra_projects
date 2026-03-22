<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Andrew Watkins <andrew.watkins@pixelfusion.co.nz>
 * @package core_ai
 */

namespace core_ai\feature\generative_prompt_with_file;

use core_ai\feature\request_base;

/**
 * Request class for generative prompt features
 *
 * @package core_ai
 */
class request extends request_base {
    /** @var prompt[] Array of prompts */
    private array $prompts;

    /** @var array|null JSON Schema for structured output */
    private ?array $schema = null;

    /** @var array|null List of tool parameters */
    private ?array $tools = null;

    /**
     * Constructor
     *
     * @param prompt[] $prompts Array of prompts
     * @param array|null $schema Optional JSON Schema for structured output
     * @param array|null $tools Optional list of tool parameters
     * @throws \coding_exception If any prompt is not a valid prompt instance
     */
    public function __construct(array $prompts, ?array $schema = null, ?array $tools = null) {
        foreach ($prompts as $prompt) {
            if (!$prompt instanceof prompt) {
                throw new \coding_exception("Only prompt instances are allowed");
            }
        }
        $this->prompts = $prompts;
        $this->schema = $schema;
        $this->tools = $tools;
    }

    /**
     * Get the JSON Schema for structured output
     *
     * @return array|null The JSON Schema or null if not set
     */
    public function get_schema(): ?array {
        return $this->schema;
    }

    /**
     * Get the list of tool parameters
     *
     * @return array|null The list of tool parameters or null if not set
     */
    public function get_tools(): ?array {
        return $this->tools;
    }

    /**
     * Get prompts
     *
     * @return prompt[]
     */
    public function get_prompts(): array {
        return $this->prompts;
    }

    /**
     * Convert to array
     *
     * @return array The request data including prompts, schema, and tools
     */
    public function to_array(): array {
        $data = [
            'prompts' => $this->get_prompts(),
            'model' => $this->get_model(),
        ];

        if ($this->schema !== null) {
            $data['schema'] = $this->get_schema();
        }

        if ($this->tools !== null) {
            $data['tools'] = $this->get_tools();
        }

        return $data;
    }
}
