<?php
/*
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Angela Kuznetsova <angela.kuznetsova@totara.com>
 * @package core
 */

use core\task\legacy_course_sortorder_task;
use core_phpunit\testcase;

defined('MOODLE_INTERNAL') || die();

class core_legacy_course_sortorder_task_test extends testcase {

    protected function setUp(): void {
        set_config('use_legacy_course_sortorder', 1);
    }

    /**
     * Test the basic operation of the \core\task\legacy_course_sortorder_task task.
     */
    public function test_basic_operation() {
        global $DB;
        // Create a category
        $category1 = coursecat::create(array('name' => 'Test Category1'));
        $category2 = coursecat::create(array('name' => 'Test Category2', 'parent' => $category1->id));
        $categorysys1 = coursecat::create(array('name' => 'CatSys1', 'parent' => $category1->id, 'issystem' => 1));
        $categorysys2 = coursecat::create(array('name' => 'CatSys2', 'parent' => $category1->id, 'issystem' => 1, 'visible' => 0));
        $categorysys3 = coursecat::create(array('name' => 'CatSys3', 'issystem' => 1));
        // Totara: include a container category (which is never seen)
        $container1 = coursecat::create(array('name' => 'ContainerCat', 'parent' => 0, 'issystem' => 1, 'iscontainer' => 1, 'idnumber' => 'container_course-0'));
        $category3 = coursecat::create(array('name' => 'Test Category3', 'parent' => $category2->id));
        $category4 = coursecat::create(array('name' => 'Test Category4', 'parent' => $category2->id));

        // Create some courses with incorrect sort orders
        $course1 = $this->getDataGenerator()->create_course(array ('fullname' => 'Course 1', 'category' => $category1->id));
        $course2 = $this->getDataGenerator()->create_course(array('fullname' => 'Course 2', 'category' => $category1->id));
        $course3 = $this->getDataGenerator()->create_course(array('fullname' => 'Course 3', 'category' => $category2->id));
        $course4 = $this->getDataGenerator()->create_course(array ('fullname' => 'Course 4', 'category' => $category2->id));
        $course5 = $this->getDataGenerator()->create_course(array('fullname' => 'Course 5', 'category' => $category3->id));
        $course6 = $this->getDataGenerator()->create_course(array('fullname' => 'Course 6', 'category' => $category3->id));
        $course7 = $this->getDataGenerator()->create_course(array ('fullname' => 'Course 7', 'category' => $category4->id));
        $course8 = $this->getDataGenerator()->create_course(array('fullname' => 'Course 8', 'category' => $category4->id));
        $course9 = $this->getDataGenerator()->create_course(array('fullname' => 'Course 9', 'category' => $category3->id));
        $course10 = $this->getDataGenerator()->create_course(array ('fullname' => 'Course 10', 'category' => $category2->id));
        $course11 = $this->getDataGenerator()->create_course(array('fullname' => 'Course 11', 'category' => $category1->id));
        $course12 = $this->getDataGenerator()->create_course(array('fullname' => 'Course 12', 'category' => $category3->id));

        // Ensure that the initial sort order is incorrect.
        $this->assertEquals(20001, $course1->sortorder);
        $this->assertEquals(20001, $course2->sortorder);
        $this->assertEquals(30001, $course3->sortorder);
        $this->assertEquals(30001, $course4->sortorder);
        $this->assertEquals(40001, $course5->sortorder);
        $this->assertEquals(40001, $course6->sortorder);
        $this->assertEquals(50001, $course7->sortorder);
        $this->assertEquals(50001, $course8->sortorder);
        $this->assertEquals(40001, $course9->sortorder);
        $this->assertEquals(30001, $course10->sortorder);
        $this->assertEquals(20001, $course11->sortorder);
        $this->assertEquals(40001, $course12->sortorder);

        // Run the task.
        $sink = $this->redirectEvents();
        $task = new legacy_course_sortorder_task();
        ob_start();
        $task->execute();
        $output = ob_get_contents();
        ob_end_clean();
        $sink->close();
        $events = $sink->get_events();

        // Reload courses and check if the sort order has been corrected.
        $course1 = $DB->get_record('course', array('id' => $course1->id));
        $course2 = $DB->get_record('course', array('id' => $course2->id));
        $course3 = $DB->get_record('course', array('id' => $course3->id));
        $course4 = $DB->get_record('course', array('id' => $course4->id));
        $course5 = $DB->get_record('course', array('id' => $course5->id));
        $course6 = $DB->get_record('course', array('id' => $course6->id));
        $course7 = $DB->get_record('course', array('id' => $course7->id));
        $course8 = $DB->get_record('course', array('id' => $course8->id));
        $course9 = $DB->get_record('course', array('id' => $course9->id));
        $course10 = $DB->get_record('course', array('id' => $course10->id));
        $course11 = $DB->get_record('course', array('id' => $course11->id));
        $course12 = $DB->get_record('course', array('id' => $course12->id));

        $this->assertEquals(20003, $course1->sortorder);
        $this->assertEquals(20002, $course2->sortorder);
        $this->assertEquals(30003, $course3->sortorder);
        $this->assertEquals(30002, $course4->sortorder);
        $this->assertEquals(40004, $course5->sortorder);
        $this->assertEquals(40003, $course6->sortorder);
        $this->assertEquals(50002, $course7->sortorder);
        $this->assertEquals(50001, $course8->sortorder);
        $this->assertEquals(40002, $course9->sortorder);
        $this->assertEquals(30001, $course10->sortorder);
        $this->assertEquals(20001, $course11->sortorder);
        $this->assertEquals(40001, $course12->sortorder);
    }
}