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
 * Response class for retrieving file information from the AI provider
 */
class retrieve extends remote_file_response_base {
    /** @var string File ID */
    protected string $id = '';

    /** @var string Filename */
    protected string $filename = '';

    /** @var int Created timestamp */
    protected int $created_at = 0;

    /** @var string Purpose of the file */
    protected string $purpose = '';

    /** @var int Size of file in bytes */
    protected int $bytes = 0;

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
     * Set filename
     *
     * @param string $filename
     * @return self
     */
    public function set_filename(string $filename): self {
        $this->filename = $filename;
        return $this;
    }

    /**
     * Get filename
     *
     * @return string
     */
    public function get_filename(): string {
        return $this->filename;
    }

    /**
     * Set creation timestamp
     *
     * @param int $created_at
     * @return self
     */
    public function set_created_at(int $created_at): self {
        $this->created_at = $created_at;
        return $this;
    }

    /**
     * Get creation timestamp
     *
     * @return int
     */
    public function get_created_at(): int {
        return $this->created_at;
    }

    /**
     * Set purpose
     *
     * @param string $purpose
     * @return self
     */
    public function set_purpose(string $purpose): self {
        $this->purpose = $purpose;
        return $this;
    }

    /**
     * Get purpose
     *
     * @return string
     */
    public function get_purpose(): string {
        return $this->purpose;
    }

    /**
     * Set file size in bytes
     *
     * @param int $bytes
     * @return self
     */
    public function set_bytes(int $bytes): self {
        $this->bytes = $bytes;
        return $this;
    }

    /**
     * Get file size in bytes
     *
     * @return int
     */
    public function get_bytes(): int {
        return $this->bytes;
    }

    /**
     * Get data for API response
     *
     * @return array
     */
    public function to_array(): array {
        return [
            'id' => $this->id,
            'filename' => $this->filename,
            'created_at' => $this->created_at,
            'purpose' => $this->purpose,
            'bytes' => $this->bytes
        ];
    }
}
