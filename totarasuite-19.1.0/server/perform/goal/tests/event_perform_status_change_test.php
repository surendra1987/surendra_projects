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
 * @author Scott Davies <scott.davies@totara.com>
 * @package perform_goal
 */

use core\event\base;
use perform_goal\entity\perform_status_change as perform_status_change_entity;
use perform_goal\model\perform_status_change as perform_status_change_model;
use perform_goal\event\perform_goal_perform_status_changed;

require_once(__DIR__.'/perform_goal_perform_status_change_testcase.php');

/**
 * Unit tests for the perform_goal_perform_status_changed event.
 */
class perform_goal_event_perform_status_change_test extends perform_goal_perform_status_change_testcase {

    public function test_event_triggered_by_model(): void {
        $data = $this->create_activity_data();

        self::setUser($data->manager_user);
        $participant_instance_id = $data->manager_participant_instance1->id;

        // Operate
        $sink = $this->redirectEvents();
        $perform_status_change_model = perform_status_change_model::create(
            $participant_instance_id,
            $data->section_element->id,
            'not_started',
            25.0,
            $data->goal1->id
        );

        $events = $sink->get_events();
        $events = array_filter($events, function (base $event) {
            return $event instanceof perform_goal_perform_status_changed;
        });
        $event = array_pop($events);

        $this->assertEquals($data->goal1->context_id, $event->get_context()->id);
        $this->assertSame($perform_status_change_model->id, $event->objectid);
        $this->assertSame('c', $event->crud);
        $this->assertSame($event::LEVEL_OTHER, $event->edulevel);
        $this->assertEquals($data->manager_user->id, $event->userid);
        $this->assertSame(perform_status_change_entity::TABLE, $event->objecttable);

        $expected_description = "The user with id '{$perform_status_change_model->status_changer_user_id}' has changed the status to " .
            "'{$perform_status_change_model->status}' in the performance activity with id '{$perform_status_change_model->activity_id}', " .
            "for the goal with id '{$perform_status_change_model->goal_id}'.";
        $this->assertSame($expected_description, $event->get_description());
        $this->assertEventContextNotUsed($event);

        // We could also have tested manually triggering the event - for reference:
        // $event = perform_goal_perform_status_changed::create_from_instance($perform_status_change_model->get_entity_copy());
        // $event->trigger();
    }
}
