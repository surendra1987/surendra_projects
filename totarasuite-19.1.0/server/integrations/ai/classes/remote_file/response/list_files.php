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

namespace core_ai\remote_file\response;

/**
 * Response class for listing files from the AI provider
 */
class list_files extends remote_file_response_base {
    /** @var array Array of file objects */
    protected array $files = [];

    /**
     * Set list of files returned from the API
     *
     * @param array $files
     * @return self
     */
    public function set_files(array $files): self {
        $this->files = $files;
        return $this;
    }

    /**
     * Get list of files
     *
     * @return array
     */
    public function get_files(): array {
        return $this->files;
    }

    /**
     * Get data for API response
     *
     * @return array
     */
    public function to_array(): array {
        return [
            'files' => $this->files
        ];
    }
}
