<?php
/**
 * This file is part of Totara Perform
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
 * @author  Matthias Bonk <matthias.bonk@totaralearning.com>
 * @package perform_goal
 */

use core_phpunit\testcase;
use perform_goal\entity\goal as goal_entity;
use perform_goal\entity\goal_activity as goal_activity_entity;
use perform_goal\entity\goal_category as goal_category_entity;
use perform_goal\model\goal as goal_model;
use perform_goal\model\goal_activity;
use perform_goal\model\goal_category as goal_category;
use perform_goal\testing\generator as goal_generator;
use perform_goal\testing\goal_activity_generator_config;
use perform_goal\testing\goal_generator_config;
use perform_goal\model\goal_task as goal_task_model;
use perform_goal\entity\goal_task as goal_task_entity;
use perform_goal\testing\goal_task_generator_config;
use perform_goal\model\goal_task_type\course_type;

class perform_goal_generator_test extends testcase {

    public function test_create_goal_category_default(): void {
        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        // There is already a system basic goal_category.
        $this->assertEquals(1, goal_category_entity::repository()->count());

        $goal_category = goal_generator::instance()->create_goal_category();

        $this->assertInstanceOf(goal_category::class, $goal_category);
        $this->assertEquals(2, goal_category_entity::repository()->count());

        /** @var goal_entity $goal_db */
        $goal_category_db = goal_category_entity::repository()->find($goal_category->id);

        // Assert expected default data.
        $this->assertEquals('basic', $goal_category_db->plugin_name);
        $this->assertEquals('Test Goal Category 2', $goal_category_db->name);
        $this->assertEquals('test-goal_category-id-number_2', $goal_category_db->id_number);
        $this->assertEquals(true, $goal_category->active);

        // Create another one.
        $goal_category2 = goal_generator::instance()->create_goal_category();

        $this->assertInstanceOf(goal_category::class, $goal_category2);
        $this->assertEquals(3, goal_category_entity::repository()->count());
        $this->assertEquals('Test Goal Category 3', $goal_category2->name);
    }

    public function test_create_goal_default(): void {
        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        $this->assertEquals(0, goal_entity::repository()->count());

        $now = time();
        $goal = goal_generator::instance()->create_goal();

        $this->assertInstanceOf(goal_model::class, $goal);
        $this->assertEquals(1, goal_entity::repository()->count());

        /** @var goal_entity $goal_db */
        $goal_db = goal_entity::repository()->find($goal->id);

        // Assert expected default data.
        $this->assertEquals('Test goal 1', $goal_db->name);
        $this->assertEquals(context_user::instance($user->id)->id, $goal_db->context_id);
        $this->assertNotNull($goal_db->category_id);
        $this->assertEquals($user->id, $goal_db->owner_id);
        $this->assertEquals(null, $goal_db->user_id);
        $this->assertEquals('test-goal-id-number_1', $goal_db->id_number);
        $this->assertEquals('{"type":"doc","content":[{"type":"paragraph","attrs":{},"content":[{"type":"text","text":"Test goal description"}]}]}', $goal_db->description);
        $this->assertGreaterThanOrEqual($now, $goal_db->start_date);
        $this->assertGreaterThanOrEqual($now + WEEKSECS, $goal_db->target_date);
        $this->assertEquals(80, $goal_db->target_value);
        $this->assertEquals(72.5, $goal_db->current_value);
        $this->assertGreaterThanOrEqual($now, $goal_db->current_value_updated_at);
        $this->assertEquals('not_started', $goal_db->status);
        $this->assertGreaterThanOrEqual($now, $goal_db->status_updated_at);
        $this->assertEquals(null, $goal_db->closed_at);
        $this->assertGreaterThanOrEqual($now, $goal_db->created_at);
        $this->assertGreaterThanOrEqual($now, $goal_db->updated_at);

        // Create another one.
        $goal2 = goal_generator::instance()->create_goal();

        $this->assertInstanceOf(goal_model::class, $goal2);
        $this->assertEquals(2, goal_entity::repository()->count());
        $this->assertEquals('test-goal-id-number_2', $goal2->id_number);
    }

    public function test_create_goal_all_overrides(): void {
        $logged_in_user = self::getDataGenerator()->create_user();
        $owner_user = self::getDataGenerator()->create_user();
        $goal_subject_user = self::getDataGenerator()->create_user();
        self::setUser($logged_in_user);

        $user_context = context_user::instance($goal_subject_user->id);
        $goal_category = goal_generator::instance()->create_goal_category();

        $this->assertEquals(0, goal_entity::repository()->count());

        $now = time();
        $goal_test_config = goal_generator_config::with_category($goal_category, [
            'context' => $user_context,
            'name' => 'Test goal custom name',
            'owner_id' => $owner_user->id,
            'user_id' => $goal_subject_user->id,
            'id_number' => 'custom-id-number',
            'description' => 'Test goal custom description',
            'start_date' => $now - DAYSECS,
            'target_date' => $now + YEARSECS,
            'target_value' => 0.09,
            'current_value' => 0.002,
            'status' => 'in_progress',
        ]);
        $goal = goal_generator::instance()->create_goal($goal_test_config);

        $this->assertInstanceOf(goal_model::class, $goal);
        $this->assertEquals(1, goal_entity::repository()->count());

        /** @var goal_entity $goal_db */
        $goal_db = goal_entity::repository()->find($goal->id);

        $this->assertEquals('Test goal custom name', $goal_db->name);
        $this->assertEquals($user_context->id, $goal_db->context_id);
        $this->assertEquals($goal_category->id, $goal_db->category_id);
        $this->assertEquals($owner_user->id, $goal_db->owner_id);
        $this->assertEquals($goal_subject_user->id, $goal_db->user_id);
        $this->assertEquals('custom-id-number', $goal_db->id_number);
        $this->assertEquals($now - DAYSECS, $goal_db->start_date);
        $this->assertEquals($now + YEARSECS, $goal_db->target_date);
        $this->assertEquals(0.09, $goal_db->target_value);
        $this->assertEquals(0.002, $goal_db->current_value);
        $this->assertGreaterThanOrEqual($now, $goal_db->current_value_updated_at);
        $this->assertEquals('in_progress', $goal_db->status);
        $this->assertGreaterThanOrEqual($now, $goal_db->status_updated_at);
        $this->assertEquals(null, $goal_db->closed_at);
        $this->assertGreaterThanOrEqual($now, $goal_db->created_at);
        $this->assertGreaterThanOrEqual($now, $goal_db->updated_at);

        // Create another one with same config, you cannot because id_number is the same.
        $goal_test_config = goal_generator_config::with_category($goal_category, [
            'context' => $user_context,
            'name' => 'Test goal custom name',
            'owner_id' => $owner_user->id,
            'user_id' => $goal_subject_user->id,
            'id_number' => 'custom-id-number',
            'description' => 'Test goal custom description',
            'start_date' => $now - DAYSECS,
            'target_date' => $now + YEARSECS,
            'target_value' => 0.09,
            'current_value' => 0.002,
            'status' => 'in_progress',
        ]);
        try {
            $goal = goal_generator::instance()->create_goal($goal_test_config);
            $this->fail('Expected coding exception');
        } catch (\coding_exception $e) {
            $this->assertStringContainsString('id_number already exists: custom-id-number', $e->getMessage());
        }
    }

    public function test_create_goal_activity() {
        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        $this->assertEquals(0, goal_entity::repository()->count());
        $this->assertEquals(0, goal_activity_entity::repository()->count());

        $goal = goal_generator::instance()->create_goal();

        $this->assertEquals(1, goal_entity::repository()->count());
        $this->assertEquals(0, goal_activity_entity::repository()->count());

        $goal_activity_test_config = goal_activity_generator_config::new([
            'goal_id' => $goal->id,
            'user_id' => $user->id,
        ]);
        $goal_activity = goal_generator::instance()->create_goal_activity($goal_activity_test_config);

        $this->assertInstanceOf(goal_activity::class, $goal_activity);

        $this->assertEquals(1, goal_entity::repository()->count());
        $this->assertEquals(1, goal_activity_entity::repository()->count());

        $goal_activity_db = goal_activity_entity::repository()->find($goal_activity->id);

        // Assert expected default data.
        $this->assertEquals($goal->id, $goal_activity_db->goal_id);
        $this->assertEquals('Test type', $goal_activity_db->activity_type);
        $this->assertEquals('Test info 1', $goal_activity_db->activity_info);
        $this->assertEquals($user->id, $goal_activity_db->user_id);
    }

    public function test_create_goal_with_activities(): void {
        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        $this->assertEquals(0, goal_entity::repository()->count());
        $this->assertEquals(0, goal_activity_entity::repository()->count());

        $goal_test_config = goal_generator_config::new([
            'number_of_activities' => 2
        ]);
        $goal = goal_generator::instance()->create_goal($goal_test_config);
        $goal_model_activity_ids = $goal->activities->pluck('id');

        $this->assertInstanceOf(goal_model::class, $goal);

        $this->assertEquals(1, goal_entity::repository()->count());

        $activities_db = goal_activity_entity::repository()->get();
        $this->assertEquals([$goal->id, $goal->id], $activities_db->pluck('goal_id'));
        $this->assertEqualsCanonicalizing($goal_model_activity_ids, $activities_db->pluck('id'));
    }

    public function test_create_goal_for_behat_default(): void {
        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        $this->assertEquals(0, goal_entity::repository()->count());

        $now = time();
        $id_number = 'test-goal-behat-id-number_1';
        $goal = goal_generator::instance()->create_goal_for_behat(['id_number' => $id_number]);

        $this->assertInstanceOf(goal_model::class, $goal);
        $this->assertEquals(1, goal_entity::repository()->count());

        /** @var goal_entity $goal_db */
        $goal_db = goal_entity::repository()->find($goal->id);

        // Assert expected default data.
        $this->assertEquals('Test goal 1', $goal_db->name);
        $this->assertEquals(context_user::instance($user->id)->id, $goal_db->context_id);
        $this->assertNotNull($goal_db->category_id);
        $this->assertEquals($user->id, $goal_db->owner_id);
        $this->assertEquals(null, $goal_db->user_id);
        $this->assertEquals($id_number, $goal_db->id_number);
        $this->assertEquals('{"type":"doc","content":[{"type":"paragraph","attrs":{},"content":[{"type":"text","text":"Test goal description"}]}]}', $goal_db->description);
        $this->assertGreaterThanOrEqual($now, $goal_db->start_date);
        $this->assertGreaterThanOrEqual($now + WEEKSECS, $goal_db->target_date);
        $this->assertEquals(80, $goal_db->target_value);
        $this->assertEquals(72.5, $goal_db->current_value);
        $this->assertGreaterThanOrEqual($now, $goal_db->current_value_updated_at);
        $this->assertGreaterThanOrEqual($now, $goal_db->status_updated_at);
        $this->assertEquals(null, $goal_db->closed_at);
        $this->assertGreaterThanOrEqual($now, $goal_db->created_at);
        $this->assertGreaterThanOrEqual($now, $goal_db->updated_at);

        // Create another one.
        $id_number2 = 'test-goal-behat-id-number_2';
        $goal2 = goal_generator::instance()->create_goal_for_behat(['id_number' => $id_number2]);

        $this->assertInstanceOf(goal_model::class, $goal2);
        $this->assertEquals(2, goal_entity::repository()->count());
        $this->assertEquals($id_number2, $goal2->id_number);
    }

    public function test_create_goal_for_behat_compute_timestamp(): void {
        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        $this->assertEquals(0, goal_entity::repository()->count());

        $datetimeobject = new \DateTime('-2 days');
        $expected_time_val = $datetimeobject->getTimestamp();
        $data = [
            'id_number' => 'test-goal-behat-id-number_1',
            'created_at' => '-2 days'
        ];
        $goal = goal_generator::instance()->create_goal_for_behat($data);
        $goal_db = goal_entity::repository()->find($goal->id);

        // Assert expected default data.
        $this->assertEquals($data['id_number'], $goal_db->id_number);
        $this->assertGreaterThanOrEqual($expected_time_val, $goal_db->created_at);
    }

    public function test_create_goal_activity_for_behat_default() {
        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        $this->assertEquals(0, goal_entity::repository()->count());
        $this->assertEquals(0, goal_activity_entity::repository()->count());

        $id_number = 'test-goal-behat-id-number_1';
        $goal = goal_generator::instance()->create_goal_for_behat(['user_id' => $user->id, 'id_number' => $id_number]);

        $this->assertEquals(1, goal_entity::repository()->count());
        $this->assertEquals(0, goal_activity_entity::repository()->count());

        $data = [
            'id_number' => $id_number,
            'user_id' => $user->id
        ];
        $goal_activity = goal_generator::instance()->create_goal_activity_for_behat($data);

        $this->assertInstanceOf(goal_activity::class, $goal_activity);

        $this->assertEquals(1, goal_entity::repository()->count());
        $this->assertEquals(1, goal_activity_entity::repository()->count());

        $goal_activity_db = goal_activity_entity::repository()->find($goal_activity->id);

        // Assert expected default data.
        $this->assertEquals($goal->id, $goal_activity_db->goal_id);
        $this->assertEquals('Test type', $goal_activity_db->activity_type);
        $this->assertEquals('Test info 1', $goal_activity_db->activity_info);
        $this->assertEquals($user->id, $goal_activity_db->user_id);
    }

    public function test_create_goal_activity_for_behat_compute_timestamp() {
        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        $this->assertEquals(0, goal_entity::repository()->count());
        $this->assertEquals(0, goal_activity_entity::repository()->count());

        $id_number = 'test-goal-behat-id-number_1';
        $goal = goal_generator::instance()->create_goal_for_behat(['user_id' => $user->id, 'id_number' => $id_number]);

        $this->assertEquals(1, goal_entity::repository()->count());
        $this->assertEquals(0, goal_activity_entity::repository()->count());

        $datetimeobject = new \DateTime('-2 days');
        $expected_time_val = $datetimeobject->getTimestamp();
        $data = [
            'id_number' => $id_number,
            'user_id' => $user->id,
            'timestamp' => '-2 days'
        ];
        $goal_activity = goal_generator::instance()->create_goal_activity_for_behat($data);
        $goal_activity_db = goal_activity_entity::repository()->find($goal_activity->id);

        // Assert expected default data.
        $this->assertEquals($goal->id, $goal_activity_db->goal_id);
        $this->assertEquals($user->id, $goal_activity_db->user_id);
        $this->assertGreaterThanOrEqual($expected_time_val, $goal_activity_db->timestamp);
    }

    public function test_create_goal_task_for_behat_default(): void {
        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        $this->assertEquals(0, goal_task_entity::repository()->count());

        $now = time();
        $id_number = 'test-goal-behat-id-number_1';
        $goal = goal_generator::instance()->create_goal_for_behat(['id_number' => $id_number]);
        $goal_task = goal_generator::instance()->create_goal_task_for_behat(['goal_id_number' => $goal->id_number]);

        $this->assertInstanceOf(goal_task_model::class, $goal_task);
        $this->assertEquals(1, goal_task_entity::repository()->count());

        /** @var goal_task_entity $goal_task_db */
        $goal_task_db = goal_task_entity::repository()->find($goal_task->id);

        // Assert expected default data.
        $this->assertEquals($goal->id, $goal_task_db->goal_id);
        $this->assertNotNull($goal_task_db->description);
        $this->assertNull($goal_task_db->completed_at);
        $this->assertGreaterThanOrEqual($now, $goal_task_db->created_at);
        $this->assertGreaterThanOrEqual($now, $goal_task_db->updated_at);

        // Create another one.
        $goal_task2 = goal_generator::instance()->create_goal_task_for_behat(['goal_id_number' => $goal->id_number]);

        $this->assertInstanceOf(goal_task_model::class, $goal_task2);
        $this->assertEquals(2, goal_task_entity::repository()->count());
    }

    public function test_create_goal_task_for_behat_override_defaults(): void {
        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        $this->assertEquals(0, goal_task_entity::repository()->count());

        $now = time();
        $id_number = 'test-goal-behat-id-number_1';
        $goal = goal_generator::instance()->create_goal_for_behat(['id_number' => $id_number]);

        $description = 'This goal task description';
        $created_at = '-2 days';
        $data = [
            'goal_id_number' => $goal->id_number,
            'description' => $description,
            'completed_at' => 1,
            'created_at' => $created_at,
            'updated_at' => $now
        ];
        $goal_task = goal_generator::instance()->create_goal_task_for_behat($data);

        $this->assertInstanceOf(goal_task_model::class, $goal_task);
        $this->assertEquals(1, goal_task_entity::repository()->count());

        /** @var goal_task_entity $goal_task_db */
        $goal_task_db = goal_task_entity::repository()->find($goal_task->id);

        // Assert expected default data.
        $this->assertEquals($goal->id, $goal_task_db->goal_id);
        $config = goal_task_generator_config::new([
            'goal_id' => $goal->id,
            'description' => $description
        ]);
        $this->assertEquals($config->description, $goal_task_db->description);

        $datetimeobject = new \DateTime($created_at);
        $date_created_at = $datetimeobject->getTimestamp();

        $this->assertGreaterThanOrEqual($goal_task_db->created_at, $date_created_at);
        $this->assertGreaterThanOrEqual($now, $goal_task_db->updated_at);
        $this->assertEquals($goal_task_db->updated_at, $goal_task_db->completed_at);
    }

    public function test_create_goal_task_for_behat_resource(): void {
        $course = self::getDataGenerator()->create_course(['shortname' => 'C1']);
        $user = self::getDataGenerator()->create_user();
        self::getDataGenerator()->enrol_user($user->id, $course->id, 'student');

        self::setUser($user);

        $this->assertEquals(0, goal_task_entity::repository()->count());

        $id_number = 'test-goal-behat-id-number_1';
        $goal = goal_generator::instance()->create_goal_for_behat(['id_number' => $id_number]);
        $goal_task = goal_generator::instance()->create_goal_task_for_behat([
            'goal_id_number' => $goal->id_number,
            'resource_id_string' => $course->shortname,
            'resource_type' => course_type::TYPECODE
        ]);

        $this->assertInstanceOf(goal_task_model::class, $goal_task);
        $this->assertEquals(1, goal_task_entity::repository()->count());
        $goal_task->refresh();
        $this->assertNotNull($goal_task->resource);
    }

    public function test_create_goal_task_default(): void {
        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        $this->assertEquals(0, goal_task_entity::repository()->count());

        $now = time();
        $goal = goal_generator::instance()->create_goal();
        $config = goal_task_generator_config::new([
            'goal_id' => $goal->id,
            'description' => 'This goal task description'
        ]);
        $goal_task = goal_generator::instance()->create_goal_task($config);

        $this->assertInstanceOf(goal_task_model::class, $goal_task);
        $this->assertEquals(1, goal_task_entity::repository()->count());

        /** @var goal_task_entity $goal_task_db */
        $goal_task_db = goal_task_entity::repository()->find($goal_task->id);

        // Assert expected default data.
        $this->assertEquals($goal->id, $goal_task_db->goal_id);
        $this->assertNotNull($goal_task_db->description);
        $this->assertNull($goal_task_db->completed_at);
        $this->assertGreaterThanOrEqual($now, $goal_task_db->created_at);
        $this->assertGreaterThanOrEqual($now, $goal_task_db->updated_at);
    }

    public function test_create_goal_task_with_resource(): void {
        $course = self::getDataGenerator()->create_course(['shortname' => 'C1']);
        $user = self::getDataGenerator()->create_user();
        self::getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        self::setUser($user);

        $this->assertEquals(0, goal_task_entity::repository()->count());

        $goal = goal_generator::instance()->create_goal();
        $config = goal_task_generator_config::new([
            'goal_id' => $goal->id,
            'resource_id' => $course->id,
            'resource_type' => course_type::TYPECODE

        ]);
        $goal_task = goal_generator::instance()->create_goal_task($config);
        goal_generator::instance()->create_goal_task_resource($goal_task, $config);
        $goal_task->refresh(true);

        $this->assertInstanceOf(goal_task_model::class, $goal_task);
        $this->assertEquals(1, goal_task_entity::repository()->count());
        $this->assertNotNull($goal_task->resource);
    }
}