<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package totara_webhook
 */

use core_phpunit\testcase;
use totara_webapi\client_aware_exception;
use totara_webapi\fixtures\mock_webhook_auth;
use totara_webapi\phpunit\webapi_phpunit_helper;
use totara_webhook\auth\webhook_hmac_auth;
use totara_webhook\model\totara_webhook;

defined('MOODLE_INTERNAL') || die();

class totara_webhook_totara_webhook_webapi_mutation_create_webhook_test extends testcase {
    use webapi_phpunit_helper;

    protected $mutation_name = 'totara_webhook_create_totara_webhook';

    private function get_user_with_permissions(): stdClass {
        $user = $this->getDataGenerator()->create_user();
        $roleid = $this->getDataGenerator()->create_role();
        $context = context_system::instance();
        assign_capability('totara/webhook:managetotara_webhooks', CAP_ALLOW, $roleid, $context);
        $this->getDataGenerator()->role_assign($roleid, $user->id, $context);
        return $user;
    }

    public function test_resolve_create_webhook_no_permissions(): void {
        $user = $this->getDataGenerator()->create_user();
        $roleid = $this->getDataGenerator()->create_role();
        $context = context_system::instance();
        $this->getDataGenerator()->role_assign($roleid, $user->id, $context);
        $this->setUser($user);

        $input = [
            'name' => 'testing-alpha',
            'endpoint' => 'https://example.com/webhook-alpha',
            'events' => [
                \core\event\badge_created::class,
                \core\event\user_created::class,
            ],
        ];

        $this->expectException(required_capability_exception::class);
        $this->resolve_graphql_mutation($this->mutation_name, ['input' => $input]);
    }

    public function test_resolve_create_webhook(): void {
        $this->setUser($this->get_user_with_permissions());

        $input = [
            'name' => 'testing-alpha',
            'endpoint' => 'example.com/webhook-alpha',
            'events' => [
                \core\event\badge_created::class,
                \core\event\user_created::class,
            ],
        ];

        $response = $this->resolve_graphql_mutation($this->mutation_name, ['input' => $input]);
        $this->assertArrayHasKey('item', $response);
        /** @var totara_webhook $created */
        $created = $response['item'];
        $this->assertSame($input['name'], $created->name);
        $this->assertSame($input['endpoint'], $created->endpoint);
        $this->assertSame(webhook_hmac_auth::class, $created->auth_class);
        $this->assertCount(2, $created->get_event_subscriptions());
    }

    public function test_resolve_create_webhook_with_empty_endpoint(): void {
        $this->setUser($this->get_user_with_permissions());
        $input = [
            'name' => 'testing-alpha',
            'endpoint' => null,
            'events' => [
                \core\event\badge_created::class,
                \core\event\user_created::class,
            ],
        ];
        $this->expectException(client_aware_exception::class);
        $this->expectExceptionMessage('You must provide a webhook endpoint URL');
        $this->resolve_graphql_mutation($this->mutation_name, ['input' => $input]);
    }

    public function test_resolve_create_webhook_removes_url_schema_from_endpoint(): void {
        $this->setUser($this->get_user_with_permissions());
        $input = [
            'name' => 'testing-alpha',
            'endpoint' => 'http://example.com/webhook-alpha',
            'events' => [
                \core\event\badge_created::class,
                \core\event\user_created::class,
            ],
        ];
        $result = $this->resolve_graphql_mutation($this->mutation_name, ['input' => $input]);
        $this->assertEquals('example.com/webhook-alpha', $result['item']->endpoint);
    }

    public function test_resolve_create_webhook_when_user_provides_only_http_schema_prefix(): void {
        $this->setUser($this->get_user_with_permissions());
        $input = [
            'name' => 'testing-alpha',
            'endpoint' => 'http://',
            'events' => [
                \core\event\badge_created::class,
                \core\event\user_created::class,
            ],
        ];
        $this->expectException(client_aware_exception::class);
        $this->expectExceptionMessage('You must provide a webhook endpoint URL');
        $this->resolve_graphql_mutation($this->mutation_name, ['input' => $input]);
    }

    public function test_resolve_create_webhook_when_user_provides_double_http_schema_prefix(): void {
        $this->setUser($this->get_user_with_permissions());
        $input = [
            'name' => 'testing-alpha',
            'endpoint' => 'http://https://',
            'events' => [
                \core\event\badge_created::class,
                \core\event\user_created::class,
            ],
        ];
        $this->expectException(client_aware_exception::class);
        $this->expectExceptionMessage('You must provide a webhook endpoint URL');
        $this->resolve_graphql_mutation($this->mutation_name, ['input' => $input]);
    }

    public function test_resolve_create_webhook_when_user_provides_capitalised_http_schema_prefix(): void {
        $this->setUser($this->get_user_with_permissions());
        $input = [
            'name' => 'testing-alpha',
            'endpoint' => 'HTTP://',
            'events' => [
                \core\event\badge_created::class,
                \core\event\user_created::class,
            ],
        ];
        $this->expectException(client_aware_exception::class);
        $this->expectExceptionMessage('You must provide a webhook endpoint URL');
        $this->resolve_graphql_mutation($this->mutation_name, ['input' => $input]);
    }

    public function test_resolve_create_webhook_when_user_provides_whitespace_endpoint(): void {
        $this->setUser($this->get_user_with_permissions());
        $input = [
            'name' => 'testing-alpha',
            'endpoint' => '      ',
            'events' => [
                \core\event\badge_created::class,
                \core\event\user_created::class,
            ],
        ];
        $this->expectException(client_aware_exception::class);
        $this->expectExceptionMessage('You must provide a webhook endpoint URL');
        $this->resolve_graphql_mutation($this->mutation_name, ['input' => $input]);
    }

    public function test_resolve_create_webhook_succesfully_when_user_provides_no_status(): void {
        $this->setUser($this->get_user_with_permissions());
        $input = [
            'name' => 'testing-alpha',
            'endpoint' => 'http://google.com',
        ];
        $result = $this->resolve_graphql_mutation($this->mutation_name, ['input' => $input]);
        $this->assertTrue($result['item']->status);
    }

    public function test_resolve_create_webhook_with_auth_class(): void {
        require_once __DIR__ . '/fixtures/mock_webhook_auth.php';
        $this->setUser($this->get_user_with_permissions());
        $input = [
            'name' => 'testing-alpha',
            'endpoint' => 'https://example.com/webhook',
            'events' => [
                \core\event\badge_created::class,
                \core\event\user_created::class,
            ],
            'auth_class' => mock_webhook_auth::class,
        ];
        $response = $this->resolve_graphql_mutation($this->mutation_name, ['input' => $input]);
        /** @var totara_webhook $webhook */
        $webhook = $response['item'];
        $this->assertSame(mock_webhook_auth::class, $webhook->auth_class);
        $this->assertSame('mock_generated', $webhook->auth_config);
    }

    public function test_resolve_create_webhook_with_empty_name(): void {
        require_once __DIR__ . '/fixtures/mock_webhook_auth.php';
        $this->setUser($this->get_user_with_permissions());
        $input = [
            'name' => '',
            'endpoint' => 'https://example.com/webhook',
            'events' => [
                \core\event\badge_created::class,
                \core\event\user_created::class,
            ],
            'auth_class' => mock_webhook_auth::class,
        ];
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('name cannot be empty');
        $this->resolve_graphql_mutation($this->mutation_name, ['input' => $input]);
    }
}
