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
 * @package perform_goal
 */

use core_phpunit\testcase;
use totara_core\feature_not_available_exception;
use totara_webapi\phpunit\webapi_phpunit_helper;
use perform_goal\model\goal;
use perform_goal\model\status\in_progress;
use perform_goal\settings_helper;
use perform_goal\testing\generator;
use perform_goal\testing\goal_generator_config;

/**
 * @group perform_goal
 */
class perform_goal_webapi_resolver_mutation_update_progress_test extends testcase {
    private const MUTATION = 'perform_goal_update_progress';

    use webapi_phpunit_helper;

    public function test_execute_query_successful(): void {
        settings_helper::enable_perform_goals();

        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        $data = goal_generator_config::new(['user_id' => $user->id]);
        /** @var goal $goal */
        $goal = generator::instance()->create_goal($data);

        $args['goal_reference'] = [
            'id' => $goal->id,
            'id_number' => $goal->id_number
        ];
        $args['input'] = [
            'status' => in_progress::get_code(),
            'current_value' => 12.5
        ];

        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        self::assert_webapi_operation_successful($result);

        $goal_updated = $this->get_webapi_operation_data($result);
        self::assertEquals(
            ['id' => in_progress::get_code(), 'label' => in_progress::get_label()],
            $goal_updated['status']
        );
        self::assertEqualsWithDelta(12.5, $goal_updated['current_value'], 0.0001);
    }

    public function test_failed_ajax_query(): void {
        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        $data = goal_generator_config::new(['user_id' => $user->id]);
        /** @var goal $goal */
        $goal = generator::instance()->create_goal($data);

        $result = $this->parsed_graphql_operation(self::MUTATION, []);
        $this->assert_webapi_operation_failed(
            $result,
            'Variable "$goal_reference" of required type "perform_goal_reference!" was not provided.'
        );

        $args['goal_reference'] = [
            'id' => $goal->id,
            'id_number' => $goal->id_number
        ];
        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_failed(
            $result,
            'Variable "$input" of required type "perform_goal_update_progress_input!" was not provided.'
        );
    }

    public function test_perform_goals_disabled() {
        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        $data = goal_generator_config::new(['user_id' => $user->id]);
        /** @var goal $goal */
        $goal = generator::instance()->create_goal($data);

        $args['goal_reference'] = [
            'id' => $goal->id,
            'id_number' => $goal->id_number
        ];
        $args['input'] = [
            'status' => in_progress::get_code(),
            'current_value' => 12.5
        ];

        settings_helper::disable_perform_goals();

        $this->expectException(feature_not_available_exception::class);
        $this->expectExceptionMessage(
            'Feature perform_goals is not available.'
        );
        $this->resolve_graphql_mutation(self::MUTATION, $args);
    }

    public function test_invalid_status_parameter() {
        settings_helper::enable_perform_goals();

        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        $data = goal_generator_config::new(['user_id' => $user->id]);
        /** @var goal $goal */
        $goal = generator::instance()->create_goal($data);

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Invalid status: none');

        $args['goal_reference'] = [
            'id' => $goal->id,
            'id_number' => $goal->id_number
        ];
        $args['input'] = [
            'status' => 'none',
            'current_value' => 12.5
        ];
        $this->resolve_graphql_mutation(self::MUTATION, $args);
    }

    public function test_update_status_without_capability(): void {
        settings_helper::enable_perform_goals();

        $owner = self::getDataGenerator()->create_user();
        $this->setUser($owner);
        $data = goal_generator_config::new(['user_id' => $owner->id]);
        $goal = generator::instance()->create_goal($data);

        $user2 = self::getDataGenerator()->create_user();
        self::setUser($user2);

        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessage(
            'You do not currently have permissions to do that (set progress on a goal in this context.)'
        );

        $args['goal_reference'] = [
            'id' => $goal->id,
            'id_number' => $goal->id_number
        ];
        $args['input'] = [
            'status' => in_progress::get_code(),
            'current_value' => 12.5
        ];
        $this->resolve_graphql_mutation(self::MUTATION, $args);
    }
}
