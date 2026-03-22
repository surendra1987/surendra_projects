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
 * @author Nathaniel Walmsley <nathaniel.walmsley@totara.com>
 * @package totara_webhook
 */

namespace totara_webhook\exception;

use totara_webapi\client_aware_exception;

/**
 * An exception for the create_totara_webhook GraphQL mutation
 */
class webhook_creation_exception extends \Exception {

    /**
     * Create an exception that can be passed to the client,
     * so they see more than 'internal server error'
     *
     * @param string $message
     * @return client_aware_exception
     */
    public static function make(string $message): client_aware_exception {
        $error = new static($message);
        return new client_aware_exception($error, ['category' => 'webhook_creation_exception']);
    }
}