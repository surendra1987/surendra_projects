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
 * Response class for uploading a file to the AI provider
 */
class create extends remote_file_response_base {
    /** @var string File ID */
    protected string $id = '';

    /** @var string Filename */
    protected string $filename = '';

    /** @var string Purpose of the file */
    protected string $purpose = '';

    /** @var int Size of file in bytes */
    protected int $bytes = 0;

    /** @var int Created timestamp */
    protected int $created_at = 0;

    /** @var string Status of upload */
    protected string $status = '';

    /** @var string|null File content for generating prompts */
    protected ?string $file_content = null;

    /** @var callable|null Callback function to generate prompt */
    protected $prompt_callback = null;

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
     * Set status
     *
     * @param string $status
     * @return self
     */
    public function set_status(string $status): self {
        $this->status = $status;
        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function get_status(): string {
        return $this->status;
    }

    /**
     * Set file content for prompt generation
     *
     * @param string|null $content
     * @return self
     */
    public function set_file_content(?string $content): self {
        $this->file_content = $content;
        return $this;
    }

    /**
     * Get file content
     *
     * @return string|null
     */
    public function get_file_content(): ?string {
        return $this->file_content;
    }

    /**
     * Set prompt callback function
     *
     * @param callable $callback Function that takes this response object and returns prompt array
     * @return self
     */
    public function set_prompt_callback(callable $callback): self {
        $this->prompt_callback = $callback;
        return $this;
    }

    /**
     * Generate a prompt item for this file using the callback
     *
     * @return array|null The prompt item array or null if not handled
     */
    public function get_prompt(): ?array {
        // If we have a callback, use it to generate the prompt
        if (is_callable($this->prompt_callback)) {
            return call_user_func($this->prompt_callback, $this);
        }

        // Default implementation for backwards compatibility
        if ($this->file_content !== null) {
            return [
                'type' => 'input_text',
                'text' => "File: {$this->filename}\n\n{$this->file_content}"
            ];
        }

        return null;
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
            'purpose' => $this->purpose,
            'bytes' => $this->bytes,
            'created_at' => $this->created_at,
            'status' => $this->status,
            'file_content' => $this->file_content
        ];
    }
}
