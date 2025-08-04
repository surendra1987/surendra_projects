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
use core\orm\query\exceptions\record_not_found_exception;
use totara_webapi\phpunit\webapi_phpunit_helper;
use perform_goal\model\goal;
use perform_goal\entity\goal as goal_entity;
use perform_goal\settings_helper;
use perform_goal\testing\generator;
use perform_goal\testing\goal_generator_config;

/**
 * @group perform_goal
 */
class perform_goal_webapi_resolver_mutation_delete_goal_test extends testcase {

    private const MUTATION = 'perform_goal_delete_goal';

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

        $result = $this->resolve_graphql_mutation(self::MUTATION, $args);
        self::assertTrue($result['success']);
        self::assertNull(goal_entity::repository()->find($goal->id));
    }

    public function test_failed_ajax_query(): void {
        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        $data = goal_generator_config::new(['user_id' => $user->id]);
        /** @var goal $goal */
        $goal = generator::instance()->create_goal($data);

        $args['goal_reference'] = [
            'id' => $goal->id,
            'id_number' => $goal->id_number
        ];

        settings_helper::disable_perform_goals();
        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_failed(
            $result,
            'Feature perform_goals is not available.'
        );

        settings_helper::enable_perform_goals();

        $result = $this->parsed_graphql_operation(self::MUTATION, []);
        $this->assert_webapi_operation_failed(
            $result,
            'Variable "$goal_reference" of required type "perform_goal_reference!" was not provided.'
        );
    }

    public function test_invalid_id_parameter() {
        settings_helper::enable_perform_goals();

        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        $data = goal_generator_config::new(['user_id' => $user->id]);
        /** @var goal $goal */
        $goal = generator::instance()->create_goal($data);

        $this->expectException(record_not_found_exception::class);
        $this->expectExceptionMessage(get_string('invalidrecordunknown', 'error'));

        $args['goal_reference'] = [
            'id' => '0',
        ];
        $this->resolve_graphql_mutation(self::MUTATION, $args);
    }

    public function test_delete_activity_without_capability(): void {
        settings_helper::enable_perform_goals();

        $owner = self::getDataGenerator()->create_user();
        $this->setUser($owner);
        $data = goal_generator_config::new(['user_id' => $owner->id]);
        $goal = generator::instance()->create_goal($data);

        $user2 = self::getDataGenerator()->create_user();
        self::setUser($user2);

        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessage('you do not currently have permissions to do that (create/edit/delete a goal in this context.)');

        $args['goal_reference'] = [
            'id' => $goal->id,
            'id_number' => $goal->id_number
        ];
        $this->resolve_graphql_mutation(self::MUTATION, $args);
    }
}