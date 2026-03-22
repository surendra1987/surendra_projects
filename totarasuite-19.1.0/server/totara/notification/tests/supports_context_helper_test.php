<?php
/**
 * This file is part of Totara Learn
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
 * @package totara_notification
 */

use core\orm\query\builder;
use core_phpunit\testcase;
use mod_label\testing\generator as label_generator;
use mod_perform\testing\generator as perform_generator;
use totara_core\extended_context;
use totara_core\identifier\component_area;
use totara_notification\supports_context_helper;
use totara_program\testing\generator as program_generator;

/**
 * @group totara_notification
 */
class totara_notification_supports_context_helper_test extends testcase {

    public function test_supports_extended_component_area(): void {
        $natural_context = extended_context::make_system();
        $extended_context = extended_context::make_with_context(
            context_system::instance(),
            'test_component',
            'test_area',
            123
        );

        // The supported context is a natural context.
        self::assertTrue(supports_context_helper::supports_extended_component_area(
            $natural_context,
            null
        ));
        self::assertFalse(supports_context_helper::supports_extended_component_area(
            $extended_context,
            null
        ));

        // The supported context is an extended context.
        self::assertTrue(supports_context_helper::supports_extended_component_area(
            $natural_context,
            new component_area('test_component', 'test_area')
        ));
        self::assertTrue(supports_context_helper::supports_extended_component_area(
            $extended_context,
            new component_area('test_component', 'test_area')
        ));

        // The supported context has a different component.
        self::assertTrue(supports_context_helper::supports_extended_component_area(
            $natural_context,
            new component_area('test_component', 'test_area')
        ));
        self::assertFalse(supports_context_helper::supports_extended_component_area(
            $extended_context,
            new component_area('other_component', 'test_area')
        ));

        // The supported context has a different area.
        self::assertTrue(supports_context_helper::supports_extended_component_area(
            $natural_context,
            new component_area('test_component', 'test_area')
        ));
        self::assertFalse(supports_context_helper::supports_extended_component_area(
            $extended_context,
            new component_area('test_component', 'other_area')
        ));
    }

    public function test_supports_context_level(): void {
        $system_context = extended_context::make_system();
        $category = self::getDataGenerator()->create_category();
        $category_context = extended_context::make_with_context($category->get_context());
        $program = program_generator::instance()->create_program();
        $program_context = extended_context::make_with_context($program->get_context());
        $course = self::getDataGenerator()->create_course();
        $course_context = extended_context::make_with_context(context_course::instance($course->id));
        $module = label_generator::instance()->create_instance(['course' => $course]);
        $module_context = extended_context::make_with_context(context_module::instance($module->cmid));

        // Supports system context only.
        self::assertTrue(supports_context_helper::supports_context_level(
            $system_context,
            CONTEXT_SYSTEM
        ));
        self::assertFalse(supports_context_helper::supports_context_level(
            $category_context,
            CONTEXT_SYSTEM
        ));
        self::assertFalse(supports_context_helper::supports_context_level(
            $program_context,
            CONTEXT_SYSTEM
        ));
        self::assertFalse(supports_context_helper::supports_context_level(
            $course_context,
            CONTEXT_SYSTEM
        ));
        self::assertFalse(supports_context_helper::supports_context_level(
            $module_context,
            CONTEXT_SYSTEM
        ));

        // Supports category context and above.
        self::assertTrue(supports_context_helper::supports_context_level(
            $system_context,
            CONTEXT_COURSECAT
        ));
        self::assertTrue(supports_context_helper::supports_context_level(
            $category_context,
            CONTEXT_COURSECAT
        ));
        self::assertFalse(supports_context_helper::supports_context_level(
            $program_context,
            CONTEXT_COURSECAT
        ));
        self::assertFalse(supports_context_helper::supports_context_level(
            $course_context,
            CONTEXT_COURSECAT
        ));
        self::assertFalse(supports_context_helper::supports_context_level(
            $module_context,
            CONTEXT_COURSECAT
        ));

        // Supports program context and above.
        self::assertTrue(supports_context_helper::supports_context_level(
            $system_context,
            CONTEXT_PROGRAM
        ));
        self::assertTrue(supports_context_helper::supports_context_level(
            $category_context,
            CONTEXT_PROGRAM
        ));
        self::assertTrue(supports_context_helper::supports_context_level(
            $program_context,
            CONTEXT_PROGRAM
        ));
        self::assertFalse(supports_context_helper::supports_context_level(
            $course_context,
            CONTEXT_PROGRAM
        ));
        self::assertFalse(supports_context_helper::supports_context_level(
            $module_context,
            CONTEXT_PROGRAM
        ));

        // Supports course context and above.
        self::assertTrue(supports_context_helper::supports_context_level(
            $system_context,
            CONTEXT_COURSE
        ));
        self::assertTrue(supports_context_helper::supports_context_level(
            $category_context,
            CONTEXT_COURSE
        ));
        self::assertFalse(supports_context_helper::supports_context_level(
            $program_context,
            CONTEXT_COURSE
        ));
        self::assertTrue(supports_context_helper::supports_context_level(
            $course_context,
            CONTEXT_COURSE
        ));
        self::assertFalse(supports_context_helper::supports_context_level(
            $module_context,
            CONTEXT_COURSE
        ));

        // Supports course context and above.
        self::assertTrue(supports_context_helper::supports_context_level(
            $system_context,
            CONTEXT_MODULE
        ));
        self::assertTrue(supports_context_helper::supports_context_level(
            $category_context,
            CONTEXT_MODULE
        ));
        self::assertFalse(supports_context_helper::supports_context_level(
            $program_context,
            CONTEXT_MODULE
        ));
        self::assertTrue(supports_context_helper::supports_context_level(
            $course_context,
            CONTEXT_MODULE
        ));
        self::assertTrue(supports_context_helper::supports_context_level(
            $module_context,
            CONTEXT_MODULE
        ));
    }

    public static function test_supports_coursecat_context(): void {
        $category = self::getDataGenerator()->create_category();
        $course_category_context = extended_context::make_with_context($category->get_context());
        $perform_category = builder::table('course_categories')
            ->select('id')
            ->where_like('idnumber', 'container_perform')
            ->order_by('id')
            ->first();
        $perform_category_context = extended_context::make_with_context(context_coursecat::instance($perform_category->id));

        // Supports course container.
        self::assertTrue(supports_context_helper::supports_coursecat_context(
            $course_category_context,
            'container_course'
        ));
        self::assertFalse(supports_context_helper::supports_coursecat_context(
            $perform_category_context,
            'container_course'
        ));

        // Supports perform container.
        self::assertFalse(supports_context_helper::supports_coursecat_context(
            $course_category_context,
            'container_perform'
        ));
        self::assertTrue(supports_context_helper::supports_coursecat_context(
            $perform_category_context,
            'container_perform'
        ));

        // Supports other container.
        self::assertFalse(supports_context_helper::supports_coursecat_context(
            $course_category_context,
            'container_other'
        ));
        self::assertFalse(supports_context_helper::supports_coursecat_context(
            $perform_category_context,
            'container_other'
        ));
    }

    public static function test_supports_course_context(): void {
        $course = self::getDataGenerator()->create_course();
        $course_context = extended_context::make_with_context(context_course::instance($course->id));

        // Supports course container.
        self::assertTrue(supports_context_helper::supports_course_context(
            $course_context,
            'container_course'
        ));

        // Supports other container.
        self::assertFalse(supports_context_helper::supports_course_context(
            $course_context,
            'container_other'
        ));
    }

    public static function test_supports_module_context(): void {
        self::setAdminUser();
        $course = self::getDataGenerator()->create_course();
        $course_module = label_generator::instance()->create_instance(['course' => $course]);
        $course_module_context = extended_context::make_with_context(context_module::instance($course_module->cmid));
        $perform_module = perform_generator::instance()->create_activity_in_container();
        $perform_module_context = extended_context::make_with_context($perform_module->get_context());

        // Supports course container and module.
        self::assertTrue(supports_context_helper::supports_module_context(
            $course_module_context,
            'container_course',
            'label'
        ));
        self::assertFalse(supports_context_helper::supports_module_context(
            $perform_module_context,
            'container_course',
            'label'
        ));

        // Supports perform container and module.
        self::assertFalse(supports_context_helper::supports_module_context(
            $course_module_context,
            'container_perform',
            'perform'
        ));
        self::assertTrue(supports_context_helper::supports_module_context(
            $perform_module_context,
            'container_perform',
            'perform'
        ));

        // Supports other container and module.
        self::assertFalse(supports_context_helper::supports_module_context(
            $course_module_context,
            'container_other',
            'other'
        ));
        self::assertFalse(supports_context_helper::supports_module_context(
            $perform_module_context,
            'container_other',
            'other'
        ));

        // Supports course container and module.
        self::assertTrue(supports_context_helper::supports_module_context(
            $course_module_context,
            'container_course',
            'all_container_modules'
        ));
        self::assertFalse(supports_context_helper::supports_module_context(
            $perform_module_context,
            'container_course',
            'all_container_modules'
        ));

        // Supports perform container and module.
        // Weird! While it might seem that this should be false, module_supported::get_for_container returns true
        // when asked if normal modules (e.g. label, quiz) are supported in container_perform. In the real world,
        // non-perform modules are never actually used in perform containers, but if they did then module notifications
        // would work in that context!
        self::assertTrue(supports_context_helper::supports_module_context(
            $course_module_context,
            'container_perform',
            'all_container_modules'
        ));
        self::assertTrue(supports_context_helper::supports_module_context(
            $perform_module_context,
            'container_perform',
            'all_container_modules'
        ));
    }
}