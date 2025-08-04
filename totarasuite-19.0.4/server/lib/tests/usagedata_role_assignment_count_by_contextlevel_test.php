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
 * @package core
 */

use core\usagedata\role_assignment_count_by_contextlevel;
use core_phpunit\testcase;

class core_usagedata_role_assignment_count_by_contextlevel_test extends testcase {

    public ?array $test_users;
    public ?array $test_categories;
    public ?array $test_courses;

    public function setUp(): void {
        parent::setUp();

        $generator = $this->getDataGenerator();

        for ($i = 0; $i < 5; $i++) {
            $user = $generator->create_user();
            $this->test_users[$i] = $user->id;

            $category = $generator->create_category();
            $this->test_categories[] = $category->id;

            $course = $generator->create_course(['category' => $category->id]);
            $this->test_courses[] = $course->id;
        }
    }

    protected function tearDown(): void {
        $this->test_users = null;
        $this->test_courses = null;
        $this->test_categories = null;

        parent::tearDown();
    }

    public function test_export() {
        global $DB;

        $roles = $DB->get_fieldset_select('role', 'id', '');

        // Context: CONTEXT_COURSECAT
        role_assign($roles[3], $this->test_users[0], context_coursecat::instance($this->test_categories[2]));
        role_assign($roles[1], $this->test_users[1], context_coursecat::instance($this->test_categories[1]));
        role_assign($roles[4], $this->test_users[1], context_coursecat::instance($this->test_categories[3]));

        // Context: CONTEXT_COURSE
        role_assign($roles[2], $this->test_users[2], context_course::instance($this->test_courses[2]));

        // Context: CONTEXT_SYSTEM
        role_assign($roles[4], $this->test_users[1], context_system::instance());
        role_assign($roles[2], $this->test_users[4], context_system::instance());

        $export_result = (new role_assignment_count_by_contextlevel())->export();

        // Three contexts assigned to
        $this->assertCount(3, $export_result);

        $this->assertEquals('3', $export_result[CONTEXT_COURSECAT]);
        $this->assertEquals('1', $export_result[CONTEXT_COURSE]);
        $this->assertEquals('2', $export_result[CONTEXT_SYSTEM]);
    }
}