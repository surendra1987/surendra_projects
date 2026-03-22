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

use core\collection;
use core\date_format;
use core\orm\query\builder;
use core\webapi\formatter\field\date_field_formatter;
use core_phpunit\testcase;
use perform_goal\entity\goal as goal_entity;
use perform_goal\model\goal as goal_model;
use perform_goal\testing\generator as goal_generator;
use perform_goal\testing\goal_generator_config;
use perform_goal\testing\goal_task_generator_config;
use perform_goal\webapi\resolver\query\user_goals;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * Unit tests for the perform_goal_user_goals query resolver with the AJAX API.
 */
class perform_goal_webapi_resolver_query_user_goals_test extends testcase {
    use webapi_phpunit_helper;

    private const QUERY = 'perform_goal_user_goals';

    public function test_successful_full_graphql_stack(): void {
        self::setAdminUser();

        $subject_user1 = self::getDataGenerator()->create_user();
        $subject_user2 = self::getDataGenerator()->create_user();

        // Create two goals for user 1
        $goal1 = goal_generator::instance()->create_goal(
            goal_generator_config::new(['user_id' => $subject_user1->id, 'context' => context_user::instance($subject_user1->id)]
            )
        );
        $goal2 = goal_generator::instance()->create_goal(
            goal_generator_config::new(['user_id' => $subject_user1->id, 'context' => context_user::instance($subject_user1->id)])
        );

        // create one goal for user 2.
        $goal3 = goal_generator::instance()->create_goal(
            goal_generator_config::new(['user_id' => $subject_user2->id, 'context' => context_user::instance($subject_user2->id)])
        );

        self::setUser($subject_user1);
        $args = [
            'input' => [
                'filters' => [
                    'user' => [
                        'id' => $subject_user1->id
                    ],
                ]
            ]
        ];

        $result = $this->parsed_graphql_operation(self::QUERY, $args);

        $this->assert_webapi_operation_successful($result);
        self::assertCount(2, $result[0]["items"]);
        self::assertEquals(2, $result[0]["total"]);
        self::assertEmpty($result[0]["next_cursor"]);
        self::assertTrue($result[0]["has_goals"]);
    }

    public function test_result_structure(): void {
        self::setAdminUser();
        $goal_generator = goal_generator::instance();

        $subject_user = self::getDataGenerator()->create_user();

        $now = time();
        $target_date = $now + DAYSECS;
        $goal = $goal_generator->create_goal(
            goal_generator_config::new([
                'user_id' => $subject_user->id,
                'context' => context_user::instance($subject_user->id),
                'current_value' => 123.45,
                'description' => '{"type":"doc","content":[{"type":"paragraph","attrs":{},"content":[{"type":"text","text":"test description"}]}]}',
                'name' => 'Test goal',
                'target_date' => $target_date,
                'target_value' => '99',
            ])
        );

        // Create comments
        $goal_generator->create_goal_comment($subject_user->id, $goal->id);
        $goal_generator->create_goal_comment($subject_user->id, $goal->id);

        // Create tasks
        $goal_task_config = goal_task_generator_config::new([
            'goal_id' => $goal->id,
            'description' => 'test task'
        ]);
        $task1 = $goal_generator->create_goal_task($goal_task_config);
        $task2 = $goal_generator->create_goal_task($goal_task_config);
        $task3 = $goal_generator->create_goal_task($goal_task_config);
        $task1->set_completed(true);
        $task3->set_completed(true);

        // Set updated_at separately, so it's exactly predictable.
        builder::table('perform_goal')
            ->where('id', $goal->id)
            ->update(['updated_at' => $now]);

        self::setUser($subject_user);
        $args = [
            'input' => [
                'filters' => [
                    'user' => [
                        'id' => $subject_user->id
                    ],
                ]
            ]
        ];

        $result = $this->parsed_graphql_operation(self::QUERY, $args);

        $this->assert_webapi_operation_successful($result);
        self::assertCount(1, $result[0]["items"]);

        $goal_result = $result[0]["items"][0];

        $date_formatter = new date_field_formatter(date_format::FORMAT_DATELONG, context_system::instance());
        $formatted_target_date = $date_formatter->format($target_date);
        $formatted_updated_at = $date_formatter->format($now);
        self::assertEquals(
            [
                'goal' =>
                    [
                        'current_value' => 123.45,
                        'description' => '<p>test description</p>',
                        'id' => $goal->id,
                        'name' => 'Test goal',
                        'plugin_name' => 'basic',
                        'status' =>
                            [
                                'id' => 'not_started',
                                'label' => 'Not started',
                            ],
                        'target_date' => $formatted_target_date,
                        'target_value' => '99',
                        'updated_at' => $formatted_updated_at,
                        'comment_count' => 2,
                        'goal_tasks_metadata' =>
                            [
                                'total_count' => 3,
                                'completed_count' => 2,
                            ]
                    ],

                'permissions' =>
                    [
                        'can_manage' => 1,
                        'can_update_status' => 1,
                    ],

            ], $goal_result
        );
    }

    public function test_default_sort_by_most_recent_created(): void {
        self::setAdminUser();

        $subject_user1 = self::getDataGenerator()->create_user();

        $goal1 = goal_generator::instance()->create_goal(
            goal_generator_config::new(['user_id' => $subject_user1->id, 'context' => context_user::instance($subject_user1->id)])
        );
        $goal2 = goal_generator::instance()->create_goal(
            goal_generator_config::new(['user_id' => $subject_user1->id, 'context' => context_user::instance($subject_user1->id)])
        );
        $goal3 = goal_generator::instance()->create_goal(
            goal_generator_config::new(['user_id' => $subject_user1->id, 'context' => context_user::instance($subject_user1->id)])
        );


        goal_entity::repository()->where('id', $goal1->id)->update(['created_at' => strtotime('-8 days')]);
        goal_entity::repository()->where('id', $goal2->id)->update(['created_at' => strtotime('-6 days')]);
        goal_entity::repository()->where('id', $goal3->id)->update(['created_at' => strtotime('-4 days')]);

        self::setUser($subject_user1);
        $args = [
            'input' => [
                'filters' => [
                    'user' => [
                        'id' => $subject_user1->id
                    ],
                ]
            ]
        ];

        $result = $this->resolve_graphql_query(self::QUERY, $args);
        /** @var collection $items */
        $items = $result["items"];
        self::assertCount(3, $result["items"]);
        self::assertEquals(
            [$goal3->id, $goal2->id, $goal1->id],
            $items->map(fn (goal_model $goal): int => $goal->id)->all()
        );

        // Adjust created time in DB, so we should get a different order.
        goal_entity::repository()->where('id', $goal1->id)->update(['created_at' => strtotime('-2 days')]);
        goal_entity::repository()->where('id', $goal2->id)->update(['created_at' => strtotime('-1 days')]);
        $result = $this->resolve_graphql_query(self::QUERY, $args);
        /** @var collection $items */
        $items = $result["items"];
        self::assertCount(3, $result["items"]);
        self::assertEquals(
            [$goal2->id, $goal1->id, $goal3->id],
            $items->map(fn (goal_model $goal): int => $goal->id)->all()
        );
    }

    public function test_defaults_to_logged_in_user(): void {
        self::setAdminUser();

        $subject_user1 = self::getDataGenerator()->create_user();
        $subject_user2 = self::getDataGenerator()->create_user();

        $goal1 = goal_generator::instance()->create_goal(
            goal_generator_config::new(['user_id' => $subject_user1->id, 'context' => context_user::instance($subject_user1->id)])
        );
        $goal2 = goal_generator::instance()->create_goal(
            goal_generator_config::new(['user_id' => $subject_user1->id, 'context' => context_user::instance($subject_user1->id)])
        );
        $goal3 = goal_generator::instance()->create_goal(
            goal_generator_config::new(['user_id' => $subject_user2->id, 'context' => context_user::instance($subject_user2->id)])
        );

        $args = [];

        self::setUser($subject_user1);
        $result = $this->resolve_graphql_query(self::QUERY, $args);
        /** @var collection $items */
        $items = $result["items"];
        self::assertEqualsCanonicalizing(
            [$goal1->id, $goal2->id],
            $items->map(fn (goal_model $goal): int => $goal->id)->all()
        );

        self::setUser($subject_user2);
        $result = $this->resolve_graphql_query(self::QUERY, $args);
        /** @var collection $items */
        $items = $result["items"];
        self::assertEquals(
            [$goal3->id],
            $items->map(fn (goal_model $goal): int => $goal->id)->all()
        );
    }

    public function test_pagination(): void {
        self::setAdminUser();

        $subject_user = self::getDataGenerator()->create_user();

        // Create 5 goals.
        $goals = [];
        for ($i = 1; $i <= 5; $i ++) {
            $goal = goal_generator::instance()->create_goal(
                goal_generator_config::new(['user_id' => $subject_user->id, 'context' => context_user::instance($subject_user->id)])
            );
            // Adjust creation date so we have a predictable order.
            goal_entity::repository()->where('id', $goal->id)->update(['created_at' => strtotime("-{$i} days")]);
            $goals[] = $goal;
        }

        self::setUser($subject_user);
        $args = [
            'input' => [
                'filters' => [
                    'user' => [
                        'id' => $subject_user->id
                    ],
                ]
            ]
        ];

        // Works without specifying pagination (default page size is 20).
        $result = $this->resolve_graphql_query(self::QUERY, $args);
        /** @var collection $items */
        $items = $result["items"];
        self::assertCount(5, $items);
        self::assertEquals(
            [$goals[0]->id, $goals[1]->id, $goals[2]->id, $goals[3]->id, $goals[4]->id],
            $items->map(fn (goal_model $goal): int => $goal->id)->all()
        );
        self::assertEmpty($result['next_cursor']);

        // Set page limit to 2.
        $args['input']['pagination']['limit'] = 2;
        $result = $this->resolve_graphql_query(self::QUERY, $args);
        /** @var collection $items */
        $items = $result["items"];
        self::assertCount(2, $items);
        self::assertEquals(
            [$goals[0]->id, $goals[1]->id],
            $items->map(fn (goal_model $goal): int => $goal->id)->all()
        );
        self::assertNotEmpty($result['next_cursor']);

        // Use the returned cursor for the next request.
        $args['input']['pagination']['cursor'] = $result['next_cursor'];
        $result = $this->resolve_graphql_query(self::QUERY, $args);
        /** @var collection $items */
        $items = $result["items"];
        self::assertCount(2, $items);
        self::assertEquals(
            [$goals[2]->id, $goals[3]->id],
            $items->map(fn (goal_model $goal): int => $goal->id)->all()
        );
        self::assertNotEmpty($result['next_cursor']);

        // Last page should have only one goal.
        $args['input']['pagination']['cursor'] = $result['next_cursor'];
        $result = $this->resolve_graphql_query(self::QUERY, $args);
        /** @var collection $items */
        $items = $result["items"];
        self::assertCount(1, $items);
        self::assertEquals(
            [$goals[4]->id],
            $items->map(fn (goal_model $goal): int => $goal->id)->all()
        );
        self::assertEmpty($result['next_cursor']);
    }

    /**
     * Just make sure the search filter works - details are tested in the data provider test.
     *
     * @return void
     */
    public function test_search_filter(): void {
        [
            $subject_user1,
            $subject_user2,
            $goal1,
            $goal2,
            $goal3,
            $goal4
        ] = $this->create_users_and_goals();

        self::setUser($subject_user1);
        $args = [
            'input' => [
                'filters' => [
                    'user' => [
                        'id' => $subject_user1->id
                    ],
                    'search' => 'Goal2'
                ]
            ]
        ];

        $result = $this->resolve_graphql_query(self::QUERY, $args);
        /** @var collection $items */
        $items = $result["items"];
        self::assertCount(1, $result["items"]);
        self::assertEquals($goal2->id, $items->first()->id);

        $args = [
            'input' => [
                'filters' => [
                    'user' => [
                        'id' => $subject_user1->id
                    ],
                    'search' => 'Goal'
                ]
            ]
        ];

        $result = $this->resolve_graphql_query(self::QUERY, $args);
        /** @var collection $items */
        $items = $result["items"];
        self::assertEqualsCanonicalizing(
            [$goal1->id, $goal2->id, $goal3->id, $goal4->id],
            $items->map(fn (goal_model $goal): int => $goal->id)->all()
        );
    }

    public function test_has_goals_with_filter(): void {
        [
            $subject_user1,
            $subject_user2,
            $goal1,
            $goal2,
            $goal3,
            $goal4
        ] = $this->create_users_and_goals();

        self::setUser($subject_user1);
        $args = [
            'input' => [
                'filters' => [
                    'user' => [
                        'id' => $subject_user1->id
                    ],
                    'search' => 'not-matching-any-goals'
                ]
            ]
        ];

        $result = $this->resolve_graphql_query(self::QUERY, $args);
        self::assertCount(0, $result["items"]);
        self::assertTrue($result["has_goals"]);

        goal_entity::repository()->delete();

        $result = $this->resolve_graphql_query(self::QUERY, $args);
        self::assertCount(0, $result["items"]);
        self::assertFalse($result["has_goals"]);
    }

    /**
     * Just make sure the status filter works - details are tested in the data provider test.
     *
     * @return void
     */
    public function test_status_filter(): void {
        [
            $subject_user1,
            $subject_user2,
            $goal1,
            $goal2,
            $goal3,
            $goal4
        ] = $this->create_users_and_goals();

        self::setUser($subject_user1);
        $args = [
            'input' => [
                'filters' => [
                    'user' => [
                        'id' => $subject_user1->id
                    ],
                    'status' => ['cancelled', 'completed', 'unknown-status-code-should-be-ignored']
                ]
            ]
        ];

        $result = $this->resolve_graphql_query(self::QUERY, $args);
        /** @var collection $items */
        $items = $result["items"];
        self::assertEqualsCanonicalizing(
            [$goal2->id, $goal3->id, $goal4->id],
            $items->map(fn (goal_model $goal): int => $goal->id)->all()
        );
    }

    public function test_sort_by_created_at(): void {
        [
            $subject_user1,
            $subject_user2,
            $goal1,
            $goal2,
            $goal3,
            $goal4
        ] = $this->create_users_and_goals();

        $now = time();
        $this->adjust_created_date($goal3, $now);
        $this->adjust_created_date($goal2, $now - 11);
        $this->adjust_created_date($goal4, $now - 22);
        $this->adjust_created_date($goal1, $now - 33);

        self::setUser($subject_user1);
        $args = [
            'input' => [
                'options' => [
                    'sort_by' => 'created_at'
                ]
            ]
        ];

        $result = $this->resolve_graphql_query(self::QUERY, $args);
        /** @var collection $items */
        $items = $result["items"];
        self::assertSame(
            [$goal3->id, $goal2->id, $goal4->id, $goal1->id],
            $items->map(fn (goal_model $goal): int => $goal->id)->all()
        );
    }

    public function test_sort_by_target_date(): void {
        [
            $subject_user1,
            $subject_user2,
            $goal1,
            $goal2,
            $goal3,
            $goal4
        ] = $this->create_users_and_goals();

        $now = time();
        $this->adjust_target_date($goal4, $now);
        $this->adjust_target_date($goal1, $now - 11);
        $this->adjust_target_date($goal2, $now - 22);
        $this->adjust_target_date($goal3, $now - 33);

        self::setUser($subject_user1);
        $args = [
            'input' => [
                'options' => [
                    'sort_by' => 'target_date'
                ]
            ]
        ];

        $result = $this->resolve_graphql_query(self::QUERY, $args);
        /** @var collection $items */
        $items = $result["items"];
        self::assertSame(
            [$goal3->id, $goal2->id, $goal1->id, $goal4->id],
            $items->map(fn (goal_model $goal): int => $goal->id)->all()
        );
    }

    public function test_sort_by_completion_percent(): void {
        [
            $subject_user1,
            $subject_user2,
            $goal1,
            $goal2,
            $goal3,
            $goal4
        ] = $this->create_users_and_goals();

        self::setUser($subject_user1);
        $args = [
            'input' => [
                'options' => [
                    'sort_by' => 'least_complete'
                ]
            ]
        ];

        $result = $this->resolve_graphql_query(self::QUERY, $args);
        /** @var collection $items */
        $items = $result["items"];
        self::assertSame(
            [$goal4->id, $goal2->id, $goal1->id, $goal3->id],
            $items->map(fn (goal_model $goal): int => $goal->id)->all()
        );


        $args = [
            'input' => [
                'options' => [
                    'sort_by' => 'most_complete'
                ]
            ]
        ];

        $result = $this->resolve_graphql_query(self::QUERY, $args);
        /** @var collection $items */
        $items = $result["items"];
        self::assertSame(
            [$goal3->id, $goal1->id, $goal2->id, $goal4->id],
            $items->map(fn (goal_model $goal): int => $goal->id)->all()
        );
    }

    public function test_comment_count(): void {
        [
            $user1,
            $user2,
            $goal1,
            $goal2,
            $goal3,
            $goal4,
            $goal5,
        ] = $this->create_users_and_goals();

        $goal_generator = goal_generator::instance();

        // Create a bunch of comments for user1 and user2 goals.
        $goal_generator->create_goal_comment($user1->id, $goal1->id);
        $goal_generator->create_goal_comment($user1->id, $goal1->id);
        $goal_generator->create_goal_comment($user1->id, $goal1->id);

        $goal_generator->create_goal_comment($user1->id, $goal3->id);
        $goal_generator->create_goal_comment($user1->id, $goal3->id);

        $goal_generator->create_goal_comment($user1->id, $goal4->id);

        $goal_generator->create_goal_comment($user2->id, $goal5->id);

        self::setUser($user1);
        $args = ['input' => []];

        $result = $this->resolve_graphql_query(self::QUERY, $args);
        /** @var collection $items */
        $items = $result["items"];
        $result_goals = $items->all();

        // Sort items by id, so we get them in a known order.
        usort($result_goals, static fn (goal_model $a, goal_model $b): int => $a->id <=> $b->id);

        // Assert the order
        self::assertSame(
            [$goal1->id, $goal2->id, $goal3->id, $goal4->id],
            array_column($result_goals, 'id')
        );

        // Assert the counts
        self::assertSame(
            [3, 0, 2, 1],
            array_column($result_goals, 'comment_count')
        );
    }

    private function adjust_target_date(goal_model $goal, int $timestamp): void {
        $this->adjust_date('target_date', $goal, $timestamp);
    }

    private function adjust_created_date(goal_model $goal, int $timestamp): void {
        $this->adjust_date('created_at', $goal, $timestamp);
    }

    private function adjust_date(string $date_field, goal_model $goal, int $timestamp): void {
        builder::table('perform_goal')
            ->where('id', $goal->id)
            ->update([$date_field => $timestamp]);
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
                'current_value' => 1.5, // 50.00%
                'target_value' => 3,
            ])
        );
        $goal2 = goal_generator::instance()->create_goal(
            goal_generator_config::new([
                'name' => 'User1 Goal2',
                'user_id' => $subject_user1->id,
                'context' => context_user::instance($subject_user1->id),
                'status' => 'completed',
                'current_value' => 200, // 33.33%
                'target_value' => 600,
            ])
        );
        $goal3 = goal_generator::instance()->create_goal(
            goal_generator_config::new([
                'name' => 'User1 Goal3',
                'user_id' => $subject_user1->id,
                'context' => context_user::instance($subject_user1->id),
                'status' => 'cancelled',
                'current_value' => 110, // 110.00%
                'target_value' => 100,
            ])
        );
        $goal4 = goal_generator::instance()->create_goal(
            goal_generator_config::new([
                'name' => 'User1 Goal4',
                'user_id' => $subject_user1->id,
                'context' => context_user::instance($subject_user1->id),
                'status' => 'cancelled',
                'current_value' => 123, // 0.00%
                'target_value' => 0,
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

        return [$subject_user1, $subject_user2, $goal1, $goal2, $goal3, $goal4, $goal5];
    }

    public static function format_sort_options_data_provider(): array {
        $created_at = ['column' => 'created_at', 'direction' => 'DESC'];
        $target_date = ['column' => 'target_date', 'direction' => 'ASC'];
        $name = ['column' => 'name', 'direction' => 'ASC'];
        $most_complete = ['column' => 'completion_percent', 'direction' => 'DESC'];
        $least_complete = ['column' => 'completion_percent', 'direction' => 'ASC'];

        return [
            [
                'created_at',
                [$created_at, $target_date, $name]
            ],
            [
                'target_date',
                [$target_date, $created_at, $name]
            ],
            [
                'most_complete',
                [$most_complete, $created_at, $target_date, $name]
            ],
            [
                'least_complete',
                [$least_complete, $created_at, $target_date, $name]
            ],
            [
                null, // non-string results in defaults
                [$created_at, $target_date, $name]
            ],
        ];
    }

    /**
     * @dataProvider format_sort_options_data_provider
     * @param $sort_by_input
     * @param array $expected_result
     * @return void
     */
    public function test_format_sort_options($sort_by_input, array $expected_result): void {
        $class = new ReflectionClass(user_goals::class);
        $method = $class->getMethod('format_sort_options');
        $args = [
            'options' => [
                'sort_by' => $sort_by_input
            ]
        ];
        $result = $method->invokeArgs(null, [$args]);

        self::assertEquals($expected_result, $result);
    }

    public function test_invalid_sort_option(): void {
        [$subject_user1 ] = $this->create_users_and_goals();

        self::setUser($subject_user1);
        $args = [
            'input' => [
                'options' => [
                    'sort_by' => 'invalid'
                ]
            ]
        ];

        $result = $this->resolve_graphql_query(self::QUERY, $args);
        self::assertFalse($result["success"]);
        self::assertEquals($result["errors"]["message"], "Invalid sort option 'invalid'.");
    }

    public function test_invalid_sort_options_when_combined_with_cursor(): void {
        [$subject_user1 ] = $this->create_users_and_goals();
        self::setUser($subject_user1);

        $bad_sort_options = ['least_complete', 'most_complete'];
        foreach ($bad_sort_options as $bad_option) {
            $args = [
                'input' => [
                    'options' => [
                        'sort_by' => $bad_option
                    ],
                    'pagination' => [
                        'limit' => 2,
                        'cursor' => 'abc'
                    ]
                ]
            ];

            $result = $this->resolve_graphql_query(self::QUERY, $args);
            self::assertFalse($result["success"]);
            self::assertEquals($result["errors"]["message"],
                "The sorting option '{$bad_option}' is invalid when it is combined with a cursor."
            );
        }
    }
}
