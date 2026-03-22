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
 * @author Murali Nair <murali.nair@totaralearning.com>
 * @package perform_goal
 */

use core\collection;
use core\entity\user;
use core\testing\generator as core_generator;
use core_my\models\perform_overview\state;
use perform_goal\entity\goal;
use perform_goal\model\status\cancelled;
use perform_goal\model\status\completed;
use perform_goal\model\status\in_progress;
use perform_goal\model\status\not_started;
use perform_goal\model\status\status;
use perform_goal\model\target_type\date;
use perform_goal\testing\generator;

/**
 * Holds convenient methods for generating and test goals for perform overviews.
 */
trait perform_overview_helper {
    /**
     * Generates test data.
     *
     * @param int $start reference time as seconds since the Epoch indicating
     *        the start of the overview period.
     * @param int $cutoff indicates the starting period in the far past from
     *        which to search for records.
     *
     * @return mixed[] a tuple in this order:
     *         - array<string,collection<goal>> goals: perform goals belonging
     *           to various overview states
     *         - user subject: the subject of these goals.
     */
    final protected static function setup_env(
        int $start,
        int $cutoff
    ): array {
        self::setAdminUser();

        $core_generator = core_generator::instance();
        $subject = $core_generator->create_user();

        $common = [
            'context_id' => context_user::instance($subject->id)->id,
            'category_id' => generator::instance()->create_goal_category()->id,
            'owner_id' => $subject->id,
            'user_id' => $subject->id,
            'target_type' => date::get_type(),
            'target_value' => 100,
            'current_value' => 55
        ];

        $exp_goals = [
            state::not_started()->name => self::create_not_started_goals(
                $common, $cutoff, $start
            ),

            state::not_progressed()->name => self::create_not_progressed_goals(
                $common, $cutoff, $start
            ),

            state::progressed()->name => self::create_progressed_goals(
                $common, $cutoff, $start
            ),

            state::achieved()->name => self::create_completed_goals(
                $common, $cutoff, $start
            )
        ];

        self::create_cancelled_goals($common, $cutoff, $start);


        // Create goals for other users; these should not be picked at all.
        collection::new(range(0, 2))
            ->map(fn(int $i): int => $core_generator->create_user()->id)
            ->map(
                function (int $uid) use ($common, $cutoff, $start): void {
                    $common['context_id'] = context_user::instance($uid)->id;
                    $common['owner_id'] = $uid;
                    $common['user_id'] = $uid;

                    self::create_not_started_goals($common, $cutoff, $start);
                    self::create_not_progressed_goals($common, $cutoff, $start);
                    self::create_progressed_goals($common, $cutoff, $start);
                    self::create_completed_goals($common, $cutoff, $start);
                    self::create_cancelled_goals($common, $cutoff, $start);
                }
            );

        return [$exp_goals, new user($subject)];
    }

    /**
     * Creates not started goals.
     *
     * @param array<string,mixed> $common common goal attribute values.
     * @param int $start reference time as seconds since the Epoch indicating
     *        the start of the overview period.
     * @param int $cutoff indicates the starting period in the far past from
     *        which to search for records.
     *
     * @return collection<goal> goals in the overview 'not started' state.
     */
    final protected static function create_not_started_goals(
        array $common,
        int $cutoff,
        int $start
    ): collection {
        $create_goals = fn(array $times): collection => self::create_goals(
            state::not_started()->name, new not_started(), $common, $times
        );

        // Invalid goals for this overview state.
        $create_goals([
            [
                'closed_at' => null,
                'current_value_updated_at' => null,
                'created_at' => self::add_years($cutoff, -5),
                'status_updated_at' => self::add_years($cutoff, -5),
                'target_date' => self::add_weeks($cutoff, -4)
            ],
            [
                'closed_at' => self::add_weeks($start, 1),
                'current_value_updated_at' => self::add_weeks($start, 1),
                'created_at' => self::add_days($cutoff, -2),
                'status_updated_at' => self::add_days($cutoff, -2),
                'target_date' => self::add_weeks($start, 1)
            ]
        ]);

        // These should be picked up in the subject's 'not started' overview due
        // to these conditions:
        // Status = not started
        // AND created_at >= cutoff (start of day)
        // AND user_id = subject
        return $create_goals([
            [
                'closed_at' => self::add_weeks($cutoff, 6),
                'current_value_updated_at' => null,
                'created_at' => $cutoff,
                'status_updated_at' => $cutoff,
                'target_date' => self::add_weeks($cutoff, 3)
            ],
            [
                'closed_at' => null,
                'current_value_updated_at' => self::add_days($start, 3),
                'created_at' => self::add_days($start, 1),
                'status_updated_at' => self::add_days($start, 1),
                'target_date' => self::add_days($start, 5)
            ],
            [
                'closed_at' => self::add_days($start, 6),
                'current_value_updated_at' => self::add_days($start, 6),
                'created_at' => self::add_days($start, -3),
                'status_updated_at' => self::add_days($start, -3),
                'target_date' => self::add_days($start, 20)
            ]
        ]);
    }

    /**
     * Creates in progress goals that fall in the overview 'not progressed'
     * state.
     *
     * @param array<string,mixed> $common common goal attribute values.
     * @param int $start reference time as seconds since the Epoch indicating
     *        the start of the overview period.
     * @param int $cutoff indicates the starting period in the far past from
     *        which to search for records.
     *
     * @return collection<goal> goals in the overview 'not progressed' state.
     */
    final protected static function create_not_progressed_goals(
        array $common,
        int $cutoff,
        int $start
    ): collection {
        $create_goals = fn(array $times): collection => self::create_goals(
            state::not_progressed()->name, new in_progress(), $common, $times
        );

        // Invalid goals for this overview state.
        $create_goals([
            [
                'closed_at' => null,
                'current_value_updated_at' => self::add_days($cutoff, -2),
                'created_at' => self::add_years($cutoff, -5),
                'status_updated_at' => self::add_years($cutoff, -4),
                'target_date' => self::add_years($cutoff, -3)
            ],
            [
                'closed_at' => null,
                'current_value_updated_at' => self::add_days($start, -2),
                'created_at' => self::add_years($cutoff, -1),
                'status_updated_at' => self::add_days($cutoff, 2),
                'target_date' => self::add_weeks($cutoff, 5)
            ]
        ]);

        // These should be picked up in the subject's 'not progressed' overview
        // due to these conditions:
        // Status != not started and status != closed
        // AND created_at >= cutoff (start of day)
        // AND user_id = subject
        // AND (
        //   status_updated_at < start
        //   AND (
        //     current_value_update is null
        //     OR current value updated < start
        //   )
        // )
        return $create_goals([
            [
                'closed_at' => null,
                'current_value_updated_at' => self::add_days($start, -5),
                'created_at' => $cutoff,
                'status_updated_at' => self::add_days($start, -2),
                'target_date' => self::add_weeks($start, 5)
            ],
            [
                'closed_at' => null,
                'current_value_updated_at' => null,
                'created_at' => self::add_days($start, -2),
                'status_updated_at' => self::add_days($start, -2),
                'target_date' => self::add_days($start, 3)
            ]
        ]);
    }

    /**
     * Creates in progress goals that fall in the overview 'progressed' state.
     *
     * @param array<string,mixed> $common common goal attribute values.
     * @param int $start reference time as seconds since the Epoch indicating
     *        the start of the overview period.
     * @param int $cutoff indicates the starting period in the far past from
     *        which to search for records.
     *
     * @return collection<goal> goals in the overview 'progressed' state.
     */
    final protected static function create_progressed_goals(
        array $common,
        int $cutoff,
        int $start
    ): collection {
        $create_goals = fn(array $times): collection => self::create_goals(
            state::progressed()->name, new in_progress(), $common, $times
        );

        // Invalid goals for this overview state.
        $create_goals([
            [
                'closed_at' => self::add_days($start, 2),
                'current_value_updated_at' => self::add_days($cutoff, 1),
                'created_at' => self::add_weeks($cutoff, -1),
                'status_updated_at' => self::add_weeks($cutoff, -1),
                'target_date' => self::add_weeks($cutoff, 1)
            ]
        ]);

        // These should be picked up in the subject's 'progressed' overview due
        // to these conditions:
        // Status != not started and status != closed
        // AND created_at >= cutoff (start of day)
        // AND user_id = subject
        // AND (
        //   status_updated_at >= start
        //   OR (
        //     current_value_update not null
        //     AND current value updated >= start
        //   )
        // )
        return $create_goals([
            [
                'closed_at' => null,
                'current_value_updated_at' => self::add_days($start, 2),
                'created_at' => self::add_years($cutoff, -2),
                'status_updated_at' => self::add_days($cutoff, -2),
                'target_date' => self::add_weeks($cutoff, 5)
            ],
            [
                'closed_at' => null,
                'current_value_updated_at' => null,
                'created_at' => $cutoff,
                'status_updated_at' => self::add_days($start, 2),
                'target_date' => self::add_weeks($cutoff, 5)
            ],
            [
                'closed_at' => null,
                'current_value_updated_at' => self::add_days($start, 2),
                'created_at' => self::add_days($start, -2),
                'status_updated_at' => self::add_days($start, -22),
                'target_date' => self::add_days($start, 3)
            ],
            [
                'closed_at' => self::add_days($start, 5), // Does not matter!
                'current_value_updated_at' => self::add_days($start, 2),
                'created_at' => $start,
                'status_updated_at' => $start,
                'target_date' => self::add_days($start, 4)
            ]
        ]);
    }

    /**
     * Creates completed goals.
     *
     * @param array<string,mixed> $common common goal attribute values.
     * @param int $start reference time as seconds since the Epoch indicating
     *        the start of the overview period.
     * @param int $cutoff indicates the starting period in the far past from
     *        which to search for records.
     *
     * @return collection<goal> goals in the overview 'achieved' state.
     */
    final protected static function create_completed_goals(
        array $common,
        int $cutoff,
        int $start
    ): collection {
        $create_goals = fn(array $times): collection => self::create_goals(
            state::achieved()->name, new completed(), $common, $times
        );

        // Invalid goals for this overview state.
        $create_goals([
            [
                'closed_at' => null,
                'current_value_updated_at' => self::add_days($start, 1),
                'created_at' => self::add_years($cutoff, -1),
                'status_updated_at' => self::add_weeks($cutoff, -1),
                'target_date' => self::add_weeks($cutoff, 1)
            ],
            [
                'closed_at' => null,
                'current_value_updated_at' => null,
                'created_at' => $start,
                'status_updated_at' => $start,
                'target_date' => self::add_weeks($cutoff, 1)
            ],
            [
                'closed_at' => self::add_days($start, -2),
                'current_value_updated_at' => null,
                'created_at' => self::add_days($start, -10),
                'status_updated_at' => self::add_days($start, -2),
                'target_date' => self::add_days($start, 15)
            ]
        ]);

        // These should be picked up in the subject's 'achieved' overview due
        // to these conditions:
        // Status = completed
        // AND created_at >= cutoff (start of day)
        // AND user_id = subject
        // AND close_at >= start (start of day)
        return $create_goals([
            [
                'closed_at' => self::add_days($start, 2),
                'current_value_updated_at' => self::add_days($start, 2),
                'created_at' => self::add_years($cutoff, -1),
                'status_updated_at' => self::add_days($start, 2),
                'target_date' => self::add_days($start, 5)
            ],
            [
                'closed_at' => self::add_days($start, 10),
                'current_value_updated_at' => null,
                'created_at' => $start,
                'status_updated_at' => self::add_days($start, 10),
                'target_date' => self::add_days($start, 5)
            ]
        ]);
    }

    /**
     * Creates cancelled goals. These should never be picked up for a subject's
     * overview.
     *
     * @param array<string,mixed> $common common goal attribute values.
     * @param int $start reference time as seconds since the Epoch indicating
     *        the start of the overview period.
     * @param int $cutoff indicates the starting period in the far past from
     *        which to search for records.
     */
    final protected static function create_cancelled_goals(
        array $common,
        int $cutoff,
        int $start
    ): void {
        $times = [
            [
                'closed_at' => self::add_days($start, 2),
                'current_value_updated_at' => self::add_days($start, 1),
                'created_at' => self::add_weeks($cutoff, -1),
                'status_updated_at' => self::add_weeks($cutoff, -1),
                'target_date' => self::add_weeks($cutoff, 1)
            ],
            [
                'closed_at' => null,
                'current_value_updated_at' => null,
                'created_at' => self::add_days($start, 2),
                'status_updated_at' => self::add_days($start, 2),
                'target_date' => self::add_days($start, 15)
            ],
            [
                'closed_at' => self::add_days($start, 10),
                'current_value_updated_at' => self::add_days($start, 2),
                'created_at' => $cutoff,
                'status_updated_at' => self::add_days($start, 2),
                'target_date' => self::add_days($start, 5)
            ],[
                'closed_at' => self::add_days($start, 5),
                'current_value_updated_at' => null,
                'created_at' => $start,
                'status_updated_at' => $start,
                'target_date' => self::add_days($start, 4)
            ]
        ];

        self::create_goals('none', new cancelled(), $common, $times);
    }

    /**
     * Creates goals given their timing details.
     *`
     * @param string $state overview state this goal should belong to.
     * @param status $status goal status.
     * @param array<string,mixed> $common common goal attribute values.
     * @param mixed[array<string,mixed>] $times_per_goal goal specific times.
     *        See create_goal() for which times should be provided.
     *
     * @return collection<goal> generated goals, one per $times_per_goal item.
     */
    final protected static function create_goals(
        string $state,
        status $status,
        array $common,
        array $times_per_goal
    ): collection {
        return collection::new($times_per_goal)->map(
            fn (array $times): goal => self::create_goal(
                $state, $status, $common, $times
            )
        );
    }

    /**
     * Creates a goal given its timing details.
     *`
     * @param string $state overview state this goal should belong to.
     * @param status $status goal status.
     * @param array<string,mixed> $common common goal attribute values.
     * @param array<string,mixed> $times goal times comprising these fields:
     *        - ?int closed_at
     *        - ?int current_value_updated_at
     *        - int created_at
     *        - int status_updated_at
     *        - int target_date
     *
     * @return goal generated goal. Each goal has start_date = created_at
     *         and updated_at = closed_at (or target_date if closed_at is null).
     */
    final protected static function create_goal(
        string $state,
        status $status,
        array $common,
        array $times
    ): goal {
        static $i = 0;
        $base_name = sprintf(
            '[uid %d][gs %s][ov %s] %d',
            $common['user_id'],
            $status::get_label(),
            $state,
            $i++
        );

        $attrs = array_merge(
            $common,
            [
                'name' => $base_name,
                'id_number' => $base_name,
                'closed_at' => $times['closed_at'],
                'created_at' => $times['created_at'],
                'current_value_updated_at' => $times['current_value_updated_at'],
                'description' => $base_name,
                'start_date' => $times['created_at'],
                'status' => $status::get_code(),
                'status_updated_at' => $times['status_updated_at'],
                'target_date' => $times['target_date'],
                'updated_at' => $times['closed_at'] ?? $times['target_date']
            ]
        );

        return (new goal($attrs))->save();
    }

    /**
     * Returns the specified timestamp +/- the given number of days.
     *
     * @param int $ts timestamp to adjust.
     * @param int $delta no of days to add or subtract.
     *
     * @return int the updated timestamp.
     */
    final protected static function add_days(int $ts, int $delta): int {
        return $ts + $delta * DAYSECS;
    }

    /**
     * Returns the specified timestamp +/- the given number of weeks.
     *
     * @param int $ts timestamp to adjust.
     * @param int $delta no of weeks to add or subtract.
     *
     * @return int the updated timestamp.
     */
    final protected static function add_weeks(int $ts, int $delta): int {
        return $ts + $delta * WEEKSECS;
    }

    /**
     * Returns the specified timestamp +/- the given number of years.
     *
     * @param int $ts timestamp to adjust.
     * @param int $delta no of years to add or subtract.
     *
     * @return int the updated timestamp.
     */
    final protected static function add_years(int $ts, int $delta): int {
        return $ts + $delta * YEARSECS;
    }

    /**
     * Convenience function to print details for debugging purposes.
     *
     * @param int $start start of overview period.
     * @param int $cutoff end of cutoff period.
     * @param user $subject personal goal subject.
     * @param state $state overview state under test.
     * @param collection<mixed> $exp_goals expected goal entities/models in this
     *        overview state.
     * @param collection<mixed> $act_goals actual goal entities/models in this
     *        overview state.
     */
    final protected static function print(
        int $start,
        int $cutoff,
        user $subject,
        state $state,
        collection $exp_goals,
        collection $act_goals
    ): void {
        $format_ts = fn(?int $ts): string => is_null($ts)
            ? 'null'
            : (new DateTimeImmutable())->setTimestamp($ts)->format(DATE_ATOM);

        $format_goal = fn($goal): string => implode(
            "\n",
            [
                "id " . $goal->id,
                "name " . $goal->name,
                "created_at " . $format_ts($goal->created_at),
                "updated_at " . $format_ts($goal->updated_at),
                "closed_at " . $format_ts($goal->closed_at),
                "start_date " . $format_ts($goal->start_date),
                "status_updated_at " . $format_ts($goal->status_updated_at),
                "current_value_updated_at " . $format_ts(
                    $goal->current_value_updated_at
                ),
            ]
        );

        print("\nTotal no of goals = " . goal::repository()->count() . "\n");
        print("Overview state = " . $state->name . "\n");
        print("Overview start = " . $format_ts($start) . "\n");
        print("Cutoff = " . $format_ts($cutoff) . "\n");
        print("Subject = " . $subject->id . "\n");

        print("\nExpected goals:\n");
        $exp_goals->map(
            function ($goal) use ($format_goal): void {
                print($format_goal($goal) . "\n\n");
            }
        );

        print("\nActual goals:\n");
        $act_goals->map(
            function ($goal) use ($format_goal): void {
                print($format_goal($goal) . "\n\n");
            }
        );
    }
}
