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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package perform_goal
 */

use core\collection;
use core_phpunit\testcase;
use perform_goal\plugininfo\goal as goal_plugininfo;
use perform_goal\model\goal;
use perform_goal\testing\generator;
use perform_goal\testing\goal_generator_config;
use perform_goal\settings_helper;

/**
 * @group perform_goal
 * @coversDefaultClass perform_goal\plugininfo\goal
 */
class perform_goal_plugininfo_goal_test extends testcase {

    public function test_is_not_enabled() {
        set_config('enableperform_goals', false);
        $plugininfo = new goal_plugininfo();

        $this->assertFalse($plugininfo->is_enabled());
    }

    public function test_is_enabled() {
        // Enabled by default.
        $plugininfo = new goal_plugininfo();

        $this->assertTrue($plugininfo->is_enabled());
    }

    public function test_plugininfo_data() {
        $this->setAdminUser();

        $plugininfo = new goal_plugininfo();

        $result = $plugininfo->get_usage_for_registration_data();
        $this->assertEquals(1, $result['perform_goals_enabled']);
        $this->assertEquals(0, $result['number_of_totara_goals']);

        // Generate test data
        $goals = self::create_test_goals();

        $result = $plugininfo->get_usage_for_registration_data();
        $this->assertEquals(1, $result['perform_goals_enabled']);
        $this->assertEquals(5, $result['number_of_totara_goals']);

        settings_helper::disable_perform_goals();
        $result = $plugininfo->get_usage_for_registration_data();

        // Data should be returned even if features are disabled.
        $this->assertEquals(0, $result['perform_goals_enabled']);
        $this->assertEquals(5, $result['number_of_totara_goals']);
    }

    private static function create_test_goals(
        int $goal_count = 5,
        ?\context $context = null
    ): collection {
        self::setAdminUser();

        $generator = generator::instance();
        return collection::new(range(0, $goal_count - 1))
            ->map(
                function(int $i) use ($context): goal_generator_config {
                    $values = ['id_number' => "idn_$i"];
                    if ($context) {
                        $values['context'] = $context;
                    }

                    return goal_generator_config::new($values);
                }
            )
            ->map(
                fn(goal_generator_config $cfg): goal => $generator->create_goal(
                    $cfg
                )
            );
    }
}