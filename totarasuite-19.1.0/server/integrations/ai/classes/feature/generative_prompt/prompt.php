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

use coding_exception;
use core_ai\data_transfer_object_base;

/**
 * A generative_prompt prompt consists of a role and a message.
 */
class prompt extends data_transfer_object_base {

    public const SYSTEM_ROLE = 'system';

    public const USER_ROLE = 'user';

    public const ASSISTANT_ROLE = 'assistant';

    private string $role;

    private string $message;

    public function __construct(string $message, string $role = self::USER_ROLE) {
        if (!in_array($role, [prompt::USER_ROLE, prompt::ASSISTANT_ROLE, prompt::SYSTEM_ROLE])) {
            throw new coding_exception("Invalid role provided");
        }
        $this->message = clean_string($message);
        $this->role = $role;
    }

    public function get_message(): string {
        return $this->message;
    }

    public function get_role(): string {
        return $this->role;
    }

    public function to_array(): array {
        return [
            'message' => $this->message,
            'role' => $this->role,
        ];
    }
}
