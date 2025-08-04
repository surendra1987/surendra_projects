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
use core\entity\course_module;
use core_course\usagedata\modules_created_per_month;
use core_phpunit\testcase;
use tool_usagedata\helper\time;

class core_course_usagedata_modules_created_per_month_test extends testcase {
    /**
     * @throws coding_exception
     */
    public function test_export() {
        self::setAdminUser();

        $course = new stdClass();
        $course->category = 1;
        $course = course_helper::create_course($course);

        $timestamps = time::get_timestamps_for_past_months();
        $timestamp_keys = array_keys($timestamps);

        // Generate our modules
        // Forum 1
        $cm_id = ($this->getDataGenerator()->create_module('forum', ['course' => $course->id]))->cmid;
        $module_entity = new course_module($cm_id);
        $module_entity->added = $timestamps[$timestamp_keys[2]]['start'] + 1000;
        $module_entity->save();
        $cm_id = ($this->getDataGenerator()->create_module('forum', ['course' => $course->id]))->cmid;
        $module_entity = new course_module($cm_id);
        $module_entity->added = $timestamps[$timestamp_keys[2]]['start'] + 1000;
        $module_entity->save();

        // Quiz 1
        $cm_id = ($this->getDataGenerator()->create_module('quiz', ['course' => $course->id]))->cmid;
        $module_entity = new course_module($cm_id);
        $module_entity->added = $timestamps[$timestamp_keys[5]]['start'] + 1000;
        $module_entity->save();
        // Quiz 2
        $cm_id = ($this->getDataGenerator()->create_module('quiz', ['course' => $course->id]))->cmid;
        $module_entity = new course_module($cm_id);
        $module_entity->added = $timestamps[$timestamp_keys[1]]['start'] + 1000;
        $module_entity->save();

        // Lesson 1
        $cm_id = ($this->getDataGenerator()->create_module('lesson', ['course' => $course->id]))->cmid;
        $module_entity = new course_module($cm_id);
        $module_entity->added = $timestamps[$timestamp_keys[1]]['start'] + 1000;
        $module_entity->save();


        $results = (new modules_created_per_month())->export();

        $this->assertEquals(1, $results[$timestamp_keys[1]]->quiz);
        $this->assertEquals(1, $results[$timestamp_keys[1]]->lesson);

        $this->assertEquals(2, $results[$timestamp_keys[2]]->forum);

        $this->assertEquals(1, $results[$timestamp_keys[5]]->quiz);
    }
}