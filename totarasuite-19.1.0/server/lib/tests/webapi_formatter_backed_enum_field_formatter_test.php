<?php
/**
 *  This file is part of Totara Core
 *
 *  Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 *
 * @package core
 * @author Simon Coggins <simon.coggins@totara.com>
 */

use core\fixtures\color_enum;
use core\format;
use core\webapi\formatter\field\backed_enum_field_formatter;

defined('MOODLE_INTERNAL') || die();

require_once __DIR__ . '/fixtures/color_enum.php';

class core_webapi_formatter_backed_enum_field_formatter_test extends \core_phpunit\testcase {

    public function test_single_backed_enum_field_formatter_test() {
        $context = context_system::instance();
        $formatter = new backed_enum_field_formatter(format::FORMAT_PLAIN, $context);
        $value = color_enum::RED;
        $result = $formatter->format($value);

        $this->assertIsString($result);
        $this->assertEquals('RED', $result);
    }

    public function test_single_backed_enum_default_format_field_formatter_test() {
        $context = context_system::instance();
        $formatter = new backed_enum_field_formatter(null, $context);
        $value = color_enum::RED;
        $result = $formatter->format($value);

        // Default format is plain
        $this->assertIsString($result);
        $this->assertEquals('RED', $result);
    }

    public function test_single_backed_enum_raw_field_formatter_test() {
        $context = context_system::instance();
        $formatter = new backed_enum_field_formatter(format::FORMAT_RAW, $context);
        $value = color_enum::YELLOW;
        $result = $formatter->format($value);

        $this->assertInstanceOf(\BackedEnum::class, $result);
        $this->assertEquals($value, $result);
    }

    public function test_backed_enum_array_field_formatter_test() {
        $context = context_system::instance();
        $formatter = new backed_enum_field_formatter(format::FORMAT_PLAIN, $context);
        $value = [color_enum::BLUE, color_enum::GREEN];
        $result = $formatter->format($value);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertIsString($result[0]);
        $this->assertEquals('BLUE', $result[0]);
        $this->assertEquals('GREEN', $result[1]);
    }

    public function test_backed_enum_array_default_format_field_formatter_test() {
        $context = context_system::instance();
        $formatter = new backed_enum_field_formatter(null, $context);
        $value = [color_enum::BLUE, color_enum::GREEN];
        $result = $formatter->format($value);

        // Default format is plain
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertIsString($result[0]);
        $this->assertEquals('BLUE', $result[0]);
        $this->assertEquals('GREEN', $result[1]);
    }

    public function test_backed_enum_array_with_invalid_format_field_formatter_test() {
        $context = context_system::instance();
        // Error if you specify an existing but unsupported format
        $formatter = new backed_enum_field_formatter(format::FORMAT_JSON_EDITOR, $context);
        $value = color_enum::RED;
        $this->expectException(\coding_exception::class);
        $this->expectExceptionMessage(' format is currently not supported by the backed enum formatter.');
        $formatter->format($value);
    }

    public function test_backed_enum_array_with_unknown_format_field_formatter_test() {
        $context = context_system::instance();
        // Error if you specify a format that is unknown
        $formatter = new backed_enum_field_formatter('unknown', $context);
        $value = color_enum::RED;
        $this->expectException(\coding_exception::class);
        $this->expectExceptionMessage('Invalid format given');
        $formatter->format($value);
    }
}
