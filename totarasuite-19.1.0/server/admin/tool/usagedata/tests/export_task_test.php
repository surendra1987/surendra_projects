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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package tool_usagedata
 */

use core_phpunit\testcase;
use tool_usagedata\config;
use tool_usagedata\task\export_task;

class tool_usagedata_export_task_test extends testcase {

    public function test_task_execute_opt_out_is_enabled() {
        set_config('sitetype', 'production');
        set_config(config::CONFIG_OPT_OUT, true, config::COMPONENT);

        $task = new export_task();

        $this->expectOutputString(
            'opt_out enabled, disable opt_out to continue.'
            . "\n"
        );
        self::assertFalse($task->execute());
    }

    public function test_task_execute_sitetype_not_production() {
        set_config('sitetype', 'development');

        $task = new export_task();

        $this->expectOutputString(
            'Site type is not production or registration is exempted.'
            . "\n"
        );
        self::assertFalse($task->execute());
    }

    public function test_task_execute_missing_config_values(): void {
        // The task should handle the config values missing and still pass
        set_config(config::CONFIG_OPT_OUT, false, config::COMPONENT);
        set_config('sitetype', 'production');
        set_config('registrationcode', '12345abcdefg');

        $task = new export_task();
        // Mock the responses in reverse order (the last response is mocked first)
        curl::mock_response(
            file_get_contents(__DIR__ . '/fixtures/data_collector/response.json')
        );
        curl::mock_response(
            file_get_contents(__DIR__ . '/fixtures/signing_service/response.json')
        );

        $this->expectOutputString('Usage data exported successfully.' . "\n");
        self::assertTrue($task->execute());
    }

    public function test_task_execute() {
        set_config(config::CONFIG_OPT_OUT, false, config::COMPONENT);
        set_config('signing_service_base_url', 'localhost', config::COMPONENT);
        set_config('signing_service_endpoint', 'test', config::COMPONENT);
        set_config('sitetype', 'production');
        set_config('registrationcode', '12345abcdefg');

        $task = new export_task();

        // Mock the responses in reverse order (the last response is mocked first)
        curl::mock_response(
            file_get_contents(__DIR__ . '/fixtures/data_collector/response.json')
        );
        curl::mock_response(
            file_get_contents(__DIR__ . '/fixtures/signing_service/response.json')
        );

        $this->expectOutputString('Usage data exported successfully.' . "\n");
        self::assertTrue($task->execute());
    }
}
