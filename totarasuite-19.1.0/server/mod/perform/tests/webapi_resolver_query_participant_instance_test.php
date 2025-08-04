<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2020 onwards Totara Learning Solutions LTD
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Jaron Steenson <jaron.steenson@totaralearning.com>
 * @package mod_perform
 */

use mod_perform\models\activity\activity_setting;
use mod_perform\models\activity\participant_instance;
use totara_core\advanced_feature;
use totara_webapi\phpunit\webapi_phpunit_helper;
use mod_perform\entity\activity\participant_section as participant_section_entity;
use mod_perform\state\participant_section\complete as participant_section_complete;
use mod_perform\models\activity\participant_instance as participant_instance_model;

require_once(__DIR__ . '/subject_instance_testcase.php');

/**
 * @group perform
 */
class mod_perform_webapi_resolver_query_participant_instance_test extends mod_perform_subject_instance_testcase {
    private const QUERY = 'mod_perform_participant_instance';

    use webapi_phpunit_helper;

    public function test_query_successful(): void {
        self::setAdminUser();

        /** @var participant_instance $subject_participant_instance */
        $subject_participant_instance = self::$about_user_and_participating->participant_instances->find(
            function (participant_instance $pi) {
                return (int) $pi->participant_id === (int) self::$about_user_and_participating->subject_user->id;
            }
        );

        $user_fullname = self::$about_user_and_participating->subject_user->fullname;

        $args = [
            'participant_instance_id' => $subject_participant_instance->get_id()
        ];

        $result = $this->parsed_graphql_operation(self::QUERY, $args);

        $this->assert_webapi_operation_successful($result);
        $actual = $this->get_webapi_operation_data($result);

        $expected = [
            'participant' => [
                'fullname' => $user_fullname,
            ],
            'subject_instance' => [
                'subject_user' => [
                    'fullname' => $user_fullname,
                ],
            ],
            'core_relationship' => [
                'name' => 'Subject',
            ],
        ];

        self::assertEquals($expected, $this->strip_expected_dates($actual));
    }

    public function test_get_as_participation_manager(): void {
        /** @var participant_instance $subject_participant_instance */
        $subject_participant_instance = self::$about_user_and_participating->participant_instances->find(
            function (participant_instance $pi) {
                return (int) $pi->participant_id === (int) self::$about_user_and_participating->subject_user->id;
            }
        );

        $args = [
            'participant_instance_id' => $subject_participant_instance->get_id()
        ];

        $manager = self::getDataGenerator()->create_user();
        $employee = self::$about_user_but_not_participating->subject_user;

        self::setUser($manager);

        $context = self::create_webapi_context(self::QUERY);
        $context->set_relevant_context(self::$about_user_and_participating->get_context());

        $returned_participant_instance = $this->resolve_graphql_query(self::QUERY, $args);
        self::assertNull($returned_participant_instance);

        $this->setup_manager_employee_job_assignment($manager, $employee);

        $returned_participant_instance = $this->resolve_graphql_query(self::QUERY, $args);
        $this->assertEquals($subject_participant_instance->id, $returned_participant_instance->id);
    }

    public function test_get_as_participant(): void {
        /** @var participant_instance $subject_participant_instance */
        $subject_participant_instance = self::$about_user_and_participating->participant_instances->find(
            function (participant_instance $pi) {
                return (int) $pi->participant_id === (int) self::$about_user_and_participating->subject_user->id;
            }
        );

        $args = [
            'participant_instance_id' => $subject_participant_instance->get_id()
        ];

        self::setUser($subject_participant_instance->participant_id);

        $returned_participant_instance = $this->resolve_graphql_query(self::QUERY, $args);
        $this->assertEquals($subject_participant_instance->id, $returned_participant_instance->id);
    }

    public function test_failed_ajax_query(): void {
        /** @var participant_instance $subject_participant_instance */
        $subject_participant_instance = self::$about_user_and_participating->participant_instances->find(
            function (participant_instance $pi) {
                return (int) $pi->participant_id === (int) self::$about_user_and_participating->subject_user->id;
            }
        );

        $args = [
            'participant_instance_id' => $subject_participant_instance->get_id()
        ];

        $feature = 'performance_activities';
        advanced_feature::disable($feature);
        $result = $this->parsed_graphql_operation(self::QUERY, $args);
        $this->assert_webapi_operation_failed($result, 'Feature performance_activities is not available.');
        advanced_feature::enable($feature);

        $result = $this->parsed_graphql_operation(self::QUERY, []);
        $this->assert_webapi_operation_failed($result, 'participant_instance_id');

        self::setUser();
        $result = $this->parsed_graphql_operation(self::QUERY, $args);
        $this->assert_webapi_operation_failed($result, 'not logged in');
    }

    public function test_is_closed_false(): void {
        static::setAdminUser();

        /** @var participant_instance $subject_participant_instance */
        $subject_participant_instance = static::$about_user_and_participating->participant_instances->find(
            function (participant_instance $pi) {
                return (int)$pi->participant_id === (int)static::$about_user_and_participating->subject_user->id;
            }
        );
        $activity = $subject_participant_instance->subject_instance->activity;
        $activity->settings->update([
            activity_setting::CLOSE_ON_COMPLETION => false,
            activity_setting::CLOSE_ON_SECTION_SUBMISSION => false,
            activity_setting::MANUAL_CLOSE => true
        ]);

        $participant_instance = participant_instance::load_by_id($subject_participant_instance->get_id());
        foreach ($participant_instance->get_participant_sections() as $participant_section) {
            $participant_section_entity = participant_section_entity::repository()->find($participant_section->id);
            // closure_conditions verify_required_questions_completed requires this.
            $participant_section_entity->progress = participant_section_complete::get_code();
            $participant_section_entity->save();
            $participant_section->refresh();
        }

        $args = [
            'participant_instance_id' => $subject_participant_instance->get_id()
        ];
        static::setUser($subject_participant_instance->participant_id);
        $participant_instance = $this->resolve_graphql_query(self::QUERY, $args);

        static::assertFalse($participant_instance->is_closed);
        static::assertEquals($participant_instance->is_closed, $subject_participant_instance->is_closed);
        static::assertTrue($participant_instance->can_be_manually_closed_by_participant);
        static::assertEquals(
            $participant_instance->can_be_manually_closed_by_participant,
            $subject_participant_instance->can_be_manually_closed_by_participant
        );
    }

    public function test_is_closed_true(): void {
        static::setAdminUser();

        /** @var participant_instance $subject_participant_instance */
        $subject_participant_instance = static::$about_user_and_participating->participant_instances->find(
            function (participant_instance $pi) {
                return (int)$pi->participant_id === (int)static::$about_user_and_participating->subject_user->id;
            }
        );
        $activity = $subject_participant_instance->subject_instance->activity;
        $activity->settings->update([
            activity_setting::CLOSE_ON_COMPLETION => false,
            activity_setting::CLOSE_ON_SECTION_SUBMISSION => false,
            activity_setting::MANUAL_CLOSE => true
        ]);

        $args = [
            'participant_instance_id' => $subject_participant_instance->get_id()
        ];
        static::setUser($subject_participant_instance->participant_id);

        $subject_participant_instance->manually_close();
        $participant_instance = $this->resolve_graphql_query(self::QUERY, $args);

        static::assertTrue($participant_instance->is_closed);
        static::assertEquals($participant_instance->is_closed, $subject_participant_instance->is_closed);
        static::assertFalse($participant_instance->can_be_manually_closed_by_participant);
        static::assertEquals(
            $participant_instance->can_be_manually_closed_by_participant,
            $subject_participant_instance->can_be_manually_closed_by_participant
        );
    }

    public function test_for_access_removed(): void {
        self::setAdminUser();
        $subject_participant_instance = self::$about_user_and_participating->participant_instances->find(
            function (participant_instance $pi) {
                return (int)$pi->participant_id === (int)self::$about_user_and_participating->subject_user->id;
            }
        );
        $pi_model = participant_instance_model::load_by_id($subject_participant_instance->id);
        $args = [
            'participant_instance_id' => $subject_participant_instance->get_id()
        ];

        // Make a request as the participant.
        $this->setUser($subject_participant_instance->participant_id);
        $result = $this->parsed_graphql_operation(self::QUERY, $args);
        $this->assert_webapi_operation_successful($result);

        // The participant now has their access removed to their instance. We expect no results to be returned.
        $pi_model->manually_close();
        $pi_model->set_access_removed(true);
        $result = $this->parsed_graphql_operation(self::QUERY, $args);
        $this->assertEmpty($result[0]);
    }
}
