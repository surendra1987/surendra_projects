<?php
/*
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

use core\entity\user;
use core\orm\query\builder;
use mod_perform\entity\activity\element;
use mod_perform\models\activity\activity;
use mod_perform\util;
use totara_job\job_assignment;

/**
 * @coversDefaultClass \mod_perform\util
 *
 * @group perform
 */
class mod_perform_util_test extends \core_phpunit\testcase {

    public function test_admin_can_manage_participation_of_all_activities(): void {
        self::setAdminUser();

        $names = ['Mid year performance', 'End year performance'];
        $this->create_activity_data($names);

        $activities = util::get_participant_manageable_activities(user::logged_in()->id);
        $activities = $activities->pluck('name');
        $names[] = 'hidden-activity'; // Admin can see "hidden" activities.

        $this->assertCount(count($names), $activities);
        $this->assertEqualsCanonicalizing($names, $activities);
    }

    public function test_manager_can_manage_participation_of_activities_about_his_employees(): void {
        $manager = self::getDataGenerator()->create_user();
        $employee = self::getDataGenerator()->create_user();

        $this->assign_manager_capability_over_employee(
            'mod/perform:manage_subject_user_participation',
            $manager,
            $employee
        );

        self::setAdminUser();

        /** @var \mod_perform\testing\generator $perform_generator */
        $perform_generator = \mod_perform\testing\generator::instance();
        $subject_instance = $perform_generator->create_subject_instance(['subject_user_id' => $employee->id]);
        $expected_activity = $subject_instance->activity();

        // Add some other activities too.
        $names = ['Mid year performance', 'End year performance'];
        $this->create_activity_data($names);

        self::setUser($manager);

        $activities =  util::get_participant_manageable_activities(user::logged_in()->id);

        /** @var activity $actual_activity */
        $actual_activity = $activities->first();

        $this->assertCount(1, $activities);
        $this->assertEquals($expected_activity->id, $actual_activity->id);
    }

    public function test_manager_can_manage_participation_of_activities_no_employees_no_capability(): void {
        // Set up
        self::setAdminUser();
        $manager = self::getDataGenerator()->create_user();
        $employee = self::getDataGenerator()->create_user();

        $perform_generator = \mod_perform\testing\generator::instance();
        $subject_instance = $perform_generator->create_subject_instance(['subject_user_id' => $employee->id]);
        // Add some other activities too.
        $names = ['Mid year performance', 'End year performance'];
        $this->create_activity_data($names);

        // Operate - we expect a blank collection of activities to be returned since the manager does not have:
        // (a) staff assigned to them
        // (b) the 'mod/perform:manage_subject_user_participation' capability
        self::setUser($manager);
        $activities = util::get_participant_manageable_activities(user::logged_in()->id);

        // Assert
        $this->assertEmpty($activities);
    }

    public function test_full_participation_manger_can_manage_all_activities(): void {
        $full_participation_manager = self::getDataGenerator()->create_user();

        // Add some other activities too.
        $names = ['Mid year performance', 'End year performance'];
        $this->create_activity_data($names);

        self::setUser($full_participation_manager);

        $activities = util::get_participant_manageable_activities(user::logged_in()->id);
        $this->assertCount(0, $activities);

        $manager_role = builder::get_db()->get_record('role', ['shortname' => 'manager'], '*', MUST_EXIST);
        $manager_context = context_user::instance($full_participation_manager->id);
        role_assign($manager_role->id, $full_participation_manager->id, $manager_context);

        $activities =  util::get_participant_manageable_activities(user::logged_in()->id);
        $activities = $activities->pluck('name');

        $this->assertCount(count($names), $activities);
        $this->assertEqualsCanonicalizing($names, $activities);
    }

    private function create_activity_data(array $activity_names): void {
        $user = user::logged_in();

        self::setAdminUser();

        /** @var \mod_perform\testing\generator $perform_generator */
        $perform_generator = \mod_perform\testing\generator::instance();

        $activity_names[] = 'hidden-activity';

        foreach ($activity_names as $name) {
            $perform_generator->create_activity_in_container(['activity_name' => $name]);
        }

        /** @var activity $hidden_activity */
        $hidden_activity = mod_perform\entity\activity\activity::repository()
            ->where('name', 'hidden-activity')
            ->order_by('id')
            ->first(true);

        // Set "hidden-activity" to be hidden, as all queries under test should
        // be applying filter_by_visible  filter.
        builder::table('course')
            ->where('id', $hidden_activity->course)
            ->update([
                'visible' => 0,
                'visibleold' => 0
            ]);

        if ($user) {
            self::setUser($user->id);
        }
    }

    public function test_admin_can_potentially_report_on_subjects(): void {
        self::setAdminUser();

        $this->assertTrue(util::can_potentially_report_on_subjects(user::logged_in()->id));
    }

    public function test_can_potentially_manage_participants_for_manage_all_participation_cap(): void {
        $user = self::getDataGenerator()->create_user();
        $other_user = self::getDataGenerator()->create_user();

        $this->assertFalse(util::can_potentially_manage_participants($user->id));
        $this->assertFalse(util::can_potentially_manage_participants($other_user->id));

        // Grant manage_all_participation capability to user.
        $role_id = self::getDataGenerator()->create_role();
        assign_capability('mod/perform:manage_all_participation', CAP_ALLOW, $role_id, context_system::instance());
        $user_context = context_user::instance($user->id);
        role_assign($role_id, $user->id, $user_context->id);

        $this->assertTrue(util::can_potentially_manage_participants($user->id));
        $this->assertFalse(util::can_potentially_manage_participants($other_user->id));

        role_unassign($role_id, $user->id, $user_context->id);
        $this->assertFalse(util::can_potentially_manage_participants($user->id));
        $this->assertFalse(util::can_potentially_manage_participants($other_user->id));
    }

    public function test_manage_staff_participation_default_capability() {
        // Make sure manage_staff_participation capability is assigned to authenticated user role (happens on installation).
        $user_role = builder::table('role')
            ->where('shortname','user')
            ->one(true);

        self::assertTrue(
            builder::table('role_capabilities')
                ->where('roleid', $user_role->id)
                ->where('contextid', context_system::instance()->id)
                ->where('capability', 'mod/perform:manage_staff_participation')
                ->exists()
        );

        // Make sure every user has this capability in their own context.
        $user = self::getDataGenerator()->create_user();
        self::assertTrue(has_capability('mod/perform:manage_staff_participation', context_user::instance($user->id), $user->id));
    }

    public function test_can_potentially_manage_participants_for_manage_staff_participation_cap(): void {
        $user = self::getDataGenerator()->create_user();
        $other_user = self::getDataGenerator()->create_user();

        self::assertFalse(util::can_potentially_manage_participants($user->id));
        self::assertFalse(util::can_potentially_manage_participants($other_user->id));

        // Assign 'other user' as staff.
        $manager_ja = job_assignment::create_default($user->id);
        job_assignment::create_default($other_user->id, ['managerjaid' => $manager_ja->id]);

        self::assertTrue(util::can_potentially_manage_participants($user->id));
        self::assertFalse(util::can_potentially_manage_participants($other_user->id));

        // Make sure 'manage_subject_user_participation' capability doesn't interfere.
        builder::table('role_capabilities')
            ->where('capability', 'mod/perform:manage_subject_user_participation')
            ->delete();

        self::assertTrue(util::can_potentially_manage_participants($user->id));
        self::assertFalse(util::can_potentially_manage_participants($other_user->id));
    }

    public function test_user_with_subject_capability_can_potentially_report_on_subjects(): void {
        $subject = self::getDataGenerator()->create_user();
        $reporter = self::getDataGenerator()->create_user();

        $this->assign_reporter_cap_over_subject('mod/perform:report_on_subject_responses', $reporter, $subject);

        self::setUser($reporter);
        $this->assertTrue(util::can_potentially_report_on_subjects(user::logged_in()->id));
    }

    public function test_user_with_all_subjects_capability_can_potentially_report_on_subjects(): void {
        $reporter = self::getDataGenerator()->create_user();

        $reporter_role_id = create_role(
            'Perform Reporter Role',
            'perform_reporter_role',
            'Can report on perform data'
        );

        $system_context = context_system::instance();
        assign_capability(
            'mod/perform:report_on_subject_responses',
            CAP_ALLOW,
            $reporter_role_id,
            $system_context
        );

        self::getDataGenerator()->role_assign(
            $reporter_role_id,
            $reporter->id,
            context_user::instance($reporter->id)
        );

        self::setUser($reporter);
        $this->assertTrue(util::can_potentially_report_on_subjects(user::logged_in()->id));
    }

    public function test_user_cannot_report_without_capability(): void {
        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        $this->assertFalse(util::can_potentially_report_on_subjects(user::logged_in()->id));
    }

    /**
     * The capability 'mod/perform:report_on_staff_responses' assigned to a user means they will have to rights to report
     * on staff user assigned to them.
     * @return void
     */
    public function test_can_potentially_report_on_subjects_if_user_has_staff_assigned(): void {
        $manager_user = self::getDataGenerator()->create_user();
        $staff_user = self::getDataGenerator()->create_user();

        $role_id = create_role(
            'testrole1234',
            'testrole1234',
            'testrole'
        );
        $manager_context = context_user::instance($manager_user->id);
        assign_capability('mod/perform:report_on_staff_responses', CAP_ALLOW, $role_id, $manager_context->id);
        role_assign($role_id, $manager_user->id, $manager_context->id);

        // Assign 'other user' as staff.
        $manager_ja = job_assignment::create_default($manager_user->id);
        $staff_user_ja = job_assignment::create_default($staff_user->id, ['managerjaid' => $manager_ja->id]);

        $this->assertTrue(util::can_potentially_report_on_subjects($manager_user->id));
    }

    public function test_user_that_can_report_on_all_subjects_responses_can_report_on_any_element(): void {
        $reporter = self::getDataGenerator()->create_user();

        $reporter_role_id = create_role(
            'Perform Reporter Role',
            'perform_reporter_role',
            'Can report on perform data'
        );

        $subject = self::getDataGenerator()->create_user();

        self::setAdminUser();

        /** @var \mod_perform\testing\generator $perform_generator */
        $perform_generator = \mod_perform\testing\generator::instance();

        $perform_generator->create_subject_instance([
            'subject_is_participating' => true,
            'subject_user_id' => $subject->id,
            'other_participant_id' => null,
            'include_questions' => true,
        ]);

        /** @var element $element */
        $element = element::repository()->order_by('id')->first();

        $system_context = context_system::instance();
        assign_capability(
            'mod/perform:report_on_subject_responses',
            CAP_ALLOW,
            $reporter_role_id,
            $system_context
        );

        self::getDataGenerator()->role_assign(
            $reporter_role_id,
            $reporter->id,
            context_user::instance($reporter->id)
        );

        self::assertTrue(util::can_report_on_element(user::logged_in()->id, $element->id));
    }

    public function test_can_report_on_element_where_user_has_permission_over_subject_using_element(): void {
        $reporter = self::getDataGenerator()->create_user();
        $subject = self::getDataGenerator()->create_user();

        self::setAdminUser();

        /** @var \mod_perform\testing\generator $perform_generator */
        $perform_generator = \mod_perform\testing\generator::instance();

        $perform_generator->create_subject_instance([
            'subject_is_participating' => true,
            'subject_user_id' => $subject->id,
            'other_participant_id' => null,
            'include_questions' => true,
        ]);

        /** @var element $element */
        $element = element::repository()->order_by('id')->first();

        self::assertFalse(util::can_report_on_element($reporter->id, $element->id));

        $this->assign_reporter_cap_over_subject('mod/perform:report_on_subject_responses', $reporter, $subject);

        self::assertTrue(util::can_report_on_element($reporter->id, $element->id));
    }

    public function test_cannot_report_on_element_if_has_capability_but_no_staff(): void {
        // Set up.
        self::setAdminUser();
        $manager_user = self::getDataGenerator()->create_user();
        $subject = self::getDataGenerator()->create_user();
        $role_id = create_role(
            'testrole1234',
            'testrole1234',
            'testrole'
        );
        $manager_context = context_user::instance($manager_user->id);
        assign_capability('mod/perform:report_on_staff_responses', CAP_ALLOW, $role_id, $manager_context->id);
        role_assign($role_id, $manager_user->id, $manager_context->id);

        $perform_generator = \mod_perform\testing\generator::instance();
        $perform_generator->create_subject_instance([
            'subject_is_participating' => true,
            'subject_user_id' => $subject->id,
            'other_participant_id' => null,
            'include_questions' => true,
        ]);

        $element = element::repository()->order_by('id')->first();
        // Operate.
        $result = util::can_report_on_element($manager_user->id, $element->id);
        // Assert.
        self::assertFalse($result);
    }

    public function test_can_report_on_element_if_has_capability_and_staff(): void {
        // Set up.
        self::setAdminUser();
        $manager_user = self::getDataGenerator()->create_user();
        $subject = self::getDataGenerator()->create_user();
        $role_id = create_role(
            'testrole12345',
            'testrole12345',
            'testrole12345'
        );
        $manager_context = context_user::instance($manager_user->id);
        assign_capability('mod/perform:report_on_staff_responses', CAP_ALLOW, $role_id, $manager_context->id);
        role_assign($role_id, $manager_user->id, $manager_context->id);

        // Assign 'other user' as staff.
        $manager_ja = job_assignment::create_default($manager_user->id);
        $subject_user_ja = job_assignment::create_default($subject->id, ['managerjaid' => $manager_ja->id]);

        $perform_generator = \mod_perform\testing\generator::instance();
        $perform_generator->create_subject_instance([
            'subject_is_participating' => true,
            'subject_user_id' => $subject->id,
            'other_participant_id' => null,
            'include_questions' => true,
        ]);

        $element = element::repository()->order_by('id')->first();
        // Operate.
        $result = util::can_report_on_element($manager_user->id, $element->id);
        // Assert.
        self::assertTrue($result);
    }

    public function test_can_report_on_user(): void {
        $subject_user = self::getDataGenerator()->create_user();
        $viewing_user1 = self::getDataGenerator()->create_user();
        $viewing_user2 = self::getDataGenerator()->create_user();
        self::setUser($viewing_user1);

        self::assertFalse(util::can_report_on_user($subject_user->id, 0));
        self::assertFalse(util::can_report_on_user(0, $viewing_user1->id));

        self::assertFalse(util::can_report_on_user($subject_user->id, $viewing_user1->id));
        self::assertFalse(util::can_report_on_user($subject_user->id, $viewing_user2->id));

        // Grant report_on_subject_responses capability to viewing user 1 in the context of the subject user.
        $roleid = self::getDataGenerator()->create_role();
        assign_capability('mod/perform:report_on_subject_responses', CAP_ALLOW, $roleid, context_system::instance());
        $subject_user_context = context_user::instance($subject_user->id);
        role_assign($roleid, $viewing_user1->id, $subject_user_context);

        self::assertTrue(util::can_report_on_user($subject_user->id, $viewing_user1->id));

        // Grant report_on_all_subjects_responses capability to viewing user 2.
        $roleid = self::getDataGenerator()->create_role();
        assign_capability('mod/perform:report_on_all_subjects_responses', CAP_ALLOW, $roleid, context_system::instance());
        $viewing_user2_context = context_user::instance($viewing_user2->id);
        role_assign($roleid, $viewing_user2->id, $viewing_user2_context);

        self::assertTrue(util::can_report_on_user($subject_user->id, $viewing_user2->id));

        // Delete subject user.
        delete_user($subject_user);
        self::assertFalse(util::can_report_on_user($subject_user->id, $viewing_user1->id));
        self::assertFalse(util::can_report_on_user($subject_user->id, $viewing_user2->id));
    }

    public function test_can_report_on_user_if_has_capability_and_staff(): void {
        // Set up.
        $subject_user = self::getDataGenerator()->create_user();
        $viewing_manager_user = self::getDataGenerator()->create_user();

        $role_id = create_role(
            'testrole12346',
            'testrole12346',
            'testrole12346'
        );
        $manager_context = context_user::instance($viewing_manager_user->id);
        assign_capability('mod/perform:report_on_staff_responses', CAP_ALLOW, $role_id, $manager_context->id);
        role_assign($role_id, $viewing_manager_user->id, $manager_context->id);

        // Assign 'other user' as staff.
        $manager_ja = job_assignment::create_default($viewing_manager_user->id);
        $subject_user_ja = job_assignment::create_default($subject_user->id, ['managerjaid' => $manager_ja->id]);

        // Operate.
        self::setUser($viewing_manager_user);
        $result = util::can_report_on_user($subject_user->id, $viewing_manager_user->id);
        // Assert.
        self::assertTrue($result);
    }

    public function test_can_manage_participation(): void {
        $subject_user = self::getDataGenerator()->create_user();
        $manager1 = self::getDataGenerator()->create_user();
        $manager2 = self::getDataGenerator()->create_user();
        self::setUser($manager1);

        self::assertFalse(util::can_manage_participation($manager1->id, $subject_user->id));
        self::assertFalse(util::can_manage_participation($manager2->id, $subject_user->id));

        // Grant manage_all_participation capability to manager 1.
        $roleid = self::getDataGenerator()->create_role();
        assign_capability('mod/perform:manage_all_participation', CAP_ALLOW, $roleid, context_system::instance());
        $manager1_context = context_user::instance($manager1->id);
        role_assign($roleid, $manager1->id, $manager1_context);

        self::assertTrue(util::can_manage_participation($manager1->id, $subject_user->id));

        // Grant manage_subject_user_participation capability to manager 2 in the context of the subject user.
        $roleid = self::getDataGenerator()->create_role();
        assign_capability('mod/perform:manage_subject_user_participation', CAP_ALLOW, $roleid, context_system::instance());
        $subject_user_context = context_user::instance($subject_user->id);
        role_assign($roleid, $manager2->id, $subject_user_context);

        // Still can't manage that user because 'manage_staff_participation' is checked first by default and subject user is not in manager1's staff.
        self::assertFalse(util::can_manage_participation($manager2->id, $subject_user->id));

        // Remove 'manage_staff_participation' cap from user role.
        $user_role = builder::table('role')->where('shortname', 'user')->one(true);
        unassign_capability('mod/perform:manage_staff_participation', $user_role->id);

        // Now 'manage_subject_user_participation' check should be relevant.
        self::assertTrue(util::can_manage_participation($manager2->id, $subject_user->id));

        // Delete subject user.
        delete_user($subject_user);
        self::assertFalse(util::can_manage_participation($manager1->id, $subject_user->id));
        self::assertFalse(util::can_manage_participation($manager2->id, $subject_user->id));
    }

    /**
     * @param string $capability
     * @param stdClass $manager
     * @param stdClass $employee
     * @throws coding_exception
     */
    private function assign_manager_capability_over_employee(string $capability, stdClass $manager, stdClass $employee): void {
        $manager_job_assignment = job_assignment::create(
            [
                'userid' => $manager->id,
                'idnumber' => $manager->id,
            ]
        );

        job_assignment::create(
            [
                'userid' => $employee->id,
                'idnumber' => $employee->id,
                'managerjaid' => $manager_job_assignment->id,
            ]
        );

        $employee_context = context_user::instance($employee->id);
        assign_capability($capability, CAP_ALLOW, $manager->id, $employee_context);
    }

    /**
     * @param string $cap
     * @param stdClass $reporter
     * @param stdClass $subject
     */
    private function assign_reporter_cap_over_subject(string $cap, stdClass $reporter, stdClass $subject): void {
        $reporter_role_id = create_role(
            'Perform Reporter Role',
            'perform_reporter_role',
            'Can report on perform data'
        );

        $system_context = context_system::instance();
        assign_capability(
            $cap,
            CAP_ALLOW,
            $reporter_role_id,
            $system_context
        );

        self::getDataGenerator()->role_assign(
            $reporter_role_id,
            $reporter->id,
            context_user::instance($subject->id)
        );
    }

}
