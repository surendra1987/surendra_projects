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

use core_phpunit\testcase;
use container_course\course_helper;
use core_course\usagedata\course_count_of_configuration;
use container_course\course as course_container;
use core\entity\course as course_entity;

class core_course_usagedata_course_count_of_configuration_test extends testcase {

    /**
     * @throws coding_exception
     *
     * @covers course_count_of_configuration::export()
     * @covers course_count_of_configuration::get_container_type()
     * @covers course_count_of_configuration::get_course_type()
     * @covers course_count_of_configuration::get_audience_visible()
     * @covers course_count_of_configuration::get_lang()
     * @covers course_count_of_configuration::get_group_mode()
     * @covers course_count_of_configuration::get_summary_formats()
     * @covers course_count_of_configuration::get_course_formats()
     * @covers course_count_of_configuration::get_toggle_options()
     */
    public function test_export() {

        $initial_result = (new course_count_of_configuration())->export();

        // Initial result will always have a 'site' format 'course'
        $this->assertEquals(1, $this->array_search_by_nested_property(
            $initial_result['format'],
            'site',
            'format')['count']
        );

        $course = new stdClass();
        $course->category = 1;

        $course->format = 'pathway';
        $course->summaryformat = FORMAT_JSON_EDITOR;
        $course->audiencevisible = 5;
        $course->coursetype = 5;
        $course->icon = 'public-relations';

        // Toggle options
        $course->showgrades = 1;
        $course->startdate = time();
        $course->enddate = time();
        $course->marker = 1;
        $course->legacyfiles = 1;
        $course->visible = 1;
        $course->requested = 1;
        $course->enablecompletion = 1;
        $course->completionstartonenrol = 1;
        $course->completionprogressonview = 1;
        $course->completionnotify = 1;
        $course->groupmodeforce = 1;
        $course->theme = 'legacy';
        $course->lang = 'en';

        $course = course_helper::create_course($course);

        $course_entity = new course_entity($course->id);
        $course_entity->duedate = time() + 1000;
        $course_entity->duedateoffsetamount = 2;
        $course_entity->duedateoffsetunit = course_container::DUEDATEOFFSETUNIT_MONTHS;
        $course_entity->save();

        $result = (new course_count_of_configuration())->export();

        $this->assertEquals($initial_result['total'] + 1, $result['total']);

        // (Course) Format
        $initial_count = $this->array_search_by_nested_property(
            $initial_result['format'],
            'pathway',
            'format')['count'] ?? 0;
        $new_count = $this->array_search_by_nested_property(
            $result['format'],
            'pathway',
            'format')['count'];
        $this->assertEquals(((int) $initial_count) + 1, $new_count);

        // Summary Format
        $initial_count = $this->array_search_by_nested_property(
            $initial_result['summaryformat'],
            (int) FORMAT_JSON_EDITOR,
            'format')['count'] ?? 0;
        $new_count = $this->array_search_by_nested_property(
            $result['summaryformat'],
            (int) FORMAT_JSON_EDITOR,
            'format')['count'];
        $this->assertEquals($initial_count + 1, $new_count);

        // Audience Visible
        $initial_count = $this->array_search_by_nested_property(
            $initial_result['audiencevisible'],
            5,
            'visible')['count'] ?? 0;
        $new_count = $this->array_search_by_nested_property(
            $result['audiencevisible'],
            5,
            'visible')['count'];
        $this->assertEquals($initial_count + 1, $new_count);

        // Course Type
        $initial_count = $this->array_search_by_nested_property(
            $initial_result['coursetype'],
            5,
            'type')['count'] ?? 0;
        $new_count = $this->array_search_by_nested_property(
            $result['coursetype'],
            5,
            'type')['count'];
        $this->assertEquals($initial_count + 1, $new_count);

        // Lang
        $initial_count = $this->array_search_by_nested_property(
            $initial_result['lang'],
            'en',
            'lang')['count'] ?? 0;
        $new_count = $this->array_search_by_nested_property(
            $result['lang'],
            'en',
            'lang')['count'];
        $this->assertEquals($initial_count + 1, $new_count);

        $this->assertEquals($initial_result['theme'] + 1, $result['theme']);
        $this->assertEquals($initial_result['icon'] + 1, $result['icon']);
        $this->assertEquals($initial_result['duedate'] + 1, $result['duedate']);
        $this->assertEquals($initial_result['duedateoffsetamount'] + 1, $result['duedateoffsetamount']);
        $this->assertEquals($initial_result['duedateoffsetunit'] + 1, $result['duedateoffsetunit']);

        // Toggle options
        $this->assertEquals($initial_result['showgrades'] + 1, $result['showgrades']);
        $this->assertEquals($initial_result['startdate'] + 1, $result['startdate']);
        $this->assertEquals($initial_result['enddate'] + 1, $result['enddate']);
        $this->assertEquals($initial_result['marker'] + 1, $result['marker']);
        $this->assertEquals($initial_result['legacyfiles'] + 1, $result['legacyfiles']);
        $this->assertEquals($initial_result['visible'] + 1, $result['visible']);
        $this->assertEquals($initial_result['requested'] + 1, $result['requested']);
        $this->assertEquals($initial_result['enablecompletion'] + 1, $result['enablecompletion']);
        $this->assertEquals($initial_result['completionstartonenrol'] + 1, $result['completionstartonenrol']);
        $this->assertEquals($initial_result['completionprogressonview'] + 1, $result['completionprogressonview']);
        $this->assertEquals($initial_result['completionnotify'] + 1, $result['completionnotify']);
        $this->assertEquals($initial_result['groupmodeforce'] + 1, $result['groupmodeforce']);
    }

    public function array_search_by_nested_property($array_set, $value_to_search_for, $property_to_search_by): array {
        foreach ($array_set as $nested_array) {
            if( $nested_array[$property_to_search_by] === $value_to_search_for) {
                return $nested_array;
            }
        }

        return [];
    }
}