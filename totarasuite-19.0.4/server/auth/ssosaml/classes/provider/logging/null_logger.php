<?php
/**
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
 * @author Simon Chester <simon.chester@totara.com>
 * @package auth_ssosaml
 */

namespace auth_ssosaml\provider\logging;

/**
 * Logger that just ignores all messages.
 */
class null_logger implements contract {
    /**
     * @inheritDoc
     */
    public function log_request(string $request_id, string $type, string $content, ?int $user_id = null): int {
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function log_response(string $type, string $content, int $status, ?log_context $log_info = null): int {
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function clear(): void {
    }

    /**
     * @inheritDoc
     */
    public function update_log_entry_user_id(int $log_id, ?int $user_id): void {
    }

    /**
     * @inheritDoc
     */
    public function log_error(int $log_id, string $error): void {
    }

    /**
     * @inheritDoc
     */
    public function log_notice(int $log_id, string $notice): void {
    }
}
