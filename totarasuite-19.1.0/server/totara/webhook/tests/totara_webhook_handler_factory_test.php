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
use totara_webapi\fixtures\mock_handler;
use totara_webhook\exception\insufficient_webhook_handler_exception;
use totara_webhook\handler\default_handler\default_handler;
use totara_webhook\handler\totara_webhook_handler_factory;

defined('MOODLE_INTERNAL') || die();

require_once __DIR__ . '/fixtures/mock_handler.php';

class totara_webhook_totara_webhook_handler_factory_test extends testcase {
    /**
     * @return void
     * @throws insufficient_webhook_handler_exception
     */
    public function test_create_instance_with_class(): void {
        $handler_class = mock_handler::class;
        /** @var mock_handler $handler */
        $handler = totara_webhook_handler_factory::create_instance($handler_class);
        $this->assertInstanceOf($handler_class, $handler, "handler factory returns expected instance of $handler_class");
    }

    /**
     * @return void
     * @throws insufficient_webhook_handler_exception
     */
    public function test_create_instance_default(): void {
        $handler = totara_webhook_handler_factory::create_instance();
        $this->assertInstanceOf(default_handler::class, $handler, "handler factory returns expected instance of default_handler");
    }

    /**
     * @return void
     * @throws insufficient_webhook_handler_exception
     */
    public function test_create_instance_incorrect_class(): void {
        $class = stdClass::class;
        $this->expectException(insufficient_webhook_handler_exception::class);
        totara_webhook_handler_factory::create_instance($class);
    }

    /**
     * @return void
     * @throws insufficient_webhook_handler_exception
     */
    public function tests_create_instance_non_existent_class(): void {
        $class = "madeup_class";
        $this->expectException(insufficient_webhook_handler_exception::class);
        totara_webhook_handler_factory::create_instance($class);
    }
}
