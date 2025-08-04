<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be use`ful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author  Murali Nair <murali.nair@totaralearning.com>
 * @package perform_goal
 */

use core\entity\course;
use core_phpunit\testcase;
use perform_goal\model\goal_task_type\type;
use perform_goal\model\goal_task_type\course_type;
use perform_goal\settings_helper;
use perform_goal\testing\generator;
use perform_goal\testing\goal_generator_config;
use perform_goal\testing\goal_task_generator_config;
use mod_perform\testing\generator as perform_generator;

global $CFG;
require_once($CFG->dirroot . '/course/lib.php');

/**
 * @group perform_goal
 * @group perform_goal_task
 */
class perform_goal_goal_task_type_test extends testcase {
    /**
     * @covers ::by_code
     */
    public function test_by_code(): void {
        // The goal task course resource type is provided out of the box; it is
        [$course] = $this->setup_env();
        $code = course_type::TYPECODE;
        $type = type::by_code($code, $course->id);

        $this->assertEquals($code, $type->code());
        $this->assertNotNull($type->resource_id());
        $this->assertEquals(course_type::class, $type->name());
    }

    /**
     * @covers ::by_code
     */
    public function test_by_code_with_resource_id(): void {
        [$course] = $this->setup_env();
        $code = course_type::TYPECODE;
        $resource_id = $course->id;
        $type = type::by_code($code, $resource_id);

        $this->assertEquals($code, $type->code());
        $this->assertEquals($resource_id, $type->resource_id());
        $this->assertEquals(course_type::class, $type->name());
        $this->assertTrue($type->resource_exists());
    }

    public function test_course_resource_type_values(): void {
        $this->setAdminUser();

        // Enable the multilang filter and set it to apply to headings and content.
        filter_set_global_state('multilang', TEXTFILTER_ON);
        filter_set_applies_to_strings('multilang', true);
        filter_manager::reset_caches();

        $gen = $this->getDataGenerator();
        $course = $gen->create_course(['fullname' => '<span lang="en" class="multilang">English course title</span><span lang="de" class="multilang">German course title</span>']);
        settings_helper::enable_perform_goals();
        $subject1 = self::getDataGenerator()->create_user();
        $owner = self::getDataGenerator()->create_user();

        $goal_test_config = goal_generator_config::new([
            'context' => context_user::instance($subject1->id),
            'name' => 'Test goal 1',
            'owner_id' => $owner->id,
            'user_id' => $subject1->id,
            'id_number' => 'goal_1',
            'description' => 'Test goal 1 description',
        ]);
        $goal1 = generator::instance()->create_goal($goal_test_config);
        $goal_task_config = goal_task_generator_config::new([
            'goal_id' => $goal1->id,
            'description' => "Task 2 goal 3 with resource",
            'resource_id' => $course->id,
            'resource_type' => course_type::TYPECODE
        ]);
        $goal_task1 = generator::instance()->create_goal_task($goal_task_config);
        $gt_resource = generator::instance()->create_goal_task_resource($goal_task1, $goal_task_config);

        $course_type_resource = type::by_code(course_type::TYPECODE, $course->id);
        $custom_data = $course_type_resource->custom_data($gt_resource);

        $this->assertEquals('English course title', $custom_data['name']);
        $this->assertEquals(course_get_url($course->id)->out(false), $custom_data['url']);
        $this->assertArrayHasKey('subject_progress', $custom_data);
        $this->assertEquals(course_get_image($course->id)->out(false), $custom_data['image_data']);

        course::repository()->delete();
    }

    /**
     * @covers ::by_code
     */
    public function test_by_invalid_code(): void {
        $code = -100;

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage($code);
        type::by_code($code);
    }

    public function test_by_code_with_invalid_resource_id(): void {
        $perform_generator = perform_generator::instance();
        $user = $this->getDataGenerator()->create_user();
        self::setUser($user);
        $activity = $perform_generator->create_activity_in_container();
        $this->executeAdhocTasks();

        // container type is 'container_perform' and exists under 'course' table
        $resource_id = $activity->course;
        $code = course_type::TYPECODE;
        $type = type::by_code($code, $resource_id);

        static::assertNull($type->resource());
        static::assertFalse($type->resource_exists());
    }

    private function setup_env() {
        $this->setAdminUser();
        $gen = $this->getDataGenerator();
        $course = $gen->create_course();
        $this->executeAdhocTasks();
        return [$course];
    }
}
