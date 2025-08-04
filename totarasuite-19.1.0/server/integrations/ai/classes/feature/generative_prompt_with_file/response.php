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

use core_ai\feature\response_base;

/**
 * Response class for generative prompt features
 *
 * @package core_ai
 */
class response extends response_base {
    /** @var prompt[]|array Array of prompts or structured data */
    private $data;

    /** @var string|null Error message if any */
    private ?string $error;

    /**
     * Constructor
     *
     * @param prompt[]|array $data Array of prompts or structured data
     * @param string|null $error Optional error message
     */
    public function __construct($data = [], ?string $error = null) {
        $this->data = $data;
        $this->error = $error ? clean_string($error) : null;
    }

    /**
     * Get the error message if any
     *
     * @return string|null The error message or null if no error
     */
    public function get_error(): ?string {
        return $this->error;
    }

    /**
     * Check if there was an error
     *
     * @return bool True if there was an error
     */
    public function has_error(): bool {
        return $this->error !== null;
    }

    /**
     * Check if the response contains structured data
     *
     * @return bool True if the response contains structured data
     */
    public function is_structured(): bool {
        return !empty($this->data) && !($this->data[0] instanceof prompt);
    }

    /**
     * Convert to array
     *
     * @return array The response data
     */
    public function to_array(): array {
        return $this->data;
    }
}
