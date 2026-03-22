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
 * @author Andrew Watkins <andrew.watkins@pixelfusion.co.nz>
 * @package core_ai
 */

namespace core_ai\remote_file;

use core_ai\data_transfer_object_base;

/**
 * Request class for file operations with AI providers
 */
class remote_file_request extends data_transfer_object_base {

    /** @var string Action to perform (list, retrieve, download, upload, delete) */
    protected string $action = '';

    /** @var string File ID for operations that work on specific files */
    protected string $file_id = '';

    /** @var array Additional parameters for file operations */
    protected array $parameters = [];

    /**
     * Set the action to perform
     *
     * @param string $action
     * @return self
     */
    public function set_action(string $action): self {
        $this->action = $action;
        return $this;
    }

    /**
     * Get the current action
     *
     * @return string
     */
    public function get_action(): string {
        return $this->action;
    }

    /**
     * Set file ID for operations on specific files
     *
     * @param string $file_id
     * @return self
     */
    public function set_file_id(string $file_id): self {
        $this->file_id = $file_id;
        return $this;
    }

    /**
     * Get file ID
     *
     * @return string
     */
    public function get_file_id(): string {
        return $this->file_id;
    }

    /**
     * Set additional parameters
     *
     * @param array $parameters
     * @return self
     */
    public function set_parameters(array $parameters): self {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * Get additional parameters
     *
     * @return array
     */
    public function get_parameters(): array {
        return $this->parameters;
    }

    /**
     * Get a specific parameter
     *
     * @param string $key Parameter key
     * @param mixed $default Default value if parameter doesn't exist
     * @return mixed Parameter value or default
     */
    public function get_parameter(string $key, $default = null) {
        return $this->parameters[$key] ?? $default;
    }

    /**
     * Check if a parameter exists
     *
     * @param string $key Parameter key
     * @return bool
     */
    public function has_parameter(string $key): bool {
        return isset($this->parameters[$key]);
    }

    public function to_array(): array {
        return [
            'action' => $this->action,
            'file_id' => $this->file_id,
            'parameters' => $this->parameters,
        ];
    }
}
