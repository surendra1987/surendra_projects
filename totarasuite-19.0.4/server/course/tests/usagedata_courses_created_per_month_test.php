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

use core_phpunit\testcase;
use core_course\usagedata\courses_created_per_month;
use container_course\course_helper;
use tool_usagedata\helper\time;

class core_course_usagedata_courses_created_per_month_test extends testcase {

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_export() {
        $timestamps = time::get_timestamps_for_past_months();
        $timestamp_keys = array_keys($timestamps);

        // Course template
        $course = new stdClass();
        $course->category = 1;

        $course->timecreated = $timestamps[$timestamp_keys[2]]['start'] + 1000;
        course_helper::create_course($course);
        $course->timecreated = $timestamps[$timestamp_keys[2]]['start'] + 1000;
        course_helper::create_course($course);

        $course->timecreated = $timestamps[$timestamp_keys[4]]['start'] + 1000;
        course_helper::create_course($course);
        $course->timecreated = $timestamps[$timestamp_keys[4]]['start'] + 1000;
        course_helper::create_course($course);
        $course->timecreated = $timestamps[$timestamp_keys[4]]['start'] + 1000;
        course_helper::create_course($course);

        $course->timecreated = $timestamps[$timestamp_keys[6]]['start'] + 1000;
        course_helper::create_course($course);

        $results = (new courses_created_per_month())->export();

        $this->assertEquals(2, $results[$timestamp_keys[2]]);
        $this->assertEquals(3, $results[$timestamp_keys[4]]);
        $this->assertEquals(1, $results[$timestamp_keys[6]]);
    }
}