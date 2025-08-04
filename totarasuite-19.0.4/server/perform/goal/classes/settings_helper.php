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

namespace perform_goal;

use totara_core\advanced_feature;
use totara_flavour\helper;

class settings_helper {

    const NO_GOALS = 0;

    const LEGACY_GOALS = 1;

    const TOTARA_GOALS = 2;

    const TOTARA_GOALS_TRANSITION_MODE = 3;

    /**
     * Return the option list for admin setting config select
     *
     * @return array
     */
    public static function get_options(): array {
        $options = [
            self::NO_GOALS => get_string('enable_perform_goal_none', 'perform_goal'),
            self::LEGACY_GOALS => get_string('enable_perform_goal_legacy', 'perform_goal'),
            self::TOTARA_GOALS => get_string('enable_perform_goal_totara', 'perform_goal'),
            self::TOTARA_GOALS_TRANSITION_MODE => get_string('enable_perform_goal_totara_transition_mode', 'perform_goal'),
        ];

        // Don't show 'Totara goals' & 'Transition mode' options if we're not on a perform flavour.
        $flavour = helper::get_active_flavour_definition();
        if ($flavour && !helper::is_allowed($flavour, 'perform_goals')) {
            unset(
                $options[self::TOTARA_GOALS],
                $options[self::TOTARA_GOALS_TRANSITION_MODE]
            );
        }

        return $options;
    }

    /**
     * Handle enablegoals and enableperform_goals settings
     *
     * @return void
     */
    public static function advanced_features_callback(): void {
        $config = get_config('perform_goal');
        if (!isset($config->goals_choice)) {
            return;
        }
        switch ($config->goals_choice) {
            case self::NO_GOALS:
                self::disable_legacy_goals();
                self::disable_perform_goals();
                break;
            case self::LEGACY_GOALS:
                self::enable_legacy_goals();
                self::disable_perform_goals();
                break;
            case self::TOTARA_GOALS:
                self::disable_legacy_goals();
                self::enable_perform_goals();
                break;
            case self::TOTARA_GOALS_TRANSITION_MODE:
                self::enable_legacy_goals();
                self::enable_perform_goals();
                break;
            default:
                break;
        }
    }

    /**
     * Check to see if a legacy(hierarchy) goal feature is set to be enabled (visible)
     *
     * @return bool
     */
    public static function is_legacy_goals_enabled(): bool {
        return advanced_feature::is_enabled('goals');
    }

    /**
     * Check to see if a legacy(hierarchy) goal feature is set to be disabled
     *
     * @return bool
     */
    public static function is_legacy_goals_disabled(): bool {
        return advanced_feature::is_disabled('goals');
    }

    /**
     * Check to see if a Totara goal feature is set to be enabled (visible)
     *
     * @return bool
     */
    public static function is_perform_goals_enabled(): bool {
        return advanced_feature::is_enabled('perform_goals');
    }

    /**
     * Check to see if a Totara goal feature is set to be disabled
     *
     * @return bool
     */
    public static function is_perform_goals_disabled(): bool {
        return advanced_feature::is_disabled('perform_goals');
    }

    /**
     * Check to see if a Totara goal transition mode feature is set to be enabled (visible)
     *
     * @return bool
     */
    public static function is_perform_goals_transition_mode_enabled(): bool {
        $config = get_config('perform_goal');
        if (!isset($config->goals_choice)) {
            return false;
        }
        return (self::TOTARA_GOALS_TRANSITION_MODE === (int)$config->goals_choice);
    }

    /**
     * Check to see if a Totara goal transition mode feature is set to be disabled
     *
     * @return bool
     */
    public static function is_perform_goals_transition_mode_disabled(): bool {
        $config = get_config('perform_goal');
        if (!isset($config->goals_choice)) {
            return true;
        }
        return (self::TOTARA_GOALS_TRANSITION_MODE !== (int)$config->goals_choice);
    }

    /**
     * Helper method to enable the perform goals feature
     *
     * @return void
     */
    public static function enable_perform_goals(): void {
        advanced_feature::enable('perform_goals');
    }

    /**
     * Helper method to disable the perform goals feature
     *
     * @return void
     */
    public static function disable_perform_goals(): void {
        advanced_feature::disable('perform_goals');
    }

    /**
     * Helper method to enable the legacy goals feature
     *
     * @return void
     */
    public static function enable_legacy_goals(): void {
        advanced_feature::enable('goals');
    }

    /**
     * Helper method to disable the legacy goals feature
     *
     * @return void
     */
    public static function disable_legacy_goals(): void {
        advanced_feature::disable('goals');
    }

    /**
     * Returns component name
     *
     * @return string
     */
    public static function get_component() {
        return 'perform_goal';
    }

    /**
     * Returns file area name, this divides files of one component into groups with different access control.
     * All files in one area have the same access control.
     *
     * @return string
     */
    public static function get_filearea() {
        return 'description';
    }
}