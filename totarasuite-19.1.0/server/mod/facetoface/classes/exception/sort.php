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
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package mod_facetoface
 */

namespace mod_facetoface\exception;

use Exception;
use totara_webapi\client_aware_exception;

/**
 * Thrown when encountering a sort error.
 */
class sort extends Exception {

    /**
     * Create a client aware exception (client-facing) based on this exception
     * @param string $public_facing_message The message to show. This will be visible to consumers of the API.
     * @return client_aware_exception The exception to throw.
     */
    public static function make_client_aware(string $public_facing_message): client_aware_exception {
        $error = new static($public_facing_message);
        return new client_aware_exception(
            $error,
            [
                'category' => 'sort',
            ]
        );
    }
}
