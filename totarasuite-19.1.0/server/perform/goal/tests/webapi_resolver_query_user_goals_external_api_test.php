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

use core_phpunit\testcase;
use perform_goal\testing\generator as goal_generator;
use perform_goal\testing\goal_generator_config;

require_once(__DIR__.'/external_api_phpunit_helper.php');

/**
 * Unit tests for the perform_goal_user_goals query resolver with the External API.
 */
class perform_goal_webapi_resolver_query_user_goals_external_api_test extends testcase {

    use external_api_phpunit_helper;

    private const QUERY = "query {
        perform_goal_user_goals(
            input: {
                %s
            }
        ) {
            items {
                goal {
                    id
                    name
                    target_date
                }                
            }
            total
            next_cursor
            success
            errors {
                code
                message
            }
        }
    }";

    /**
     * Just a basic test to make sure it works with the external API.
     * The other resolver features are tested in perform_goal_webapi_resolver_query_user_goals_test.
     *
     * @return void
     */
    public function test_successful(): void {
        $api_user = self::helper_set_auth_header_get_api_user();

        self::setAdminUser();
        $subject_user = self::getDataGenerator()->create_user();

        // Create two goals.
        $goal1 = goal_generator::instance()->create_goal(
            goal_generator_config::new(['user_id' => $subject_user->id, 'context' => context_user::instance($subject_user->id)]
            )
        );
        $goal2 = goal_generator::instance()->create_goal(
            goal_generator_config::new(['user_id' => $subject_user->id, 'context' => context_user::instance($subject_user->id)])
        );

        self::setUser($api_user);

        // Filter by user.
        $args = "filters: {
            user: {
                username: \"{$subject_user->username}\"
            }
        },
        pagination: {
            cursor: null
        }";
        $op = sprintf(self::QUERY, $args);

        ob_start();
        self::get_external_api_instance()
            ->set_input_request_data($op)
            ->process('graphql_request');
        $response = ob_get_clean();

        $result = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        self::assertTrue($result['data']['perform_goal_user_goals']['success']);
        self::assert_external_operation_successful($result);
        $result_items = $result['data']['perform_goal_user_goals']['items'];

        self::assertEqualsCanonicalizing(
            [$goal1->id, $goal2->id],
            array_map(static fn ($item) => $item['goal']['id'], $result_items)
        );
    }

    public function test_invalid_sort_options_when_combined_with_cursor(): void {
        $subject_user = self::helper_set_auth_header_get_api_user();
        self::setAdminUser();
        for ($i = 0; $i < 5; $i++) {
            goal_generator::instance()->create_goal(
                goal_generator_config::new(['user_id' => $subject_user->id, 'context' => context_user::instance($subject_user->id)]
                )
            );
        }
        self::setUser($subject_user);

        $bad_sort_options = ['least_complete', 'most_complete'];
        foreach ($bad_sort_options as $bad_option) {
            $args = "
            filters: {
                user: {
                    id: {$subject_user->id}
                }
            },
            options: {
                sort_by: \"{$bad_option}\"
            },
            pagination: {
                limit: 2,
                cursor: \"abc\"
            }";
            $op = sprintf(self::QUERY, $args);
            ob_start();
            self::get_external_api_instance()
                ->set_input_request_data($op)
                ->process('graphql_request');
            $response = ob_get_clean();
            $result = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

            self::assert_external_operation_successful($result);
            self::assertFalse($result['data']['perform_goal_user_goals']['success']);
            self::assertEquals(
                $result['data']['perform_goal_user_goals']['errors']['message'],
                "The sorting option '{$bad_option}' is invalid when it is combined with a cursor."
            );
        }
    }
}
