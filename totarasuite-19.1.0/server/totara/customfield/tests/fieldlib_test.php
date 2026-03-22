<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Nathaniel Walmsley <nathaniel.walmsley@totara.com>
 * @package totara_customfield
 */

defined('MOODLE_INTERNAL') || die();

class totara_customfield_fieldlib_test extends \core_phpunit\testcase {

    /**
     * This test checks that we can retrieve selected options from a multiselect field,
     * and that the strings are formatted as expected (e.g. parsing HTML)
     *
     * @return void
     */
    public function test_customfield_get_data_for_multiselect() {
        global $DB;

        // Enable the multilang filter and set it to apply to headings and content.
        filter_set_global_state('multilang', TEXTFILTER_ON);
        filter_set_applies_to_strings('multilang', true);
        filter_manager::reset_caches();

        $html_fullname = '<span lang="en" class="multilang">English multi-select</span><span lang="es" class="multilang">Spanish multi-select</span>';
        $multilang_opt1 = '<span lang="en" class="multilang">English opt1</span><span lang="es" class="multilang">Spanish opt1</span>';
        $multilang_opt2 = '<span lang="en" class="multilang">English opt2</span><span lang="es" class="multilang">Spanish opt2</span>';
        $multilang_opt3 = '<span lang="en" class="multilang">English opt3</span><span lang="es" class="multilang">Spanish opt3</span>';
        /** @var \totara_customfield\testing\generator $cfgenerator */
        $cfgenerator = $this->getDataGenerator()->get_plugin_generator('totara_customfield');
        $result = $cfgenerator->create_multiselect(
            'course',
            [$html_fullname => [$multilang_opt1, $multilang_opt2, $multilang_opt3]],
            'multilangselect'
        );
        $course = new stdClass();
        $course->id = 1;

        $cfgenerator->set_multiselect(
            $course, $result[$html_fullname], array($multilang_opt1, $multilang_opt3), 'course', 'course'
        );

        $json_data = $DB->get_record('course_info_data', array('courseid' => $course->id, 'fieldid' => $result[$html_fullname]));
        $data = json_decode($json_data->data);
        $this->assertEquals(2, sizeof((array) $data)); // Check that we selected and stored both options correctly

        $item = new stdClass();
        $item->id = $result[$html_fullname];
        $customfields = customfield_get_data(
            $item, 'course', 'field', true, array(), 'multilangselect'
        );
        $this->assertEquals(
            ['English multi-select' => 'English opt1, English opt3'], $customfields
        );
    }

    public function test_customfield_get_custom_fields_and_data_for_items(): void {
        global $DB;
        // __with_empty_item_ids
        $custom_fields = customfield_get_custom_fields_and_data_for_items("course", []);
        $this->assertCount(0, $custom_fields);

        // __with_non_existent_item_ids
        $custom_fields = customfield_get_custom_fields_and_data_for_items("course", [4, 6, 2]);
        $this->assertCount(0, $custom_fields);

        // __with_invalid_values
        $custom_fields = customfield_get_custom_fields_and_data_for_items("course", ['abc', 'myvalue']);
        $this->assertCount(0, $custom_fields);
    }

    public function test_customfield_field_data_with_maxinparams(): void {
        global $DB;

        // setup reflection on the DB so that we can set the dboptions['maxinparams'] and test chunking of query
        $class = new \ReflectionClass('moodle_database');
        $dboptions = $class->getProperty("dboptions");
        $dboptions->setAccessible(true);
        $original_dboptions = $dboptions->getValue($DB);
        $cloned_dboptions = $original_dboptions;
        $cloned_dboptions['maxinparams'] = 2;
        $dboptions->setValue($DB, $cloned_dboptions);

        // create a couple of custom fields and override one of them with values for courses
        /** @var totara_customfield_generator $custom_field_generator */
        $custom_field_generator = $this->getDataGenerator()->get_plugin_generator('totara_customfield');
        $custom_fields = $custom_field_generator->create_text('course', ['text_one', 'text_two']);
        // create 5 courses - this in conjunction with the maxinparams set to 2 will
        // cause the chunking to happen x 3 = ( 2 x 'IN', 1 x '=').
        $courses = [];
        $course_ids = [];
        $gen = $this->getDataGenerator();
        for ($i = 0; $i < 5; $i++) {
            $course = $gen->create_course();
            $courses[] = $course;
        }
        foreach ($courses as $course) {
            $course_ids[] = $course->id;
            $custom_field_generator->set_text(
                $course,
                $custom_fields['text_one'],
                'test' . $course->id,
                'course',
                'course'
            );
        }

        $item_custom_fields = customfield_get_custom_fields_and_data_for_items("course", $course_ids);
        $this->assertDebuggingCalled("The number of parameters passed (5) exceeds maximum number allowed (2). Query will be chunked, consider calling with less items");
        $this->assertCount(5, $item_custom_fields);
        foreach ($item_custom_fields as $custom_field) {
            $this->assertCount(2, $custom_field);
        }

        // cleanup by reverting back to original values
        $dboptions->setValue($DB, $original_dboptions);
    }

    /**
     * Check that defaults item saved values override the default value of a custom field
     *
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_customfield_get_custom_fields_and_data_for_items__overridden(): void {
        // Given we set up one course custom field
        $custom_field_generator = $this->getDataGenerator()->get_plugin_generator('totara_customfield');
        $result = $custom_field_generator->create_text(
            'course',
            ['text_one']
        );
        $course = new stdClass();
        $course->id = 1;

        // And we set the custom field on a course with id '1'
        $custom_field_generator->set_text(
            $course,
            $result['text_one'],
            'hello',
            'course',
            'course'
        );

        // When we fetch the course custom fields for the course id '1'
        $custom_fields = customfield_get_custom_fields_and_data_for_items("course", [1]);

        // Then we should have only one custom field for course id '1' and it should contain the data we set
        $this->assertCount(1, $custom_fields);
        $this->assertCount(1, $custom_fields[1]);
        $this->assertEquals('hello', $custom_fields[1][0]['value']);
        $this->assertEquals('hello', $custom_fields[1][0]['raw_value']);
    }

    /**
     * Check that defaults are returned when none are overridden
     *
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_customfield_get_custom_fields_and_data_for_items__default(): void {
        // Given we set three course custom fields, and we don't set the data on an individual course
        $custom_field_generator = $this->getDataGenerator()->get_plugin_generator('totara_customfield');
        $result = $custom_field_generator->create_text(
            'course',
            ['text_one', 'text_two', 'text_three']
        );

        // When we fetch the custom fields
        $custom_fields = customfield_get_custom_fields_and_data_for_items("course", [1]);

        // Then we should have 3 custom field records
        $this->assertCount(1, $custom_fields);
        $this->assertCount(3, $custom_fields[1]);

        // And they should represent the custom fields we made, and have no data set
        foreach ($custom_fields[1] as $custom_field) {
            if ($custom_field['definition']['id'] === $result['text_one']) {
                $this->assertEquals('text_one', $custom_field['definition']['fullname']);
                $this->assertNull($custom_field['value']);
                $this->assertNull($custom_field['raw_value']);
                continue;
            }

            if ($custom_field['definition']['id'] === $result['text_two']) {
                $this->assertEquals('text_two', $custom_field['definition']['fullname']);
                $this->assertNull($custom_field['value']);
                $this->assertNull($custom_field['raw_value']);
                continue;
            }

            if ($custom_field['definition']['id'] === $result['text_three']) {
                $this->assertEquals('text_three', $custom_field['definition']['fullname']);
                $this->assertNull($custom_field['value']);
                $this->assertNull($custom_field['raw_value']);
            }
        }
    }

    /**
     * Check for a mixture of default and overridden values on the custom fields
     * You should have the defaults returned unless the item overrides the defaults
     *
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_customfield_get_custom_fields_and_data_for_items__mixed(): void {
        // Given I set up 4 course custom fields
        $custom_field_generator = $this->getDataGenerator()->get_plugin_generator('totara_customfield');
        $result = $custom_field_generator->create_text(
            'course',
            ['text_one', 'text_two', 'text_three', 'text_four']
        );

        $course = new stdClass();
        $course->id = 1;

        // And set two of them to have data on the course with id '1'
        $custom_field_generator->set_text(
            $course,
            $result['text_one'],
            'hello',
            'course',
            'course'
        );
        $custom_field_generator->set_text(
            $course,
            $result['text_four'],
            'hello',
            'course',
            'course'
        );

        // When we fetch the custom fields for the course '1'
        $custom_fields = customfield_get_custom_fields_and_data_for_items("course", [1]);

        // Then we should see 4 custom fields for the course with id '1'
        $this->assertCount(1, $custom_fields);
        $this->assertCount(4, $custom_fields[1]);

        foreach ($custom_fields[1] as $custom_field) {
            // Then we should get the value we set on the custom field data record
            if ($custom_field['definition']['id'] === $result['text_one']) {
                $this->assertEquals('text_one', $custom_field['definition']['fullname']);
                $this->assertEquals('hello', $custom_field['value']);
                $this->assertEquals('hello', $custom_field['raw_value']);
                continue;
            }

            // Then we should get null for the data record for fields not set on the course
            if ($custom_field['definition']['id'] === $result['text_two']) {
                $this->assertNull($custom_field['value']);
                $this->assertNull($custom_field['raw_value']);
            }
        }
    }

    /**
     * Check for customfields with types
     * You should have the defaults returned unless the item overrides the defaults
     *
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_customfield_get_custom_fields_and_data_for_items_with_types(): void {
        $this->setAdminUser();

        // Given I set up a position type with a position type custom field
        $generator = $this->getDataGenerator();

        /** @var \totara_hierarchy\testing\generator $hierarchy_generator */
        $hierarchy_generator = $generator->get_plugin_generator('totara_hierarchy');

        $position_type_id = $hierarchy_generator->create_pos_type(
            ['idnumber' => 'type1']
        );

        $hierarchy_generator->create_hierarchy_type_text(
            [
                'hierarchy' => 'position',
                'typeidnumber' => 'type1',
                'fullname' => 'position_type_text',
                'value' => 'value',
            ]
        );
        // And set up two positions
        $framework= $hierarchy_generator->create_framework('position');
        // One with no type set
        $positon_with_no_type = $hierarchy_generator->create_pos(
            [
                'frameworkid' => $framework->id,
            ]
        );
        $positon_with_type = $hierarchy_generator->create_pos(
            [
                'frameworkid' => $framework->id,
                'typeid' => $position_type_id,
            ]
        );

        // When we fetch the custom fields for the positions
        $custom_fields = customfield_get_custom_fields_and_data_for_items("position", [$positon_with_no_type->id, $positon_with_type->id]);

        // Then we should see 1 custom field for the position with the type set
        $this->assertCount(1, $custom_fields);
        $this->assertCount(1, $custom_fields[$positon_with_type->id]);
    }

    public function test_customfield_get_item_information(): void {
        $information = customfield_get_item_information("course");

        $this->assertCount(3, array_keys($information));
        $this->assertEquals("course", $information['item_table_name']);
        $this->assertEquals("course", $information['info_table_prefix']);
        $this->assertEquals("courseid", $information['info_data_item_id_key']);

        $information = customfield_get_item_information("position");

        $this->assertEquals("typeid", $information['info_field_type_id_key']);
        $this->assertEquals("typeid", $information['item_type_id_key']);
    }

    public function test_customfield_get_item_information__unsupported_type(): void {
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage("Unsupported item type `my_fake_type`");

        customfield_get_item_information("my_fake_type");
    }
}
