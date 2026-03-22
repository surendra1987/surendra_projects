<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package perform_goal
 */

namespace perform_goal\trait;

use perform_goal\model\goal;
use perform_goal\interactor\goal_interactor;
use perform_goal\model\goal_task_type\type as goal_task_type;

trait webapi_task_operation_error {

    /**
     * Formats an error response.
     *
     * @param string $message error message.
     *
     * @return array the formatted error.
     */
    protected static function format_error(string $message): array {
        return ['code' => '', 'message' => $message];
    }

    /**
     * Create false result with an error string
     *
     * @param string $identifier
     * @return array
     */
    protected static function format_result(string $identifier): array {
        return [
            'success' => false,
            'errors' => static::format_error(
                get_string($identifier, 'perform_goal')
            )
        ];
    }
}
