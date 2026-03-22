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
use format_pathway\webapi\resolver\type\course_module;
use totara_webapi\graphql;

/**
 * @group format_pathway
 */
class format_pathway_webapi_type_course_module_test extends testcase {

    public function test_resolve_blacklisted(): void {
        $course = self::getDataGenerator()->create_course(['format' => 'pathway']);

        $blacklisted_module = self::getDataGenerator()->create_module('book', array('course' => $course));
        $blacklisted_cm = get_coursemodule_from_id(null, $blacklisted_module->cmid);
        $blacklisted_cm_info = cm_info::create($blacklisted_cm);

        $valid_module = self::getDataGenerator()->create_module('choice', array('course' => $course));
        $valid_cm = get_coursemodule_from_id(null, $valid_module->cmid);
        $valid_cm_info = cm_info::create($valid_cm);

        $execution_context = execution_context::create(graphql::TYPE_AJAX);

        self::assertTrue(course_module::resolve('blacklisted', $blacklisted_cm_info, [], $execution_context));
        self::assertFalse(course_module::resolve('blacklisted', $valid_cm_info, [], $execution_context));
    }

    public function test_resolve_viewurl(): void {
        self::setAdminUser();

        $course = self::getDataGenerator()->create_course(['format' => 'pathway']);

        $normal_module = self::getDataGenerator()->create_module('book', array('course' => $course));
        $normal_cm = get_coursemodule_from_id(null, $normal_module->cmid);
        $normal_cm_info = cm_info::create($normal_cm);

        $label_module = self::getDataGenerator()->create_module('label', array('course' => $course));
        $label_cm = get_coursemodule_from_id(null, $label_module->cmid);
        $label_cm_info = cm_info::create($label_cm);

        $execution_context = execution_context::create(graphql::TYPE_AJAX);

        self::assertEquals(
            $normal_cm_info->get_url(),
            course_module::resolve('viewurl', $normal_cm_info, [], $execution_context)
        );
        self::assertEquals(
            new moodle_url('/course/modedit.php', ['update' => $label_cm_info->id]),
            course_module::resolve('viewurl', $label_cm_info, [], $execution_context)
        );
    }

    public function test_resolve_availablereason(): void {
        self::setAdminUser();
        $gen = self::getDataGenerator();
        $course = $gen->create_course(['format' => 'pathway']);
        $url = $gen->create_module('url',
            ['course' => $course],
            ['availability' => '{"op":"&","c":[{"type":"date","d":">=","t":2713528000}],"showc":[false]}']
        );

        $cm_info = cm_info::create(get_coursemodule_from_id(null, $url->cmid));

        $execution_context = execution_context::create(graphql::TYPE_AJAX);
        $result = course_module::resolve('availablereason', $cm_info, [], $execution_context);
        self::assertEquals('Available from <strong>27 December 2055, 9:46 PM</strong> (hidden otherwise)', $result[0]);

        $user = $gen->create_user();
        self::setUser($user);
        $result = course_module::resolve('availablereason', $cm_info, [], $execution_context);
        self::assertEmpty($result);
    }
}