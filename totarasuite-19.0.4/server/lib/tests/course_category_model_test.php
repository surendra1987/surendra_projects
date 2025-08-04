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
 * @category test
 */

use core\orm\collection;
use core\model\course_category;
use core\entity\course_categories;
use core\entity\course;
use core_phpunit\testcase;

defined('MOODLE_INTERNAL') || die();

class core_course_category_model_test extends testcase {

    public function test_ensure_sequential_course_sortorder() {
        global $DB;

        // Create a category and courses with incorrect sort orders.
        $category1 = coursecat::create(array('name' => 'Category 1'));
        $course1 = $this->getDataGenerator()->create_course(array('category' => $category1->id));
        $course2 = $this->getDataGenerator()->create_course(array('category' => $category1->id));

        // Ensure that the initial sort order is incorrect.
        $this->assertEquals(20001, $course1->sortorder);
        $this->assertEquals(20001, $course2->sortorder);

        // Call the function to fix the course sort order.
        $category1 = course_category::load_by_id($category1->id);
        $category1->ensure_sequential_course_sortorder();

        // Reload courses and check if the sort order has been corrected.
        $course1 = $DB->get_record('course', array('id' => $course1->id));
        $course2 = $DB->get_record('course', array('id' => $course2->id));

        $this->assertEquals(20002, $course1->sortorder);
        $this->assertEquals(20001, $course2->sortorder);
    }

    public function test_get_course_count() {
        // Create a category and some courses.
        $category1 = coursecat::create(array('name' => 'Category 1'));
        $this->getDataGenerator()->create_course(array('category' => $category1->id));
        $this->getDataGenerator()->create_course(array('category' => $category1->id));

        // Test that the course count is correct.
        $category1 = course_category::load_by_id($category1->id);
        $this->assertEquals(2, $category1->get_course_count());
    }

    public function test_update_course_count() {
        global $DB;

        // Create a category and some courses.
        $category1 = coursecat::create(array('name' => 'Category 1'));
        $this->getDataGenerator()->create_course(array('category' => $category1->id));

        // Update course count.
        $category1 = course_category::load_by_id($category1->id);
        $category1->update_course_count();

        // Verify that the course count is correct in the database.
        $updated_category = $DB->get_record('course_categories', array('id' => $category1->id));
        $this->assertEquals(1, $updated_category->coursecount);
    }

    public function test_update_program_count() {
        global $DB;

        // Create a category and a program (assuming a program generator is available).
        $category1 = coursecat::create(array('name' => 'Category 1'));
        $data_generator = $this->getDataGenerator();
        /** @var \totara_program\testing\generator $program_generator */
        $program_generator = $data_generator->get_plugin_generator('totara_program');
        $program_generator->create_program(array('category' => $category1->id));

        // Update program count.
        $category1 = course_category::load_by_id($category1->id);
        $category1->update_program_count();

        // Verify that the program count is correct in the database.
        $updated_category = $DB->get_record('course_categories', array('id' => $category1->id));
        $this->assertEquals(1, $updated_category->programcount);
    }

    public function test_update_certification_count() {
        global $DB;

        // Create a category and a certification (assuming a certification generator is available).
        $category1 = coursecat::create(array('name' => 'Category 1'));
        $data_generator = $this->getDataGenerator();
        /** @var \totara_program\testing\generator $program_generator */
        $cert_generator = $data_generator->get_plugin_generator('totara_program');
        $cert_generator->create_certification(array('category' => $category1->id));

        // Update certification count.
        $category1 = course_category::load_by_id($category1->id);
        $category1->update_certification_count();

        // Verify that the certification count is correct in the database.
        $updated_category = $DB->get_record('course_categories', array('id' => $category1->id));
        $this->assertEquals(1, $updated_category->certifcount);
    }

    public function test_fix_course_sortorder_private() {
        global $DB;

        // Create a category
        $category1 = coursecat::create(array('name' => 'Test Category'));

        // Create some courses with incorrect sort orders
        $course1 = $this->getDataGenerator()->create_course(array ('fullname' => 'Course 1', 'category' => $category1->id));
        $course2 = $this->getDataGenerator()->create_course(array('fullname' => 'Course 2', 'category' => $category1->id));
        $course3 = $this->getDataGenerator()->create_course(array('fullname' => 'Course 3', 'category' => $category1->id));

        // Ensure that the initial sort order is incorrect.
        $this->assertEquals(20001, $course1->sortorder);
        $this->assertEquals(20001, $course2->sortorder);
        $this->assertEquals(20001, $course3->sortorder);

        // Use Reflection to access the private method
        $reflection = new ReflectionClass(course_category::class);
        $method = $reflection->getMethod('fix_course_sortorder');
        $method->setAccessible(true);

        // Call the private method to fix the course sort order
        $category1 = course_category::load_by_id($category1->id);

        $course1 = course::repository()->find($course1->id);
        $course2 = course::repository()->find($course2->id);
        $course3 = course::repository()->find($course3->id);

        $courses = new collection ([$course1, $course2, $course3]); // Pass the courses manually to the method

        $method->invoke($category1, $courses);

        // Reload the courses and check if the sort orders were fixed.
        $course1 = $DB->get_record('course', array('id' => $course1->id));
        $course2 = $DB->get_record('course', array('id' => $course2->id));
        $course3 = $DB->get_record('course', array('id' => $course3->id));

        // Assert the sort orders are now sequential and correct.
        $this->assertEquals(20001, $course1->sortorder);
        $this->assertEquals(20002, $course2->sortorder);
        $this->assertEquals(20003, $course3->sortorder);
    }


    public function test_fix_category_sortorder() {
        global $DB;

        // Create some top-level categories with incorrect sort orders.
        $container1 = coursecat::create(array('name' => 'ContainerCat', 'parent' => 0, 'issystem' => 1, 'iscontainer' => 1, 'idnumber' => 'container_course-0'));
        $category1 = coursecat::create(array('name' => 'Category 1'));
        $categorysys1 = coursecat::create(array('name' => 'CatSys1', 'parent' => $category1->id, 'issystem' => 1));
        $category2 = coursecat::create(array('name' => 'Category 2', 'parent' => $category1->id,));
        $categorysys2 = coursecat::create(array('name' => 'CatSys2', 'parent' => $category1->id, 'issystem' => 1, 'visible' => 0));
        $category3 = coursecat::create(array('name' => 'Category 3', 'parent' => $category1->id,));

        // Verify the initial incorrect sort order.
        $this->assertEquals(20000, $category1->sortorder);
        $this->assertEquals(30000, $category2->sortorder);
        $this->assertEquals(40000, $category3->sortorder);
        $this->assertEquals(50000, $container1->sortorder);
        $this->assertEquals(30000, $categorysys1->sortorder);
        $this->assertEquals(50000, $categorysys2->sortorder);

        $DB->set_field('course_categories', 'sortorder', 70000, ['id' => $category1->id]);
        $DB->set_field('course_categories', 'sortorder', 90000, ['id' => $category3->id]);

        $category1 = $DB->get_record('course_categories', array('id' => $category1->id));
        $category3 = $DB->get_record('course_categories', array('id' => $category3->id));

        $this->assertEquals(70000, $category1->sortorder);
        $this->assertEquals(90000, $category3->sortorder);

        // Call the function to fix the category sort order.
        $category1 = course_category::load_by_id($category1->id);
        $category1->fix_category_sortorder();

        // Reload categories and check if the sort order has been corrected.
        $category1 = $DB->get_record('course_categories', array('id' => $category1->id));
        $category2 = $DB->get_record('course_categories', array('id' => $category2->id));
        $category3 = $DB->get_record('course_categories', array('id' => $category3->id));
        $container1 = $DB->get_record('course_categories', array('id' => $container1->id));
        $categorysys1 = $DB->get_record('course_categories', array('id' => $categorysys1->id));
        $categorysys2 = $DB->get_record('course_categories', array('id' => $categorysys2->id));

        // Ensure the sort order is now sequential and correct.
        $this->assertEquals(20000, $category1->sortorder); // Assuming base sort order starts at 100
        $this->assertEquals(30000, $category2->sortorder);
        $this->assertEquals(40000, $category3->sortorder);
        $this->assertEquals(100000, $container1->sortorder);
        $this->assertEquals(50000, $categorysys1->sortorder);
        $this->assertEquals(60000, $categorysys2->sortorder);


        // Ensure paths have been updated (if applicable).
        $this->assertStringContainsString('/'.$category1->id, $category1->path);
        $this->assertStringContainsString('/'.$category2->id, $category2->path);
        $this->assertStringContainsString('/'.$category3->id, $category3->path);
    }

    public function test_fix_course_cats() {
        global $DB;

        // Create top-level categories and subcategories with gaps in sort order.
        $parentCategory = coursecat::create(array('name' => 'Parent Category'));
        $childCategory1 = coursecat::create(array('name' => 'Child Category 1', 'parent' => $parentCategory->id, 'issystem' => 1, 'visible' => 0));
        $childCategory2 = coursecat::create(array('name' => 'Child Category 2', 'parent' => $parentCategory->id));

        // Assume both children have additional subcategories
        $grandChild1 = coursecat::create(array('name' => 'GrandChild 1', 'parent' => $childCategory1->id));
        $grandChild2 = coursecat::create(array('name' => 'GrandChild 2', 'parent' => $childCategory2->id, 'issystem' => 1, 'visible' => 0));

        $this->assertEquals(20000, $parentCategory->sortorder);
        $this->assertEquals(30000, $childCategory1->sortorder);
        $this->assertEquals(30000, $childCategory2->sortorder);
        $this->assertEquals(50000, $grandChild1->sortorder);
        $this->assertEquals(40000, $grandChild2->sortorder);

        $DB->set_field('course_categories', 'sortorder', 70000, ['id' => $grandChild1->id]);
        $DB->set_field('course_categories', 'sortorder', 90000, ['id' => $childCategory1->id]);

        $grandChild1 = $DB->get_record('course_categories', array('id' => $grandChild1->id));
        $childCategory1 = $DB->get_record('course_categories', array('id' => $childCategory1->id));

        $this->assertEquals(70000, $grandChild1->sortorder);
        $this->assertEquals(90000, $childCategory1->sortorder);

        // Simulate fetching categories and calling the fix function
        $parentCategory = course_category::load_by_id($parentCategory->id);
        $parentCategory->fix_category_sortorder();

        // Reload categories and check sort orders
        $childCategory1 = $DB->get_record('course_categories', array('id' => $childCategory1->id));
        $childCategory2 = $DB->get_record('course_categories', array('id' => $childCategory2->id));
        $grandChild1 = $DB->get_record('course_categories', array('id' => $grandChild1->id));
        $grandChild2 = $DB->get_record('course_categories', array('id' => $grandChild2->id));

        // Check that the sort orders are now sequential
        $this->assertEquals(50000, $childCategory1->sortorder);
        $this->assertEquals(30000, $childCategory2->sortorder);
        $this->assertEquals(60000, $grandChild1->sortorder);
        $this->assertEquals(40000, $grandChild2->sortorder);
    }
    public function test_sort_system_categories_last() {
        // Create several categories with mixed `issystem` values
        $category1 = coursecat::create(array('name' => 'Category 1', 'issystem' => 0)); // Regular category
        $category2 = coursecat::create(array('name' => 'Category 2', 'issystem' => 1)); // System category
        $category3 = coursecat::create(array('name' => 'Category 3', 'issystem' => 0)); // Regular category
        $category4 = coursecat::create(array('name' => 'Category 4', 'issystem' => 1)); // System category

        // Array of categories simulating subcategories
        $categories = [$category1, $category2, $category3, $category4];

        // Retrieve an existing coursecat object from the database.
        $category1 = course_category::load_by_id($category1->id);

        // Use reflection to access the private method sort_system_categories_last
        $reflection = new ReflectionMethod($category1, 'sort_system_categories_last');
        $reflection->setAccessible(true);

        // Invoke the private method and sort the categories
        $sortedCategories = $reflection->invoke($category1, $categories);

        // Assert that regular categories come before system categories
        $this->assertEquals([$category1->id, $category3->id, $category2->id, $category4->id], array_column($sortedCategories, 'id'));
    }

    public function test_update_category_if_needed() {
        global $DB;

        // Create a category with initial incorrect values
        $category = coursecat::create(array(
            'name' => 'Test Category',
            'parent' => 0,
            'sortorder' => 10,
            'depth' => 1,
            'path' => '/1'
        ));

        // Simulate new correct values for parent, depth, and path
        $newParent = 2; // Assume 2 is a valid parent category ID
        $newDepth = 2;
        $newPath = '/2/1'; // New correct path
        $newSortOrder = 15;
        $fixcontexts = [];

        $category_entity = course_categories::repository()->find($category->id);
        $category = course_category::load_by_id($category->id);
        // Use reflection to access the private method update_category_if_needed
        $reflection = new ReflectionMethod($category, 'update_category_if_needed');
        $reflection->setAccessible(true);

        // Call the method using the existing category instance
        $result = $reflection->invokeArgs(
            $category,
            [$category_entity, $newSortOrder, $newParent, $newDepth, $newPath, &$fixcontexts]
        );

        // Reload the category and check if it was updated
        $updatedCategory = $DB->get_record('course_categories', array('id' => $category->id));

        // Assertions to ensure the category was updated correctly
        $this->assertTrue($result);
        $this->assertEquals($newParent, $updatedCategory->parent);
        $this->assertEquals($newDepth, $updatedCategory->depth);
        $this->assertEquals($newPath .'/'. $updatedCategory->id, $updatedCategory->path);
        $this->assertEquals($newSortOrder, $updatedCategory->sortorder);
    }
}
