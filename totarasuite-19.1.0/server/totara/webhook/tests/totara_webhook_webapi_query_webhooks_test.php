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
use totara_webapi\phpunit\webapi_phpunit_helper;
use totara_webhook\testing\generator as totara_webhook_generator;

defined('MOODLE_INTERNAL') || die();

class totara_webhook_totara_webhook_webapi_query_webhooks_test extends testcase {
    use webapi_phpunit_helper;

    protected $query_name = 'totara_webhook_totara_webhooks';

    public function test_resolve_existing_webhooks(): void {
        $user = $this->getDataGenerator()->create_user();
        $roleid = $this->getDataGenerator()->create_role();
        $context = context_system::instance();
        assign_capability('totara/webhook:viewtotara_webhooks', CAP_ALLOW, $roleid, $context);
        $this->getDataGenerator()->role_assign($roleid, $user->id, $context);
        $this->setUser($user);

        $generator = totara_webhook_generator::instance();
        $webhook1 = $generator->create_totara_webhook(
            [
                'name' => 'testing-alpha',
                'endpoint' => 'https://example.com/webhook-alpha',
                'events' => [
                    \core\event\badge_created::class,
                ],
            ]
        );
        $webhook2 = $generator->create_totara_webhook(
            [
                'name' => 'testing-beta',
                'endpoint' => 'https://example.com/webhook-beta',
                'events' => [
                    \core\event\user_created::class,
                ],
            ]
        );
        $webhook3 = $generator->create_totara_webhook(
            [
                'name' => 'testing-beta',
                'endpoint' => 'https://example.com/webhook-gamma',
                'events' => [
                    \core\event\user_created::class,
                ],
            ]
        );
        $query_input = [
            'sort' => [
                [
                    'column' => 'endpoint',
                    'direction' => 'desc',
                ]
            ],
            'pagination' => [
                'limit' => 1
            ]
        ];
        $response = $this->resolve_graphql_query($this->query_name, ['input' => $query_input]);
        $this->assertArrayHasKey('items', $response);
        $this->assertArrayHasKey('total', $response);
        $this->assertArrayHasKey('next_cursor', $response);
        $this->assertCount(1, $response['items']);
        $this->assertSame(3, $response['total']);
        $items = $response['items'];
        $first_item = reset($items);
        $this->assertSame($webhook3->name, $first_item->name);
        $this->assertSame($webhook3->id, $first_item->id);
        $this->assertSame($webhook3->endpoint, $first_item->endpoint);
        $this->assertTrue($webhook3->status);
    }

    public function test_resolve_webhooks_default_order(): void {
        $user = $this->getDataGenerator()->create_user();
        $roleid = $this->getDataGenerator()->create_role();
        $context = context_system::instance();
        assign_capability('totara/webhook:viewtotara_webhooks', CAP_ALLOW, $roleid, $context);
        $this->getDataGenerator()->role_assign($roleid, $user->id, $context);
        $this->setUser($user);

        $generator = totara_webhook_generator::instance();
        $webhook1 = $generator->create_totara_webhook(
            [
                'name' => 'testing-alpha',
                'endpoint' => 'https://example.com/webhook-alpha',
                'events' => [
                    \core\event\badge_created::class,
                ],
            ]
        );
        $webhook2 = $generator->create_totara_webhook(
            [
                'name' => 'testing-beta',
                'endpoint' => 'https://example.com/webhook-beta',
                'events' => [
                    \core\event\user_created::class,
                ],
            ]
        );
        $webhook3 = $generator->create_totara_webhook(
            [
                'name' => 'testing-beta',
                'endpoint' => 'https://example.com/webhook-gamma',
                'events' => [
                    \core\event\user_created::class,
                ],
            ]
        );
        $query_input = [
            'pagination' => [
                'limit' => 1
            ]
        ];
        $response = $this->resolve_graphql_query($this->query_name, ['input' => $query_input]);
        $this->assertArrayHasKey('items', $response);
        $this->assertArrayHasKey('total', $response);
        $this->assertArrayHasKey('next_cursor', $response);
        $this->assertCount(1, $response['items']);
        $this->assertSame(3, $response['total']);
        $items = $response['items'];
        $first_item = reset($items);
        $this->assertSame($webhook1->name, $first_item->name);
        $this->assertSame($webhook1->id, $first_item->id);
        $this->assertSame($webhook1->endpoint, $first_item->endpoint);
    }

    public function test_resolve_webhooks_pagination_cursor(): void {
        $user = $this->getDataGenerator()->create_user();
        $roleid = $this->getDataGenerator()->create_role();
        $context = context_system::instance();
        assign_capability('totara/webhook:viewtotara_webhooks', CAP_ALLOW, $roleid, $context);
        $this->getDataGenerator()->role_assign($roleid, $user->id, $context);
        $this->setUser($user);

        $generator = totara_webhook_generator::instance();
        $webhook1 = $generator->create_totara_webhook(
            [
                'name' => 'testing-alpha',
                'endpoint' => 'https://example.com/webhook-alpha',
                'events' => [
                    \core\event\badge_created::class,
                ],
            ]
        );
        $webhook2 = $generator->create_totara_webhook(
            [
                'name' => 'testing-beta',
                'endpoint' => 'https://example.com/webhook-beta',
                'events' => [
                    \core\event\user_created::class,
                ],
            ]
        );
        $webhook3 = $generator->create_totara_webhook(
            [
                'name' => 'testing-beta',
                'endpoint' => 'https://example.com/webhook-gamma',
                'events' => [
                    \core\event\user_created::class,
                ],
            ]
        );
        $query_input = [
            'pagination' => [
                'limit' => 1
            ]
        ];
        $response = $this->resolve_graphql_query($this->query_name, ['input' => $query_input]);
        $cursor = $response['next_cursor'];
        $items = $response['items'];
        $first_item = reset($items);
        $this->assertSame($webhook1->id, $first_item->id);
        $query_input = [
            'pagination' => [
                'cursor' => $cursor,
                'limit' => 1,
            ],
        ];
        $response = $this->resolve_graphql_query($this->query_name, ['input' => $query_input]);
        $next_item = reset($response['items']);
        $this->assertSame($webhook2->id, $next_item->id);
    }
}
