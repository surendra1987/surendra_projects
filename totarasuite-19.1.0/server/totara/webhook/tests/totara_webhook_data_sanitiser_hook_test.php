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

use core\event\user_profile_viewed;
use core_phpunit\testcase;
use totara_webapi\fixtures\mock_handler;
use totara_webhook\data_processor\data_sanitiser;
use totara_webhook\event_processor;
use totara_webhook\testing\generator as totara_webhook_generator;

defined('MOODLE_INTERNAL') || die();

require_once __DIR__ . '/fixtures/mock_handler.php';

class totara_webhook_totara_webhook_data_sanitiser_hook_test extends testcase {

    /**
     * @return void
     */
    public function test_hook_triggered_by_event_processor(): void {
        // flush the hook sink
        $hook_sink = $this->redirectHooks();
        $hook_sink->clear();
        $this->assertSame(0, $hook_sink->count());

        // set up a webhook
        $generator = totara_webhook_generator::instance();
        $generator->create_totara_webhook([
            'name' => 'test',
            'endpoint' => 'https://example.com/webhook',
            'events' => [
                user_profile_viewed::class,
            ]
        ]);

        // trigger the event processor
        $user_viewed = $this->getDataGenerator()->create_user();
        $user_actioning = $this->getDataGenerator()->create_user();
        $this->setUser($user_actioning);
        // set the webhook handler to use the fixture
        event_processor::set_webhook_handler(mock_handler::class);
        $context = context_system::instance();
        $event = user_profile_viewed::create([
            'objectid' => $user_viewed->id,
            'contextid' => $context->id,
            'relateduserid' => $user_actioning->id,
        ]);
        event_processor::process_event($event);
        /** @var mock_handler $event_handler_mock */
        $event_handler_mock = event_processor::get_webhook_handler();
        // check that the fixture dispatch functionality was called
        $this->assertTrue($event_handler_mock->dispatch_called);
        $webhook_payload_body = $event_handler_mock->totara_webhook_payload->get_body();
        $this->assertSame(data_sanitiser::REDACTED_MASK, $webhook_payload_body['password']);
        $this->assertSame($user_viewed->email, $webhook_payload_body['email']);

        // check the hook sink has the data sanitiser hook being called
        $hooks = $hook_sink->get_hooks();
        $data_sanitiser_hook_called = false;
        foreach ($hooks as $hook) {
            if ($hook instanceof \totara_webhook\hook\data_sanitiser_hook) {
                $data_sanitiser_hook_called = true;
            }
        }
        $this->assertTrue($data_sanitiser_hook_called);
    }
}
