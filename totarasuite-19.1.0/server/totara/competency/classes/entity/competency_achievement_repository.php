<?php
/*
 * This file is part of Totara Perform
 *
 * Copyright (C) 2020 onwards Totara Learning Solutions LTD
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
 * @author Simon Coggins <simon.coggins@totaralearning.com>
 * @package totara_competency
 */

namespace totara_competency\entity;

use coding_exception;
use core\collection;
use DateTimeImmutable;
use core\entity\user;
use core_my\models\perform_overview\state;
use core\orm\entity\repository;
use core\orm\query\builder;
use moodle_database;
use totara_hierarchy\entity\competency;
use totara_hierarchy\entity\competency_framework;
use totara_hierarchy\entity\scale;
use totara_hierarchy\entity\scale_assignment;
use totara_hierarchy\entity\scale_value;

class competency_achievement_repository extends repository {
    /**
     * Returns a repository targeting the specified overview state, users and
     * time period.
     *
     * @param int $days_ago indicates start of the overview period. The overview
     *        period is from $days ago in the past to today.
     * @param int $from_years_ago indicates the starting period in the far past
     *        from which to search for records. In other words records _before_
     *        this date are ignored for the overview.
     * @param state $state target overview state.
     * @param collection<user> $users users for whom to generate the overviews.
     * @param string $tbl_ca explicit competency achievement table alias to use
     *        in the base query if any.
     *
     * @return self the primed repository.
     */
    public static function overview_for(
        int $days_ago,
        int $from_years_ago,
        state $state,
        collection $users,
        string $tbl_ca = 'ca'
    ): self {
        $instant = (new DateTimeImmutable())
            ->setTimestamp(time() - ($days_ago * DAYSECS))
            ->setTime(0, 0, 0)
            ->getTimestamp();

        $from = (new DateTimeImmutable())
            ->setTimestamp(time() - ($from_years_ago * YEARSECS))
            ->setTime(0, 0, 0)
            ->getTimestamp();

        switch ($state->name) {
            case state::not_started()->name:
                return self::overview_for_not_started(
                    $users, $tbl_ca, $instant, $from
                );

            case state::not_progressed()->name:
                return self::overview_for_not_progressed(
                    $users, $tbl_ca, $instant, $from
                );

            case state::progressed()->name:
                return self::overview_for_progressed($users, $tbl_ca, $instant);

            case state::achieved()->name:
                return self::overview_for_achieved($users, $tbl_ca, $instant);

            default:
                throw new coding_exception("unknown overview state: {$state->name}");
        }
    }

    /**
     * Returns a query to retrieve 'not started' achievements for user(s).
     *
     * @param collection<user> $users users for whom to generate the overviews.
     * @param string $tbl_ca competency achievement table alias to use in the
     *        base query.
     * @param int $instant reference time as seconds since the Epoch indicating
     *        the start of the overview period.
     * @param int $from_years_ago indicates the starting period in the far past
     *        from which to search for records.
     *
     * @return self the query.
     */
    private static function overview_for_not_started(
        collection $users,
        string $tbl_ca,
        int $instant,
        int $from_years_ago
    ): self {
        $tbl_sc = "{$tbl_ca}_sc";
        $tbl_sv = "{$tbl_ca}_sv";

        [$latest_id_in_period, $parm_in] = self::id_subquery(
            $tbl_ca, $instant, true, true
        );

        [$latest_id_bf_period, $parm_bf] = self::id_subquery(
            $tbl_ca, $instant, false, true
        );

        [$any_id_in_period, $parm_in_1] = self::id_subquery(
            $tbl_ca, $instant, true, false
        );

        $latest_id_bf_period .= "AND NOT EXISTS($any_id_in_period)";
        $parm_bf += $parm_in_1;

        return self::query_base($users, $tbl_ca, $tbl_sc, $tbl_sv)
            ->where("$tbl_ca.proficient", false)
            ->where(
                fn (builder $builder): builder => self::is_least_complete(
                    $builder, $tbl_sc, $tbl_sv
                )
            )
            ->where(
                fn (builder $builder): builder => $builder
                    ->where_raw("$tbl_ca.id = ($latest_id_in_period)", $parm_in)
                    ->or_where_raw("$tbl_ca.id = ($latest_id_bf_period)", $parm_bf)
            )
            ->where("$tbl_ca.last_aggregated", '>=', $from_years_ago);
    }

    /**
     * Returns a query to retrieve 'not progressed' achievements for user(s).
     *
     * @param collection<user> $users users for whom to generate the overviews.
     * @param string $tbl_ca competency achievement table alias to use in the
     *        base query.
     * @param int $instant reference time as seconds since the Epoch indicating
     *        the start of the overview period.
     * @param int $from_years_ago indicates the starting period in the far past
     *        from which to search for records.
     *
     * @return self the query.
     */
    private static function overview_for_not_progressed(
        collection $users,
        string $tbl_ca,
        int $instant,
        int $from_years_ago
    ): self {
        $tbl_sc = "{$tbl_ca}_sc";
        $tbl_sv = "{$tbl_ca}_sv";

        [$any_id_in_period, $parm_in] = self::id_subquery(
            $tbl_ca, $instant, true, false
        );

        [$latest_id_bf_period, $parm_bf] = self::id_subquery(
            $tbl_ca, $instant, false, true
        );

        return self::query_base($users, $tbl_ca, $tbl_sc, $tbl_sv)
            ->where("$tbl_ca.proficient", false)
            ->where(
                fn (builder $builder): builder => self::is_not_least_complete(
                    $builder, $tbl_sc, $tbl_sv
                )
            )
            ->where_raw("$tbl_ca.id = ($latest_id_bf_period)", $parm_bf)
            ->where_raw("NOT EXISTS($any_id_in_period)", $parm_in)
            ->where("$tbl_ca.last_aggregated", '>=', $from_years_ago);
    }

    /**
     * Returns a query to retrieve 'progressed' achievements for user(s).
     *
     * @param collection<user> $users users for whom to generate the overviews.
     * @param string $tbl_ca competency achievement table alias to use in the
     *        base query.
     * @param int $instant reference time as seconds since the Epoch indicating
     *        the start of the overview period.
     *
     * @return self the query.
     */
    private static function overview_for_progressed(
        collection $users, string $tbl_ca, int $instant
    ): self {
        $tbl_ca = 'ca';
        $tbl_sc = "{$tbl_ca}_sc";
        $tbl_sv = "{$tbl_ca}_sv";

        [$latest_id_in_period, $parm_in] = self::id_subquery(
            $tbl_ca, $instant, true, true
        );

        return self::query_base($users, $tbl_ca, $tbl_sc, $tbl_sv)
            ->where("$tbl_ca.proficient", false)
            ->where(
                fn (builder $builder): builder => self::is_not_least_complete(
                    $builder, $tbl_sc, $tbl_sv
                )
            )
            ->where_raw("$tbl_ca.id = ($latest_id_in_period)", $parm_in);
    }

    /**
     * Returns a query to retrieve 'proficient' achievements for user(s).
     *
     * @param collection<user> $users users for whom to generate the overviews.
     * @param string $tbl_ca competency achievement table alias to use in the
     *        base query.
     * @param int $instant reference time as seconds since the Epoch indicating
     *        the start of the overview period.
     *
     * @return self the query.
     */
    private static function overview_for_achieved(
        collection $users, string $tbl_ca, int $instant
    ): self {
        $tbl_ca = 'ca';
        $tbl_sc = "{$tbl_ca}_sc";
        $tbl_sv = "{$tbl_ca}_sv";

        [$latest_id_in_period, $parm_in] = self::id_subquery(
            $tbl_ca, $instant, true, true
        );

        return self::query_base($users, $tbl_ca, $tbl_sc, $tbl_sv)
            ->where("$tbl_ca.proficient", true)
            ->where_raw("$tbl_ca.id = ($latest_id_in_period)", $parm_in);
    }

    /**
     * Returns a starting query to get the latest _non archived_ competency
     * overviews for the previously specified users.
     *
     * @param collection<user> $users users for whom to generate the overviews.
     * @param string $ca_alias competency achievement table alias.
     * @param string $sc_alias scale table alias.
     * @param string $sv_alias scale _value_ table alias.
     *
     * @return self the repository.
     */
    private static function query_base(
        collection $users, string $ca_alias, string $sc_alias, string $sv_alias
    ): self {
        $comp_alias = "{$ca_alias}_c";
        $comp_fw_alias = "{$ca_alias}_cf";
        $scale_assign_alias = "{$ca_alias}_csa";

        return (new self(competency_achievement::class))
            ->as($ca_alias)
            ->with('assignment')
            ->with('competency')
            ->with('value')
            ->with('assignment.current_achievement')
            ->join(
                [competency::TABLE, $comp_alias],
                "$comp_alias.id",
                "$ca_alias.competency_id"
            )
            ->join(
                [competency_framework::TABLE, $comp_fw_alias],
                "$comp_fw_alias.id",
                "$comp_alias.frameworkid"
            )
            ->join(
                [scale_assignment::TABLE, $scale_assign_alias],
                "$scale_assign_alias.frameworkid",
                "$comp_fw_alias.id"
            )
            ->join(
                [scale::TABLE, $sc_alias],
                "$sc_alias.id",
                "$scale_assign_alias.scaleid"
            )
            ->left_join(
                [scale_value::TABLE, $sv_alias],
                "$sv_alias.id",
                "$ca_alias.scale_value_id"
            )
            ->where("$ca_alias.user_id", $users->pluck('id'))
            ->where(
                "$ca_alias.status",
                '!=',
                competency_achievement::ARCHIVED_ASSIGNMENT
            );
    }

    /**
     * Returns a subquery targeting the latest competency achievement record for
     * a user/competency/assignment before or after a reference time.
     *
     * This creates a self join against the competency achievement table in the
     * main query and matches against the user/competency/assignment that is
     * retrieved there. In other words, the main query must target the user,
     * competency and assignment for this subquery to work.
     *
     * @param string $alias competency achievement table alias used in the
     *        base query.
     * @param int $instant the reference time as seconds since the Epoch.
     * @param bool $after if true, indicates the method should return the latest
     *        achievement record after the reference time.
     * @param bool $latest if true, indicates the subquery targets the latest id
     *        ie 'select MAX(id)' instead of just 'select id'.
     *
     * @return array [string SQL, [$placeholder => $instant]] tuple.
     */
    private static function id_subquery(
        string $alias,
        int $instant,
        bool $after,
        bool $latest
    ): array {
        $compare_to = $after ? '>=' : '<';
        $placeholder = moodle_database::get_unique_param();
        $achievement_table = competency_achievement::TABLE;

        $my_alias = "{$alias}_1";
        $id = $latest ? "MAX($my_alias.id)" : "$my_alias.id";

        $sql = "
            SELECT $id
              FROM {{$achievement_table}} $my_alias
             WHERE $my_alias.user_id = $alias.user_id
               AND $my_alias.competency_id = $alias.competency_id
               AND $my_alias.assignment_id = $alias.assignment_id
               AND $my_alias.time_scale_value $compare_to :$placeholder
        ";

        return [$sql, [$placeholder => $instant]];
    }

    /**
     * Primes the incoming builder to target the 'least complete' scale value.
     *
     * The 'least complete' is determined from the scale value as follows:
     * - A null scale value is always 'least complete'
     * - if the scale value is the lowest scale value and the parent scale's
     *   'lowest scale value is not started' value is true, then it is 'least
     *   complete'
     *
     * @param builder $builder builder from the parent where() method.
     * @param string $sc_alias scale table alias.
     * @param string $sv_alias scale _value_ table alias.
     *
     * @return builder the primed builder.
     */
    private static function is_least_complete(
        builder $builder,
        string $sc_alias,
        string $sv_alias
    ): builder {
        $sort_order = self::least_complete_scale_value_sort_order_subquery(
            $sc_alias, $sv_alias
        );

        return $builder
            ->where_null("$sv_alias.id")
            ->or_where(
                fn(builder $b): builder => $b
                    ->where("$sc_alias.lowest_is_not_started", true)
                    ->where_raw("$sv_alias.sortorder = $sort_order")
            );
    }

    /**
     * Primes the incoming builder NOT to target the 'least complete' scale value.
     *
     * See notes in self::is_least_complete().
     *
     * @param builder $builder builder from the parent where() method.
     * @param string $sc_alias scale table alias.
     * @param string $sv_alias scale _value_ table alias.
     *
     * @return builder the primed builder.
     */
    private static function is_not_least_complete(
        builder $builder,
        string $sc_alias,
        string $sv_alias
    ): builder {
        $sort_order = self::least_complete_scale_value_sort_order_subquery(
            $sc_alias, $sv_alias
        );

        return $builder
            ->where_not_null("$sv_alias.id")
            ->where(
                fn(builder $b): builder => $b
                    ->where("$sc_alias.lowest_is_not_started", false)
                    ->or_where(
                        fn(builder $b): builder => $b
                            ->where("$sc_alias.lowest_is_not_started", true)
                            ->where_raw("$sv_alias.sortorder != $sort_order")
                    )
            );
    }

    /**
     * Returns a subquery targeting the least complete scale value sort order.
     *
     * @param string $sc_alias scale table alias.
     * @param string $sv_alias scale _value_ table alias.
     *
     * @return string the sql snippet.
     */
    private static function least_complete_scale_value_sort_order_subquery(
        string $sc_alias,
        string $sv_alias
    ): string {
        // For competency scale values, the 'most complete' is at sort order 1
        // and the 'least complete' is at sort order N.
        $sv_table = scale_value::TABLE;
        $sv_mx = "{$sv_alias}_mx";

        return "(
            SELECT MAX($sv_mx.sortorder)
            FROM {{$sv_table}} $sv_mx
            WHERE $sv_mx.scaleid = $sc_alias.id
        )";
    }
}
