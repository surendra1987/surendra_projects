<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package tool_usagedata
 */

use core_phpunit\testcase;
use tool_usagedata\api\signing_service_api;
use tool_usagedata\config as usagedata_config;

class tool_usagedata_config_test extends testcase {

    /**
     * @return void
     */
    public function test_is_opt_out_enabled_default_disabled(): void {
        unset_config(usagedata_config::CONFIG_OPT_OUT, usagedata_config::COMPONENT);

        $this->assertFalse(usagedata_config::is_opt_out_enabled());
    }

    /**
     * @return void
     */
    public function test_is_opt_out_enabled_disabled(): void {
        set_config(usagedata_config::CONFIG_OPT_OUT, false, usagedata_config::COMPONENT);

        $this->assertFalse(usagedata_config::is_opt_out_enabled());
    }

    /**
     * @return void
     */
    public function test_is_opt_out_enabled_enabled(): void {
        set_config(usagedata_config::CONFIG_OPT_OUT, true, usagedata_config::COMPONENT);

        $this->assertTrue(usagedata_config::is_opt_out_enabled());
    }

    /**
     * @return void
     */
    public function test_get_registration_hash(): void {
        global $CFG;
        set_config('registrationcode', '12345abcdefg');
        $hash = usagedata_config::get_registration_hash();
        $this->assertEquals(hash('sha256', $CFG->registrationcode.$CFG->wwwroot), $hash);

        set_config('registrationcode', '');
        $hash = usagedata_config::get_registration_hash();
        $this->assertEquals("", $hash);
    }

    public function test_get_config(): void {
        global $CFG;

        $CFG->forced_plugin_settings['tool_usagedata']['signing_service_base_url'] = 'http://example.com';
        $this->assertSame('http://example.com', usagedata_config::get_signing_service_base_url());

        $CFG->forced_plugin_settings['tool_usagedata']['signing_service_endpoint'] = '/test';
        $this->assertSame('/test', usagedata_config::get_signing_service_endpoint());
    }

    public function test_get_config_default_value(): void {
        $this->assertEquals(
            usagedata_config::SIGNING_SERVICE_ENDPOINT_DEFAULT,
            usagedata_config::get_signing_service_endpoint()
        );
        $this->assertEquals(
            usagedata_config::SIGNING_SERVICE_BASE_URL_DEFAULT,
            usagedata_config::get_signing_service_base_url()
        );
    }

    public function test_get_config_config_overrides_default_value(): void {
        set_config('signing_service_endpoint', 'config_value_1', usagedata_config::COMPONENT);

        $this->assertEquals(
            'config_value_1',
            usagedata_config::get_signing_service_endpoint()
        );
    }
}
