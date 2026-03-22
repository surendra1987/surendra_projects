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
 * @author  Aaron Machin <aaron.machin@totara.com>
 * @package tool_usagedata
 */

use core_phpunit\testcase;
use tool_usagedata\api\data_collector_api;
use tool_usagedata\exception\data_collector_exception;

class tool_usagedata_data_collector_api_test extends testcase {

    public function test_send_export_data(): void {
        $api = new data_collector_api();

        curl::mock_response(
            file_get_contents(__DIR__ . '/fixtures/data_collector/response.json')
        );

        $this->assertTrue($api->send_export_data(
            'https://anexample.com',
            []
        ));
    }

    public function test_send_export_data_incorrect_url(): void {
        $api = new data_collector_api();

        $this->expectException(data_collector_exception::class);
        $this->expectExceptionMessage('Could not export data: ');

        $api->send_export_data(
            'http://example.invalid',
            []
        );
    }

}