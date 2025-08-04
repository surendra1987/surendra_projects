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

class totara_webhook_totara_webhook_formatter_totara_webhook_test extends testcase {
    public function test_totara_webhook_formatter(): void {
        $generator = totara_webhook_generator::instance();
        $webhook = $generator->create_totara_webhook([
           'name' => 'test',
           'endpoint' => 'https://example.com/webhook',
        ]);
        $context = context_system::instance();
        $formatter = new \totara_webhook\formatter\totara_webhook($webhook, $context);
        $this->assertSame($webhook->get_id(), $formatter->format('id'));
        $this->assertSame($webhook->get_name(), $formatter->format('name', \core\format::FORMAT_RAW));
        $this->assertSame($webhook->get_name(), $formatter->format('name', \core\format::FORMAT_HTML));
        $this->assertSame($webhook->get_status(), $formatter->format('status'));
        $this->assertSame($webhook->get_endpoint(), $formatter->format('endpoint', \core\format::FORMAT_PLAIN));
        $this->assertEquals($webhook->created_at, $formatter->format('created_at', \core\date_format::FORMAT_TIMESTAMP));
        $expected = userdate($webhook->created_at, get_string('strftimedate', 'langconfig'));
        $this->assertEquals($expected, $formatter->format('created_at', \core\date_format::FORMAT_DATE));
        $this->assertSame($webhook->auth_class, $formatter->format('auth_class'));
        $this->assertSame($webhook->auth_config, $formatter->format('auth_config'));
    }
}
