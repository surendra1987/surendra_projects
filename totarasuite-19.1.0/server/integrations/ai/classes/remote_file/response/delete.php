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
 * Response class for deleting a file from the AI provider
 */
class delete extends remote_file_response_base {
    /** @var string File ID */
    protected string $id = '';

    /** @var bool Whether deletion was successful */
    protected bool $deleted = false;

    /**
     * Set file ID
     *
     * @param string $id
     * @return self
     */
    public function set_id(string $id): self {
        $this->id = $id;
        return $this;
    }

    /**
     * Get file ID
     *
     * @return string
     */
    public function get_id(): string {
        return $this->id;
    }

    /**
     * Set deleted status
     *
     * @param bool $deleted
     * @return self
     */
    public function set_deleted(bool $deleted): self {
        $this->deleted = $deleted;
        return $this;
    }

    /**
     * Check if file was deleted
     *
     * @return bool
     */
    public function is_deleted(): bool {
        return $this->deleted;
    }

    /**
     * Get data for API response
     *
     * @return array
     */
    public function to_array(): array {
        return [
            'id' => $this->id,
            'deleted' => $this->deleted
        ];
    }
}
