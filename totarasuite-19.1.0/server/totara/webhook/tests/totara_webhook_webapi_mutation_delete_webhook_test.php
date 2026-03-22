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

class totara_webhook_totara_webhook_webapi_mutation_delete_webhook_test extends testcase {
    use webapi_phpunit_helper;

    protected $mutation_name = 'totara_webhook_delete_totara_webhook';

    public function test_resolve_delete_webhook_no_permissions(): void {
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
        $this->resolve_graphql_mutation($this->mutation_name, ['reference' => ['id' => $webhook->id]]);
    }

    public function test_resolve_delete_webhook(): void {
        $user = $this->getDataGenerator()->create_user();
        $roleid = $this->getDataGenerator()->create_role();
        $context = context_system::instance();
        assign_capability('totara/webhook:managetotara_webhooks', CAP_ALLOW, $roleid, $context);
        $this->getDataGenerator()->role_assign($roleid, $user->id, $context);
        $this->setUser($user);

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

        $response = $this->resolve_graphql_mutation($this->mutation_name, ['reference' => ['id' => $webhook->id]]);
        $this->assertArrayHasKey('deleted_id', $response);
        $this->assertSame($webhook->id, $response['deleted_id']);
    }
}
