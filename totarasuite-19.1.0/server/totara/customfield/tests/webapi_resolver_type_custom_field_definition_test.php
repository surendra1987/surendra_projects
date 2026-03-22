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
use totara_customfield\webapi\resolver\type\definition;
use totara_webapi\phpunit\webapi_phpunit_helper;

defined('MOODLE_INTERNAL') || die();

class totara_customfield_webapi_resolver_type_custom_field_definition_test extends testcase {
    use webapi_phpunit_helper;

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_resolve_core_custom_field_definition_id(): void {
        list($course, $custom_field_data) = $this->create_defaults();
        $execution_context = execution_context::create('dev');
        $course_context = context_course::instance($course->id, IGNORE_MISSING);
        $execution_context->set_variable('context', $course_context);

        $course_custom_field_data = reset($custom_field_data[$course->id]);
        $course_custom_field_value = definition::resolve(
            'id',
            $course_custom_field_data['definition'],
            [],
            $execution_context
        );
        $this->assertSame($course_custom_field_data['definition']['id'], $course_custom_field_value);
    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_resolve_core_custom_field_definition_fullname(): void {
        list($course, $custom_field_data) = $this->create_defaults();
        $execution_context = execution_context::create('dev');
        $course_context = context_course::instance($course->id, IGNORE_MISSING);
        $execution_context->set_variable('context', $course_context);

        $course_custom_field_data = reset($custom_field_data[$course->id]);
        $course_custom_field_value = definition::resolve(
            'fullname',
            $course_custom_field_data['definition'],
            [],
            $execution_context
        );
        $this->assertSame('text_one', $course_custom_field_value);
    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_resolve_core_custom_field_definition_shortname(): void {
        list($course, $custom_field_data) = $this->create_defaults();
        $execution_context = execution_context::create('dev');
        $course_context = context_course::instance($course->id, IGNORE_MISSING);
        $execution_context->set_variable('context', $course_context);

        $course_context = context_course::instance($course->id, IGNORE_MISSING);
        $execution_context->set_variable('context', $course_context);
        $course_custom_field_data = reset($custom_field_data[$course->id]);
        $course_custom_field_value = definition::resolve(
            'shortname',
            $course_custom_field_data['definition'],
            [],
            $execution_context
        );
        $this->assertSame('text_one', $course_custom_field_value);
    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_resolve_core_custom_field_definition_type__known_type(): void {
        list($course, $custom_field_data) = $this->create_defaults();
        $execution_context = execution_context::create('dev');
        $course_context = context_course::instance($course->id, IGNORE_MISSING);
        $execution_context->set_variable('context', $course_context);

        $course_custom_field_data = reset($custom_field_data[$course->id]);
        $this->assertEquals('text', $course_custom_field_data['definition']['type']);

        $course_custom_field_value = definition::resolve(
            'type',
            $course_custom_field_data['definition'],
            [],
            $execution_context
        );
        $this->assertSame('TEXT', $course_custom_field_value);
    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_resolve_core_custom_field_definition_type__unknown_type(): void {
        list($course, $custom_field_data) = $this->create_defaults();
        $execution_context = execution_context::create('dev');
        $course_context = context_course::instance($course->id, IGNORE_MISSING);
        $execution_context->set_variable('context', $course_context);

        $course_custom_field_data = reset($custom_field_data[$course->id]);
        $course_custom_field_data['definition']['type'] = 'my_new_and_unknown_type';

        $course_custom_field_value = definition::resolve(
            'type',
            $course_custom_field_data['definition'],
            [],
            $execution_context
        );
        $this->assertSame('UNKNOWN', $course_custom_field_value);
    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_resolve_core_custom_field_definition_description(): void {
        list($course, $custom_field_data) = $this->create_defaults();
        $execution_context = execution_context::create('dev');
        $course_context = context_course::instance($course->id, IGNORE_MISSING);
        $execution_context->set_variable('context', $course_context);

        $course_custom_field_data = reset($custom_field_data[$course->id]);
        $course_custom_field_value = definition::resolve(
            'description',
            $course_custom_field_data['definition'],
            [],
            $execution_context
        );
        $this->assertSame('default_description', $course_custom_field_value);
    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_resolve_core_custom_field_definition_default_value(): void {
        list($course, $custom_field_data) = $this->create_defaults();
        $execution_context = execution_context::create('dev');
        $course_context = context_course::instance($course->id, IGNORE_MISSING);
        $execution_context->set_variable('context', $course_context);

        $course_custom_field_data = reset($custom_field_data[$course->id]);
        $course_custom_field_value = definition::resolve(
            'default_value',
            $course_custom_field_data['definition'],
            [],
            $execution_context
        );
        $this->assertSame('default_data', $course_custom_field_value);
    }

    /**
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    public function create_defaults(): array {
        global $DB;
        self::setAdminUser();
        $gen = self::getDataGenerator();
        $time = time();
        $course = $gen->create_course(['timecreated' => $time + 1, 'timemodified' => $time + 10]);

        /** @var generator $custom_field_generator */
        $custom_field_generator = $this->getDataGenerator()->get_plugin_generator('totara_customfield');
        $custom_fields = $custom_field_generator->create_text('course', ['text_one']);
        $custom_field_generator->set_text($course, $custom_fields['text_one'], 'course_text', 'course', 'course');
        $field = $DB->get_record('course_info_field', array('id' => $custom_fields['text_one']), '*', MUST_EXIST);
        $field->defaultdata = 'default_data';
        $field->description = 'default_description';
        $DB->update_record('course_info_field', $field);

        $custom_field_data = customfield_get_custom_fields_and_data_for_items(
            'course',
            [
                $course->id,
            ]
        );
        return array($course, $custom_field_data);
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

        $course_custom_field_data = reset($custom_field_data[$course->id])['definition'];

        // When I resolve the raw default value of the field
        $course_custom_field_value = definition::resolve(
            'raw_default_value',
            $course_custom_field_data,
            [],
            $execution_context
        );
        // Then I should get null (as file is unsupported)
        $this->assertNull($course_custom_field_value);

        // When I resolve the default value of the field
        $course_custom_field_value = definition::resolve(
            'default_value',
            $course_custom_field_data,
            [],
            $execution_context
        );
        // Then I should get null (as file is unsupported)
        $this->assertNull($course_custom_field_value);
    }
}
