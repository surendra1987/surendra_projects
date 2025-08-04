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
use totara_webapi\fixtures\mock_webhook_auth;
use totara_webapi\phpunit\webapi_phpunit_helper;
use totara_webhook\auth\webhook_hmac_auth;
use totara_webhook\model\totara_webhook;
use totara_webhook\testing\generator as totara_webhook_generator;
use totara_webhook\entity\totara_webhook as totara_webhook_entity;

defined('MOODLE_INTERNAL') || die();

class totara_webhook_totara_webhook_webapi_mutation_update_webhook_test extends testcase {
    use webapi_phpunit_helper;

    protected $mutation_name = 'totara_webhook_update_totara_webhook';

    private function get_user_with_permissions(): stdClass {
        $user = $this->getDataGenerator()->create_user();
        $roleid = $this->getDataGenerator()->create_role();
        $context = context_system::instance();
        assign_capability('totara/webhook:managetotara_webhooks', CAP_ALLOW, $roleid, $context);
        $this->getDataGenerator()->role_assign($roleid, $user->id, $context);
        return $user;
    }

    public function test_resolve_update_webhook_no_permissions(): void {
        $webhook_generator = totara_webhook_generator::instance();
        $input = [
            'name' => 'testing-alpha',
            'endpoint' => 'https://example.com/webhook-alpha',
            'events' => [
                \core\event\badge_created::class,
                \core\event\user_created::class,
            ],
        ];
        $webhook = $webhook_generator->create_totara_webhook($input);
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $this->expectException(moodle_exception::class);
        $this->resolve_graphql_mutation($this->mutation_name, [
            'input' => [
                'reference' => ['id' => $webhook->id],
                'name' => 'updated',
                'events' => [\core\event\user_updated::class],
            ],
        ]);
    }

    public function test_resolve_update_webhook(): void {
        $this->setUser(self::get_user_with_permissions());

        $webhook_generator = totara_webhook_generator::instance();
        $input = [
            'name' => 'testing-alpha',
            'endpoint' => 'https://example.com/webhook-alpha',
            'events' => [
                \core\event\badge_created::class,
                \core\event\user_created::class,
            ],
        ];
        $webhook = $webhook_generator->create_totara_webhook($input);

        $response = $this->resolve_graphql_mutation($this->mutation_name, [
            'input' => [
                'reference' => ['id' => $webhook->id],
                'name' => 'updated',
                'events' => [\core\event\user_updated::class],
            ],
        ]);

        /** @var totara_webhook $item */
        $item = $response['item'];
        $this->assertSame($webhook->id, $item->id);
        $this->assertSame('updated', $item->name);
        $this->assertSame(webhook_hmac_auth::class, $item->auth_class);
        $this->assertCount(1, $item->get_event_subscriptions());
    }

    public function test_resolve_update_webhook_sanitize_endpoint(): void {
        $this->setUser(self::get_user_with_permissions());
        $entity = new totara_webhook_entity();
        $entity->name = 'test';
        $entity->endpoint = '';
        $entity->auth_class = webhook_hmac_auth::class;
        $entity->save();

        $input = [
            'reference' => ['id' => $entity->id],
            'endpoint' => 'http://example.com/webhook-alpha',
        ];
        $result = $this->resolve_graphql_mutation($this->mutation_name, ['input' => $input]);
        /** @var totara_webhook $item */
        $item = $result['item'];
        $this->assertSame($entity->id, $item->id);
        $this->assertSame('example.com/webhook-alpha', $item->endpoint);
    }

    public function test_resolve_update_webhook_when_user_provides_only_http_schema_prefix(): void {
        $this->setUser(self::get_user_with_permissions());
        $entity = new totara_webhook_entity();
        $entity->name = 'test';
        $entity->endpoint = 'example.com/webhook';
        $entity->auth_class = webhook_hmac_auth::class;
        $entity->save();

        $input = [
            'reference' => ['id' => $entity->id],
            'endpoint' => 'http://',
        ];
        $result = $this->resolve_graphql_mutation($this->mutation_name, ['input' => $input]);
        /** @var totara_webhook $item */
        $item = $result['item'];
        $this->assertSame($entity->id, $item->id);
        // If the endpoint is invalid, we don't want the field to update
        $this->assertSame('example.com/webhook', $item->endpoint);
    }

    public function test_resolve_update_webhook_when_user_provides_double_http_schema_prefix(): void {
        $this->setUser(self::get_user_with_permissions());
        $entity = new totara_webhook_entity();
        $entity->name = 'test';
        $entity->endpoint = 'example.com/webhook';
        $entity->auth_class = webhook_hmac_auth::class;
        $entity->save();

        $input = [
            'reference' => ['id' => $entity->id],
            'endpoint' => 'http://https://',
        ];
        $result = $this->resolve_graphql_mutation($this->mutation_name, ['input' => $input]);
        /** @var totara_webhook $item */
        $item = $result['item'];
        $this->assertSame($entity->id, $item->id);
        // If the endpoint is invalid, we don't want the field to update
        $this->assertSame('example.com/webhook', $item->endpoint);
    }

    public function test_resolve_update_webhook_when_user_provides_capitalised_http_schema_prefix(): void {
        $this->setUser(self::get_user_with_permissions());
        $entity = new totara_webhook_entity();
        $entity->name = 'test';
        $entity->endpoint = 'example.com/webhook';
        $entity->auth_class = webhook_hmac_auth::class;
        $entity->save();

        $input = [
            'reference' => ['id' => $entity->id],
            'endpoint' => 'HTTP://HTTPS://',
        ];
        $result = $this->resolve_graphql_mutation($this->mutation_name, ['input' => $input]);
        /** @var totara_webhook $item */
        $item = $result['item'];
        $this->assertSame($entity->id, $item->id);
        // If the endpoint is invalid, we don't want the field to update
        $this->assertSame('example.com/webhook', $item->endpoint);
    }

    public function test_resolve_update_webhook_when_user_provides_whitespace_endpoint(): void {
        $this->setUser(self::get_user_with_permissions());
        $entity = new totara_webhook_entity();
        $entity->name = 'test';
        $entity->endpoint = 'example.com/webhook';
        $entity->auth_class = webhook_hmac_auth::class;
        $entity->save();

        $input = [
            'reference' => ['id' => $entity->id],
            'endpoint' => '       ',
        ];
        $result = $this->resolve_graphql_mutation($this->mutation_name, ['input' => $input]);
        /** @var totara_webhook $item */
        $item = $result['item'];
        $this->assertSame($entity->id, $item->id);
        // If the endpoint is invalid, we don't want the field to update
        $this->assertSame('example.com/webhook', $item->endpoint);
    }

    public function test_resolve_update_webhook_status(): void {
        $this->setUser(self::get_user_with_permissions());
        $entity = new totara_webhook_entity();
        $entity->name = 'test';
        $entity->endpoint = 'example.com/webhook';
        $entity->status = true;
        $entity->auth_class = webhook_hmac_auth::class;
        $entity->save();

        $input = [
            'reference' => ['id' => $entity->id],
            'status' => false,
        ];

        $result = $this->resolve_graphql_mutation($this->mutation_name, ['input' => $input]);
        $this->assertFalse($result['item']->status);
    }

    public function test_resolve_update_webhook_with_auth_class(): void {
        require_once __DIR__ . '/fixtures/mock_webhook_auth.php';

        $this->setUser(self::get_user_with_permissions());
        $entity = new totara_webhook_entity();
        $entity->name = 'test';
        $entity->endpoint = 'example.com/webhook';
        $entity->auth_class = webhook_hmac_auth::class;
        $entity->save();

        $input = [
            'reference' => ['id' => $entity->id],
            'auth_class' => mock_webhook_auth::class,
        ];
        $result = $this->resolve_graphql_mutation($this->mutation_name, ['input' => $input]);
        /** @var totara_webhook $item */
        $item = $result['item'];
        $this->assertSame(mock_webhook_auth::class, $item->auth_class);
        // If the endpoint is invalid, we don't want the field to update
        $this->assertSame(mock_webhook_auth::$generated_config, $item->auth_config);
    }

    public function test_resolve_update_webhook_with_no_inputs(): void {
        $this->setUser(self::get_user_with_permissions());

        $webhook_generator = totara_webhook_generator::instance();
        $input = [
            'name' => 'testing',
            'endpoint' => 'https://example.com/webhook-alpha',
            'events' => [
                \core\event\badge_created::class,
                \core\event\user_created::class,
            ],
        ];
        $webhook = $webhook_generator->create_totara_webhook($input);

        $response = $this->resolve_graphql_mutation($this->mutation_name, [
            'input' => [
                'reference' => ['id' => $webhook->id]
            ],
        ]);

        $this->assertEmpty($response);
    }

    public function test_resolve_update_webhook_with_empty_events(): void {
        $this->setUser(self::get_user_with_permissions());

        $webhook_generator = totara_webhook_generator::instance();
        $input = [
            'name' => 'testing',
            'endpoint' => 'https://example.com/webhook-alpha',
            'immediate' => false,
            'events' => [
                \core\event\badge_created::class,
                \core\event\user_created::class,
            ],
        ];
        $webhook = $webhook_generator->create_totara_webhook($input);

        $response = $this->resolve_graphql_mutation($this->mutation_name, [
            'input' => [
                'reference' => ['id' => $webhook->id],
                'name' => 'testinger',
                'endpoint' => 'https://webhooks/webhook',
                'immediate' => true
            ],
        ]);

        /** @var totara_webhook $item */
        $item = $response['item'];
        $this->assertSame($webhook->id, $item->id);
        $this->assertSame('testinger', $item->name);
        $this->assertSame('webhooks/webhook', $item->endpoint);
        $this->assertTrue($item->get_immediate());
        $this->assertCount(2, $item->get_event_subscriptions());
    }

    public function test_resolve_update_webhook_with_empty_immediate(): void {
        $this->setUser(self::get_user_with_permissions());

        $webhook_generator = totara_webhook_generator::instance();
        $input = [
            'name' => 'testing',
            'endpoint' => 'https://example.com/webhook-alpha',
            'immediate' => true,
            'events' => [
                \core\event\badge_created::class,
                \core\event\user_created::class,
            ],
        ];
        $webhook = $webhook_generator->create_totara_webhook($input);

        $response = $this->resolve_graphql_mutation($this->mutation_name, [
            'input' => [
                'reference' => ['id' => $webhook->id],
                'name' => 'testering',
                'endpoint' => 'https://webhooks/webhook',
                'events' => [
                    \core\event\user_deleted::class,
                ]
            ],
        ]);

        /** @var totara_webhook $item */
        $item = $response['item'];
        $this->assertSame($webhook->id, $item->id);
        $this->assertSame('testering', $item->name);
        $this->assertSame('webhooks/webhook', $item->endpoint);
        $this->assertTrue($item->get_immediate());
        $this->assertCount(1, $item->get_event_subscriptions());
    }

    public function test_resolve_update_webhook_with_empty_name(): void {
        $this->setUser(self::get_user_with_permissions());
        $entity = new totara_webhook_entity();
        $entity->name = 'test';
        $entity->endpoint = 'example.com/webhook';
        $entity->auth_class = webhook_hmac_auth::class;
        $entity->save();

        $input = [
            'reference' => ['id' => $entity->id],
            'name' => '',
        ];
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('name cannot be empty');
        $this->resolve_graphql_mutation($this->mutation_name, ['input' => $input]);
    }
}
