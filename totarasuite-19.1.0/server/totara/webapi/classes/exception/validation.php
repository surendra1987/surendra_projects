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
 * @author Stefenie Pickston <stefenie.pickston@totara.com>
 * @package totara_webapi
 */

namespace totara_webapi\exception;

use Exception;
use totara_webapi\client_aware_exception;

/**
 * Used for validation errors.
 */
class validation extends Exception {

    /**
     * Makes a client_aware_exception.
     * @param string $user_facing_error_message
     * @return client_aware_exception
     */
    public static function make(string $user_facing_error_message): client_aware_exception {
        $exception = new self($user_facing_error_message);

        return new client_aware_exception(
            $exception,
            [
                'category' => 'validation',
            ]
        );
    }
}
