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

use core\webapi\execution_context;
use core_phpunit\testcase;
use format_pathway\webapi\resolver\type\course_section;
use totara_webapi\graphql;

/**
 * @group format_pathway
 */
class format_pathway_webapi_resolver_type_course_section_test extends testcase {

    public function test_resolve_modules_blacklisted(): void {
        // Create some stuff.
        $user = self::getDataGenerator()->create_user();
        $course = self::getDataGenerator()->create_course(['format' => 'pathway']);
        $blacklisted_module = self::getDataGenerator()->create_module('book', array('course' => $course));
        $blacklisted_cm = get_coursemodule_from_id(null, $blacklisted_module->cmid);
        $valid_module = self::getDataGenerator()->create_module('choice', array('course' => $course));
        $valid_cm = get_coursemodule_from_id(null, $valid_module->cmid);

        $modinfo = course_modinfo::instance($course->id);
        $section = $modinfo->get_section_info(0);
        $execution_context = execution_context::create(graphql::TYPE_AJAX);

        // Admin sees all activities.
        self::setAdminUser();
        $results = course_section::resolve('modules', $section, [], $execution_context);
        self::assertCount(2, $results);
        $first_result = reset($results);
        self::assertEquals($blacklisted_cm->id, $first_result->id);

        // Non-admin sees only the non-blacklist activity.
        self::setUser($user);
        $results = course_section::resolve('modules', $section, [], $execution_context);
        self::assertCount(1, $results);
        $first_result = reset($results);
        self::assertEquals($valid_cm->id, $first_result->id);
    }
}