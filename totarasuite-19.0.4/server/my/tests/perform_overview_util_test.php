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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package core_my
 */

use core_my\perform_overview_util;
use core_phpunit\testcase;
use flavour_learn\definition as learn_flavour;
use flavour_perform\definition as perform_flavour;
use totara_flavour\definition as flavour;
use totara_job\job_assignment;
use totara_core\advanced_feature;

class core_my_perform_overview_util_test extends testcase {

    protected function setUp(): void {
        parent::setUp();
        self::setAdminUser();
        advanced_feature::enable('performance_activities');
        advanced_feature::enable('perform_goals');
        advanced_feature::enable('competency_assignment');

        perform_overview_util::reset_permission_cache();
    }

    protected function tearDown(): void {
        perform_overview_util::reset_permission_cache();

        parent::tearDown();
    }

    public function test_check_guest_permission() {
        $this->setGuestUser();
        self::assertFalse(perform_overview_util::has_any_permission());
    }

    public function test_check_not_logged_in_user_permission() {
        $this->setUser();
        self::assertFalse(perform_overview_util::has_any_permission(null));
        self::assertFalse(perform_overview_util::has_any_permission());
    }

    public function test_check_admin_permission() {
        self::setAdminUser();
        self::assertTrue(perform_overview_util::has_any_permission());

    }

    public function test_check_user_permission() {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        self::assertTrue(perform_overview_util::has_any_permission());
    }

    public function test_check_manager_permission() {
        $user = $this->getDataGenerator()->create_user();
        $manager = $this->getDataGenerator()->create_user();
        $managerja = job_assignment::create_default($manager->id);
        $data = [
            'userid' => $user->id,
            'managerjaid' => $managerja->id,
            'idnumber' => 'id1',
        ];
        job_assignment::create($data);

        $this->setUser($manager);
        self::assertTrue(perform_overview_util::has_any_permission());
    }

    public function test_check_manager_permission_for_user() {
        $user = $this->getDataGenerator()->create_user();
        $manager = $this->getDataGenerator()->create_user();
        $managerja = job_assignment::create_default($manager->id);
        $data = [
            'userid' => $user->id,
            'managerjaid' => $managerja->id,
            'idnumber' => 'id1',
        ];
        job_assignment::create($data);

        $this->setUser($manager);
        self::assertTrue(perform_overview_util::has_any_permission($user->id));
    }

    public function test_check_user_permission_for_manager() {
        $user = $this->getDataGenerator()->create_user();
        $manager = $this->getDataGenerator()->create_user();
        $managerja = job_assignment::create_default($manager->id);
        $data = [
            'userid' => $user->id,
            'managerjaid' => $managerja->id,
            'idnumber' => 'id1',
        ];
        job_assignment::create($data);

        $this->setUser($user);
        self::assertFalse(perform_overview_util::has_any_permission($manager->id));
    }

    public function test_can_view_activities_overview(): void {
        self::assertFalse(perform_overview_util::can_view_activities_overview_for(null));

        $subject_user = self::getDataGenerator()->create_user();
        $manager1 = self::getDataGenerator()->create_user();

        self::setUser($subject_user);
        perform_overview_util::reset_permission_cache();
        self::assertTrue(perform_overview_util::can_view_activities_overview_for($subject_user->id));

        self::setUser($manager1);
        perform_overview_util::reset_permission_cache();
        self::assertFalse(perform_overview_util::can_view_activities_overview_for($subject_user->id));

        // Grant manage_all_participation capability to manager 1.
        $roleid = self::getDataGenerator()->create_role();
        assign_capability('mod/perform:manage_all_participation', CAP_ALLOW, $roleid, context_system::instance());
        $manager1_context = context_user::instance($manager1->id);
        role_assign($roleid, $manager1->id, $manager1_context);

        self::setUser($manager1);
        perform_overview_util::reset_permission_cache();
        self::assertTrue(perform_overview_util::can_view_activities_overview_for($subject_user->id));
    }

    public function test_can_view_competencies_overview(): void {
        self::assertFalse(perform_overview_util::can_view_competencies_overview_for(null));

        $subject_user = self::getDataGenerator()->create_user();
        $manager1 = self::getDataGenerator()->create_user();

        self::setUser($subject_user);
        perform_overview_util::reset_permission_cache();
        self::assertTrue(perform_overview_util::can_view_competencies_overview_for($subject_user->id));

        self::setUser($manager1);
        perform_overview_util::reset_permission_cache();
        self::assertFalse(perform_overview_util::can_view_competencies_overview_for($subject_user->id));

        // Grant view_other_profile capability to manager 1.
        $roleid = self::getDataGenerator()->create_role();
        assign_capability('totara/competency:view_other_profile', CAP_ALLOW, $roleid, context_user::instance($subject_user->id));
        $subject_user_context = context_user::instance($subject_user->id);
        role_assign($roleid, $manager1->id, $subject_user_context);

        self::setUser($manager1);
        perform_overview_util::reset_permission_cache();
        self::assertTrue(perform_overview_util::can_view_competencies_overview_for($subject_user->id));
    }

    public function test_can_view_goals_overview(): void {
        self::assertFalse(perform_overview_util::can_view_perform_goals_overview_for(null));

        $subject_user = self::getDataGenerator()->create_user();
        $manager1 = self::getDataGenerator()->create_user();

        self::setUser($subject_user);
        perform_overview_util::reset_permission_cache();
        self::assertTrue(perform_overview_util::can_view_perform_goals_overview_for($subject_user->id));

        self::setUser($manager1);
        perform_overview_util::reset_permission_cache();
        self::assertFalse(perform_overview_util::can_view_perform_goals_overview_for($subject_user->id));

        // Grant goals capability to manager 1.
        $roleid = self::getDataGenerator()->create_role();
        assign_capability('perform/goal:viewpersonalgoals', CAP_ALLOW, $roleid, context_system::instance());
        role_assign($roleid, $manager1->id, context_system::instance());

        self::setUser($manager1);
        perform_overview_util::reset_permission_cache();
        self::assertTrue(perform_overview_util::can_view_perform_goals_overview_for($subject_user->id));
    }

    /**
     * Data provider for test_is_overview_disabled().
     */
    public static function td_is_overview_disabled(): array {
        $activities = 'performance_activities';
        $competencies = 'competency_assignment';
        $goals = 'perform_goals';

        $learn_flavour = new learn_flavour();
        $perform_flavour = new perform_flavour();

        return [
            'learn flavour, no features enabled' => [
                true,
                $learn_flavour,
                [],
                [$activities, $competencies, $goals]
            ],
            'learn flavour, one feature enabled' => [
                true,
                $learn_flavour,
                [$activities],
                [$competencies, $goals]
            ],
            'learn flavour, all features' => [
                true,
                $learn_flavour,
                [$activities, $competencies, $goals],
                []
            ],
            'perform flavour, no features enabled' => [
                true,
                $perform_flavour,
                [],
                [$activities, $competencies, $goals]
            ],
            'perform flavour, one feature enabled' => [
                false,
                $perform_flavour,
                [$activities],
                [$competencies, $goals]
            ],
            'perform flavour, all features' => [
                false,
                $perform_flavour,
                [$activities, $competencies, $goals],
                []
            ]
        ];
    }

    /**
     * @dataProvider td_is_overview_disabled
     */
    public function test_is_overview_disabled(
        bool $expected,
        flavour $flavour,
        array $features_enabled,
        array $feature_disabled
    ): void {
        foreach ($features_enabled as $feature) {
            advanced_feature::enable($feature);
        }

        foreach ($feature_disabled as $feature) {
            advanced_feature::disable($feature);
        }

        self::assertEquals(
            $expected,
            perform_overview_util::is_overview_disabled($flavour)
        );
    }
}
