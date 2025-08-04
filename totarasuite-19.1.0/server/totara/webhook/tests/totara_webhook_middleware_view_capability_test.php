<?php

/**
 *  This file is part of Totara TXP
 *
 *  Copyright (C) 2025 onwards Totara Learning Solutions LTD
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.

 * @package totara_webhook
 * @author ben fesili <ben.fesili@totara.com>
 */

use core\webapi\execution_context;
use core\webapi\resolver\payload;
use core\webapi\resolver\result;
use core_phpunit\testcase;
use totara_webhook\middleware\require_totara_webhook_view_capability;
use totara_webhook\testing\generator as totara_webhook_generator;

/**
 * @group totara_webhook
 */

defined('MOODLE_INTERNAL') || die();

class totara_webhook_totara_webhook_middleware_view_capability_test extends testcase {

    public function test_middleware_without_webhook(): void {
        $middleware = new require_totara_webhook_view_capability();
        $context = execution_context::create("dev");
        $payload = payload::create([], $context);
        $next = function (payload $payload): result {
            return new result('success');
        };
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Webhook was not loaded. Make sure a previous middleware loads the Webhook before this one is called.');
        $middleware->handle($payload, $next);
    }

    public function test_middleware_without_permission(): void {
        $generator = totara_webhook_generator::instance();
        $webhook = $generator->create_totara_webhook();

        $middleware = new require_totara_webhook_view_capability();
        $context = execution_context::create("dev");
        $payload = payload::create([], $context);
        $payload->set_variable('totara_webhook', $webhook);

        $next = function (payload $payload): result {
            return new result('success');
        };
        $this->expectException(moodle_exception::class);
        $middleware->handle($payload, $next);
    }

    public function test_middleware_with_permission(): void {
        $generator = totara_webhook_generator::instance();
        $webhook = $generator->create_totara_webhook();

        $middleware = new require_totara_webhook_view_capability();
        $context = execution_context::create("dev");
        $payload = payload::create([], $context);
        $payload->set_variable('totara_webhook', $webhook);

        $user = $this->getDataGenerator()->create_user();
        $role_id = $this->getDataGenerator()->create_role();
        $context = context_system::instance();
        assign_capability('totara/webhook:viewtotara_webhooks', CAP_ALLOW, $role_id, $context);
        role_assign($role_id, $user->id, $context);
        $this->setUser($user);

        $next = function (payload $payload): result {
            return new result('success');
        };
        $result = $middleware->handle($payload, $next);
        $this->assertSame('success', $result->get_data());
    }

}
