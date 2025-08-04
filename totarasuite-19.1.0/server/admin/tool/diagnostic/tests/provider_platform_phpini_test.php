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
use tool_diagnostic\provider\platform_phpini;

/**
 * @group tool_diagnostic
 */
class tool_diagnostic_provider_platform_phpini_test extends testcase {

    public function test_missing_whitelist(): void {
        $config = [
            'enabled' => true,
            'blacklist' => []
        ];

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage("missing key 'whitelist' for provider platform_phpini");

        new platform_phpini($config);
    }

    public function test_empty_whitelist(): void {
        $config = [
            'enabled' => true,
            'whitelist' => []
        ];
        $provider = new platform_phpini($config);
        $content = $provider->get_content();
        $this->assertEquals([], json_decode($content->get_data()));
    }

    public function test_whitelist(): void {
        $config = [
            'enabled' => true,
            'whitelist' => ['max_input_vars', 'memory_limit', 'does_not_exist']
        ];
        $provider = new platform_phpini($config);
        $content = $provider->get_content();
        $this->assertEqualsCanonicalizing(
            [
                'max_input_vars' => ini_get('max_input_vars'),
                'memory_limit' => ini_get('memory_limit'),
            ],
            json_decode($content->get_data(), true)
        );
    }

}
