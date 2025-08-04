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

use core\orm\query\builder;
use core_phpunit\testcase;
use tool_diagnostic\provider\totara_upgrade_history;

/**
 * @group tool_diagnostic
 */
class tool_diagnostic_provider_totara_upgrade_history_test extends testcase {

    public function test_missing_whitelist(): void {
        $config = [
            'enabled' => true,
            'blacklist' => []
        ];

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage("missing key 'whitelist' for provider totara_upgrade_history");

        new totara_upgrade_history($config);
    }

    public function test_empty_whitelist(): void {
        $config = [
            'enabled' => true,
            'whitelist' => []
        ];
        $provider = new totara_upgrade_history($config);
        $content = $provider->get_content();
        $this->assertEquals([], json_decode($content->get_data()));
    }

    public function test_whitelist(): void {
        builder::table('upgrade_log')->delete();

        $upgrade_data_core = [
            "type" => 0,
            "plugin" => "core",
            "version" => "111",
            "targetversion" => "222",
            "info" => "Upgrade savepoint reached",
            "details" => "test details",
            "backtrace" => "test backtrace",
            "userid" => 0,
            "timemodified" => 1721811111,
        ];

        $upgrade_data_totara_core = [
            "type" => 0,
            "plugin" => "totara_core",
            "version" => "333",
            "targetversion" => "444",
            "info" => "Upgrade savepoint reached",
            "details" => "test details",
            "backtrace" => "test backtrace",
            "userid" => 0,
            "timemodified" => 1721822222,
        ];

        builder::table('upgrade_log')->insert($upgrade_data_core);
        builder::table('upgrade_log')->insert($upgrade_data_totara_core);

        // No results.
        $config = [
            'enabled' => true,
            'whitelist' => ['non_existent']
        ];
        $provider = new totara_upgrade_history($config);
        $content = $provider->get_content();
        $decoded = json_decode($content->get_data(), true);
        $this->assertEmpty($decoded);

        // One plugin
        $config = [
            'enabled' => true,
            'whitelist' => ['core']
        ];
        $provider = new totara_upgrade_history($config);
        $content = $provider->get_content();
        $decoded = json_decode($content->get_data(), true);
        $this->assertCount(1, $decoded);
        unset($decoded[0]['id']);
        // timestamp will be converted to readable format
        $upgrade_data_core['timemodified'] = '2024-07-24 08:51:51 UTC';

        $this->assertEquals($upgrade_data_core, $decoded[0]);

        // Two plugins
        $config = [
            'enabled' => true,
            'whitelist' => ['core', 'totara_core']
        ];
        $provider = new totara_upgrade_history($config);
        $content = $provider->get_content();
        $decoded = json_decode($content->get_data(), true);
        unset($decoded[0]['id']);
        unset($decoded[1]['id']);
        // timestamp will be converted to readable format
        $upgrade_data_totara_core['timemodified'] = '2024-07-24 11:57:02 UTC';

        $this->assertEquals([
            0 => $upgrade_data_core,
            1 => $upgrade_data_totara_core
        ], $decoded);
    }
}
