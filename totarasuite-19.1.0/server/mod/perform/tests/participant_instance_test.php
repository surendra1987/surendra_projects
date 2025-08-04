<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2021 onwards Totara Learning Solutions LTD
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
 * @author Murali Nair <murali.nair@totaralearning.com>
 * @package mod_perform
 * @category test
 */

use core_phpunit\testcase;
use core\collection;
use core\entity\user;
use mod_perform\constants;
use mod_perform\entity\activity\element_response as element_response_entity;
use mod_perform\entity\activity\participant_instance as participant_instance_entity;
use mod_perform\entity\activity\section_element as section_element_entity;
use mod_perform\entity\activity\subject_instance as subject_instance_entity;
use mod_perform\event\participant_instance_access_changed;
use mod_perform\models\activity\activity as activity_model;
use mod_perform\models\activity\activity_setting;
use mod_perform\models\activity\helpers\participant_instance_helper;
use mod_perform\models\activity\participant_instance;
use mod_perform\models\activity\participant_source;
use mod_perform\models\activity\section_element;
use mod_perform\models\activity\subject_instance as subject_instance_model;
use mod_perform\models\response\helpers\manual_closure\closure_evaluation_result;
use mod_perform\models\response\section_element_response;
use mod_perform\state\participant_instance\availability_not_applicable as pi_availability_not_applicable;
use mod_perform\state\participant_instance\closed as pi_availability_closed;
use mod_perform\state\participant_section\complete as section_progress_complete;
use mod_perform\testing\generator as perform_generator;
use totara_core\relationship\relationship;

/**
 * @group perform
 */
class mod_perform_participant_instance_test extends testcase {
    public function test_get_activity_roles(): void {
        $sub_r = constants::RELATIONSHIP_SUBJECT;
        $mgr_r = constants::RELATIONSHIP_MANAGER;
        $appr_r = constants::RELATIONSHIP_APPRAISER;
        $peer_r = constants::RELATIONSHIP_PEER;
        $mtr_r = constants::RELATIONSHIP_MENTOR;
        $rwr_r = constants::RELATIONSHIP_REVIEWER;

        [$uid1, $uid2, $uid3, $mgr_uid, $appr_uid] = $this->create_users(5)->pluck('id');

        $subject_instance_details = [
            'relationships_can_view' => '',
            'subject_is_participating' => false,
            'include_questions' => false
        ];

        $this->create_participant_instances(
            $uid1,
            [
                $mgr_uid => [$mgr_r, $mtr_r],
                $appr_uid => [$appr_r],
                $uid2 => [$rwr_r]
            ],
            $subject_instance_details
        );

        $this->create_participant_instances(
            $uid2,
            [
                $mgr_uid => [$rwr_r],
                $appr_uid => [$appr_r],
                $uid1 => [$peer_r]
            ],
            $subject_instance_details
        );

        $expected = [
            $uid1 => [$sub_r, $peer_r],
            $uid2 => [$sub_r, $rwr_r],
            $uid3 => [],
            $mgr_uid => [$mgr_r, $mtr_r, $rwr_r],
            $appr_uid => [$appr_r]
        ];

        $this->assert_activity_roles($expected, false);
    }

    public function test_get_activity_roles_for_suspended_subjects(): void {
        global $CFG;
        require_once("{$CFG->dirroot}/user/lib.php");

        $users = collection
            ::new([
                constants::RELATIONSHIP_SUBJECT,
                constants::RELATIONSHIP_MANAGER,
                constants::RELATIONSHIP_APPRAISER,
                constants::RELATIONSHIP_PEER,
                constants::RELATIONSHIP_MENTOR,
                constants::RELATIONSHIP_REVIEWER
            ])
            ->map(
                function (string $role): array {
                    return [$this->create_users(1)->first()->id, $role];
                }
            );

        $active_role_assignments = $users->reduce(
            function (array $assignments, array $tuple): array {
                [$user_id, $role] = $tuple;
                $assignments[$user_id] = [$role];

                return $assignments;
            },
            []
        );

        [$subject_id, ] = $users->first();
        $this->create_participant_instances(
            $subject_id,
            $active_role_assignments,
            [
                'relationships_can_view' => '',
                'subject_is_participating' => false,
                'include_questions' => false
            ]
        );

        // Initially all roles are visible for an active subject whatever the
        // value for the active user only filter.
        $this->assert_activity_roles($active_role_assignments, false);
        $this->assert_activity_roles($active_role_assignments, true);

        // If the subject is now suspended, all role assignments should still be
        // returned if the active user only filter is disabled.
        user_suspend_user($subject_id);
        $this->assert_activity_roles($active_role_assignments, false);

        // However, if the active user only filter is enabled, there should be no
        // roles returned.
        $suspended_assignments = $users->reduce(
            function (array $assignments, array $tuple): array {
                [$user_id, ] = $tuple;
                $assignments[$user_id] = [];

                return $assignments;
            },
            []
        );
        $this->assert_activity_roles($suspended_assignments, true);

        // If the subject is now reactivated, all original role assignments are
        // returned whatever the value for the active user only filter.
        user_unsuspend_user($subject_id);

        $this->assert_activity_roles($active_role_assignments, false);
        $this->assert_activity_roles($active_role_assignments, true);
    }

    public function test_get_activity_roles_for_removed_access(): void {
        static::setAdminUser();

        // Create subject and manager users.
        $subject = static::getDataGenerator()->create_user([
            'firstname' => 'Subject',
            'lastname' => 'Nontarget',
        ]);

        $manager = static::getDataGenerator()->create_user();

        // Create performance activity.
        $perform_generator = perform_generator::instance();

        // Create participant instance for subject with manager relationship.
        $subject_instance = subject_instance_model::load_by_entity(
            $perform_generator->create_subject_instance([
                'activity_name' => 'Test activity',
                'activity_type' => 'appraisal',
                'subject_user_id' => $subject->id,
                'other_participant_username' => $manager->username,
                'subject_is_participating' => true,
            ])
        );

        $relationships = participant_instance::get_activity_roles_for($manager->id);

        // Confirm the manager relationship is returned.
        static::assertCount(1, $relationships);

        // Confirm the subject relationship is returned.
        $relationships = participant_instance::get_activity_roles_for($subject->id);
        static::assertCount(1, $relationships);

        // Remove access for manager role.
        $manager_role = $perform_generator->get_core_relationship(constants::RELATIONSHIP_MANAGER);
        $participant_instances_manager = $subject_instance->participant_instances->filter('core_relationship_id', $manager_role->id);
        static::assertCount(1, $participant_instances_manager);
        /** @var participant_instance $participant_instance_manager */
        $participant_instance_manager = $participant_instances_manager->first();
        $participant_instance_manager->manually_close();
        $participant_instance_manager->set_access_removed(true);

        // Confirm the manager relationship is not returned.
        $relationships = participant_instance::get_activity_roles_for($manager->id);
        static::assertCount(0, $relationships);

        // Confirm the subject relationship is still returned.
        $relationships = participant_instance::get_activity_roles_for($subject->id);
        static::assertCount(1, $relationships);
    }

    /**
     * Data provider for test_manually_delete
     */
    public static function td_manually_delete(): array {
        return [
            'answering participant' => [true],
            'read only participant' => [true]
        ];
    }

    /**
     * @dataProvider td_manually_delete
     *
     * Test to delete participant instance, participant sections and section element responses.
     */
    public function test_manually_delete(bool $is_viewonly): void {
        [$subject_uid, $participant_uid] = $this->create_users(2)->pluck('id');

        [$subject_pi, [$appraiser_pi]] = $this->create_participant_instances(
            $subject_uid,
            [$participant_uid => [constants::RELATIONSHIP_APPRAISER]],
            [
                'subject_is_participating' => true,
                'other_participant_id' => $participant_uid,
                'include_questions' => true,
                'relationships_can_answer' => $is_viewonly ? 'subject' : 'subject,manager',
                'relationships_can_view' => $is_viewonly ? 'manager' : ''
            ]
        );

        $section = section_element::load_by_entity(
            section_element_entity::repository()->get()->first()
        );

        $this->create_response($subject_pi, $section);
        if (!$is_viewonly) {
            $this->create_response($appraiser_pi, $section);
        }

        $subject_pi_id = $subject_pi->id;
        $appraiser_pi_id = $appraiser_pi->id;

        $this->assert_participant_instance_and_response('subject', $subject_pi_id, true, true);
        $this->assert_participant_instance_and_response('appraiser', $appraiser_pi_id, true, !$is_viewonly);

        $subject_pi->manually_delete();
        $this->assert_participant_instance_and_response('subject', $subject_pi_id, false, false);
        $this->assert_participant_instance_and_response('appraiser', $appraiser_pi_id, true, !$is_viewonly);

        $appraiser_pi->manually_delete();
        $this->assert_participant_instance_and_response('subject', $subject_pi_id, false, false);
        $this->assert_participant_instance_and_response('appraiser', $appraiser_pi_id, false, false);
    }

    /**
     * Generates test data.
     *
     * @param int $subject_userid subject user id.
     * @param array $all_relationships [user id => [relationship idnumbers]] mappings for
     *        other participants.
     * @param array<string,mixed> $values additional subject instance details
     *        (other than the activity id and subject user id) to use when
     *        creating the subject instance.
     *
     * @return array a [subject participant instance, other participant_instance[]] tuple.
     */
    private function create_participant_instances(
        int $subject_userid, array $all_relationships, array $values = []
    ): array {
        $this->setAdminUser();

        $perform_generator = perform_generator::instance();
        $subject_instance_id = $this
            ->create_subject_instance($subject_userid, $values)
            ->id;

        $subject_participant_instance = $this->create_participant_instance(
            $subject_instance_id,
            $subject_userid,
            $perform_generator->get_core_relationship(constants::RELATIONSHIP_SUBJECT)
        );

        $participant_instances = [];
        foreach ($all_relationships as $userid => $relationships) {
            foreach ($relationships as $relationship) {
                $participant_instances[] = $this->create_participant_instance(
                    $subject_instance_id,
                    $userid,
                    $perform_generator->get_core_relationship($relationship)
                );
            }
        }

        return [$subject_participant_instance, $participant_instances];
    }

    /**
     * Generates test users.
     *
     * @param int count no of users to generate.
     *
     * @return collection|stdClass[] the created users.
     */
    private function create_users(int $count): collection {
        $this->setAdminUser();

        return collection::new(range(0, $count - 1))
            ->map(
                function (int $i): stdClass {
                    return self::getDataGenerator()->create_user();
                }
            );
    }

    /**
     * Creates a participant instance.
     *
     * @return participant_instance participant instance.
     */
    private function create_participant_instance(
        int $subject_instance_id, int $participant_userid, relationship $relationship
    ): participant_instance {
        $pi = new participant_instance_entity();
        $pi->core_relationship_id = $relationship->id;
        $pi->participant_id = $participant_userid;
        $pi->participant_source = participant_source::INTERNAL;
        $pi->subject_instance_id = $subject_instance_id;
        $pi->save();

        return participant_instance::load_by_entity($pi);
    }

    public function test_get_participant_instances_to_close_for_suspended_users(): void {
        [$user1_id, $user2_id, $user3_id] = $this->create_users(3)->pluck('id');

        $subject_instance_details = [
            'relationships_can_view' => '',
            'subject_is_participating' => false,
            'include_questions' => false
        ];

        $this->create_participant_instances(
            $user1_id,
            [
                $user2_id => [constants::RELATIONSHIP_MANAGER],
                $user3_id => [constants::RELATIONSHIP_APPRAISER],
            ],
            $subject_instance_details
        );

        $this->create_participant_instances(
            $user2_id,
            [
                $user3_id => [constants::RELATIONSHIP_APPRAISER],
            ],
            $subject_instance_details
        );

        self::assertEquals(5, participant_instance_entity::repository()->count());
        $user_1_instance_ids = participant_instance_entity::repository()
            ->where('participant_id', $user1_id)
            ->get()
            ->pluck('id');
        self::assertCount(1, $user_1_instance_ids);
        $user_2_instance_ids = participant_instance_entity::repository()
            ->where('participant_id', $user2_id)
            ->get()
            ->pluck('id');
        self::assertCount(2, $user_2_instance_ids);

        // There are no suspended users, so result should be empty.
        $result = participant_instance::get_participant_instances_to_close_for_suspended_users()->to_array();
        self::assertEmpty($result);

        // Set users 1 & 2 to suspended.
        user::repository()
            ->where_in('id', [$user1_id, $user2_id])
            ->update(['suspended' => 1]);

        $result = participant_instance::get_participant_instances_to_close_for_suspended_users()->to_array();
        self::assertCount(3, $result);
        self::assertEqualsCanonicalizing(
            array_merge($user_1_instance_ids, $user_2_instance_ids),
            [$result[0]->id, $result[1]->id, $result[2]->id]
        );

        // Close one participant instance. It should not be in the result anymore.
        $participant_instance = participant_instance::load_by_id($user_2_instance_ids[0]);
        $participant_instance->manually_close();

        $result = participant_instance::get_participant_instances_to_close_for_suspended_users()->to_array();
        self::assertCount(2, $result);
        self::assertEqualsCanonicalizing(
            [$user_1_instance_ids[0], $user_2_instance_ids[1]],
            [$result[0]->id, $result[1]->id]
        );
    }

    /**
     * Basic test for the `can_be_manually_closed_by_participant` and 'participant_manual_closure_status' properties.
     * The details of allowing manual closure are tested elsewhere (manual_closure_conditions_helper_test).
     *
     * @return void
     */
    public function test_can_be_manually_closed_by_participant(): void {
        $perform_generator = perform_generator::instance();
        $subject_user = static::getDataGenerator()->create_user();
        $subject_instance = $this->create_subject_instance($subject_user->id);
        $participant_instance = $this->create_participant_instance(
            $subject_instance->id,
            $subject_user->id,
            $perform_generator->get_core_relationship(constants::RELATIONSHIP_SUBJECT)
        );

        static::setUser($subject_user);
        static::assertEquals(closure_evaluation_result::NOT_APPLICABLE, $participant_instance->manual_closure_evaluation_result);
        static::assertFalse($participant_instance->can_be_manually_closed_by_participant);

        static::setAdminUser();
        $activity = $participant_instance->subject_instance->activity;
        $activity->settings->update([
            activity_setting::CLOSE_ON_COMPLETION => false,
            activity_setting::CLOSE_ON_SECTION_SUBMISSION => false,
            activity_setting::MANUAL_CLOSE => true
        ]);

        // Refresh model.
        $participant_instance = participant_instance::load_by_id($participant_instance->id);
        static::setUser($subject_user);
        static::assertEquals(closure_evaluation_result::ALLOWED, $participant_instance->manual_closure_evaluation_result);
        static::assertTrue($participant_instance->can_be_manually_closed_by_participant);
    }

    public function test_manually_closing_marks_multisection_progress_complete(): void {
        $participant_instance_model = $this->create_activity_and_sections(3, false);
        $participant_instance_model->manually_close_and_complete();

        foreach ($participant_instance_model->participant_sections as $section) {
            self::assertEquals(section_progress_complete::get_code(), $section->progress);
        }
    }

    public function test_manually_closing_does_not_mark_multisection_progress_complete(): void {
        $participant_instance_model = $this->create_activity_and_sections(3, false);
        // Mark the participant_instance as already closed.
        $pi_entity = participant_instance_entity::repository()->find($participant_instance_model->id);
        $pi_entity->availability = pi_availability_closed::get_code();
        $pi_entity->save();
        $participant_instance_model->refresh();

        self::expectException('coding_exception');
        self::expectExceptionMessage('This function can only be called if the participant instance is open');

        $participant_instance_model->manually_close_and_complete();
    }

    public function test_manually_closing_marks_complete_when_close_on_completion_setting_enabled(): void {
        $participant_instance_model = $this->create_activity_and_sections(3, true);
        $participant_instance_model->manually_close_and_complete();

        foreach ($participant_instance_model->participant_sections as $section) {
            self::assertEquals(section_progress_complete::get_code(), $section->progress);
        }
    }

    public function test_manually_closing_does_not_mark_complete_when_view_only_participants(): void {
        $participant_instance_model = $this->create_activity_and_sections(2, true);
        // Set the participant's instance as a view-only participant one.
        $pi_entity = participant_instance_entity::repository()->find($participant_instance_model->id);
        $pi_entity->availability = pi_availability_not_applicable::get_code();
        $pi_entity->save();
        $participant_instance_model->refresh();

        self::expectException('coding_exception');
        self::expectExceptionMessage('This function can only be called if the participant instance is open');

        $participant_instance_model->manually_close_and_complete();
    }

    public function test_manually_open_does_nothing_when_access_removed(): void {
        $participant_instance_model = $this->create_activity_and_sections(1, true);
        $participant_instance_model->manually_close();

        /** @var  $participant_instance_entity */
        $participant_instance_entity = participant_instance_entity::repository()->find($participant_instance_model->id);
        static::assertSame(pi_availability_closed::get_code(), (int)$participant_instance_entity->availability);

        $participant_instance_model->set_access_removed(true);

        $participant_instance_model->manually_open();
        static::assertDebuggingCalled(
            'mod_perform\models\activity\participant_instance::manually_open called for a participant instance with access removed. Cannot open. Doing nothing.'
        );

        // Should still be closed.
        /** @var  $participant_instance_entity */
        $participant_instance_entity = participant_instance_entity::repository()->find($participant_instance_model->id);
        static::assertSame(pi_availability_closed::get_code(), (int)$participant_instance_entity->availability);
    }

    /**
     * Creates a subject instance.
     *
     * @param int $subject_userid subject user id.
     * @param array<string,mixed> $values additional subject instance details
     *        (other than the activity id and subject user id) to use when
     *        creating the subject instance.
     *
     * @return subject_instance_entity the created subject instance.
     */
    private function create_subject_instance(
        int $subject_userid, array $values = []
    ): subject_instance_entity {
        $this->setAdminUser();

        $generator = perform_generator::instance();
        $data = array_merge(
            $values,
            [
                'activity_id' => $generator->create_activity_in_container()->id,
                'subject_user_id' => $subject_userid
            ]
        );

        return $generator->create_subject_instance($data);
    }

    /**
     * Creates a participant response.
     *
     * @return participant_instance participant instance.
     */
    private function create_response(
        participant_instance $pi, section_element $section
    ): section_element_response {
        $response = new section_element_response($pi, $section, null, new collection());

        return $response
            ->set_response_data(json_encode('Hooooray'))
            ->save();
    }

    /**
     * Validates whether the specified participant instance exists and has
     * responses.
     */
    private function assert_participant_instance_and_response(
        string $tag,
        int $pi_id,
        bool $has_pi,
        bool $has_responses
    ): void {
        $this->assertEquals(
            $has_pi,
            participant_instance_entity::repository()->where('id', $pi_id)->exists(),
            $has_pi
                ? "$tag participant instance does not exist when it should"
                : "$tag participant instance exists when it should not"
        );

        $responses = element_response_entity::repository()
            ->where('participant_instance_id', $pi_id)
            ->count();

        $has_responses
            ? $this->assertTrue($responses > 0, "$tag has no responses")
            : $this->assertEquals(0, $responses, "$tag has responses");
    }

    /**
     * Validates whether the given user ids have the expected activity roles.
     */
    private function assert_activity_roles(
        array $expected,
        bool $only_active_users
    ): void {
        foreach ($expected as $uid => $expected_roles) {
            $actual_roles = participant_instance::get_activity_roles_for($uid, $only_active_users)
                ->map(
                    function (relationship $relationship): string {
                        return $relationship->idnumber;
                    }
                )
                ->all();

            $this->assertEqualsCanonicalizing($expected_roles, $actual_roles);
        }
    }

    /**
     * Create a test activity, a number of settings within it and some participants.
     * @param int $num_sections
     * @param bool $close_on_completion
     * @return participant_instance
     */
    private function create_activity_and_sections(
        int $num_sections = 1,
        bool $close_on_completion = false
    ): participant_instance {
        self::setAdminUser();
        $core_generator = \core\testing\generator::instance();
        $perform_generator = $core_generator->get_plugin_generator('mod_perform');
        $subject = $core_generator->create_user();

        /* @var activity_model $activity */
        $activity = $perform_generator->create_activity_in_container();
        $activity->settings->update(
            [
                activity_setting::MANUAL_CLOSE => true,
                activity_setting::CLOSE_ON_COMPLETION => $close_on_completion
            ]
        );

        $si = $perform_generator->create_subject_instance([
            'activity_id' => $activity->id,
            'subject_user_id' => $subject->id,
            'include_questions' => false,
        ]);

        $section = $activity->get_sections()->first();
        for ($i = 0; $i < $num_sections; $i++) {
            if ($i > 0) {
                $section = $perform_generator->create_section($activity, ['title' => 'Section ' . ($i + 1)]);
            }
            $perform_generator->create_participant_instance_and_section(
                $activity,
                $subject,
                $si->id,
                $section,
                $perform_generator->get_core_relationship(constants::RELATIONSHIP_SUBJECT)->id
            );

            $participant_section = $perform_generator->create_participant_instance_and_section(
                $activity,
                $core_generator->create_user(),
                $si->id,
                $section,
                $perform_generator->get_core_relationship(constants::RELATIONSHIP_PEER)->id
            );
        }

        return participant_instance::load_by_entity(
            $participant_section->participant_instance
        );
    }

    public function test_user_can_change_access_when_already_access_removed_fails(): void {
        $participant_instance_model = $this->helper_create_participant_instance(1);

        $user_can_change_access_error = $participant_instance_model->user_can_change_access_find_errors(user::logged_in(), 1);
        $this->assertEquals('Access was already removed.', $user_can_change_access_error);
    }

    public function test_user_can_change_access_when_already_access_granted_fails(): void {
        $participant_instance_model = $this->helper_create_participant_instance(0);

        $user_can_change_access_error = $participant_instance_model->user_can_change_access_find_errors(user::logged_in(), 0);
        $this->assertEquals('Access is already granted.', $user_can_change_access_error);
    }

    public function test_user_can_change_access_when_invalid_status_fails(): void {
        $participant_instance_model = $this->helper_create_participant_instance(0);

        $valid_statuses = [0, 1];
        $invalid_statuses = [min($valid_statuses) - 1,  max($valid_statuses) + 1];
        foreach ($invalid_statuses as $invalid_status) {
            $user_can_change_access_error = $participant_instance_model->user_can_change_access_find_errors(user::logged_in(),
                $invalid_status
            );
            $this->assertEquals('Invalid status code.', $user_can_change_access_error);
        }
    }

    public function test_user_can_change_access_as_outside_user_fails(): void {
        $participant_instance_model = $this->helper_create_participant_instance(0);

        $outsider_user = static::getDataGenerator()->create_user();
        $this->setUser($outsider_user);

        $user_can_change_access_error = $participant_instance_model->user_can_change_access_find_errors(user::logged_in(), 0);
        $this->assertEquals('Insufficient permissions to remove access on the participant instance.', $user_can_change_access_error);
    }

    public function test_user_can_change_access_as_view_only_participant_succeeds(): void {
        $participant_instance_model = $this->helper_create_participant_instance(false);
        $pi_entity = participant_instance_entity::repository()->find($participant_instance_model->id);
        $pi_entity->availability = pi_availability_not_applicable::get_code(); // i.e. for a view only participant
        $pi_entity->save();

        $view_only_user = user::repository()->find($participant_instance_model->participant_id);
        $role_id = self::getDataGenerator()->create_role();
        assign_capability('mod/perform:manage_all_participation', CAP_ALLOW, $role_id, context_system::instance());
        role_assign($role_id, $view_only_user->id, context_system::instance()->id);
        $this->setUser($view_only_user);

        // This should succeed for a view-only participant even though the participant_instance is not closed.
        $pi_entity->refresh();
        $this->assertNotEquals(pi_availability_closed::get_code(), $pi_entity->availability);
        $user_can_change_access_error = $participant_instance_model->user_can_change_access_find_errors(user::logged_in(), 1);
        $this->assertNull($user_can_change_access_error);
    }

    public function test_user_can_access_page_find_errors(): void {
        $participant_instance_model = $this->helper_create_participant_instance(false);
        $page_access_error_msg1 = participant_instance_helper::find_errors_for_page_access($participant_instance_model);

        $participant_instance_model->set_access_removed(true);
        $page_access_error_msg2 = participant_instance_helper::find_errors_for_page_access($participant_instance_model);

        $this->assertNull($page_access_error_msg1);
        $this->assertEquals('You no longer have access to this activity.',  $page_access_error_msg2);
    }

    public function test_access_remove_only_possible_when_closed(): void {
        $participant_instance = $this->create_activity_and_sections(1, true);

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Cannot remove access for an open participant instance.');

        $participant_instance->set_access_removed(true);
    }

    public function test_access_changed_event(): void {
        $participant_instance = $this->create_activity_and_sections(1, true);
        $participant_instance->manually_close();

        // Check removing access.
        $event_sink = static::redirectEvents();
        $participant_instance->set_access_removed(true);
        $event_sink->close();
        $events = $event_sink->get_events();
        $this->assertCount(1, $events);

        $event = reset($events);
        $this->assertInstanceOf(participant_instance_access_changed::class, $event);
        $this->assertEquals($participant_instance->id, $event->objectid);
        $this->assertEquals($participant_instance->participant_id, $event->relateduserid);
        $this->assertEquals($participant_instance->subject_instance->get_context()->id, $event->contextid);
        $this->assertEquals($participant_instance->subject_instance->id, $event->other['subject_instance_id']);
        $this->assertEquals('removed', $event->other['action']);
        $this->assertEquals(get_admin()->id, $event->userid);
        $this->assertEquals(
            "The access for the participant instance with id '$participant_instance->id' for the user with id '$participant_instance->participant_id' has been 'removed' by the user with id '" . get_admin()->id . "'",
            $event->get_description()
        );


        // Check restoring access.
        $event_sink = static::redirectEvents();
        $participant_instance->set_access_removed(false);
        $event_sink->close();
        $events = $event_sink->get_events();
        $this->assertCount(1, $events);

        $event = reset($events);
        $this->assertInstanceOf(participant_instance_access_changed::class, $event);
        $this->assertEquals($participant_instance->id, $event->objectid);
        $this->assertEquals($participant_instance->participant_id, $event->relateduserid);
        $this->assertEquals($participant_instance->subject_instance->get_context()->id, $event->contextid);
        $this->assertEquals($participant_instance->subject_instance->id, $event->other['subject_instance_id']);
        $this->assertEquals('restored', $event->other['action']);
        $this->assertEquals(get_admin()->id, $event->userid);
        $this->assertEquals(
            "The access for the participant instance with id '$participant_instance->id' for the user with id '$participant_instance->participant_id' has been 'restored' by the user with id '" . get_admin()->id . "'",
            $event->get_description()
        );

        $this->assertEquals('Performance activity participant instance changed access', participant_instance_access_changed::get_name());
    }


    /**
     * @param int $access_removed
     * @return participant_instance
     */
    private function helper_create_participant_instance(int $access_removed): participant_instance {
        $participant_instance_model = $this->create_activity_and_sections(2, true);
        $pi_entity = participant_instance_entity::repository()->find($participant_instance_model->id);
        $pi_entity->access_removed = $access_removed;
        $pi_entity->availability = pi_availability_closed::get_code();
        $pi_entity->save();
        return $participant_instance_model->refresh();
    }
}
