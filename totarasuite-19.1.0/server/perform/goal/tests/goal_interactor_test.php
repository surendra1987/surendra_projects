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

use core_phpunit\testcase;
use core\entity\user;
use perform_goal\interactor\goal_interactor;
use perform_goal\testing\generator as goal_generator;
use perform_goal\testing\goal_generator_config;
use totara_core\advanced_feature;
use totara_tenant\local\util as tenant_util;

/**
 * Unit tests for the goal_interactor class and 2023 perform goals.
 */
class perform_goal_goal_interactor_test extends testcase {

    /**
     * Helper method to provide some data on a role.
     * @param string $test_capability
     * @param string $role_shortname
     * @return array
     */
    private function helper_get_roles_info_for_checking(string $test_capability, string $role_shortname): array {
        global $DB;
        // Find the authenticated user role, which should have the desired capability.
        $roles = get_roles_with_capability($test_capability);
        $role_ids_to_check = array_map(function ($role) {
            return $role->id;
        }, $roles);
        $role = $DB->get_record('role', ['shortname' => $role_shortname]);
        return [$role_ids_to_check, $role];
    }

    public function test_default_creation(): void {
        self::setAdminUser();
        $goal = goal_generator::instance()->create_goal();
        $test_user = self::getDataGenerator()->create_user();
        $context_user = context_user::instance($test_user->id);
        // Operate.
        $goal_interactor = new goal_interactor($context_user, $goal);
        // Assert
        self::assertInstanceOf(goal_interactor::class, $goal_interactor);
    }

    public function test_from_goal_creation(): void {
        self::setAdminUser();
        $goal = goal_generator::instance()->create_goal();
        // Operate
        $goal_interactor = goal_interactor::from_goal($goal);
        // Assert
        self::assertInstanceOf(goal_interactor::class, $goal_interactor);
    }

    public function test_for_user_creation(): void {
        self::setAdminUser();
        $test_user_model = self::getDataGenerator()->create_user();
        $test_user_entity = user::repository()->find($test_user_model->id);
        // Operate
        $goal_interactor = goal_interactor::for_user($test_user_entity);
        // Assert
        self::assertInstanceOf(goal_interactor::class, $goal_interactor);
    }

    public function test_can_view_own_personal_goals(): void {
        self::setAdminUser();
        $test_user = self::getDataGenerator()->create_user();
        $context_user = context_user::instance($test_user->id);
        // Find the authenticated user role, which should have the desired capability.
        $test_capability = 'perform/goal:viewownpersonalgoals';
        list($role_ids_to_check, $authenticated_user_role) = $this->helper_get_roles_info_for_checking($test_capability, 'user');
        self::setUser($test_user);

        // Operate #1. Check when it's just a user context passed in.
        $goal_interactor1 = new goal_interactor($context_user);
        $user_can_view_result1 = $goal_interactor1->can_view_personal_goals();
        // #2. Specific check for when the current user is the same as the goal subject user passed in for the goal_interactor.
        $test_user_entity = user::repository()->find($test_user->id);
        $goal_interactor2 = goal_interactor::for_user($test_user_entity);
        $user_can_view_result2 = $goal_interactor2->can_view_personal_goals();

        // Assert - when a user's role has the capability, we expect them to be able to view own personal goals.
        self::assertContains($authenticated_user_role->id, $role_ids_to_check);
        self::assertTrue($user_can_view_result1);
        self::assertTrue($user_can_view_result2);
    }

    public function test_cannot_view_own_personal_goals_without_capability(): void {
        global $DB;
        self::setAdminUser();
        $test_user = self::getDataGenerator()->create_user();
        $context_user = context_user::instance($test_user->id); // this will act like the "currently logged in user"'s context.
        $test_capability = 'perform/goal:viewownpersonalgoals';

        // Find the 'authenticated user' role that the user already has, which should have the desired capability. Remove
        // (prevent) the capability for testing.
        $authenticated_user_role = $DB->get_record('role', ['shortname' => 'user']);
        assign_capability($test_capability, CAP_PREVENT, $authenticated_user_role->id, $context_user->id, true);
        self::setUser($test_user);

        // Operate.
        $goal_interactor = new goal_interactor($context_user);
        $user_can_view_result = $goal_interactor->can_view_personal_goals();

        // Assert - the user does not have the capability from their role to match the capability check in the context, so
        // we expect a negative result.
        self::assertFalse($user_can_view_result);
    }

    public function test_can_create_own_personal_goal(): void {
        self::setAdminUser();
        $test_capability = 'perform/goal:manageownpersonalgoals';
        $test_user = self::getDataGenerator()->create_user();
        $test_user_entity = user::repository()->find($test_user->id);
        list($role_ids_to_check, $authenticated_user_role) = $this->helper_get_roles_info_for_checking($test_capability, 'user');
        self::setUser($test_user);

        // Operate - check for when the current user is the same user passed in for the goal_interactor.
        $goal_interactor = goal_interactor::for_user($test_user_entity);
        $user_can_create_result = $goal_interactor->can_create_personal_goal();

        // Assert - when a user's role has the capability, we expect them to be able to create own personal goals.
        self::assertContains($authenticated_user_role->id, $role_ids_to_check);
        self::assertTrue($user_can_create_result);
    }

    public function test_cannot_create_own_personal_goals_without_capability(): void {
        global $DB;
        self::setAdminUser();
        $test_capability = 'perform/goal:manageownpersonalgoals';
        $test_user = self::getDataGenerator()->create_user();
        $context_user = context_user::instance($test_user->id); // this will act like the "currently logged in user"'s context.

        // Find the 'authenticated user' role that the user already has, which should have the desired capability. Remove
        // (prevent) the capability.
        $authenticated_user_role = $DB->get_record('role', ['shortname' => 'user']);
        assign_capability($test_capability, CAP_PREVENT, $authenticated_user_role->id, $context_user->id, true);
        self::setUser($test_user);

        // Operate.
        $goal_interactor = new goal_interactor($context_user);
        $user_can_create_result = $goal_interactor->can_create_personal_goal();

        // Assert - the user does not have the capability from their role to match the capability check in the context, so
        // we expect a negative result.
        self::assertFalse($user_can_create_result);
    }

    public function test_can_set_own_progress(): void {
        self::setAdminUser();
        $test_capability = 'perform/goal:setownpersonalgoalprogress';
        $test_user = self::getDataGenerator()->create_user();
        $test_user_context = context_user::instance($test_user->id);
        list($role_ids_to_check, $authenticated_user_role) = $this->helper_get_roles_info_for_checking($test_capability, 'user');
        self::setUser($test_user);

        // Create a goal with test_user as the subject
        $config = goal_generator_config::new(['context' => $test_user_context, 'user_id' => $test_user->id]);
        $goal = goal_generator::instance()->create_goal($config);

        // Operate - check for when the current user is the same as the subject user on the goal passed to the goal_interactor.
        $goal_interactor = goal_interactor::from_goal($goal);
        $user_can_set_result = $goal_interactor->can_set_progress();

        // Assert - when a user's role has the capability, we expect them to be able to set own goal progress.
        self::assertContains($authenticated_user_role->id, $role_ids_to_check);
        self::assertTrue($user_can_set_result);
    }

    public function test_cannot_set_own_progress_without_capability(): void {
        global $DB;
        self::setAdminUser();
        $test_capability = 'perform/goal:setownpersonalgoalprogress';
        $test_user = self::getDataGenerator()->create_user();
        $test_user_context = context_user::instance($test_user->id); // this will act like the "currently logged in user"'s context.

        // Create a goal with test_user as the subject
        $config = goal_generator_config::new(['context' => $test_user_context, 'user_id' => $test_user->id]);
        $goal = goal_generator::instance()->create_goal($config);

        // Find the 'authenticated user' role that the user already has, which should have the desired capability. Remove
        // (prevent) the capability.
        $authenticated_user_role = $DB->get_record('role', ['shortname' => 'user']);
        assign_capability($test_capability, CAP_PREVENT, $authenticated_user_role->id, $test_user_context->id, true);
        self::setUser($test_user);

        // Operate.
        $goal_interactor = goal_interactor::from_goal($goal);
        $user_can_set_result = $goal_interactor->can_set_progress();

        // Assert - the user does not have the capability from their role to match the capability check in the context, so
        // we expect a negative result.
        self::assertFalse($user_can_set_result);
    }

    public function test_can_view_or_create_or_setprogress_on_personal_goals_for_others(): void {
        global $DB;
        self::setAdminUser();
        $test_capabilities = ['perform/goal:viewpersonalgoals', 'perform/goal:managepersonalgoals', 'perform/goal:setpersonalgoalprogress'];
        // We expect a 'staffmanager' user to be able to deal with the personal goals of a 'learner' user.
        $learner_user = self::getDataGenerator()->create_user();
        $staffmanager_user = self::getDataGenerator()->create_user();
        // Find the 'staffmanager' role, which should have the desired capabilities. Assign it.
        $staffmanager_user_role = $DB->get_record('role', ['shortname' => 'staffmanager']);
        $context_learner_user = context_user::instance($learner_user->id); // this will act like the "goal subject user"'s context.
        role_assign($staffmanager_user_role->id, $staffmanager_user->id, $context_learner_user->id);

        $goal_test_config = goal_generator_config::new([
            'context' => $context_learner_user,
            'name' => 'Test goal custom name' . time(),
            'owner_id' => $staffmanager_user->id,
            'user_id' => $learner_user->id,
        ]);
        $test_goal = goal_generator::instance()->create_goal($goal_test_config);

        $goal_interactor = goal_interactor::from_goal($test_goal);
        self::setUser($staffmanager_user);

        foreach($test_capabilities as $test_capability) {
            $roles = get_roles_with_capability($test_capability);
            $role_ids_to_check = array_map(function ($role) {
                return $role->id;
            }, $roles);

            // Operate.
            if ($test_capability === 'perform/goal:viewpersonalgoals') {
                $staffmanager_user_result = $goal_interactor->can_view();
            } else if ($test_capability === 'perform/goal:managepersonalgoals') {
                $staffmanager_user_result = $goal_interactor->can_manage();
            } else { // 'perform/goal:setpersonalgoalprogress'
                $staffmanager_user_result = $goal_interactor->can_set_progress();
            }

            // Assert - when a user's role has the capability, we expect them to be able.
            self::assertContains($staffmanager_user_role->id, $role_ids_to_check);
            self::assertTrue($staffmanager_user_result);
        }
    }

    /**
     * Test when capability is assigned to role in tenant context, for tenant member personal goal (expect yes).
     * @return void
     */
    public function test_role_in_tenant_context_for_tenant_member_personal_goal(): void {
        global $CFG;
        // Set up.
        self::setAdminUser();
        // Create a tenant.
        $tenant_generator = self::getDataGenerator()->get_plugin_generator('totara_tenant');
        $tenant_generator->enable_tenants();
        $original_config_setting = $CFG->tenantsisolated;
        set_config('tenantsisolated', '0');
        $tenant1 = $tenant_generator->create_tenant();
        $context_tenant = context_tenant::instance($tenant1->id);

        $test_capability = 'perform/goal:viewpersonalgoals';
        // Create a tenant subject user.
        $subject_user = $this->getDataGenerator()->create_user(['tenantid' => $tenant1->id]);
        $subject_user_context = context_user::instance($subject_user->id);

        // Create a staffmanager user (also a tenant member).
        $staffmanager_user = self::getDataGenerator()->create_user(['tenantid' => $tenant1->id]);
        // Create a new test role. Assign the role in tenant context to the $staffmanager_user.
        $role_id = self::getDataGenerator()->create_role();
        assign_capability($test_capability, CAP_ALLOW, $role_id, $context_tenant);
        role_assign($role_id, $staffmanager_user->id, $context_tenant);
        $goal_test_config = goal_generator_config::new([
            'context' => $subject_user_context,
            'name' => 'Test goal custom name' . time(),
            'owner_id' => $staffmanager_user->id,
            'user_id' => $subject_user->id,
        ]);
        $test_goal = goal_generator::instance()->create_goal($goal_test_config);
        $goal_interactor = goal_interactor::from_goal($test_goal);
        self::setUser($staffmanager_user);

        // Operate.
        $result = $goal_interactor->can_view();

        // Assert.
        self::assertTrue($result);

        // Tear down.
        set_config('tenantsisolated', $original_config_setting);
    }

    /**
     * When capability is assigned to role in tenant context...
     * a) Test for tenant participant personal goal with isolation off (expect no)
     * b) Test for tenant participant personal goal with isolation on (expect no).
     * @return void
     */
    public function test_role_in_tenant_context_for_tenant_participant_personal_goal(): void {
        global $CFG;
        // Set up.
        self::setAdminUser();
        // Create a tenant.
        $tenant_generator = self::getDataGenerator()->get_plugin_generator('totara_tenant');
        $tenant_generator->enable_tenants();
        $original_config_setting = $CFG->tenantsisolated;
        $tenant1 = $tenant_generator->create_tenant();
        $context_tenant = context_tenant::instance($tenant1->id);

        $test_capability = 'perform/goal:viewpersonalgoals';

        // Create a tenant member user
        $tenant_member_user = self::getDataGenerator()->create_user(['tenantid' => $tenant1->id]);
        // Create a new test role. Assign the role in tenant context to the $staffmanager_user.
        $role_id = self::getDataGenerator()->create_role();
        assign_capability($test_capability, CAP_ALLOW, $role_id, $context_tenant);
        // Note that role is assigned in tenant context.
        role_assign($role_id, $tenant_member_user->id, $context_tenant);

        // Create a user who is only a participant of the tenant.
        $tenant_participant_user = self::getDataGenerator()->create_user();
        $tenant_participant_user_context = context_user::instance($tenant_participant_user->id);
        $tenant_generator->set_user_participation($tenant_participant_user->id, [$tenant1->id]);

        $goal_test_config = goal_generator_config::new([
            'context' => $tenant_participant_user_context,
            'name' => 'Test goal custom name' . time(),
            'owner_id' => $tenant_member_user->id,
            'user_id' => $tenant_participant_user->id,
        ]);
        $test_goal = goal_generator::instance()->create_goal($goal_test_config);

        // a) Test for tenant member viewing tenant participant personal goal with isolation off (yes)
        set_config('tenantsisolated', '0');
        self::setUser($tenant_member_user);
        $goal_interactor = goal_interactor::from_goal($test_goal);
        // Operate.
        $result_a = $goal_interactor->can_view();
        self::setUser(null);

        // b) Test for tenant member viewing tenant participant personal goal with isolation on (no).
        set_config('tenantsisolated', '1');
        self::setUser($tenant_member_user);
        $goal_interactor = goal_interactor::from_goal($test_goal);
        // Operate.
        $result_b = $goal_interactor->can_view();

        // Assert - when a user's role meets the capability check, we expect user able to view personal goals.
        self::assertFalse($result_a);
        self::assertFalse($result_b);

        // Tear down.
        set_config('tenantsisolated', $original_config_setting);
    }

    /**
     *  Capability is assigned to role in tenant context...
     * a) Test for system user personal goal with isolation off (no).
     * b) Test for system user personal goal with isolation on (no)
     * @return void
     */
    public function test_role_in_tenant_context_for_system_user_personal_goal(): void {
        global $CFG;
        // Set up.
        self::setAdminUser();
        // Create a tenant.
        $tenant_generator = self::getDataGenerator()->get_plugin_generator('totara_tenant');
        $tenant_generator->enable_tenants();
        $original_config_setting = $CFG->tenantsisolated;
        $tenant1 = $tenant_generator->create_tenant();
        $context_tenant = context_tenant::instance($tenant1->id);

        $test_capability = 'perform/goal:viewpersonalgoals';

        // Create a staffmanager user - will act as the currently logged in user.
        $staffmanager_user = self::getDataGenerator()->create_user(['tenantid' => $tenant1->id]);
        // Create a new test role. Assign the role in tenant context to the $staffmanager_user.
        $role_id = self::getDataGenerator()->create_role();
        assign_capability($test_capability, CAP_ALLOW, $role_id, $context_tenant);
        // Note that role is assigned in tenant context.
        role_assign($role_id, $staffmanager_user->id, $context_tenant);

        // Create a system learner user.
        $learner_user = self::getDataGenerator()->create_user();
        $learner_user_context = context_user::instance($learner_user->id);

        $goal_test_config = goal_generator_config::new([
            'context' => $learner_user_context,
            'name' => 'Test goal custom name' . time(),
            'owner_id' => $staffmanager_user->id,
            'user_id' => $learner_user->id,
        ]);
        $test_goal = goal_generator::instance()->create_goal($goal_test_config);
        self::setUser($staffmanager_user);

        // a) For system user personal goal with isolation off (no).
        set_config('tenantsisolated', '0');
        $goal_interactor = goal_interactor::from_goal($test_goal);
        // Operate.
        $result_a = $goal_interactor->can_view();

        // b) For system user personal goal with isolation on (no).
        set_config('tenantsisolated', '1');
        $goal_interactor = goal_interactor::from_goal($test_goal);
        // Operate.
        $result_b = $goal_interactor->can_view();

        // Assert - when a user's role meets the capability check, we expect able to view personal goals.
        self::assertFalse($result_a);
        self::assertFalse($result_b);

        // Tear down.
        set_config('tenantsisolated', $original_config_setting);
    }

    /**
     * Capability is assigned to role in system context...
     * a) For tenant member personal goal with isolation off (yes)
     * b) For tenant member personal goal with isolation on (yes)
     * @return void
     */
    public function test_role_in_system_context_for_tenant_member_personal_goal(): void {
        global $CFG;
        // Set up.
        self::setAdminUser();
        // Create a tenant.
        $tenant_generator = self::getDataGenerator()->get_plugin_generator('totara_tenant');
        $tenant_generator->enable_tenants();
        $original_config_setting = $CFG->tenantsisolated;
        $tenant1 = $tenant_generator->create_tenant();
        $context_tenant = context_tenant::instance($tenant1->id);

        $test_capability = 'perform/goal:viewpersonalgoals';
        $context_system = context_system::instance();
        // Create a tenant member subject user.
        $subject_user = $this->getDataGenerator()->create_user(['tenantid' => $tenant1->id]);
        $subject_user_context = context_user::instance($subject_user->id);

        // Create a staffmanager user.
        $staffmanager_user = self::getDataGenerator()->create_user(['tenantid' => $tenant1->id]);
        // Create a new test role. Assign the role in system context to the $staffmanager_user.
        $role_id = self::getDataGenerator()->create_role();
        assign_capability($test_capability, CAP_ALLOW, $role_id, $context_system);
        role_assign($role_id, $staffmanager_user->id, $context_system);

        $goal_test_config = goal_generator_config::new([
            'context' => $subject_user_context,
            'name' => 'Test goal custom name' . time(),
            'owner_id' => $staffmanager_user->id,
            'user_id' => $subject_user->id,
        ]);
        $test_goal = goal_generator::instance()->create_goal($goal_test_config);
        self::setUser($staffmanager_user);

        // a) For tenant member personal goal with isolation off (yes).
        set_config('tenantsisolated', '0');
        $goal_interactor = new goal_interactor($context_tenant, $test_goal);
        // Operate.
        $result_a = $goal_interactor->can_view();

        // b) For tenant member personal goal with isolation on (yes)
        set_config('tenantsisolated', '1');
        $goal_interactor = goal_interactor::from_goal($test_goal);
        // Operate.
        $result_b = $goal_interactor->can_view();

        // Assert.
        self::assertTrue($result_a);
        self::assertTrue($result_b);

        // Tear down.
        set_config('tenantsisolated', $original_config_setting);
    }

    /**
     * Test capability assigned to role in system context, for system user personal goal (yes).
     * @return void
     */
    public function test_role_in_system_context_for_system_user_personal_goal(): void {
        // Set up.
        self::setAdminUser();
        $test_capability = 'perform/goal:viewpersonalgoals';
        $context_system = context_system::instance();
        // Create a system subject user.
        $subject_user = $this->getDataGenerator()->create_user();
        $subject_user_context = context_user::instance($subject_user->id);

        // Create a system staffmanager user.
        $staffmanager_user = self::getDataGenerator()->create_user();
        // Create a new test role. Assign the role in system context to the $staffmanager_user.
        $role_id = self::getDataGenerator()->create_role();
        assign_capability($test_capability, CAP_ALLOW, $role_id, $context_system);
        role_assign($role_id, $staffmanager_user->id, $context_system);

        $goal_test_config = goal_generator_config::new([
            'context' => $subject_user_context,
            'name' => 'Test goal custom name' . time(),
            'owner_id' => $staffmanager_user->id,
            'user_id' => $subject_user->id,
        ]);
        $test_goal = goal_generator::instance()->create_goal($goal_test_config);
        self::setUser($staffmanager_user);
        $goal_interactor = goal_interactor::from_goal($test_goal);

        // Operate.
        $result = $goal_interactor->can_view();

        // Assert.
        self::assertTrue($result);
    }

    public function test_action_check_for_other_users_without_a_goal_returns_false(): void {
        $test_capabilities = ['perform/goal:viewpersonalgoals', 'perform/goal:managepersonalgoals', 'perform/goal:setpersonalgoalprogress'];
        // Set up.
        self::setAdminUser();
        // Create a system subject user.
        $subject_user = $this->getDataGenerator()->create_user();
        $subject_user_entity = new user($subject_user->id);
        // Create a system staffmanager user.
        $staffmanager_user = self::getDataGenerator()->create_user();

        $goal_interactor = goal_interactor::for_user($subject_user_entity); // no goal passed in.
        self::setUser($staffmanager_user);

        foreach($test_capabilities as $test_capability) {
            // Operate.
            if ($test_capability === 'perform/goal:viewpersonalgoals') {
                $staffmanager_user_result = $goal_interactor->can_view();
            } else if ($test_capability === 'perform/goal:managepersonalgoals') {
                $staffmanager_user_result = $goal_interactor->can_manage();
            } else { // 'perform/goal:setpersonalgoalprogress'
                $staffmanager_user_result = $goal_interactor->can_set_progress();
            }

            // Assert - when a user's role has the capability, we expect to be able.
            self::assertFalse($staffmanager_user_result);
        }
    }

    public function test_can_set_owner_id_for_goal_in_tenant_user_context() {
        self::setAdminUser();

        /** @var \totara_tenant\testing\generator $tenant_generator */
        $tenant_generator = self::getDataGenerator()->get_plugin_generator('totara_tenant');
        $tenant_generator->enable_tenants();

        set_config('tenantsisolated', '0');

        $tenant1 = $tenant_generator->create_tenant();
        $context_tenant1 = context_tenant::instance($tenant1->id);
        $tenant2 = $tenant_generator->create_tenant();
        $context_tenant2 = context_tenant::instance($tenant2->id);

        // Create tenant users and system user.
        $tenant1_user = self::getDataGenerator()->create_user(['tenantid' => $tenant1->id]);
        $tenant1_another_user = self::getDataGenerator()->create_user(['tenantid' => $tenant1->id]);
        $tenant2_user = self::getDataGenerator()->create_user(['tenantid' => $tenant2->id]);
        $system_user = self::getDataGenerator()->create_user();

        // Create a goal in the context of tenant1 user.
        $goal_test_config = goal_generator_config::new([
            'context' => context_user::instance($tenant1_user->id),
            'name' => 'Test goal user tenant 1',
            'owner_id' => $tenant1_user->id,
            'user_id' => $tenant1_user->id,
        ]);
        $test_goal = goal_generator::instance()->create_goal($goal_test_config);

        $goal_interactor = goal_interactor::from_goal($test_goal);

        // We are acting as a system user (admin).

        // Should not be allowed to set owner of different tenant.
        self::assertFalse($goal_interactor->can_set_owner_id($tenant2_user->id));

        // Should be allowed to set owner of same tenant.
        self::assertTrue($goal_interactor->can_set_owner_id($tenant1_another_user->id));

        // Should be allowed to set system user.
        self::assertTrue($goal_interactor->can_set_owner_id($system_user->id));

        set_config('tenantsisolated', '1');

        // Same results with tenant isolation because we are acting as a system user.
        self::assertFalse($goal_interactor->can_set_owner_id($tenant2_user->id));
        self::assertTrue($goal_interactor->can_set_owner_id($tenant1_another_user->id));
        self::assertTrue($goal_interactor->can_set_owner_id($system_user->id));

        $tenant_generator->disable_tenants();

        // Multitenancy disabled: no restrictions.
        self::assertTrue($goal_interactor->can_set_owner_id($tenant2_user->id));
        self::assertTrue($goal_interactor->can_set_owner_id($tenant1_another_user->id));
        self::assertTrue($goal_interactor->can_set_owner_id($system_user->id));

        // Now acting as tenant user.
        $tenant_generator->enable_tenants();
        set_config('tenantsisolated', '0');
        self::setUser($tenant1_user);

        $goal_interactor = goal_interactor::from_goal($test_goal);

        // Should not be allowed to set owner of different tenant.
        self::assertFalse($goal_interactor->can_set_owner_id($tenant2_user->id));

        // Should be allowed to set owner of same tenant.
        self::assertTrue($goal_interactor->can_set_owner_id($tenant1_another_user->id));

        // Should be allowed to set system user.
        self::assertTrue($goal_interactor->can_set_owner_id($system_user->id));

        set_config('tenantsisolated', '1');

        // With tenant isolation the difference is we can't set a system user (because we are acting as tenant user).
        self::assertFalse($goal_interactor->can_set_owner_id($tenant2_user->id));
        self::assertTrue($goal_interactor->can_set_owner_id($tenant1_another_user->id));
        self::assertFalse($goal_interactor->can_set_owner_id($system_user->id));

        $tenant_generator->disable_tenants();

        // Multitenancy disabled: no restrictions.
        self::assertTrue($goal_interactor->can_set_owner_id($tenant2_user->id));
        self::assertTrue($goal_interactor->can_set_owner_id($tenant1_another_user->id));
        self::assertTrue($goal_interactor->can_set_owner_id($system_user->id));
    }

    public function test_with_tenant_participants(): void {
        self::setAdminUser();
        $tenant_generator = self::getDataGenerator()->get_plugin_generator('totara_tenant');
        $tenant_generator->enable_tenants();
        set_config('tenantsisolated', '1');

        $tenant1 = $tenant_generator->create_tenant();
        $tenant2 = $tenant_generator->create_tenant();

        // Create tenant users.
        $tenant1_member_user = self::getDataGenerator()->create_user(['tenantid' => $tenant1->id]);
        $tenant1_member_user2 = self::getDataGenerator()->create_user(['tenantid' => $tenant1->id]);
        $tenant1_participant_user = self::getDataGenerator()->create_user();
        tenant_util::add_other_participant($tenant1->id, $tenant1_participant_user->id);
        $tenant2_member_user = self::getDataGenerator()->create_user(['tenantid' => $tenant2->id]);
        $tenant2_participant_user = self::getDataGenerator()->create_user();
        tenant_util::add_other_participant($tenant2->id, $tenant2_participant_user->id);

        // Create a goal in the context of tenant1 user.
        $goal_test_config = goal_generator_config::new([
            'context' => context_user::instance($tenant1_member_user->id),
            'name' => 'Test goal user tenant 1',
            'owner_id' => $tenant1_member_user->id,
            'user_id' => $tenant1_member_user->id,
        ]);
        $test_goal = goal_generator::instance()->create_goal($goal_test_config);

        self::setUser($tenant1_member_user);
        $goal_interactor = goal_interactor::from_goal($test_goal);

        // Scenarios - owner is a member or participant of the same tenant.
        self::assertTrue($goal_interactor->can_set_owner_id($tenant1_member_user2->id));
        self::assertTrue($goal_interactor->can_set_owner_id($tenant1_participant_user->id));
        // Scenarios - owner is a member or participant of a different tenant.
        self::assertFalse($goal_interactor->can_set_owner_id($tenant2_member_user->id));
        self::assertFalse($goal_interactor->can_set_owner_id($tenant2_participant_user->id));
    }

    public function test_goal_feature_enabled_or_disabled(): void {
        self::setAdminUser();

        $user = self::getDataGenerator()->create_user();
        $config = goal_generator_config::new(
            [
                'context' => context_user::instance($user->id),
                'user_id' => $user->id
            ]
        );

        $goal = goal_generator::instance()->create_goal($config);
        $interactor = goal_interactor::from_goal($goal);

        self::setUser($user);
        $feature = 'perform_goals';
        advanced_feature::disable($feature);

        self::assertFalse($interactor->can_view_personal_goals());
        self::assertFalse($interactor->can_create_personal_goal());
        self::assertFalse($interactor->can_view());
        self::assertFalse($interactor->can_manage());
        self::assertFalse($interactor->can_set_progress());

        advanced_feature::enable($feature);
        self::assertTrue($interactor->can_view_personal_goals());
        self::assertTrue($interactor->can_create_personal_goal());
        self::assertTrue($interactor->can_view());
        self::assertTrue($interactor->can_manage());
        self::assertTrue($interactor->can_set_progress());
    }

    public function test_acting_user_instantiated_by_method_from_goal(): void {
        self::setAdminUser();

        $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();

        $config = goal_generator_config::new(['user_id' => $user1->id]);
        $goal = goal_generator::instance()->create_goal($config);

        self::setUser($user1);

        $interactor = goal_interactor::from_goal($goal);

        // By default, the currently logged-in user.
        self::assertEquals($user1->id, $interactor->get_acting_user()->id);

        $interactor = goal_interactor::from_goal($goal, $user2->id);

        // Now the passed-in user.
        self::assertEquals($user2->id, $interactor->get_acting_user()->id);
    }

    public function test_acting_user_instantiated_by_method_for_user(): void {
        self::setAdminUser();

        $user1 = self::getDataGenerator()->create_user();
        $user1_entity = new user($user1->id);

        $user2 = self::getDataGenerator()->create_user();
        $user2_entity = new user($user2->id);

        $config = goal_generator_config::new(['user_id' => $user1->id]);
        $goal = goal_generator::instance()->create_goal($config);

        self::setUser($user1);

        $interactor = goal_interactor::for_user($user2_entity);

        // By default, the currently logged-in user.
        self::assertEquals($user1->id, $interactor->get_acting_user()->id);

        $interactor = goal_interactor::for_user($user2_entity, $user2->id);

        // Now the passed-in user.
        self::assertEquals($user2->id, $interactor->get_acting_user()->id);
    }

    public function test_external_participant_cannot_view_personal_goals_title(): void {
        // Set up.
        self::setAdminUser();
        $user1 = self::getDataGenerator()->create_user();
        $config = goal_generator_config::new(
            [
                'context' => context_user::instance($user1->id),
                'user_id' => $user1->id
            ]
        );
        $goal = goal_generator::instance()->create_goal($config);

        // Operate - null for a currently logged-in user means this acts like for an external participant, i.e. because
        // there will be no valid session auth for a normal user.
        self::setUser(null);
        $interactor = goal_interactor::from_goal($goal);

        // Assert.
        self::assertNull(user::logged_in());
        self::assertFalse($interactor->can_view_personal_goals());
    }
}
