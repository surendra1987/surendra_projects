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

defined('MOODLE_INTERNAL') || die();

class totara_webhook_totara_webhook_webapi_query_webhook_events_test extends testcase {
    use webapi_phpunit_helper;

    protected $query_name = 'totara_webhook_totara_webhook_events';

    public function test_resolve_webhook_events_with_permissions(): void {
        $user = $this->getDataGenerator()->create_user();
        $roleid = $this->getDataGenerator()->create_role();
        $context = context_system::instance();
        assign_capability('totara/webhook:viewtotara_webhooks', CAP_ALLOW, $roleid, $context);
        $this->getDataGenerator()->role_assign($roleid, $user->id, $context);
        $this->setUser($user);
        $response = $this->resolve_graphql_query($this->query_name, []);
        $this->assertArrayHasKey('events', $response);
        $sample = reset($response['events']);
        $this->assertArrayHasKey('name', $sample);
        $this->assertArrayHasKey('class', $sample);
        $this->assertArrayHasKey(\core\event\user_created::class, $response['events']);
    }
}
