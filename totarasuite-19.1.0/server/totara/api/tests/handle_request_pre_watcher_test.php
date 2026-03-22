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
use totara_api\event\api_client_request_blocked;
use totara_api\exception\api_access_client_exception;
use totara_api\exception\api_access_exception;
use totara_api\exception\api_blocked_exception;
use totara_api\watcher\handle_request_pre_watcher;
use totara_oauth2\entity\client_provider;
use totara_webapi\graphql;
use totara_webapi\hook\handle_request_pre_hook;
use totara_webapi\request;
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

    /**
     * @SuppressWarnings("php:S2043") ignore "Do not access $_SERVER directly"
     * @SuppressWarnings("php:S1313") ignore "Make sure using hardcoded IP address is safe"
     */
    public function test_watcher_throws_api_blocked_exception() {
        /** @var \totara_api\testing\generator $api_generator */
        $api_generator = $this->getDataGenerator()->get_plugin_generator('totara_api');
        /** @var \totara_oauth2\testing\generator $oauth2_generator */
        $oauth2_generator = $this->getDataGenerator()->get_plugin_generator('totara_oauth2');

        $api_user = $this->getDataGenerator()->create_user();
        $client_name = 'test_client';

        // Generate an API client that specifically blocks all IPs
        $client = $api_generator->create_api_client_instance(['name' => $client_name, 'username' => $api_user->username]);
        $client_provider_id = client_provider::repository()->where('name', $client_name)->one()->client_id;

        $client_settings = $client->client_settings;
        $client_settings->allowed_ip_list = '0.0.0.0';
        $client_settings->save();

        $token = $oauth2_generator->create_access_token($client_provider_id);

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token->__toString();
        $_SERVER['REMOTE_ADDR'] = '1.1.1.1';

        set_config('response_debug', DebugFlag::INCLUDE_TRACE, 'totara_api');

        $event_sink = static::redirectEvents();

        // When we process the request without authentication
        $mock_hook = $this->mock_hook();
        handle_request_pre_watcher::watch($mock_hook);

        // Check that an API client request blocked event is triggered
        $events = $event_sink->get_events();
        $this->assertEquals(1, count($events));
        $event = $events[0];
        $this->assertInstanceOf(api_client_request_blocked::class, $event);
        $this->assertEquals("API client request blocked", $event->get_name());
        $this->assertEquals("A request to external API client $client->id was blocked.", $event->get_description());

        // Then we get a client aware api access exception with a generic message
        $exception = $mock_hook->get_exception();
        $this->assertInstanceOf(api_blocked_exception::class, $exception);
        $this->assertEquals("Request from address 1.1.1.1 to external API client $client->id was blocked", $exception->getMessage());
    }
}
