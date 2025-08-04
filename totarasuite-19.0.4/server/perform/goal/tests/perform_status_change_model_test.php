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

use mod_perform\constants;
use perform_goal\entity\perform_status_change as perform_status_change_entity;
use perform_goal\model\perform_status_change as perform_status_change_model;

require_once(__DIR__.'/perform_goal_perform_status_change_testcase.php');

/**
 * Unit tests for the perform_status_change model class.
 */
class perform_goal_perform_status_change_model_test extends perform_goal_perform_status_change_testcase {

    public function test_with_valid_inputs(): void {
        $data = $this->create_activity_data();
        $now = time();
        self::assertEquals(0, perform_status_change_entity::repository()->count());

        self::setUser($data->manager_user);
        perform_status_change_model::create(
            $data->manager_participant_instance1->id,
            $data->section_element->id,
            'not_started',
            25.0,
            $data->goal1->id
        );
        self::assertEquals(1, perform_status_change_entity::repository()->count());

        // Operate
        $actual = perform_status_change_entity::repository()->one(true)->to_array();

        self::assertGreaterThanOrEqual($now, $actual['created_at']);
        unset($actual['id'], $actual['created_at']);
        $expected_goal_id = $data->goal1->id;
        self::assertEqualsCanonicalizing([
            'user_id' => $data->subject_user->id,
            'current_value' => $data->current_value,
            'goal_id' => $expected_goal_id,
            'activity_id' => $data->activity->id,
            'subject_instance_id' => $data->subject_instance1->id,
            'status_changer_user_id' => $data->manager_participant_instance1->participant_id,
            'status_changer_relationship_id' => $data->manager_participant_instance1->core_relationship_id,
            'status' => $data->status
        ], $actual);
    }

    public function test_create_with_invalid_existing_status(): void {
        $data = $this->create_activity_data();
        self::setUser($data->manager_user);
        perform_status_change_model::create(
            $data->manager_participant_instance1->id,
            $data->section_element->id,
            'not_started',
            25.0,
            $data->goal1->id
        );

        $subject_instance_id = $data->manager_participant_instance1->subject_instance_id;

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage(
            "A status has already been saved for subject instance {$subject_instance_id},"
            . " goal ID {$data->goal1->id}."
        );

        perform_status_change_model::create(
            $data->manager_participant_instance1->id,
            $data->section_element->id,
            'not_started',
            25.0,
            $data->goal1->id
        );
    }

    public function test_create_with_invalid_status_value(): void {
        $data = $this->create_activity_data();

        $bad_status = 'I do not exist';
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessageMatches(
            "/The specified status value '{$bad_status}' is not valid for the goal with ID {$data->goal1->id}/"
        );

        self::setUser($data->manager_user);
        perform_status_change_model::create(
            $data->manager_participant_instance1->id,
            $data->section_element->id,
            $bad_status, // bad status
            25.0,
            $data->goal1->id
        );
    }

    public function test_create_for_goal_that_does_not_exist(): void {
        // Create a test perform_status_change record.
        $data = $this->create_activity_data();
        self::assertEquals(0, perform_status_change_entity::repository()->count());
        $goal_id_non_existent = 999 + time();

        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessage("Could not update goal status for goal ID {$goal_id_non_existent}");

        // Operate.
        self::setUser($data->manager_user);
        perform_status_change_model::create(
            $data->manager_participant_instance1->id,
            $data->section_element->id,
            'not_started',
            25.0,
            $goal_id_non_existent // should not exist
        );
    }

    public function test_get_status_changer_methods(): void {
        $data = $this->create_activity_data();
        $now = time();

        self::setUser($data->manager_user);
        perform_status_change_model::create(
            $data->manager_participant_instance1->id,
            $data->section_element->id,
            'not_started',
            25.0,
            $data->goal1->id
        );

        /** @var perform_status_change_entity $status_change_entity */
        $status_change_entity = perform_status_change_entity::repository()->one(true);
        $status_change_model = perform_status_change_model::load_by_id($status_change_entity->id);

        self::assertEquals('Manager', $status_change_model->status_changer_role);
        self::assertEquals(constants::RELATIONSHIP_MANAGER, $status_change_model->status_changer_relationship->idnumber);

        $status_change_entity->status_changer_relationship_id = $data->subject_participant_instance1->core_relationship_id;
        $status_change_entity->status_changer_user_id = $data->subject_user->id;
        $status_change_entity->save();

        $status_change_model = perform_status_change_model::load_by_id($status_change_entity->id);
        self::assertEquals('Subject', $status_change_model->status_changer_role);
        self::assertEquals(constants::RELATIONSHIP_SUBJECT, $status_change_model->status_changer_relationship->idnumber);

        self::setUser($data->subject_user);

        $status_change_model = perform_status_change_model::load_by_id($status_change_entity->id);
        self::assertEquals('You', $status_change_model->status_changer_role);
        self::assertEquals(constants::RELATIONSHIP_SUBJECT, $status_change_model->status_changer_relationship->idnumber);
    }
}
