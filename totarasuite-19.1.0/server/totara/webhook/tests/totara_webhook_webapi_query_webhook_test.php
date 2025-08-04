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

class totara_webhook_totara_webhook_webapi_query_webhook_test extends testcase {
    use webapi_phpunit_helper;

    protected $query_name = 'totara_webhook_totara_webhook';

    public function test_resolve_existing_webhook(): void {
        $user = $this->getDataGenerator()->create_user();
        $roleid = $this->getDataGenerator()->create_role();
        $context = context_system::instance();
        assign_capability('totara/webhook:viewtotara_webhooks', CAP_ALLOW, $roleid, $context);
        $this->getDataGenerator()->role_assign($roleid, $user->id, $context);
        $this->setUser($user);

        $generator = totara_webhook_generator::instance();
        $webhook = $generator->create_totara_webhook(
            [
                'name' => 'testing',
                'endpoint' => 'https://example.com/webhook',
                'events' => [
                    \core\event\badge_created::class,
                ],
                'status' => true,
            ]
        );

        $response = $this->resolve_graphql_query($this->query_name, ['input' => ['id' => $webhook->id]]);
        $this->assertTrue($response['success']);
        $this->assertSame($webhook->id, $response['item']->id);
        $this->assertSame('testing', $response['item']->name);
        $this->assertTrue($response['item']->status);
    }

    public function test_resolve_non_existing_webhook(): void {
        $user = $this->getDataGenerator()->create_user();
        $roleid = $this->getDataGenerator()->create_role();
        $context = context_system::instance();
        assign_capability('totara/webhook:viewtotara_webhooks', CAP_ALLOW, $roleid, $context);
        $this->getDataGenerator()->role_assign($roleid, $user->id, $context);
        $this->setUser($user);

        $generator = totara_webhook_generator::instance();
        $webhook = $generator->create_totara_webhook(
            [
                'name' => 'testing',
                'endpoint' => 'https://example.com/webhook',
                'events' => [
                    \core\event\badge_created::class,
                ],
                'status' => true,
            ]
        );
        $non_existing_webhook_id = $webhook->id;
        $webhook->delete();

        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessage('Invalid Webhook');
        $this->resolve_graphql_query($this->query_name, ['input' => ['id' => $non_existing_webhook_id]]);
    }

    public function test_resolve_invalid_input(): void {
        $user = $this->getDataGenerator()->create_user();
        $roleid = $this->getDataGenerator()->create_role();
        $context = context_system::instance();
        assign_capability('totara/webhook:viewtotara_webhooks', CAP_ALLOW, $roleid, $context);
        $this->getDataGenerator()->role_assign($roleid, $user->id, $context);
        $this->setUser($user);

        $this->expectException(invalid_parameter_exception::class);
        $response = $this->resolve_graphql_query($this->query_name, []);
    }
}
