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
use totara_webhook\middleware\require_totara_webhook;
use totara_webhook\testing\generator as totara_webhook_generator;

defined('MOODLE_INTERNAL') || die();

/**
 * @group totara_webhook
 */
class totara_webhook_totara_webhook_middleware_require_totara_webhook_test extends testcase {

    public function test_middleware_without_webhook(): void {
        $generator = totara_webhook_generator::instance();
        $webhook = $generator->create_totara_webhook();

        $middleware = require_totara_webhook::by_totara_webhook_id('input.id');
        $context = execution_context::create("dev");
        $payload = payload::create([], $context);
        $payload->set_variable('totara_webhook', $webhook);

        $next = function (payload $payload): result {
            return new result('success');
        };
        $this->expectException(moodle_exception::class);
        $middleware->handle($payload, $next);
    }

    public function test_middleware_with_webhook(): void {
        $generator = totara_webhook_generator::instance();
        $webhook = $generator->create_totara_webhook();

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $middleware = require_totara_webhook::by_totara_webhook_id('input.id', true);
        $context = execution_context::create("dev");
        $payload = payload::create([], $context);
        $payload->set_variable('input', ['id' => $webhook->get_id()]);

        $next = function (payload $payload) use ($webhook): result {
            return new result('success');
        };
        /** @var result $found */
        $found = $middleware->handle($payload, $next);
        $data = $found->get_data();
        $this->assertSame('success', $data);
    }

    public function test_middleware_with_invalid_webhook(): void {
        $generator = totara_webhook_generator::instance();
        $webhook = $generator->create_totara_webhook();
        $invalid_webhook_id = $webhook->get_id();
        $webhook->delete(); // delete to make invalid id

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $middleware = require_totara_webhook::by_totara_webhook_id('input.id');
        $context = execution_context::create("dev");
        $payload = payload::create([], $context);
        $payload->set_variable('input', ['id' => $invalid_webhook_id]);

        $next = function (payload $payload) use ($webhook): result {
            return new result('success');
        };
        $this->expectException(moodle_exception::class);
        $found = $middleware->handle($payload, $next);
    }
}
