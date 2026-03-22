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

defined('MOODLE_INTERNAL') || die();

class totara_webhook_totara_webhook_totara_webhook_helper_event_list_test extends testcase {
    public function test_get_all_events(): void {
        $all_events = \totara_webhook\helper\event_list::get_all_events();
        $this->assertArrayHasKey(\core\event\user_created::class, $all_events);
        $this->assertSame(\core\event\user_created::class, $all_events[\core\event\user_created::class]['class']);
    }

    public function test_get_all_events_for_subscribed(): void {
        $subscribed_events = [
            \core\event\user_created::class,
            \core\event\badge_created::class,
        ];
        $subscribed_events = \totara_webhook\helper\event_list::get_all_events_for_subscribed($subscribed_events);
        $this->assertCount(2, $subscribed_events);
    }
}
