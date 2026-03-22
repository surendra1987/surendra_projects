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
 * @author Murali Nair <murali.nair@totara.com>
 * @package perform_goal
 */

namespace perform_goal\entity;

use coding_exception;
use DateTimeImmutable;
use core\entity\user;
use core_my\models\perform_overview\state;
use core\orm\entity\repository;
use core\orm\query\builder;
use perform_goal\model\status\status_helper;

/**
 * Goal entity repository.
 */
class goal_repository extends repository {
    /**
     * Returns a repository targeting personal goals with the specified overview
     * state, user and time period.
     *
     * @param int $days_ago indicates start of the overview period. The overview
     *        period is from $days ago in the past to today.
     * @param int $from_years_ago indicates the starting period in the far past
     *        from which to search for records. In other words records _before_
     *        this date are ignored for the overview.
     * @param state $state target overview state.
     * @param user $user goal subject for whom to generate the overview.
     * @param string $tbl_gl explicit perform goal table alias to use in the
     *        base query if any.
     *
     * @return self the primed repository.
     */
    public static function personal_goal_overview_for(
        int $days_ago,
        int $from_years_ago,
        state $state,
        user $user,
        string $tbl_gl = 'pg'
    ): self {
        $now = time();
        $start = self::start_of_day($now - $days_ago * DAYSECS);
        $cutoff = self::start_of_day($now - $from_years_ago * YEARSECS);

        switch (true) {
            case state::not_started()->is_equal_to($state):
                return self::personal_goal_not_started($user, $tbl_gl, $cutoff);

            case state::not_progressed()->is_equal_to($state):
                return self::personal_goal_not_progressed(
                    $user, $tbl_gl, $start, $cutoff
                );

            case state::progressed()->is_equal_to($state):
                return self::personal_goal_progressed($user, $tbl_gl, $start);

            case state::achieved()->is_equal_to($state):
                return self::personal_goal_achieved($user, $tbl_gl, $start);

            default:
                throw new coding_exception(
                    "unknown overview state: {$state->name}"
                );
        }
    }

    /**
     * Returns a query to retrieve 'not started' personal goals for user(s). Not
     * started goals are those goals after the cut off period that still have a
     * 'not started' status
     *
     * @param user $user goal subject for whom to generate the overview.
     * @param string $tbl_gl explicit perform goal table alias to use in the
     *        base query.
     * @param int $cutoff indicates the starting period in the far past from
     *        which to search for records.
     *
     * @return self the query.
     */
    private static function personal_goal_not_started(
        user $user,
        string $tbl_gl,
        int $cutoff
    ): self {
        return self::query_base($user, $tbl_gl)
            ->where("$tbl_gl.created_at", '>=', $cutoff)
            ->where_in(
                "$tbl_gl.status", status_helper::not_started_status_codes()
            );
    }

    /**
     * Returns a query to retrieve 'not progressed' personal goals for the user.
     * Unprogressed goals are the opposite of progressed goals ie NOT(status
     * change time >= start OR value change time >= start). Which by De Morgan's
     * laws becomes status change time < start AND value change time < start.
     *
     * @param user $user goal subject for whom to generate the overview.
     * @param string $tbl_gl explicit perform goal table alias to use in the
     *        base query.
     * @param int $start reference time as seconds since the Epoch indicating
     *        the start of the overview period.
     * @param int $cutoff indicates the starting period in the far past from
     *        which to search for records.
     *
     * @return self the query.
     */
    private static function personal_goal_not_progressed(
        user $user,
        string $tbl_gl,
        int $start,
        int $cutoff
    ): self {
        $excluded_statuses = array_merge(
            status_helper::not_started_status_codes(),
            status_helper::closed_status_codes()
        );

        $value_updated_at = "$tbl_gl.current_value_updated_at";

        return self::query_base($user, $tbl_gl)
            ->where("$tbl_gl.created_at", '>=', $cutoff)
            ->where_not_in("$tbl_gl.status", $excluded_statuses)
            ->where(
                fn (builder $b): builder => $b
                    ->where("$tbl_gl.status_updated_at", '<', $start)
                    ->where(
                        fn (builder $b1): builder => $b1
                            ->where_null($value_updated_at)
                            ->or_where($value_updated_at, '<', $start)
                    )
            );
    }

    /**
     * Returns a query to retrieve 'progressed' personal goals for the user.
     * Progressed goals are those 'in progress' goals whose value OR status
     * changed in the overview period.
     *
     * @param user $user goal subject for whom to generate the overview.
     * @param string $tbl_gl explicit perform goal table alias to use in the
     *        base query.
     * @param int $start reference time as seconds since the Epoch indicating
     *        the start of the overview period.
     *
     * @return self the query.
     */
    private static function personal_goal_progressed(
        user $user,
        string $tbl_gl,
        int $start
    ): self {
        $excluded_statuses = array_merge(
            status_helper::not_started_status_codes(),
            status_helper::closed_status_codes()
        );

        $value_updated_at = "$tbl_gl.current_value_updated_at";

        return self::query_base($user, $tbl_gl)
            ->where_not_in("$tbl_gl.status", $excluded_statuses)
            ->where(
                fn (builder $b): builder => $b
                    ->where("$tbl_gl.status_updated_at", '>=', $start)
                    ->or_where(
                        fn (builder $b1): builder => $b1
                            ->where_not_null($value_updated_at)
                            ->where($value_updated_at, '>=', $start)
                    )
            );
    }

    /**
     * Returns a query to retrieve 'achieved' personal goals for user. Achieved
     * goals are those goals that have an 'achieved' status and were closed in
     * the overview period.
     *
     * @param user $user goal subject for whom to generate the overview.
     * @param string $tbl_gl explicit perform goal table alias to use in the
     *        base query.
     * @param int $start reference time as seconds since the Epoch indicating
     *        the start of the overview period.
     *
     * @return self the query.
     */
    private static function personal_goal_achieved(
        user $user,
        string $tbl_gl,
        int $start
    ): self {
        $closed_at = "$tbl_gl.closed_at";

        return self::query_base($user, $tbl_gl)
            ->where_in("$tbl_gl.status", status_helper::achieved_status_codes())
            ->where_not_null($closed_at)
            ->where($closed_at, '>=', $start);
    }

    /**
     * Returns a starting query to get the personal goals overviews for the
     * previously specified user.
     *
     * @param user $user goal subject for whom to generate the overview.
     * @param string $tbl_gl explicit perform goal table alias to use in the
     *        base query.
     *
     * @return self the repository.
     */
    private static function query_base(user $user, string $tbl_gl): self {
        return (new self(goal::class))
            ->as($tbl_gl)
            ->where("$tbl_gl.user_id", $user->id);
    }

    /**
     * Returns the specified timestamp adjusted to the start of day.
     *
     * @param int $timestamp raw timestamp.
     *
     * @return int the adjusted timestamp.
     */
    private static function start_of_day(int $timestamp): int {
        return (new DateTimeImmutable())
            ->setTimestamp($timestamp)
            ->setTime(0, 0, 0)
            ->getTimestamp();
    }

    /**
     * Add select for completion_percent which is calculated from current & target values.
     * It results in a percent value rounded to two decimal places.
     *
     * @return $this
     */
    public function add_select_completion_percent(): self {
        $this->builder->add_select_raw(
            builder::get_db()->sql_round(
                "(CASE WHEN target_value > 0 THEN ((current_value / target_value) * 100) ELSE 0 END)",
                2,
                true
            ) . " as completion_percent"
        );
        return $this;
    }
}