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
use tool_usagedata\api\api;
use totara_core\http\response;

class tool_usagedata_api_test extends testcase {

    private static function get_method($name) {
        $class = new ReflectionClass(api::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    public function test_get(): void {
        $api = $this->getMockForAbstractClass(api::class);
        $get = $this->get_method('get');

        $mocked_response = "Now you're just mocking me";
        curl::mock_response(
            $mocked_response
        );

        /** @var response $resp */
        $resp = $get->invoke($api,
            'https://totara.com',
            [
                'param' => 'value'
            ]
        );

        $this->assertEquals($mocked_response, $resp->get_body());
    }

    public function test_put(): void {
        $api = $this->getMockForAbstractClass(api::class);
        $put = $this->get_method('put');

        $mocked_response = "Now you're just mocking me";
        curl::mock_response(
            $mocked_response
        );

        /** @var response $resp */
        $resp = $put->invoke($api,
            'https://totara.com',
            [
                'data' => 'value',
            ]
        );

        $this->assertEquals($mocked_response, $resp->get_body());
    }
}