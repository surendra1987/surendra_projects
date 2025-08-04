<?php
/**
 * This file is part of Totara LMS
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package totara_placeholder
 */


use block_totara_featured_links\placeholders\featured_links_placeholders;
use core\webapi\execution_context;
use core\webapi\resolver\payload;
use core_course\totara_notification\resolver\activity_completed_resolver;
use core_phpunit\testcase;
use totara_placeholder\webapi\middleware\validate_placeholder_or_resolver_class_name;

class totara_placeholder_validate_placeholder_or_resolver_class_name_test extends testcase {
    public function test_handle(): void {
        $ec = execution_context::create('dev');
        $middleware = new validate_placeholder_or_resolver_class_name('resolver_class_name', true);
        $payload = new payload([], $ec);

        $this->expectExceptionMessage('The payload does not have variable \'resolver_class_name\'');
        $middleware->handle($payload, function (payload $payload) {

        });

        // test unknown class that is not a placeholder nor a resolver
        $payload = new payload(['resolver_class_name' => 'unknown_class'], $ec);
        $this->expectExceptionMessage('The resolver class is not a notifiable event resolver');
        $middleware->handle($payload, function (payload $payload) {

        });

        // test with a valid resolver class
        $payload = new payload(['resolver_class_name', activity_completed_resolver::class], $ec);
        $middleware->handle($payload, function(payload $payload) {
            $this->assertSame($payload->get_variable('resolver_class_name'), activity_completed_resolver::class);
        });

        // test with a valid placeholder provider
        $payload = new payload(['resolver_class_name', featured_links_placeholders::class], $ec);
        $middleware->handle($payload, function(payload $payload) {
            $this->assertSame($payload->get_variable('resolver_class_name'), featured_links_placeholders::class);
        });
    }
}