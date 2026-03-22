<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package totara_customfield
 */

use core\format;
use core_phpunit\testcase;
use totara_customfield\webapi\formatter\value_type_text_formatter;

defined('MOODLE_INTERNAL') || die();

class totara_customfield_webapi_formatter_value_type_text_formatter_test extends testcase {

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_format_plain_text(): void {
        $context = context_system::instance();

        $text = [
            "value" => "my text value",
        ];

        $formatter = new value_type_text_formatter(
            $text,
            $context
        );

        $this->assertEquals($text['value'], $formatter->format('value', format::FORMAT_PLAIN));
    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_format_html(): void {
        $context = context_system::instance();

        $text = [
            "value" => "<div>my text value</div>",
        ];

        $formatter = new value_type_text_formatter(
            $text,
            $context
        );

        $this->assertEquals("my text value", $formatter->format('value', format::FORMAT_PLAIN));
    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_format_null(): void {
        $context = context_system::instance();

        $text = [
            "value" => null,
        ];

        $formatter = new value_type_text_formatter(
            $text,
            $context
        );

        $this->assertNull($formatter->format('value', format::FORMAT_PLAIN));
    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_format_empty(): void {
        $context = context_system::instance();

        $text = [
            "value" => "",
        ];

        $formatter = new value_type_text_formatter(
            $text,
            $context
        );

        $this->assertEquals("", $formatter->format('value', format::FORMAT_PLAIN));
    }
}
