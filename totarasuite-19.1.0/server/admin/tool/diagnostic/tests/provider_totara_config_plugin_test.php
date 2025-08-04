<?php
/*
 * This file is part of Totara Learn
 *
 * Copyright (C) 2022 onwards Totara Learning Solutions LTD
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
 * @author Matthias Bonk <matthias.bonk@totaralearning.com>
 * @package tool_diagnostic
 */

use core_phpunit\testcase;
use tool_diagnostic\provider\totara_config_plugin;

/**
 * @group tool_diagnostic
 */
class tool_diagnostic_provider_totara_config_plugin_test extends testcase {

    public function test_missing_whitelist(): void {
        $config = [
            'enabled' => true,
            'blacklist' => []
        ];

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage("missing key 'whitelist' for provider totara_config_plugin");

        new totara_config_plugin($config);
    }

    public function test_empty_whitelist(): void {
        $config = [
            'enabled' => true,
            'whitelist' => []
        ];
        $provider = new totara_config_plugin($config);
        $content = $provider->get_content();
        $this->assertEquals([], json_decode($content->get_data(), true));
    }

    public function test_whitelist(): void {
        $config = [
            'enabled' => true,
            'whitelist' => [
                "totara_competency" => [
                    "continuous_tracking",
                    "unassign_behaviour",
                    "does_not_exist",
                ],
                "does_not_exist" => [
                    "does_not_exist",
                ],
            ],
        ];
        $provider = new totara_config_plugin($config);
        $content = $provider->get_content();
        $this->assertEqualsCanonicalizing(
            [
                "totara_competency" => [
                    "continuous_tracking" => get_config('totara_competency', 'continuous_tracking'),
                    "unassign_behaviour" => get_config('totara_competency', 'unassign_behaviour'),
                ],
            ],
            json_decode($content->get_data(), true)
        );
    }

}
