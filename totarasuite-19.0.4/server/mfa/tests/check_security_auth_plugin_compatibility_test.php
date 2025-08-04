<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Navjeet Singh <navjeet.singh@totara.com>
 * @package core_mfa
 */

use core\check\result;
use core_mfa\check\security\auth_plugin_compatibility;
use core_phpunit\testcase;

/**
 * @coversDefaultClass \core_mfa\check\security\auth_plugin_compatibility
 * @group core_mfa
 */
class core_mfa_check_security_auth_plugin_compatibility_test extends testcase {
    /**
     * @return void
     */
    public function test_auth_plugin_compatibility(): void {
        $check = new auth_plugin_compatibility();

        $this->assertSame(get_string('check_auth_plugin_compatibility_name', 'mfa'), $check->get_name());
        $this->assertSame(get_string('auth_plugins', 'mfa'), $check->get_action_link()->text);

        // Now mock a situation where all plugins are successful
        $reflected = new ReflectionClass(\core_mfa\mfa::class);
        $original = $reflected->getStaticPropertyValue('incompatible_plugins');
        $reflected->setStaticPropertyValue('incompatible_plugins', []);

        $result = $check->get_result();
        $this->assertSame(result::OK, $result->get_status());
        $this->assertSame(get_string('check_auth_plugin_compatibility', 'mfa'), $result->get_summary());
        $this->assertSame(get_string('check_auth_plugin_compatibility_detail', 'mfa'), $result->get_details());

        // Now fail it instead
        $reflected->setStaticPropertyValue('incompatible_plugins', ['not_valid']);

        $result = $check->get_result();
        $this->assertSame(result::WARNING, $result->get_status());
        $this->assertSame(get_string('error:auth_plugins_compatibility', 'mfa'), $result->get_summary());

        // Reset
        $reflected->setStaticPropertyValue('incompatible_plugins', $original);
    }
}