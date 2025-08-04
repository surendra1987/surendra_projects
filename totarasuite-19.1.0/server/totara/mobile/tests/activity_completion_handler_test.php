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
 * @author Katherine Galano <katherine.galano@totara.com>
 * @package totara_mobile
 */

use core_phpunit\testcase;
use totara_mobile\completion\activity_completion_handler;

class totara_mobile_activity_completion_handler_test extends testcase {

    /**
     * @return void
     */
    public function test_instance(): void {
        $ref_class = new ReflectionClass(activity_completion_handler::class);
        $method = $ref_class->getMethod('instance');
        $method->setAccessible(true);

        $instance = $method->invoke($ref_class, 'page');
        self::assertInstanceOf(activity_completion_handler::class, $instance);
    }
}
