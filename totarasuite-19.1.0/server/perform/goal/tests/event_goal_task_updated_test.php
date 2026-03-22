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
 * @author Murali Nair <murali.nair@totaralearning.com>
 * @package perform_goal
 */

use core\event\base;
use core_phpunit\testcase;
use perform_goal\event\goal_task_updated;
use perform_goal\entity\goal_task as goal_task_entity;
use perform_goal\model\goal_task;
use perform_goal\model\goal_task_type\course_type;
use perform_goal\testing\generator;
use perform_goal\testing\goal_generator_config;

/**
 * Unit tests for the goal_task_updated event.
 *
 * @group perform_goal
 * @group perform_goal_task
 */
class perform_goal_event_goal_task_updated_test extends testcase {
    /**
     * Data provider for test_event().
     */
    public static function td_event(): array {
        return [
            'owner is subject' => [false],
            'owner is not subject' => [true]
        ];
    }

    /**
     * @dataProvider td_event
     */
    public function test_event(
        bool $different_subject
    ): void {
        $task = self::create_test_goal_task($different_subject);
        self::assertNotEmpty($task->description);
        self::assertNull($task->resource);

        $goal = $task->goal;
        $owner = $goal->owner;
        $this->setUser($owner);

        $sink = $this->redirectEvents();
        $task->update('new edited description', course_type::create(123));
        $sink->close();

        $events = array_filter(
            $sink->get_events(),
            fn(base $event): bool => $event instanceof goal_task_updated
        );
        self::assertCount(1, $events);

        $event = $events[0];
        $this->assertEventContextNotUsed($event);
        $this->assertEquals($goal->context_id, $event->contextid);
        $this->assertEquals($task->id, $event->objectid);
        $this->assertEquals('u', $event->crud);
        $this->assertEquals(goal_task_updated::LEVEL_OTHER, $event->edulevel);
        $this->assertEquals(goal_task_entity::TABLE, $event->objecttable);

        $desc = "The user with id '{$owner->id}' has updated a goal task with id '{$task->id}'";
        if ($different_subject) {
            $desc .= " for the user with id '{$goal->user->id}'";
        }

        $this->assertEquals($desc, $event->get_description());
    }

    /**
     * Generates test data.
     *
     * @param bool $different_subject if true, indicates the owner of the goal
     *        is different from the subject of the goal.
     *
     * @return goal_task the created goal task.
     */
    private static function create_test_goal_task(
        bool $different_subject = false
    ): goal_task {
        self::setAdminUser();

        $base_generator = self::getDataGenerator();
        $owner_id = $base_generator->create_user()->id;
        $subject_id = $different_subject
            ? $base_generator->create_user()->id
            : $owner_id;

        $cfg = goal_generator_config::new(
            [
                'number_of_tasks' => 1,
                'owner_id' => $owner_id,
                'user_id' => $subject_id
            ]
        );

        return generator::instance()->create_goal($cfg)->tasks->first();
    }
}