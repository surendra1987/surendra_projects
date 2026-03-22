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
 * @author Scott Davies <scott.davies@totara.com>
 * @package perform_goal
 */

use core_phpunit\testcase;
use perform_goal\entity\goal_task as goal_task_entity;
use perform_goal\event\goal_task_progress_changed;
use perform_goal\testing\goal_generator_config;
use core\event\base;
use perform_goal\testing\generator as goal_generator;
use perform_goal\model\target_type\date as target_type_date;
use perform_goal\settings_helper;

/**
 * Unit tests for the goal_task_progress_changed event.
 */
class perform_goal_event_goal_task_progress_changed_test  extends testcase {

    public function test_event_triggered_by_model_goal_with_same_subject_user(): void {
        settings_helper::enable_perform_goals();
        $owner = self::getDataGenerator()->create_user();
        $this->setUser($owner);
        $goal_config = goal_generator_config::new(['number_of_tasks' => 2, 'user_id' => $owner->id,
            'owner_id' => $owner->id]);
        $goal = goal_generator::instance()->create_goal($goal_config);

        $goal_task = $goal->get_tasks()->first();
        $sink = $this->redirectEvents();

        // Operate
        $goal_task->set_completed(1);

        $events = $sink->get_events();get_string('event_goal_task_progress_changed', 'perform_goal');
        $events = array_filter($events, function (base $event) {
            return $event instanceof goal_task_progress_changed;
        });
        $event = array_pop($events);

        $this->assertEquals($goal->get_context()->id, $event->get_context()->id);
        $this->assertSame($goal_task->id, $event->objectid);

        $this->assertSame('u', $event->crud);
        $this->assertSame($event::LEVEL_OTHER, $event->edulevel);
        $this->assertSame(goal_task_entity::TABLE, $event->objecttable);

        $expected_description = "The user with id '{$owner->id}' has updated the goal task completion to 1 in the goal task with id '{$goal_task->id}'";
        $this->assertSame($expected_description, $event->get_description());
        $this->assertEventContextNotUsed($event);
        $this->assertSame(get_string('event_goal_task_progress_changed', 'perform_goal'), $event->get_name());
    }

    public function test_event_triggered_by_model_goal_with_different_subject_user(): void {
        global $DB;
        settings_helper::enable_perform_goals();
        // Create some test users.

        $owner = self::getDataGenerator()->create_user();
        $subject_user = self::getDataGenerator()->create_user();
        $role_staff_manager = $DB->get_record('role', ['shortname' => 'staffmanager']);
        role_assign($role_staff_manager->id, $owner->id, context_system::instance()->id);
        $this->setUser($owner);

        $goal_config = goal_generator_config::new(['user_id' => $subject_user->id,
            'owner_id' => $owner->id,
            'target_type' => target_type_date::get_type(),
            'number_of_tasks' => 2
        ]);
        $goal = goal_generator::instance()->create_goal($goal_config);

        $sink = $this->redirectEvents();
        $goal_task = $goal->get_tasks()->first();
        // Operate
        $goal_task->set_completed(1); // first event, set to complete

        $goal_task->set_completed(0); // second event, set to incomplete

        $events = $sink->get_events();
        $events = array_filter($events, function (base $event) {
            return $event instanceof goal_task_progress_changed;
        });

        // Check the second event.
        $event = array_pop($events);
        $this->assertSame($goal_task->id, $event->objectid);
        $expected_description = "The user with id '{$owner->id}' has updated the goal task completion to 0 in the goal task " .
            "with id '{$goal_task->id}' for the user with id '{$goal_task->goal->user->id}'";
        $this->assertSame($expected_description, $event->get_description());

        // Check the first event.
        $event = array_pop($events);
        $expected_description = "The user with id '{$owner->id}' has updated the goal task completion to 1 in the goal task " .
            "with id '{$goal_task->id}' for the user with id '{$goal_task->goal->user->id}'";
        $this->assertSame($expected_description, $event->get_description());
    }
}
