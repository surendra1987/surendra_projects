<?php
/**
 * This file is part of Totara Core
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package totara_customfield
 */

use core\webapi\execution_context;
use core_phpunit\testcase;
use totara_customfield\testing\generator;
use totara_customfield\webapi\resolver\type\field;
use totara_webapi\phpunit\webapi_phpunit_helper;

defined('MOODLE_INTERNAL') || die();

class totara_customfield_webapi_resolver_type_custom_field_test extends testcase {
    use webapi_phpunit_helper;

    /**
     * @return void
     * @throws coding_exception|dml_exception
     */
    public function test_resolve_core_custom_field_value(): void {
        self::setAdminUser();
        $gen = self::getDataGenerator();
        $time = time();
        $course1 = $gen->create_course(['timecreated' => $time + 1, 'timemodified' => $time + 10]);
        $course2 = $gen->create_course(['timecreated' => $time + 1, 'timemodified' => $time + 10]);
        $course3 = $gen->create_course(['timecreated' => $time + 1, 'timemodified' => $time + 10]);
        $course4 = $gen->create_course(['timecreated' => $time + 1, 'timemodified' => $time + 10]);

        /** @var generator $custom_field_generator */
        $custom_field_generator = $this->getDataGenerator()->get_plugin_generator('totara_customfield');
        $custom_fields = $custom_field_generator->create_text('course', ['text_one', 'text_two']);
        $custom_field_generator->set_text($course1, $custom_fields['text_one'], 'course_1_text', 'course', 'course');
        $custom_field_generator->set_text($course2, $custom_fields['text_two'], 'course_2_text', 'course', 'course');
        $custom_field_generator->set_text($course3, $custom_fields['text_one'], 'course_3_text', 'course', 'course');
        $custom_field_generator->set_text($course3, $custom_fields['text_two'], 'course_3_text', 'course', 'course');

        $execution_context = execution_context::create('dev');

        $custom_field_data = customfield_get_custom_fields_and_data_for_items(
            'course',
            [
                $course1->id,
                $course2->id,
                $course3->id,
                $course4->id,
            ]
        );

        // course1
        $course_1_custom_fields_data = $custom_field_data[$course1->id];
        $null_found = 0;
        $overridden_value = null;
        $overridden_raw_value = null;
        $execution_context->set_variable('context', context_course::instance($course1->id));
        foreach ($course_1_custom_fields_data as $course_custom_field) {
            $course_1_custom_field_value = field::resolve(
                'value',
                $course_custom_field,
                [],
                $execution_context
            );
            $course_1_custom_field_raw_value = field::resolve(
                'raw_value',
                $course_custom_field,
                [],
                $execution_context
            );
            if ($course_1_custom_field_value && $course_1_custom_field_raw_value) {
                $overridden_value = $course_1_custom_field_value;
                $overridden_raw_value = $course_1_custom_field_raw_value;
                continue;
            }

            $null_found++;
        }

        $this->assertSame(1, $null_found);
        $this->assertSame('course_1_text', $overridden_value);
        $this->assertSame('course_1_text', $overridden_raw_value);

        // course2
        $course_2_custom_fields_data = $custom_field_data[$course2->id];
        $null_found = 0;
        $overridden_value = null;
        $overridden_raw_value = null;
        $execution_context->set_variable('context', context_course::instance($course2->id));
        foreach ($course_2_custom_fields_data as $course_custom_field) {
            $course_2_custom_field_value = field::resolve(
                'value',
                $course_custom_field,
                [],
                $execution_context
            );
            if ($course_2_custom_field_value) {
                $overridden_value = $course_2_custom_field_value;
                continue;
            }
            $null_found++;
        }

        $this->assertSame(1, $null_found);
        $this->assertSame('course_2_text', $overridden_value);

        // course3
        $course_3_custom_fields_data = $custom_field_data[$course3->id];
        $null_found = 0;
        $overridden_value = null;
        $execution_context->set_variable('context', context_course::instance($course3->id));
        foreach ($course_3_custom_fields_data as $course_custom_field) {
            $course_3_custom_field_value = field::resolve(
                'value',
                $course_custom_field,
                [],
                $execution_context
            );
            if ($course_3_custom_field_value) {
                $overridden_value = $course_3_custom_field_value;
                continue;
            }
            $null_found++;
        }

        $this->assertSame(0, $null_found);
        $this->assertSame('course_3_text', $overridden_value);

        // course4
        $course_4_custom_fields_data = $custom_field_data[$course4->id];
        $null_found = 0;
        $overridden_value = null;
        $execution_context->set_variable('context', context_course::instance($course4->id));
        foreach ($course_4_custom_fields_data as $course_custom_field) {
            $course_4_custom_field_value = field::resolve(
                'value',
                $course_custom_field,
                [],
                $execution_context
            );
            if ($course_4_custom_field_value) {
                $overridden_value = $course_4_custom_field_value;
                continue;
            }
            $null_found++;
        }

        $this->assertSame(2, $null_found);
        $this->assertNull($overridden_value);
    }

    /**
     * @return void
     * @throws coding_exception|dml_exception
     */
    public function test_resolve_core_custom_field_field(): void {
        self::setAdminUser();
        $gen = self::getDataGenerator();
        $time = time();
        $course = $gen->create_course(['timecreated' => $time + 1, 'timemodified' => $time + 10]);

        /** @var generator $custom_field_generator */
        $custom_field_generator = $this->getDataGenerator()->get_plugin_generator('totara_customfield');
        $custom_fields = $custom_field_generator->create_text('course', ['text_one', 'text_two']);
        $custom_field_generator->set_text($course, $custom_fields['text_one'], 'course_text', 'course', 'course');

        $execution_context = execution_context::create('dev');

        $custom_field_data = customfield_get_custom_fields_and_data_for_items(
            'course',
            [
                $course->id,
            ]
        );

        $course_custom_field_data = $custom_field_data[$course->id];
        $execution_context->set_variable('context', context_course::instance($course->id));
        foreach ($course_custom_field_data as $course_custom_field_datum) {
            $course_custom_field_value = field::resolve(
                'definition',
                $course_custom_field_datum,
                [],
                $execution_context
            );
            $this->assertEqualsCanonicalizing($course_custom_field_datum['definition'], $course_custom_field_value);
        }
    }

    /**
     * @return void
     * @throws coding_exception
     */
    public function test_resolve__custom_field_with_type_file(): void {
        self::setAdminUser();
        $gen = self::getDataGenerator();
        $time = time();

        // Given I have a course and assign a custom field to it with type FILE
        $course = $gen->create_course(['timecreated' => $time + 1, 'timemodified' => $time + 10]);

        /** @var generator $custom_field_generator */
        $custom_field_generator = $this->getDataGenerator()->get_plugin_generator('totara_customfield');
        $custom_fields = $custom_field_generator->create_file('course', ['my_file' => []]);
        $custom_field_generator->create_test_file_from_content('my_file_name', 'Contents of my file', 666);
        $custom_field_generator->set_file($course, $custom_fields['my_file'], 666, 'course', 'course');

        $custom_field_data = customfield_get_custom_fields_and_data_for_items(
            'course',
            [
                $course->id,
            ]
        );

        $execution_context = execution_context::create('dev');
        $execution_context->set_variable('context', context_course::instance($course->id));

        $course_custom_field_data = $custom_field_data[$course->id];
        foreach ($course_custom_field_data as $course_custom_field_datum) {
            // When I resolve the raw value of the field
            $course_custom_field_value = field::resolve(
                'raw_value',
                $course_custom_field_datum,
                [],
                $execution_context
            );
            // Then I should get null (as file is unsupported)
            $this->assertNull($course_custom_field_value);

            // When I resolve the value of the field
            $course_custom_field_value = field::resolve(
                'value',
                $course_custom_field_datum,
                [],
                $execution_context
            );
            // Then I should get null (as file is unsupported)
            $this->assertNull($course_custom_field_value);
        }
    }
}
