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
 * Contract for SAML communication logger.
 */
interface contract {
    const STATUS_INCOMPLETE = 0;
    const STATUS_ERROR = 1;
    const STATUS_SUCCESS = 2;

    const TYPE_LOGIN = 'Login';

    const TYPE_IDP_LOGIN = 'IdPLogin';

    const TYPE_LOGOUT = 'Logout';

    const TYPE_IDP_LOGOUT = 'IdPLogout';

    /**
     * Log a message that Totara has initiated to the IdP.
     *
     * @param string $request_id
     * @param string $type
     * @param string $content
     * @param int|null $user_id
     * @return int|null
     */
    public function log_request(string $request_id, string $type, string $content, ?int $user_id = null): int;

    /**
     * Log a SAML Response. Either creating a new log record or associating the response to an existing log using the log_info
     *
     * @param string $type
     * @param string $content
     * @param int $status
     * @param log_context|null $log_info
     * @return int
     */
    public function log_response(string $type, string $content, int $status, ?log_context $log_info = null): int;


    /**
     * Update the log with the specified user.
     *
     * @param int $log_id
     * @param int|null $user_id
     * @return void
     */
    public function update_log_entry_user_id(int $log_id, ?int $user_id): void;

    /**
     * Log an error against the provided log ID.
     *
     * @param int $log_id
     * @param string $error
     * @return void
     */
    public function log_error(int $log_id, string $error): void;

    /**
     * Log a notice against the provided log ID.
     *
     * @param int $log_id
     * @param string $notice
     * @return void
     */
    public function log_notice(int $log_id, string $notice): void;

    /**
     * Clear all log entries.
     *
     * @return void
     */
    public function clear(): void;
}
