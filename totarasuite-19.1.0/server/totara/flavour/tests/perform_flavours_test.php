<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @package totara_flavour
 */

use core_phpunit\testcase;
use perform_goal\settings_helper;
use totara_core\advanced_feature;
use totara_flavour\definition;
use totara_flavour\overview;
use totara_flavour\helper;

defined('MOODLE_INTERNAL') || die();

/**
 * Tests flavour overview class
 */
class totara_flavour_perform_flavours_test extends testcase {

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
    public function test_conditional_enforced_settings(string $flavour, bool $is_perform_flavour): void {
        settings_helper::disable_legacy_goals();

        $classname = 'flavour_' . $flavour . '\definition';
        /** @var definition $flavour_definition */
        $flavour_definition = new $classname();
        $enforced_settings = $flavour_definition->get_enforced_settings();

        if ($flavour === 'learn_perform_engage') {
            // Special case: only this flavour does not have enforced settings.
            static::assertCount(0, $enforced_settings);
        } else {
            if ($is_perform_flavour) {
                // Make sure all perform flavours have enforced settings only for core component (moodle).
                static::assertCount(1, $enforced_settings);
                static::assertArrayHasKey('moodle', $enforced_settings);
                static::assertIsArray($enforced_settings['moodle']);
                static::assertGreaterThanOrEqual(2, count($enforced_settings['moodle']));
            } else {
                // Make sure all non-perform flavours have enforced settings for core component and perform_goal.
                static::assertCount(2, $enforced_settings);
                static::assertArrayHasKey('moodle', $enforced_settings);
                static::assertArrayHasKey('perform_goal', $enforced_settings);
                static::assertIsArray($enforced_settings['moodle']);
                static::assertGreaterThanOrEqual(2, count($enforced_settings['moodle']));
                static::assertIsArray($enforced_settings['perform_goal']);
                static::assertCount(1, $enforced_settings['perform_goal']);
                static::assertSame(0, $enforced_settings['perform_goal']['goals_choice']);
            }
        }

        // Turn legacy goals on. Now even non-perform flavours should not have the enforced settings for goals_choice.
        settings_helper::enable_legacy_goals();

        /** @var definition $flavour */
        $flavour_definition = new $classname();
        $enforced_settings = $flavour_definition->get_enforced_settings();

        if ($flavour === 'learn_perform_engage') {
            // Special case: only this flavour does not have enforced settings.
            static::assertCount(0, $enforced_settings);
        } else {
            // Make sure all flavours have enforced settings only for core component (moodle).
            static::assertCount(1, $enforced_settings);
            static::assertArrayHasKey('moodle', $enforced_settings);
            static::assertIsArray($enforced_settings['moodle']);
            static::assertGreaterThanOrEqual(2, count($enforced_settings['moodle']));
        }
    }
}

