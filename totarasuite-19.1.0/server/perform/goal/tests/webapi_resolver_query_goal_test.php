<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be use`ful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author  Murali Nair <murali.nair@totaralearning.com>
 * @package perform_goal
 */

use core\event\base;
use core\testing\generator as core_generator;
use perform_goal\settings_helper;
use perform_goal\event\personal_goal_viewed;
use perform_goal\interactor\goal_interactor;
use perform_goal\model\goal_raw_data;
use perform_goal\model\target_type\date;
use perform_goal\testing\generator;
use perform_goal\testing\goal_generator_config;
use totara_webapi\phpunit\webapi_phpunit_helper;

require_once(__DIR__.'/perform_goal_testcase.php');

/**
 * @group perform_goal
 */
class perform_goal_webapi_resolver_query_goal_test extends perform_goal_testcase {
    use webapi_phpunit_helper;

    private const QUERY = 'perform_goal_view_goal';

    /**
     * @covers ::resolve
     */
    public function test_resolver(): void {
        [$args, $exp_goal, $exp_raw, $exp_permissions] = $this->setup_env();

        [
            'goal' => $goal,
            'raw' => $raw,
            'permissions' => $permissions
        ] = $this->resolve_graphql_query(self::QUERY, $args);

        self::assert_goal($exp_goal, $goal);
        self::assert_goal_raw_data($exp_raw, $raw);
        self::assert_goal_permissions($exp_permissions, (object)$permissions);
    }

    /**
     * @covers ::resolve
     */
    public function test_successful_ajax_call(): void {
        [$args, $exp_goal, $exp_raw, $exp_permissions] = $this->setup_env();

        $sink = $this->redirectEvents();

        $result = $this->parsed_graphql_operation(self::QUERY, $args);
        self::assert_webapi_operation_successful($result);

        [
            'goal' => $goal,
            'raw' => $raw,
            'permissions' => $permissions
        ] = $this->get_webapi_operation_data($result);

        self::assert_goal($exp_goal, (object)$goal);
        self::assert_goal_raw_data($exp_raw, (object)$raw);
        self::assert_goal_permissions($exp_permissions, (object)$permissions);

        $events = $sink->get_events();
        $events = array_filter($events, function (base $event) {
            return $event instanceof personal_goal_viewed;
        });
        self::assertCount(1, $events);
    }

    public function test_empty_result(): void {
        $empty = [];
        $sink = $this->redirectEvents();

        // An unknown goal should return an empty result
        $this->setAdminUser();
        $args = ['goal_reference' => ['id' => 123]];
        self::assertEquals($empty, $this->resolve_graphql_query(self::QUERY, $args));

        // Insufficient permissions should return an empty result.
        [$args, ] = $this->setup_env();
        self::setUser(core_generator::instance()->create_user());
        self::assertEquals($empty, $this->resolve_graphql_query(self::QUERY, $args));

        $events = array_filter(
            $sink->get_events(),
            fn (base $event): bool => $event instanceof personal_goal_viewed
        );
        self::assertCount(0, $events);
    }

    /**
     * @covers ::resolve
     */
    public function test_failed_ajax_query(): void {
        [$args, ] = $this->setup_env(true);

        settings_helper::disable_perform_goals();
        self::assert_webapi_operation_failed(
            $this->parsed_graphql_operation(self::QUERY, $args),
            'Feature perform_goals is not available.'
        );
        settings_helper::enable_perform_goals();

        self::setGuestUser();
        self::assert_webapi_operation_failed(
            $this->parsed_graphql_operation(self::QUERY, $args),
            'Course or activity not accessible. (Must be an authenticated user)'
        );
    }

    /**
     * Generates test data.
     *
     * @param bool $in_user_context if true creates the goal with the subject's
     *        context. Otherwise the goal is created with the default context.
     *
     * @return mixed[] (graphql execution args, goal, goal raw data, interactor)
     *         tuple.
     */
    private function setup_env(bool $in_user_context = false): array {
        $this->setAdminUser();

        $core_generator  = core_generator::instance();
        $owner_id = $core_generator->create_user()->id;
        $subject_id = $core_generator->create_user()->id;
        $context = $in_user_context
            ? context_user::instance($subject_id)
            : context_system::instance();

        self::assign_as_staff_manager($owner_id, $context);

        $cfg = [
            'description' => self::jsondoc_description(
                '<h1>This is a <strong>test</strong> description</h1>'
            ),
            'owner_id' => $owner_id,
            'user_id' => $subject_id,
            'target_type' => date::get_type(),
            'context' => $context,
        ];

        $goal = generator::instance()
            ->create_goal(goal_generator_config::new($cfg));

        return [
            ['goal_reference' => ['id' => $goal->id]],
            $goal,
            new goal_raw_data($goal),
            goal_interactor::from_goal($goal)
        ];
    }
}
