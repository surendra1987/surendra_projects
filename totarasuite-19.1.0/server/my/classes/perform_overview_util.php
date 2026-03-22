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

namespace core_my;

use core\collection;
use core\entity\user;
use coding_exception;
use mod_perform\util as util_perform;
use perform_goal\interactor\goal_interactor;
use totara_core\advanced_feature;
use totara_competency\helpers\capability_helper;
use totara_flavour\definition as flavour;
use totara_flavour\helper as flavour_helper;

class perform_overview_util {

    public const PERIOD_TWO_WEEK = 14;
    public const PERIOD_ONE_MONTH = 30;
    public const PERIOD_THREE_MONTH = 90;
    public const PERIOD_SIX_MONTH = 182;
    public const PERIOD_ONE_YEAR = 365;

    private static $permission_cache = [
        'activities' => [],
        'competencies' => [],
        'perform_goals' => []
    ];

    /**
     * Reset the static permission cache. Mainly for unit tests.
     * @return void
     */
    public static function reset_permission_cache() {
        self::$permission_cache = [
            'activities' => [],
            'competencies' => [],
            'perform_goals' => []
        ];
    }

    /**
     * Check if we can enable 'Performance overview'
     *
     * @param definition $flavour flavour to check against. If not specified,
     *        defaults to the active flavour.
     *
     * @return bool true is the perform overview is disabled.
     */
    public static function is_overview_disabled(flavour $flavour = null): bool {
        $features = collection::new([
            'performance_activities',
            'competency_assignment',
            'perform_goals'
        ]);

        $ref_flavour = $flavour ?? flavour_helper::get_active_flavour_definition();
        $flavour_allows_any = is_null($ref_flavour)
            ? true
            : $features->find(
                fn (string $it): bool => flavour_helper::is_allowed($ref_flavour, $it)
            );

        if (!$flavour_allows_any) {
            return true;
        }

        $any_enabled = $features->find(
            fn (string $it): bool => advanced_feature::is_enabled($it)
        );

        return !$any_enabled;
    }

    /**
     * Check the current user has permission to see the perform overview page for the given user.
     *
     * @param int|null $for_user_id
     * @return bool
     * @throws coding_exception
     */
    public static function has_any_permission(?int $for_user_id = null): bool {
        if (isguestuser()) {
            return false;
        }

        $target_user_id = $for_user_id;
        if (!$target_user_id) {
            $target_user = user::logged_in();
            $target_user_id = $target_user ? $target_user->id : null;

            if (!$target_user_id) {
                return false;
            }
        }

        return self::can_view_activities_overview_for($target_user_id)
            || self::can_view_competencies_overview_for($target_user_id)
            || self::can_view_perform_goals_overview_for($target_user_id);
    }

    /**
     * Get the period option by the period
     *
     * @param int $period
     * @return string
     * @throws coding_exception
     */
    public static function get_label_by_period(int $period): string {
        switch ($period) {
            case self::PERIOD_TWO_WEEK:
                return get_string('overview_period_14_days', 'core_my');
            case self::PERIOD_ONE_MONTH:
                return get_string('overview_period_30_days', 'core_my');
            case self::PERIOD_THREE_MONTH:
                return get_string('overview_period_90_days', 'core_my');
            case self::PERIOD_SIX_MONTH:
                return get_string('overview_period_6_months', 'core_my');
            case self::PERIOD_ONE_YEAR:
                return get_string('overview_period_1_year', 'core_my');
            default:
                return '';
        }
    }

    /**
     * Return the period array
     *
     * @return array[]
     * @throws coding_exception
     */
    public static function get_overview_period_options(): array {
        return [
            ['label' => self::get_label_by_period(self::PERIOD_TWO_WEEK), 'id' => self::PERIOD_TWO_WEEK],
            ['label' => self::get_label_by_period(self::PERIOD_ONE_MONTH), 'id' => self::PERIOD_ONE_MONTH],
            ['label' => self::get_label_by_period(self::PERIOD_THREE_MONTH), 'id' => self::PERIOD_THREE_MONTH],
            ['label' => self::get_label_by_period(self::PERIOD_SIX_MONTH), 'id' => self::PERIOD_SIX_MONTH],
            ['label' => self::get_label_by_period(self::PERIOD_ONE_YEAR), 'id' => self::PERIOD_ONE_YEAR],
        ];
    }

    /**
     * Check if the current user has the permission to see the activity overview section for the given user.
     * Also returns false if the performance_activities feature is not enabled.
     *
     * @param null|int $user_id
     * @return bool
     */
    public static function can_view_activities_overview_for(?int $user_id): bool {
        if (!$user_id) {
            return false;
        }

        if (!array_key_exists($user_id, self::$permission_cache['activities'])) {
            if (!advanced_feature::is_enabled('performance_activities')) {
                self::$permission_cache['activities'][$user_id] = false;
            } else {
                $logged_in_user_id = (int)user::logged_in()->id;
                self::$permission_cache['activities'][$user_id] = $logged_in_user_id === $user_id
                    || util_perform::can_manage_participation($logged_in_user_id, $user_id);
            }
        }
        return self::$permission_cache['activities'][$user_id];
    }
    /**
     * Check if the current user has the permission to see the competency overview section for the given user.
     * Also returns false if the competency_assignment feature is not enabled.
     *
     * @param null|int $user_id
     * @return bool
     */
    public static function can_view_competencies_overview_for(?int $user_id): bool {
        if (!$user_id) {
            return false;
        }

        if (array_key_exists($user_id, self::$permission_cache['competencies'])) {
            return self::$permission_cache['competencies'][$user_id];
        }
        if (!advanced_feature::is_enabled('competencies') || !advanced_feature::is_enabled('competency_assignment')) {
            self::$permission_cache['competencies'][$user_id] = false;
        } else {
            self::$permission_cache['competencies'][$user_id] = capability_helper::can_view_profile($user_id);
        }
        return self::$permission_cache['competencies'][$user_id];
    }

    /**
     * Check if the current user has the permission to see the perform goal
     * overview section for the given user.
     *
     * Also returns false if the perform_goal feature is not enabled.
     *
     * @param null|int $user_id overview subject.
     *
     * @return bool true if the current use has permissions.
     */
    public static function can_view_perform_goals_overview_for(
        ?int $user_id
    ): bool {
        if (!$user_id) {
            return false;
        }

        $cache = self::$permission_cache['perform_goals'];

        if (!array_key_exists($user_id, $cache)) {
            $cache[$user_id] = advanced_feature::is_enabled('perform_goals')
                ? goal_interactor::for_user(new user($user_id))
                    ->can_view_personal_goals()
                : false;
        }

        return $cache[$user_id];
    }
}