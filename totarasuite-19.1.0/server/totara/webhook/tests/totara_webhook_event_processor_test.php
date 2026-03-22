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

use core\event\badge_created;
use core\event\user_profile_viewed;
use core_phpunit\testcase;
use totara_core\advanced_feature;
use totara_webapi\fixtures\mock_handler;
use totara_webhook\event_processor;
use totara_webhook\testing\generator as totara_webhook_generator;

defined('MOODLE_INTERNAL') || die();

require_once __DIR__ . '/fixtures/mock_handler.php';

class totara_webhook_totara_webhook_event_processor_test extends testcase {
    public function test_process_event(): void {
        // create a custom webhook
        $webhook_generator = totara_webhook_generator::instance();
        $webhook = $webhook_generator->create_totara_webhook([
            'name' => 'test',
            'endpoint' => 'https://example.com/webhook',
            'events' => [
                user_profile_viewed::class,
                badge_created::class,
            ]
        ]);
        $user_viewed = $this->getDataGenerator()->create_user();
        $user_actioning = $this->getDataGenerator()->create_user();
        $this->setUser($user_actioning);
        // set the webhook handler to use the fixuture
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
    }

    public function test_process_event_without_id(): void {
        global $CFG;
        // require the badges lib for the badge event constants
        require_once $CFG->dirroot . '/lib/badgeslib.php';
        $admin = get_admin();
        // create a custom webhook
        $webhook_generator = totara_webhook_generator::instance();
        $webhook_generator->create_totara_webhook([
            'name' => 'test',
            'endpoint' => 'https://example.com/webhook',
            'events' => [
                user_profile_viewed::class,
                badge_created::class,
            ]
        ]);
        $user_actioning = $this->getDataGenerator()->create_user();
        $this->setUser($user_actioning);
        // set the webhook handler to use the fixuture
        event_processor::set_webhook_handler(mock_handler::class);
        $context = context_system::instance();

        // create a badge viewed event
        $badges_generator = $this->getDataGenerator()->get_plugin_generator('core_badges');
        $badge_id = $badges_generator->create_badge($admin->id, ['status' => BADGE_STATUS_ACTIVE]);
        $other = ['badgeid' => $badge_id];
        $eventparams = ['context' => $context, 'other' => $other];
        $event = \core\event\badge_viewed::create($eventparams);

        event_processor::process_event($event);
        /** @var mock_handler $event_handler_mock */
        $event_handler_mock = event_processor::get_webhook_handler();
        // check that the fixture dispatch functionality was called
        $this->assertTrue($event_handler_mock->dispatch_called);
        $body = $event_handler_mock->totara_webhook_payload->get_body();
        $this->assertSame($badge_id, intval($body['id']));
    }

    public function test_process_event_webhooks_disabled(): void {
        advanced_feature::disable('totara_webhook');
        // create a custom webhook
        $webhook_generator = totara_webhook_generator::instance();
        $webhook_generator->create_totara_webhook([
            'name' => 'test',
            'endpoint' => 'https://example.com/webhook',
            'events' => [
                user_profile_viewed::class,
                badge_created::class,
            ]
        ]);
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
        $this->assertFalse($event_handler_mock->dispatch_called);
    }

    protected function tearDown(): void {
        event_processor::set_webhook_handler(null);
        parent::tearDown();
    }
}
