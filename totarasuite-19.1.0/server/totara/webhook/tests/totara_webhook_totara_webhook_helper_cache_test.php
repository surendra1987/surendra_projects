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

class totara_webhook_totara_webhook_totara_webhook_helper_cache_test extends testcase {
    public function test_get_webhooks_for_event(): void {
        $webhook_generator = totara_webhook_generator::instance();
        $webhook = $webhook_generator->create_totara_webhook(['name' => 'testing', 'endpoint' => 'example.com']);
        $webhook->update($webhook->name, $webhook->endpoint, true, false, [
            \core\event\user_profile_viewed::class,
            \core\event\badge_created::class,
        ]);

        $found = \totara_webhook\helper\cache::get_webhooks_for_event(\core\event\user_created::class);
        $this->assertEmpty($found);

        $found = \totara_webhook\helper\cache::get_webhooks_for_event("\\".\core\event\badge_created::class);
        $this->assertNotEmpty($found);
        $subscribed_webhook = $found->first();
        $this->assertSame($subscribed_webhook->id, $webhook->id);
    }

    public function test_cache_purged_prior_to_retrieve(): void {
        $webhook_generator = totara_webhook_generator::instance();
        $webhook = $webhook_generator->create_totara_webhook(['name' => 'testing', 'endpoint' => 'example.com']);
        $webhook->update($webhook->name, $webhook->endpoint, true, false, [
            \core\event\user_profile_viewed::class,
            \core\event\badge_created::class,
        ]);

        $event_subscription_cache = \cache::make('totara_webhook', 'totara_webhook_event_subscriptions');
        $event_subscription_cache->purge();

        $found = \totara_webhook\helper\cache::get_webhooks_for_event("\\".\core\event\badge_created::class);
        $this->assertNotEmpty($found);
        $subscribed_webhook = $found->first();
        $this->assertSame($subscribed_webhook->id, $webhook->id);
    }
}
