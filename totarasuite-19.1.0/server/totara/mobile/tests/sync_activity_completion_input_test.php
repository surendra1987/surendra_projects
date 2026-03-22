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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package totara_mobile
 */

use core_phpunit\testcase;
use totara_mobile\completion\sync_activity_completion_input;

class totara_mobile_sync_activity_completion_input_test extends testcase {

    public function test_sync_activity_completion_input() {
        $course = self::getDataGenerator()->create_course();
        $mock = [
            'course' => $course,
            'not_property' => 5,
            'input' => [
                'cm_id' => 2
            ]
        ];

        $obj = new sync_activity_completion_input($mock);
        self::assertEquals(2, $obj->get_cm_id());
        self::assertEquals($course->id, $obj->get_course()->id);
        self::assertFalse(property_exists($obj, 'not_property'));
    }
}