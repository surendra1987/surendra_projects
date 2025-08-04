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
use totara_mobile\completion\completion_helper;

class totara_mobile_completion_helper_test extends testcase {
    /**
     * @return void
     */
    public function test_get_completion_value(): void {
        self::setAdminUser();
        $gen = self::getDataGenerator();
        $course = $gen->create_course(['enablecompletion' => 1]);
        $page = $gen->create_module('page', ['course' => $course->id, 'completion' => COMPLETION_TRACKING_MANUAL]);
        $cm_info = cm_info::create((object)array('id' => $page->cmid, 'course' => $course->id));
        self::assertEquals('tracking_manual', completion_helper::get_completion_value($cm_info));

        $page = $gen->create_module('page', ['course' => $course->id, 'completion' => COMPLETION_TRACKING_AUTOMATIC]);
        $cm_info = cm_info::create((object)array('id' => $page->cmid, 'course' => $course->id));
        self::assertEquals('tracking_automatic', completion_helper::get_completion_value($cm_info));
    }

    /**
     * @return void
     */
    public function test_get_completionstatus_value(): void {
        self::setAdminUser();
        $gen = self::getDataGenerator();
        $course = $gen->create_course(['enablecompletion' => 1]);
        $page = $gen->create_module('page', ['course' => $course->id, 'completion' => COMPLETION_TRACKING_MANUAL]);
        $cm_info = cm_info::create((object)array('id' => $page->cmid, 'course' => $course->id));
        self::assertEquals('incomplete', completion_helper::get_completionstatus_value($cm_info));
    }

    /**
     * @return void
     */
    public function test_get_completionenabled(): void {
        self::setAdminUser();
        $gen = self::getDataGenerator();
        $course = $gen->create_course(['enablecompletion' => 1]);
        $page = $gen->create_module('page', ['course' => $course->id]);
        $cm_info = cm_info::create((object)array('id' => $page->cmid, 'course' => $course->id));
        self::assertFalse(completion_helper::get_completionenabled($cm_info));

        $page = $gen->create_module('page', ['course' => $course->id, 'completion' => COMPLETION_TRACKING_MANUAL]);
        $cm_info = cm_info::create((object)array('id' => $page->cmid, 'course' => $course->id));
        self::assertTrue(completion_helper::get_completionenabled($cm_info));
    }
}