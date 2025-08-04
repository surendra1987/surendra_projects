<?php
/**
 * This file is part of Totara Core
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package format_pathway
 */

global $CFG;

require_once($CFG->dirroot . '/course/format/lib.php');
require_once($CFG->dirroot.'/course/format/pathway/lib.php');

use core_phpunit\testcase;

/**
 * @group format_pathway
 */
class format_pathway_lib_test extends testcase {

    public function test_format_pathway_page_set_cm(): void {
        // Create some stuff.
        $user = self::getDataGenerator()->create_user();
        $course = self::getDataGenerator()->create_course(['format' => 'pathway']);
        $valid_module = self::getDataGenerator()->create_module('choice', array('course' => $course));
        $valid_cm = get_coursemodule_from_id(null, $valid_module->cmid);
        $blacklisted_module = self::getDataGenerator()->create_module('book', array('course' => $course));
        $blacklisted_cm = get_coursemodule_from_id(null, $blacklisted_module->cmid);

        // Set up the page object, set the course, and make the cm accessible.
        $page = new moodle_page();
        $reflection = new ReflectionClass($page);
        $page_course = $reflection->getProperty('_course');
        $page_course->setAccessible(true);
        $page_course->setValue($page, $course);
        $page_cm = $reflection->getProperty('_cm');
        $page_cm->setAccessible(true);

        // Setting a blacklisted cm as admin does NOT cause a redirect.
        self::setAdminUser();
        $page_cm->setValue($page, $blacklisted_cm);
        course_get_format($course)->page_set_cm($page);

        // Setting a non-blacklisted cm does not cause a redirect.
        $page_cm->setValue($page, $valid_cm);
        course_get_format($course)->page_set_cm($page);
        self::setUser($user);
        $page_cm->setValue($page, $valid_cm);
        course_get_format($course)->page_set_cm($page);

        // Setting a blacklisted cm as non-admin causes a redirect, which we detect.
        $page_cm->setValue($page, $blacklisted_cm);
        self::expectException(moodle_exception::class);
        self::expectExceptionMessage('Unsupported redirect detected, script execution terminated');
        course_get_format($course)->page_set_cm($page);
    }

    /**
     * @return void
     */
    public function test_course_format_options(): void {
        $course = self::getDataGenerator()->create_course(['format' => 'pathway']);
        $format_pathway = format_pathway::instance($course->id);

        $options = $format_pathway->course_format_options();
        self::assertEquals(1, count($options));
        self::assertEquals('numsections', key($options));
    }

    /**
     * @return void
     */
    public function test_get_course(): void {
        $course = self::getDataGenerator()->create_course(['format' => 'pathway']);
        $format_pathway = format_pathway::instance($course->id);

        $course = $format_pathway->get_course();
        self::assertNotEmpty($course->numsections);
    }
}