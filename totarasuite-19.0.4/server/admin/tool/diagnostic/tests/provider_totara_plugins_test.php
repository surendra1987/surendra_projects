<?php
/*
 * This file is part of Totara Learn
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Matthias Bonk <matthias.bonk@totaralearning.com>
 * @package tool_diagnostic
 */

use core_phpunit\testcase;
use tool_diagnostic\provider\totara_plugins;

/**
 * @group tool_diagnostic
 */
class tool_diagnostic_provider_totara_plugins_test extends testcase {

    public function test_content(): void {
        $provider = new totara_plugins(["enabled" => true]);
        $content = json_decode($provider->get_content()->get_data(), true);

        // Just pick two expected elements and assert their properties.
        $totara_core = $content['totara']['core'];
        $this->assertCount(6, $totara_core);
        $this->assertEquals('totara', $totara_core['type']);
        $this->assertEquals('core', $totara_core['name']);
        $this->assertEquals('Totara core', $totara_core['displayname']);
        $this->assertEquals('std', $totara_core['source']);
        // null because it can not be enabled/disabled.
        $this->assertNull($totara_core['enabled']);
        $this->assertGreaterThan(2000000000, (int)$totara_core['version']);

        $mod_perform = $content['mod']['perform'];
        $this->assertCount(6, $mod_perform);
        $this->assertEquals('mod', $mod_perform['type']);
        $this->assertEquals('perform', $mod_perform['name']);
        $this->assertEquals('Performance activity', $mod_perform['displayname']);
        $this->assertEquals('std', $mod_perform['source']);
        $this->assertTrue($mod_perform['enabled']);
        $this->assertGreaterThan(2000000000, (int)$mod_perform['version']);
    }
}
