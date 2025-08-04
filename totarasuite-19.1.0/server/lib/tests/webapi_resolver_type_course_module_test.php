<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package core
 */

defined('MOODLE_INTERNAL') || die();

use core\webapi\resolver\type\course_section;
use core_phpunit\testcase;
use core\webapi\execution_context;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * Tests the user type
 */
class core_webapi_resolver_type_course_module_test extends testcase {

    use webapi_phpunit_helper;

    public function test_resolve_modules_hides_hidden_activities(): void {
        // Create a course with some activities. 1 is normal visibility, 2 is visible but inaccessible, 3 is hidden.
        $course = self::getDataGenerator()->create_course();
        $module1 = $this->getDataGenerator()->create_module('forum', array(
            'course' => $course->id,
            'name' => 'no restrictions',
        ));
        $module2 = $this->getDataGenerator()->create_module('forum', array(
            'course' => $course->id,
            'name' => 'visible inaccessible',
            'availability' => '{"op":"&","c":[{"type":"date","d":">=","t":2713528000}],"showc":[true]}'
        ));
        $this->getDataGenerator()->create_module('forum', array(
            'course' => $course->id,
            'name' => 'not visible inaccessible',
            'availability' => '{"op":"&","c":[{"type":"date","d":">=","t":2713528000}],"showc":[false]}'
        ));

        // Enrol a learner in the course.
        $learner = self::getDataGenerator()->create_user();
        self::getDataGenerator()->enrol_user($learner->id, $course->id);
        self::setUser($learner);

        // Run the function.
        $section = self::getDataGenerator()->create_course_section(['course' => $course, 'section' => 0]);
        $execution_context = execution_context::create("dev");
        $result_modules = course_section::resolve('modules', $section, [], $execution_context);

        // Check the result contains only the two courses that are visible.
        self:: assertCount(2, $result_modules);
        foreach ($result_modules as $result_module) {
            self::assertContains($result_module->name, [$module1->name, $module2->name]);
        }
    }
}