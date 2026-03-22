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
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package core_course
 */

use container_course\course_helper;
use core_phpunit\testcase;
use core_course\usagedata\modules_count;

class core_course_usagedata_modules_count_test extends testcase {

    public function test_export() {
        self::setAdminUser();

        $course = new stdClass();
        $course->category = 1;
        $course = course_helper::create_course($course);

        // Generate our modules
        $this->getDataGenerator()->create_module('forum', ['course' => $course->id]);

        $this->getDataGenerator()->create_module('quiz', ['course' => $course->id]);
        $this->getDataGenerator()->create_module('quiz', ['course' => $course->id]);
        $this->getDataGenerator()->create_module('quiz', ['course' => $course->id]);

        $this->getDataGenerator()->create_module('lesson', ['course' => $course->id]);

        $results = (new modules_count())->export();

        $this->assertEquals(2, $results['forum']);
        $this->assertEquals(3, $results['quiz']);
        $this->assertEquals(1, $results['lesson']);
    }
}