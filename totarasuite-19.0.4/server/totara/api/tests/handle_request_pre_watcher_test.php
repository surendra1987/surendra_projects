<?php
/**
 *  This file is part of Totara Talent Experience Platform
 *
 *  Copyright (C) 2024 onwards Totara Learning Solutions LTD
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  @author Aaron Machin <aaron.machin@totara.com>
 *  @package totara_api
 */

use core\webapi\execution_context;
use core_phpunit\testcase;
use GraphQL\Error\DebugFlag;
use totara_api\exception\api_access_client_exception;
use totara_api\exception\api_access_exception;
use totara_api\watcher\handle_request_pre_watcher;
use totara_webapi\graphql;
use totara_webapi\request;
use totara_webapi\hook\handle_request_pre_hook;
use totara_webapi\server;

class totara_api_handle_request_pre_watcher_test extends testcase {

    public function mock_hook() {
        $execution_context = execution_context::create(graphql::TYPE_EXTERNAL);

        return new handle_request_pre_hook(
            $this->createMock(request::class),
            $execution_context,
            new server($execution_context),
        );
    }

    public function test_watcher_throw_api_access_exception_with_debug_none() {
        // Given we've got global debug set to none
        set_config('response_debug', DebugFlag::NONE, 'totara_api');

        // When we process the request without authentication
        $mock_hook = $this->mock_hook();
        handle_request_pre_watcher::watch($mock_hook);

        // Then we get a client aware api access exception with a generic message
        $exception = $mock_hook->get_exception();
        $this->assertEquals('Authentication error', $exception->getMessage());
        $this->assertInstanceOf(api_access_client_exception::class, $exception);
    }

    public function test_watcher_throw_api_access_exception_with_debug_normal() {
        // Given we set global debug to normal
        set_config('response_debug', DebugFlag::INCLUDE_DEBUG_MESSAGE, 'totara_api');

        // When we process the request without authentication
        $mock_hook = $this->mock_hook();
        handle_request_pre_watcher::watch($mock_hook);

        // Then we get the specific api_access_exception
        $exception = $mock_hook->get_exception();
        $this->assertInstanceOf(api_access_exception::class, $exception);
    }

    public function test_watcher_throw_api_access_exception_with_debug_developer() {
        // Given we set global debug to developer
        set_config('response_debug', DebugFlag::INCLUDE_TRACE, 'totara_api');

        // When we process the request without authentication
        $mock_hook = $this->mock_hook();
        handle_request_pre_watcher::watch($mock_hook);

        // Then we get the specific api_access_exception
        $exception = $mock_hook->get_exception();
        $this->assertInstanceOf(api_access_exception::class, $exception);
    }
}