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

use coding_exception;
use core_ai\data_transfer_object_base;

class prompt extends data_transfer_object_base {

    public const SYSTEM_ROLE = 'system';

    public const USER_ROLE = 'user';

    public const ASSISTANT_ROLE = 'assistant';

    private string $role;

    private string $message;

    /**
     * @var array|null Optional structured content (e.g., for file data)
     */
    private ?array $content = null;

    /**
     * @param string $message The text message
     * @param string $role The role (system, user, or assistant)
     * @param array|null $content Optional structured content
     * @throws coding_exception
     */
    public function __construct(string $message = '', string $role = self::USER_ROLE, ?array $content = null) {
        if (!in_array($role, [prompt::USER_ROLE, prompt::ASSISTANT_ROLE, prompt::SYSTEM_ROLE])) {
            throw new coding_exception("Invalid role provided");
        }
        $this->message = clean_string($message);
        $this->role = $role;
        $this->content = $content;
    }

    public function get_message(): string {
        return $this->message;
    }

    public function get_role(): string {
        return $this->role;
    }

    /**
     * Get the structured content if available
     *
     * @return array|null The structured content or null if not set
     */
    public function get_content(): array|string {
        if ($this->has_content()) {
            return $this->content;
        }
        return $this->message;
    }

    /**
     * Check if this prompt has structured content
     *
     * @return bool True if structured content is present
     */
    public function has_content(): bool {
        return $this->content !== null;
    }

    /**
     * @inheritDoc
     */
    public function to_array(): array {
        $data = [
            'role' => $this->role,
        ];

        // If we have structured content, use that as the content field
        if ($this->has_content()) {
            // For OpenAI's format, content can be either a string or an array of content parts
            if (is_array($this->content) && isset($this->content[0]) && is_array($this->content[0])) {
                // If content is an array of content parts, use it directly
                $data['content'] = $this->content;
            } else {
                // If content is a single object, wrap it in an array
                $data['content'] = [$this->content];
            }
        } else {
            // For simple text messages, use the message field
            $data['content'] = $this->message;
        }

        return $data;
    }

}
