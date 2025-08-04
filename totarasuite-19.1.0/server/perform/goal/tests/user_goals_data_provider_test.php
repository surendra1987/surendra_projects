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
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package perform_goal
 */

use core\entity\user;
use core_phpunit\testcase;
use perform_goal\data_provider\user_goals;
use perform_goal\entity\goal as goal_entity;
use perform_goal\testing\generator as goal_generator;
use perform_goal\testing\goal_generator_config;
use perform_goal\model\goal as goal_model;
use totara_job\job_assignment;

/**
 * Unit tests for the goal_data_provider class.
 */
class perform_goal_user_goals_data_provider_test extends testcase {

    public function test_get_results_successful(): void {
        self::setAdminUser();

        $subject_user1 = self::getDataGenerator()->create_user();
        $subject_user2 = self::getDataGenerator()->create_user();

        // Create two goals for user 1
        $goal1 = goal_generator::instance()->create_goal(
            goal_generator_config::new(['user_id' => $subject_user1->id, 'context' => context_user::instance($subject_user1->id)])
        );
        $goal2 = goal_generator::instance()->create_goal(
            goal_generator_config::new(['user_id' => $subject_user1->id, 'context' => context_user::instance($subject_user1->id)])
        );

        // Create a goal for user 2
        $goal3 = goal_generator::instance()->create_goal(
            goal_generator_config::new(['user_id' => $subject_user2->id, 'context' => context_user::instance($subject_user2->id)])
        );

        self::setUser($subject_user1);
        $data_provider = new user_goals($subject_user1->id);
        $this->assert_result(
            [$goal1->id, $goal2->id],
            $data_provider
        );

        self::setUser($subject_user2);
        $data_provider = new user_goals($subject_user2->id);
        $this->assert_result(
            [$goal3->id],
            $data_provider
        );
    }

    public function test_empty_result_for_non_existing_user_id(): void {
        self::setAdminUser();

        $non_existing_user_id = 999;
        while (user::repository()->where('id', $non_existing_user_id)->exists()) {
            $non_existing_user_id ++;
        }
        $data_provider = new user_goals($non_existing_user_id);

        $items = $data_provider->get_page_results_with_permissions()->items;
        self::assertEquals(0, $items->count());
    }

    public function test_empty_result_for_missing_capabilities(): void {
        self::setAdminUser();

        $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();

        $goal = goal_generator::instance()->create_goal(
            goal_generator_config::new(['user_id' => $user1->id])
        );

        // User 2 logged in, but filter for user 1.
        self::setUser($user2);
        $data_provider = new user_goals($user1->id);

        $items = $data_provider->get_page_results_with_permissions()->items;
        self::assertEquals(0, $items->count());
    }

    public function test_contexts_without_permission_filtered_out(): void {
        self::setAdminUser();

        // Create a user and a manager.
        $subject_user = self::getDataGenerator()->create_user();
        $other_user = self::getDataGenerator()->create_user();
        $manager_user = self::getDataGenerator()->create_user();
        job_assignment::create([
            'userid' => $subject_user->id,
            'idnumber' => 'subject_job_assignment',
            'managerjaid' => job_assignment::create_default($manager_user->id)->id,
        ]);

        // Create three goals for the user.
        $goal1 = goal_generator::instance()->create_goal(
            goal_generator_config::new(['user_id' => $subject_user->id, 'context' => context_user::instance($subject_user->id)])
        );
        $goal2 = goal_generator::instance()->create_goal(
            goal_generator_config::new(['user_id' => $subject_user->id, 'context' => context_user::instance($subject_user->id)])
        );
        $goal3 = goal_generator::instance()->create_goal(
            goal_generator_config::new(['user_id' => $subject_user->id, 'context' => context_user::instance($subject_user->id)])
        );

        self::setUser($manager_user);

        // All the user's goals should be visible for the manager.
        $data_provider = new user_goals($subject_user->id);
        $this->assert_result(
            [$goal1->id, $goal2->id, $goal3->id],
            $data_provider
        );

        // Switch one goal's context to system. It should not be visible anymore to the manager.
        goal_entity::repository()
            ->where('id', $goal2->id)
            ->update(['context_id' => context_system::instance()->id]);

        $data_provider = new user_goals($subject_user->id);
        $this->assert_result(
            [$goal1->id, $goal3->id],
            $data_provider
        );

        // Switch one goal's context to the other user. It should not be visible anymore to the manager.
        goal_entity::repository()
            ->where('id', $goal1->id)
            ->update(['context_id' => context_user::instance($other_user->id)->id]);

        $data_provider = new user_goals($subject_user->id);
        $this->assert_result(
            [$goal3->id],
            $data_provider
        );

        // Make sure it works when all the contexts are filtered out.
        goal_entity::repository()
            ->where('id', $goal3->id)
            ->update(['context_id' => context_user::instance($other_user->id)->id]);

        $data_provider = new user_goals($subject_user->id);
        $this->assert_result(
            [],
            $data_provider
        );
    }

    public function test_status_filter_single_status(): void {
        [
            $subject_user1,
            $subject_user2,
            $goal1,
            $goal2,
            $goal3,
            $goal4
        ] = $this->create_users_and_goals();

        self::setUser($subject_user1);
        $data_provider = new user_goals($subject_user1->id);
        $data_provider->add_filters(['status' => ['cancelled']]);
        $this->assert_result(
            [$goal3->id, $goal4->id],
            $data_provider
        );

        $data_provider = new user_goals($subject_user1->id);
        $data_provider->add_filters(['status' => ['in_progress']]);
        $this->assert_result(
            [$goal1->id],
            $data_provider
        );
    }

    public function test_status_filter_multiple_statuses(): void {
        [
            $subject_user1,
            $subject_user2,
            $goal1,
            $goal2,
            $goal3,
            $goal4
        ] = $this->create_users_and_goals();

        $data_provider = new user_goals($subject_user1->id);
        $data_provider->add_filters(['status' => ['in_progress', 'cancelled']]);
        $this->assert_result(
            [$goal1->id, $goal3->id, $goal4->id],
            $data_provider
        );

        $data_provider = new user_goals($subject_user1->id);
        $data_provider->add_filters(['status' => ['completed', 'cancelled', 'in_progress', 'not_started']]);
        $this->assert_result(
            [$goal1->id, $goal2->id, $goal3->id, $goal4->id],
            $data_provider
        );
    }

    public function test_status_filter_empty(): void {
        [
            $subject_user1,
            $subject_user2,
            $goal1,
            $goal2,
            $goal3,
            $goal4
        ] = $this->create_users_and_goals();

        // Empty array for status
        $data_provider = new user_goals($subject_user1->id);
        $data_provider->add_filters(['status' => []]);
        $this->assert_result(
            [$goal1->id, $goal2->id, $goal3->id, $goal4->id],
            $data_provider
        );

        // Don't add status filter at all.
        $data_provider = new user_goals($subject_user1->id);
        $this->assert_result(
            [$goal1->id, $goal2->id, $goal3->id, $goal4->id],
            $data_provider
        );
    }

    public function test_status_filter_non_existent_status(): void {
        [$subject_user1] = $this->create_users_and_goals();

        $data_provider = new user_goals($subject_user1->id);
        $data_provider->add_filters(['status' => ['non_existent']]);
        $items = $data_provider->get_page_results_with_permissions()->items;
        self::assertSame(0, $items->count());
    }

    public function test_status_filter_invalid_value(): void {
        [$subject_user1] = $this->create_users_and_goals();

        $data_provider = new user_goals($subject_user1->id);
        $data_provider->add_filters(['status' => [null]]);

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Goal status filter must be an array of strings.');

        $items = $data_provider->get_page_results_with_permissions()->items;
    }

    public function test_search_filter_full_name(): void {
        [
            $subject_user1,
            $subject_user2,
            $goal1,
            $goal2,
            $goal3,
            $goal4
        ] = $this->create_users_and_goals();

        $data_provider = new user_goals($subject_user1->id);
        $data_provider->add_filters(['search' => 'User1 Goal2']);
        $this->assert_result(
            [$goal2->id],
            $data_provider
        );
    }

    public function test_search_filter_substring(): void {
        [
            $subject_user1,
            $subject_user2,
            $goal1,
            $goal2,
            $goal3,
            $goal4
        ] = $this->create_users_and_goals();

        $data_provider = new user_goals($subject_user1->id);
        $data_provider->add_filters(['search' => 'ser']);
        $this->assert_result(
            [$goal1->id, $goal2->id, $goal3->id, $goal4->id],
            $data_provider
        );
    }

    public function test_search_filter_case_insensitive(): void {
        [
            $subject_user1,
            $subject_user2,
            $goal1,
            $goal2,
            $goal3,
            $goal4
        ] = $this->create_users_and_goals();

        $data_provider = new user_goals($subject_user1->id);
        $data_provider->add_filters(['search' => 'user1 goal']);
        $this->assert_result(
            [$goal1->id, $goal2->id, $goal3->id, $goal4->id],
            $data_provider
        );
    }

    public function test_search_filter_no_result(): void {
        [
            $subject_user1,
            $subject_user2,
            $goal1,
            $goal2,
            $goal3,
            $goal4
        ] = $this->create_users_and_goals();

        $data_provider = new user_goals($subject_user1->id);
        $data_provider->add_filters(['search' => 'User2']);
        $this->assert_result(
            [],
            $data_provider
        );
    }

    public function test_search_filter_empty_string(): void {
        [
            $subject_user1,
            $subject_user2,
            $goal1,
            $goal2,
            $goal3,
            $goal4
        ] = $this->create_users_and_goals();

        $data_provider = new user_goals($subject_user1->id);
        $data_provider->add_filters(['search' => '']);
        $this->assert_result(
            [$goal1->id, $goal2->id, $goal3->id, $goal4->id],
            $data_provider
        );
    }

    public function test_search_and_status_filter_combined(): void {
        [
            $subject_user1,
            $subject_user2,
            $goal1,
            $goal2,
            $goal3,
            $goal4
        ] = $this->create_users_and_goals();

        $data_provider = new user_goals($subject_user1->id);
        $data_provider->add_filters([
            'search' => 'User1',
            'status' => ['in_progress', 'cancelled']
        ]);
        $this->assert_result(
            [$goal1->id, $goal3->id, $goal4->id],
            $data_provider
        );

        $data_provider = new user_goals($subject_user1->id);
        $data_provider->add_filters([
            'search' => 'User2',
            'status' => ['in_progress', 'cancelled']
        ]);
        $this->assert_result(
            [],
            $data_provider
        );
    }

    /**
     * @param array $expected_goal_ids
     * @param user_goals $data_provider
     * @return void
     */
    private function assert_result(array $expected_goal_ids, user_goals $data_provider): void {
        $result_items = $data_provider->get_page_results_with_permissions()->items;
        self::assertEqualsCanonicalizing(
            $expected_goal_ids,
            $result_items->map(fn (goal_model $item): int => $item->id)->all()
        );
    }

    public function create_users_and_goals(): array {
        self::setAdminUser();

        $subject_user1 = self::getDataGenerator()->create_user();
        $subject_user2 = self::getDataGenerator()->create_user();

        // Create three goals for user 1
        $goal1 = goal_generator::instance()->create_goal(
            goal_generator_config::new([
                'name' => 'User1 Goal1',
                'user_id' => $subject_user1->id,
                'context' => context_user::instance($subject_user1->id),
                'status' => 'in_progress',
            ])
        );
        $goal2 = goal_generator::instance()->create_goal(
            goal_generator_config::new([
                'name' => 'User1 Goal2',
                'user_id' => $subject_user1->id,
                'context' => context_user::instance($subject_user1->id),
                'status' => 'completed',
            ])
        );
        $goal3 = goal_generator::instance()->create_goal(
            goal_generator_config::new([
                'name' => 'User1 Goal3',
                'user_id' => $subject_user1->id,
                'context' => context_user::instance($subject_user1->id),
                'status' => 'cancelled',
            ])
        );
        $goal4 = goal_generator::instance()->create_goal(
            goal_generator_config::new([
                'name' => 'User1 Goal4',
                'user_id' => $subject_user1->id,
                'context' => context_user::instance($subject_user1->id),
                'status' => 'cancelled',
            ])
        );

        // Create a goal for user 2
        $goal5 = goal_generator::instance()->create_goal(
            goal_generator_config::new([
                'name' => 'User2 Goal5',
                'user_id' => $subject_user2->id,
                'context' => context_user::instance($subject_user2->id),
                'status' => 'in_progress',
            ])
        );

        return [$subject_user1, $subject_user2, $goal1, $goal2, $goal3, $goal4];
    }
}
