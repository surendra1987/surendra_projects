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
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package totara_webhook
 */

namespace totara_webhook\handler;

use totara_webhook\exception\insufficient_webhook_handler_exception;
use totara_webhook\handler\default_handler\default_handler;

class totara_webhook_handler_factory {
    /**
     * Private constructor - create_instance should be the only function called here
     */
    private function __construct() {}

    /**
     * @param string|null $class
     * @return totara_webhook_handler
     */
    public static function create_instance(?string $class = null): totara_webhook_handler {
        if ($class === null) {
            return new default_handler();
        }
        $class_exists = class_exists($class);
        if (!$class_exists) {
            throw new insufficient_webhook_handler_exception();
        }
        $implements_totara_webhook_handler = in_array(totara_webhook_handler::class, class_implements($class));
        if (!$implements_totara_webhook_handler) {
            throw new insufficient_webhook_handler_exception();
        }

        return new $class();
    }
}
