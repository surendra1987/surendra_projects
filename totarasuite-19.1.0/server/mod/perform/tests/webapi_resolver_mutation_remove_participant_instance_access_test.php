<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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

use totara_webapi\phpunit\webapi_phpunit_helper;
use mod_perform\testing\generator as perform_generator;
use core\entity\user;
use mod_perform\models\activity\participant_instance as participant_instance_model;
use mod_perform\entity\activity\participant_instance as participant_instance_entity;
use totara_webapi\client_aware_exception;
use mod_perform\state\participant_instance\closed as availability_closed;

/**
 * Unit tests for the 'mod_perform_remove_participant_instance_access' mutation operation.
 */
class mod_perform_webapi_resolver_mutation_remove_participant_instance_access_test extends \core_phpunit\testcase {
    private const MUTATION = 'mod_perform_remove_participant_instance_access';

    private ?participant_instance_entity $test_participant_instance = null;

    use webapi_phpunit_helper;

    /**
     * Create a test participant_instance.
     * @return void
     */
    protected function setUp(): void {
        self::setAdminUser();
        $current_user = user::logged_in();

        $subject = self::getDataGenerator()->create_user();
        $generator = perform_generator::instance();
        $subject_instance = $generator->create_subject_instance([
            'subject_is_participating' => true,
            'subject_user_id' => $subject->id,
            'other_participant_id' => $current_user->id,
        ]);
        $this->test_participant_instance = $subject_instance->participant_instances()
            ->where('participant_id', $current_user->id)
            ->one();
    }

    public function test_fails_when_no_participant_instance(): void {
        $bad_pi_id = $this->test_participant_instance->id + 1;
        $args = [
            'input' => [
                'participant_instance_id' => $bad_pi_id, // record doesn't exist
                'status' => 1
            ]
        ];
        self::expectException(client_aware_exception::class);
        self::expectExceptionMessage('Invalid parameters or missing permissions');

        $this->resolve_graphql_mutation(self::MUTATION, $args);
    }

    public function test_fails_when_open(): void {
        $pi_model = participant_instance_model::load_by_id($this->test_participant_instance->id);
        self::assertFalse($pi_model->is_closed);
        self::assertEquals(0, $pi_model->access_removed);

        $args = [
            'input' => [
                'participant_instance_id' => $this->test_participant_instance->id,
                'status' => 1
            ]
        ];
        self::expectException(client_aware_exception::class);
        self::expectExceptionMessage('Action not possible on an open participant instance.');

        $this->resolve_graphql_mutation(self::MUTATION, $args);
    }

    public function test_succeeds_when_closed(): void {
        $this->test_participant_instance->availability = availability_closed::get_code();
        $this->test_participant_instance->update();

        $pi_model = participant_instance_model::load_by_id($this->test_participant_instance->id);
        self::assertTrue($pi_model->is_closed);
        self::assertEquals(0, $pi_model->access_removed);

        $args = [
            'input' => [
                'participant_instance_id' => $this->test_participant_instance->id,
                'status' => 1
            ]
        ];

        $result = $this->resolve_graphql_mutation(self::MUTATION, $args);
        $pi_model->refresh();
        self::assertTrue(true, $result['success']);
        self::assertEquals(1, $pi_model->access_removed);
    }
}
