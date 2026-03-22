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
use totara_webhook\testing\generator as totara_webhook_generator;

defined('MOODLE_INTERNAL') || die();
class totara_webhook_totara_webhook_formatter_event_subscription_test extends testcase {
    public function test_totara_webhook_formatter(): void {
        $generator = totara_webhook_generator::instance();
        $webhook = $generator->create_totara_webhook([
           'name' => 'test',
           'endpoint' => 'https://example.com/webhook',
            'events' => [
                \core\event\user_profile_viewed::class,
            ]
        ]);
        /** @var \totara_webhook\model\totara_webhook_event_subscription $event_sub */
        $event_sub = $webhook->get_event_subscriptions()->first();
        // the fields for the other parts
        $context = context_system::instance();
        $formatter = new \totara_webhook\formatter\totara_webhook_event_subscription($event_sub, $context);
        $this->assertSame(\core\event\user_profile_viewed::class, $formatter->format('event', \core\format::FORMAT_RAW));
        $this->assertSame($webhook->get_id(), $formatter->format('webhook_id'));
    }
}
