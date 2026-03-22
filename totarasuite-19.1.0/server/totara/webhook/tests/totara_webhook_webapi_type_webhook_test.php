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

class totara_webhook_totara_webhook_webapi_type_webhook_test extends testcase {
    use webapi_phpunit_helper;

    protected $query_name = 'totara_webhook_totara_webhook';

    public function test_resolve_existing_webhook(): void {
        $webhook_generator = totara_webhook_generator::instance();
        $input = [
            'name' => 'testing-alpha',
            'endpoint' => 'https://example.com/webhook-alpha',
            'events' => [
                \core\event\badge_created::class,
                \core\event\user_created::class,
            ],
        ];
        $user = $this->getDataGenerator()->create_user();
        $context = $this->create_webapi_context('testing');
        $context->set_relevant_context(context_user::instance($user->id));
        $webhook = $webhook_generator->create_totara_webhook($input);

        $id = \totara_webhook\webapi\resolver\type\totara_webhook::resolve('id', $webhook, [], $context);
        $this->assertSame($webhook->id, $id);
        $name = \totara_webhook\webapi\resolver\type\totara_webhook::resolve('name', $webhook, [], $context);
        $this->assertSame($webhook->name, $name);
        $endpoint = \totara_webhook\webapi\resolver\type\totara_webhook::resolve('endpoint', $webhook, [], $context);
        $this->assertSame($webhook->endpoint, $endpoint);
        $events = \totara_webhook\webapi\resolver\type\totara_webhook::resolve('events', $webhook, [], $context);
        $this->assertCount(2, $events);
        $status = \totara_webhook\webapi\resolver\type\totara_webhook::resolve('status', $webhook, [], $context);
        $this->assertTrue($status);
    }

    public function test_type_checking(): void {
        $webhook_generator = totara_webhook_generator::instance();
        $input = [
            'name' => 'testing-alpha',
            'endpoint' => 'https://example.com/webhook-alpha',
            'events' => [
                \core\event\badge_created::class,
                \core\event\user_created::class,
            ],
        ];
        $user = $this->getDataGenerator()->create_user();
        // $context = \core\webapi\execution_context::create('dev');
        // $context->set_relevant_context(context_system::instance());
        $context = $this->create_webapi_context('testing');
        $context->set_relevant_context(context_user::instance($user->id));
        $webhook = new stdClass();

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Expected totara_webhook model');
        \totara_webhook\webapi\resolver\type\totara_webhook::resolve('id', $webhook, [], $context);
    }
}
