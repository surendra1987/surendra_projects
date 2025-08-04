<?php
/**
 * This file is part of Totara Perform
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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package perform_goal
 */

use core_phpunit\testcase;
use perform_goal\entity\goal_task as goal_task_entity;
use perform_goal\model\goal_task as goal_task_model;
use perform_goal\model\goal_task_resource;
use perform_goal\model\goal_task_type\type as goal_task_type;
use perform_goal\model\goal_task_type\course_type;

class perform_goal_goal_task_model_test extends testcase {

    use perform_goal\testing\traits\goal_task_trait;

    public function test_create() {
        [$goal, ] = $this->setup_env();
        $description = self::jsondoc_description(
            '<h1>This is a goal <strong>task</strong> description</h1>'
        );

        $goal_task = goal_task_model::create($goal->id, $description);

        self::assertInstanceOf(goal_task_model::class, $goal_task);
        self::assertNotEmpty($goal_task->id);
        self::assertEquals($goal_task->goal_id, $goal->id, 'wrong goal id');
        self::assertNotEmpty($goal_task->description);
        self::assertNotEmpty($goal_task->created_at);
        self::assertNotEmpty($goal_task->updated_at);
        self::assertEmpty($goal_task->completed_at);
    }

    /**
     * Data provider for test_update().
     */
    public static function td_update(): array {
        $desc1 = self::jsondoc_description(
            '<h1>This is a goal <strong>task</strong> description</h1>'
        );

        $desc2 = self::jsondoc_description(
            '<h1>This is a goal <strong>task</strong> description updated</h1>'
        );

        $rsc1 = course_type::create();
        $rsc2 = course_type::create(123);

        return [
            'no desc, no rsc => desc1, rsc1' => [null, $desc1, null, $rsc1],
            'no desc, no rsc => no desc, no rsc' => [null, null, null, null],
            'desc1, rsc1 => no desc, no rsc' => [$desc1, null, $rsc1, null],
            'desc1, rsc1 => desc2, no rsc' => [$desc1, $desc2, $rsc1, null],
            'desc1, rsc1 => desc2, rsc2' => [$desc1, $desc2, $rsc1, $rsc2],
            'desc1, rsc1 => no desc, rsc2' => [$desc1, null, $rsc1, $rsc2]
        ];
    }

    /**
     * @dataProvider td_update
     */
    public function test_update(
        ?string $old_desc,
        ?string $new_desc,
        ?goal_task_type $old_resource,
        ?goal_task_type $new_resource
    ) {
        [$goal, ] = $this->setup_env();

        $goal_task = goal_task_model::create($goal->id, $old_desc);

        self::assertInstanceOf(goal_task_model::class, $goal_task);
        self::assertNotEmpty($goal_task->id);
        self::assertEquals($goal_task->goal_id, $goal->id);
        self::assertEquals($old_desc, $goal_task->description);
        self::assertNotEmpty($goal_task->created_at);
        self::assertNotEmpty($goal_task->updated_at);
        self::assertEmpty($goal_task->completed_at);

        if ($old_resource) {
            goal_task_resource::create($goal_task, $old_resource);
            $resource_model = $goal_task->refresh(true)->resource;
            self::assertEquals($goal_task->id, $resource_model->task_id);
            self::assertEquals($old_resource, $resource_model->type);
        } else {
            self::assertNull($goal_task->resource);
        }

        $updated = $goal_task->update($new_desc, $new_resource);
        self::assertEquals($updated->goal_id, $goal->id);
        self::assertEquals($new_desc, $updated->description);

        if ($new_resource) {
            $resource_model = $updated->resource;
            self::assertEquals($goal_task->id, $resource_model->task_id);
            self::assertEquals($new_resource, $resource_model->type);
        } else {
            self::assertNull($goal_task->resource);
        }
    }

    public function test_delete() {
        [$goal, ] = $this->setup_env();
        $description1 = self::jsondoc_description(
            '<h1>This is a goal <strong>task 1</strong> description</h1>'
        );

        $goal_task1 = goal_task_model::create($goal->id, $description1);

        $description2 = self::jsondoc_description(
            '<h1>This is a goal <strong>task 2</strong> description</h1>'
        );

        $goal_task2 = goal_task_model::create($goal->id, $description2);

        self::assertInstanceOf(goal_task_model::class, $goal_task1);
        self::assertInstanceOf(goal_task_model::class, $goal_task2);
        self::assertNotEmpty($goal_task1->id);
        self::assertEquals($goal_task1->goal_id, $goal->id, 'wrong goal id');
        self::assertNotEmpty($goal_task1->description);
        self::assertNotEquals($goal_task1->id, $goal_task2->id);

        // Delete the task 1.
        $goal_task1->delete();

        // Test after deletion
        self::assertEquals(1, (goal_task_entity::repository())->count());
    }

    public function test_validate_goal() {
        $description = self::jsondoc_description(
            '<h1>This is a goal <strong>task</strong> description</h1>'
        );

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage("Goal with id 9911 does not exists");
        goal_task_model::create(9911, $description);
    }

    public function test_parent_goal_updated_timestamp(): void {
        [$goal, ] = $this->setup_env();
        $timestamp = $goal->created_at - MINSECS;
        $desc = '<h1>This is a goal <strong>task</strong> description</h1>';

        // Check if parent timestamp is updated upon task creation.
        $goal->get_entity_copy()
            ->set_attribute('created_at', $timestamp)
            ->set_attribute('updated_at', $timestamp)
            ->save()
            ->refresh();

        $task = goal_task_model::create(
            $goal->id, self::jsondoc_description($desc)
        );
        self::assertGreaterThan($timestamp, $goal->refresh()->updated_at);

        // Check if parent timestamp is updated upon task updates.
        $goal->get_entity_copy()
            ->set_attribute('created_at', $timestamp)
            ->set_attribute('updated_at', $timestamp)
            ->save()
            ->refresh();

        $task = $task->update(
            self::jsondoc_description("$desc (updated)"), null
        );
        self::assertGreaterThan($timestamp, $goal->refresh()->updated_at);

        // Check if parent timestamp is updated upon task status updates.
        $goal->get_entity_copy()
            ->set_attribute('created_at', $timestamp)
            ->set_attribute('updated_at', $timestamp)
            ->save()
            ->refresh();

        $task = $task->set_completed(true);
        self::assertGreaterThan($timestamp, $goal->refresh()->updated_at);

        // Check if parent timestamp is updated upon task deletes.
        $goal->get_entity_copy()
            ->set_attribute('created_at', $timestamp)
            ->set_attribute('updated_at', $timestamp)
            ->save();

        $task->delete();
        self::assertGreaterThan($timestamp, $goal->refresh()->updated_at);
    }
}
