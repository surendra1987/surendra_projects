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
use totara_webhook\model\totara_webhook;
use totara_webhook\testing\generator as totara_webhook_generator;

defined('MOODLE_INTERNAL') || die();

class totara_webhook_totara_webhook_webapi_mutation_rotate_auth_config_test extends testcase {
    use webapi_phpunit_helper;

    protected $mutation_name = 'totara_webhook_rotate_auth_config_totara_webhook';

    private function get_user_with_permissions(): stdClass {
        $user = $this->getDataGenerator()->create_user();
        $roleid = $this->getDataGenerator()->create_role();
        $context = context_system::instance();
        assign_capability('totara/webhook:managetotara_webhooks', CAP_ALLOW, $roleid, $context);
        $this->getDataGenerator()->role_assign($roleid, $user->id, $context);
        return $user;
    }

    public function test_resolve_rotate_auth_no_permissions(): void {
        // create the webhook

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
        // create a user without the manage webhook permissions
        // attempt to call the rotate functionality
        // expect that an exception is thrown
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $this->expectException(moodle_exception::class);
        $this->resolve_graphql_mutation($this->mutation_name, [
            'reference' => ['id' => $webhook->id],
        ]);
    }

    public function test_resolve_rotate_auth(): void {
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
        $original_config = $webhook->auth_config;

        $response = $this->resolve_graphql_mutation($this->mutation_name, [
            'reference' => ['id' => $webhook->id],
        ]);

        /** @var totara_webhook $item */
        $item = $response['item'];
        $this->assertNotSame($original_config, $item->auth_config);
    }
}
