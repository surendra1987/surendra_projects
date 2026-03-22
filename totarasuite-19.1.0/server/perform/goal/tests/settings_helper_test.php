<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
use perform_goal\settings_helper;

/**
 * Unit tests for the perform_goal settings_helper
 *
 * @group perform_goal
 */
class perform_goal_settings_helper_test extends testcase {

    /**
     * Test Totara goals transition mode is enabled.
     *
     * @return void
     */
    public function test_perform_goals_transition_mode_enabled() {
        set_config('goals_choice', settings_helper::TOTARA_GOALS_TRANSITION_MODE, 'perform_goal');
        // Trigger enable/disable legacy/perform goals feature settings.
        settings_helper::advanced_features_callback();
        $config = get_config('perform_goal');
        self::assertTrue(settings_helper::is_legacy_goals_enabled());
        self::assertTrue(settings_helper::is_perform_goals_enabled());
        self::assertTrue(settings_helper::is_perform_goals_transition_mode_enabled());
        self::assertSame(settings_helper::TOTARA_GOALS_TRANSITION_MODE, (int)$config->goals_choice);
    }

    /**
     * Test Totara goals transition mode is disabled when Totara goals is enabled.
     *
     * @return void
     */
    public function test_perform_goals_transition_mode_disabled_with_perform_goals() {
        set_config('goals_choice', settings_helper::TOTARA_GOALS, 'perform_goal');
        // Trigger enable/disable legacy/perform goals feature settings.
        settings_helper::advanced_features_callback();
        $config = get_config('perform_goal');
        self::assertTrue(settings_helper::is_legacy_goals_disabled());
        self::assertTrue(settings_helper::is_perform_goals_enabled());
        self::assertTrue(settings_helper::is_perform_goals_transition_mode_disabled());
        self::assertSame(settings_helper::TOTARA_GOALS, (int)$config->goals_choice);
    }

    /**
     * Test Totara goals transition mode is disabled when none goals feature is enabled.
     *
     * @return void
     */
    public function test_perform_goals_transition_mode_disabled_with_no_goals() {
        set_config('goals_choice', settings_helper::NO_GOALS, 'perform_goal');
        // Trigger enable/disable legacy/perform goals feature settings.
        settings_helper::advanced_features_callback();
        $config = get_config('perform_goal');
        self::assertTrue(settings_helper::is_legacy_goals_disabled());
        self::assertTrue(settings_helper::is_perform_goals_disabled());
        self::assertTrue(settings_helper::is_perform_goals_transition_mode_disabled());
        self::assertSame(settings_helper::NO_GOALS, (int)$config->goals_choice);
    }

    /**
     * Test Totara goals transition mode is disabled when Legacy goals is enabled.
     *
     * @return void
     */
    public function test_perform_goals_transition_mode_disabled_with_legacy_goals() {
        set_config('goals_choice', settings_helper::LEGACY_GOALS, 'perform_goal');
        // Trigger enable/disable legacy/perform goals feature settings.
        settings_helper::advanced_features_callback();
        $config = get_config('perform_goal');
        self::assertTrue(settings_helper::is_legacy_goals_enabled());
        self::assertTrue(settings_helper::is_perform_goals_disabled());
        self::assertTrue(settings_helper::is_perform_goals_transition_mode_disabled());
        self::assertSame(settings_helper::LEGACY_GOALS, (int)$config->goals_choice);
    }

    /**
     * @return array[]
     */
    public static function flavour_data_provider(): array {
        return [
            ['engage', false],
            ['learn', false],
            ['learn_engage', false],
            ['learn_professional', false],
            ['learn_perform', true],
            ['learn_perform_engage', true],
            ['perform', true],
            ['perform_engage', true],
        ];
    }

    /**
     * @dataProvider flavour_data_provider
     * @param string $flavour
     * @param bool $is_perform_flavour
     * @return void
     */
    public function test_get_options(string $flavour, bool $is_perform_flavour): void {
        global $CFG;

        $CFG->forceflavour = $flavour;

        if ($is_perform_flavour) {
            $expected_options = [
                settings_helper::NO_GOALS => 'None',
                settings_helper::LEGACY_GOALS => 'Legacy goals',
                settings_helper::TOTARA_GOALS => 'Totara goals',
                settings_helper::TOTARA_GOALS_TRANSITION_MODE => 'Totara goals transition mode',
            ];
        } else {
            $expected_options = [
                settings_helper::NO_GOALS => 'None',
                settings_helper::LEGACY_GOALS => 'Legacy goals',
            ];
        }
        static::assertEquals($expected_options, settings_helper::get_options());
    }
}
