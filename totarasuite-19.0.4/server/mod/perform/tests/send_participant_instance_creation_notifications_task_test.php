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
 * @package mod_perform
 */

use core\collection;
use core_phpunit\testcase;
use mod_perform\testing\generator;
use mod_perform\task\send_participant_instance_creation_notifications_task;
use mod_perform\entity\activity\participant_instance as participant_instance_entity;

/**
 * @group perform
 */
class mod_perform_send_participant_instance_creation_notifications_task_test extends testcase {
    public function test_execute(): void {
        self::setAdminUser();
        $perform_generator = generator::instance();
        $core_generator = self::getDataGenerator();
        $activity = $perform_generator->create_activity_in_container();
        $user1_id = $core_generator->create_user()->id;
        $other_participant1_id = $core_generator->create_user()->id;
        $si_data = [
            'activity_id' => $activity->id,
            'subject_user_id' => $user1_id,
            'other_participant_id' => $other_participant1_id,
            'subject_is_participating' => true,
            'include_questions' => false
        ];
        $perform_generator->create_subject_instance($si_data);

        // Check some participant instances were created.
        $participant_instance_repo = participant_instance_entity::repository();
        self::assertEquals(2, $participant_instance_repo->count());

        // Operate #1 - execute the task.
        $logs = collection::new([]);
        $instance_ids = $participant_instance_repo->get()->pluck('id');
        $task = send_participant_instance_creation_notifications_task::create_for_new_participants($instance_ids);
        $task->set_logger(
            function (string $message) use ($logs): void {
                $logs->append($message);
            }
        )
        ->execute();

        // Check no exceptions were logged.
        self::assertEmpty($logs);

        unset($task);
        // Except no exceptions from this either (no logger configured).
        (send_participant_instance_creation_notifications_task::create_for_new_participants($instance_ids))->execute();
    }
}
