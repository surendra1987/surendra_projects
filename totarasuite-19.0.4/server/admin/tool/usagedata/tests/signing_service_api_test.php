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
use tool_usagedata\api\signing_service_api;
use tool_usagedata\exception\signing_service_api_exception;

class tool_usagedata_signing_service_api_test extends testcase {

    protected function setUp():void {
        set_config('registrationcode', '12345abcdefg');
    }

    public function test_retrieve_signed_url(): void {
        global $CFG;
        $api = new signing_service_api();

        $CFG->forced_plugin_settings['tool_usagedata']['signing_service_base_url'] = 'http://example.com';
        $CFG->forced_plugin_settings['tool_usagedata']['signing_service_endpoint'] = '/upload';

        curl::mock_response(
            file_get_contents(__DIR__ . '/fixtures/signing_service/response.json')
        );
        $signed_url = $api->retrieve_signed_url();

        $this->assertEquals('https://datacollector.totara.com?s=signature', $signed_url);
    }

    public function test_retrieve_signed_url_with_empty_registration_code(): void {
        set_config('registrationcode', '');
        $api = new signing_service_api();

        $this->expectException(signing_service_api_exception::class);
        $this->expectExceptionMessage('The registration code should not be empty.');

        $api->retrieve_signed_url();
    }

    public function test_retrieve_signed_url_without_endpoint_in_config(): void {
        $api = new signing_service_api();

        curl::mock_response(
            file_get_contents(__DIR__ . '/fixtures/signing_service/response.json')
        );
        $signed_url = $api->retrieve_signed_url();
        $this->assertEquals('https://datacollector.totara.com?s=signature', $signed_url);
    }

    public function test_retrieve_signed_url_unexpected_response(): void {
        global $CFG;
        $CFG->forced_plugin_settings['tool_usagedata']['signing_service_base_url'] = 'http://example.com';
        $CFG->forced_plugin_settings['tool_usagedata']['signing_service_endpoint'] = '/upload';

        $api = new signing_service_api();

        curl::mock_response(
            'unexpected response here ;)'
        );

        $this->expectException(signing_service_api_exception::class);
        $this->expectExceptionMessage('Could not get signed URL:');

        $api->retrieve_signed_url();
    }

    public function test_retrieve_signed_url_no_signed_url(): void {
        global $CFG;
        $CFG->forced_plugin_settings['tool_usagedata']['signing_service_base_url'] = 'http://example.com';
        $CFG->forced_plugin_settings['tool_usagedata']['signing_service_endpoint'] = '/upload';

        $api = new signing_service_api();

        curl::mock_response(json_encode(
            [
                'data' => [

                ]
            ]
        ));

        $this->expectException(signing_service_api_exception::class);
        $this->expectExceptionMessage('Could not get signed URL: Response is not as expected');

        $api->retrieve_signed_url();
    }
}
