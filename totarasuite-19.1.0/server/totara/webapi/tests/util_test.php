<?php
/*
 * This file is part of Totara Learn
 *
 * Copyright (C) 2019 onwards Totara Learning Solutions LTD
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
 * @author Fabian Derschatta <fabian.derschatta@totaralearning.com>
 * @package totara_webapi
 */

use core_phpunit\testcase;
use totara_api\exception\api_access_client_exception;
use totara_webapi\client_aware_exception;
use totara_webapi\local\util;

defined('MOODLE_INTERNAL') || die();

class totara_webapi_util_test extends testcase {

    public function test_get_files_from_dir() {
        // Non-existant folder
        $files = util::get_files_from_dir(__DIR__.'/fixtures/idontexist', 'graphqls');
        $this->assertIsArray($files);
        $this->assertEmpty($files);

        // Folder without any graphqls files
        $files = util::get_files_from_dir(__DIR__.'/', 'graphqls');
        $this->assertIsArray($files);
        $this->assertEmpty($files);

        // Test folder with some test files in it
        $files = util::get_files_from_dir(__DIR__.'/fixtures/webapi', 'graphqls');
        $this->assertIsArray($files);
        $this->assertCount(9, $files);
        $this->assertEqualsCanonicalizing(
            [
                __DIR__.'/fixtures/webapi/test_deprecation.graphqls',
                __DIR__.'/fixtures/webapi/test_schema_1.graphqls',
                __DIR__.'/fixtures/webapi/test_schema_2.graphqls',
                __DIR__.'/fixtures/webapi/test_schema_3.graphqls',
                __DIR__.'/fixtures/webapi/job_assignment.graphqls',
                __DIR__.'/fixtures/webapi/test_union.graphqls',
                __DIR__.'/fixtures/webapi/schema_files_test_1.graphqls',
                __DIR__.'/fixtures/webapi/schema_files_test_2.graphqls',
                __DIR__.'/fixtures/webapi/custom_field.graphqls',
            ],
            $files
        );
    }

    public function test_is_nosession_request() {
        $request = 'foo';
        $this->assertFalse(util::is_nosession_request($request));

        $request = [];
        $this->assertFalse(util::is_nosession_request($request));

        $request = [
            'operationName' => 'my_test_operation',
            'variables' => []
        ];
        $this->assertFalse(util::is_nosession_request($request));

        $request = [
            'operationName' => 'my_test_operation_nosession',
            'variables' => []
        ];
        $this->assertTrue(util::is_nosession_request($request));

        $request = [
            'variables' => []
        ];
        $this->assertFalse(util::is_nosession_request($request));

        $request = [
            [
                'operationName' => 'my_test_operation1',
                'variables' => []
            ],
            [
                'operationName' => 'my_test_operation2',
                'variables' => []
            ],
        ];
        $this->assertFalse(util::is_nosession_request($request));

        $request = [
            [
                'operationName' => 'my_test_operation1_nosession',
                'variables' => []
            ],
            [
                'operationName' => 'my_test_operation2',
                'variables' => []
            ],
        ];
        $this->assertFalse(util::is_nosession_request($request));

        $request = [
            [
                'operationName' => 'my_test_operation1_nosession',
                'variables' => []
            ],
            [
                'operationName' => 'my_test_operation2_nosession',
                'variables' => []
            ],
        ];
        $this->assertTrue(util::is_nosession_request($request));
    }

    public function test_graphql_error_formatter_with_client_aware_exception() {
        // Given we have a registered client-aware exception
        $error = new require_login_exception('login error');

        // When we format it
        $formatted_error = util::graphql_error_formatter($error);

        // Then we expect to get specific information for the client
        $this->assertIsArray($formatted_error);
        $this->assertEquals('require_login', $formatted_error['extensions']['category']);
    }

    public function test_graphql_error_formatter_with_client_aware_previous_exception() {
        // Given we have a previous client aware exception nested within a non-client aware exception

        // Manufacture an exception with a previous exception with client aware
        $previous_exception = new api_access_client_exception();
        $top_level_exception = new Exception('Not client aware', 0, $previous_exception);

        // When we format it
        $formatted_error = util::graphql_error_formatter($top_level_exception);

        // Then we expect to get specific information related to the nested previous exception
        $this->assertIsArray($formatted_error);
        $this->assertEquals('api_access', $formatted_error['extensions']['category']);
    }

    public function test_graphql_error_formatter_with_non_client_aware_previous_exception() {
        // Given we have a previous non-client aware exception nested within a non-client aware exception

        // Manufacture an exception with a previous exception with client aware
        $previous_exception = new Exception('Not client aware');
        $top_level_exception = new Exception('Also not client aware', 0, $previous_exception);

        // When we format it
        $formatted_error = util::graphql_error_formatter($top_level_exception);

        // Then we expect to get a generic 'Internal server error' for the client
        $this->assertIsArray($formatted_error);
        $this->assertEquals(client_aware_exception::CATEGORY_INTERNAL, $formatted_error['extensions']['category']);
    }

    public function test_graphql_error_formatter_with_non_client_aware_exception() {
        // Given we have a non-client aware exception
        $exception = new Exception('Not client aware');

        // When we format it
        $formatted_error = util::graphql_error_formatter($exception);

        // Then we expect to get a generic 'Internal server error' for the client
        $this->assertIsArray($formatted_error);
        $this->assertEquals(client_aware_exception::CATEGORY_INTERNAL, $formatted_error['extensions']['category']);
    }
}
