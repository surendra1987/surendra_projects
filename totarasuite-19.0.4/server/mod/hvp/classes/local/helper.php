<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package mod_hvp
 */

namespace mod_hvp\local;

class helper {
    /**
     * Call the http_response_code method
     *
     * @param int $response_code
     * @return void
     */
    public static function http_response_code(int $response_code): void {
        if (defined('PHPUNIT_TEST') && constant('PHPUNIT_TEST')) {
            // Do not set a header during a test
            return;
        }

        self::http_response_code($response_code);
    }
}
