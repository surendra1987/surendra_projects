<?php
/*
 * This file is part of Totara Talent Experience Platform
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
 * @author Chris Snyder <chris.snyder@totara.com>
 */

use core_ai\ai_exception;
use core_ai\data_transfer_object_base;
use core_phpunit\testcase;

/**
 * @group ai_integrations
 */
class core_ai_data_transfer_object_base_test extends testcase {

    public static function encode_test_cases(): array {
        return [
            [true, 'true', 'Boolean true'],
            [false, 'false', 'Boolean false'],
            [null, 'null', 'NULL'],
            [new stdClass(), '{}', 'object'],
            [[], '[]', 'Array'],
            [[1, 2, 3], '[1,2,3]', '[1, 2, 3]'],
            [[new stdClass(), 5 => new stdClass], '{"0":{},"5":{}}', 'array of objects'],
            ['string', '"string"', 'string'],
            ['string / with a slash', '"string / with a slash"', 'string with a slash'],
            ["\xc3\x28", '"\ufffd("', 'invalid unicode sequence'],
            [[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[33]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]], 'exception', 'Maximum stack depth exceeded']
        ];
    }

    /**
     * @dataProvider encode_test_cases
     */
    public function test_json_encode($input, $expected, $message): void {
        if ($expected !== 'exception') {
            $this->assertEquals($expected, data_transfer_object_base::json_encode($input), $message);
        } else {
            try {
                $result = data_transfer_object_base::json_encode($input);
                $this->fail('Expected exception not thrown');
            } catch (Throwable $e) {
                $this->assertInstanceOf(ai_exception::class, $e);
                $this->assertStringContainsString($message, $e->getMessage());
            }
        }
    }

    public static function decode_test_cases(): array {
        return [
            ['true', true, 'Boolean true'],
            ['false', false, 'Boolean false'],
            ['null', null, 'NULL'],
            ['{}', [], 'object'],
            ['[]', [],  'Array'],
            ['[1,2,3]', [1, 2, 3], '[1, 2, 3]'],
            ['{"0":{},"5":{}}', [0 => [], 5 => []], 'array of objects'],
            ['"string"', 'string', 'string'],
            ['"string / with a slash"', 'string / with a slash', 'string with a slash'],
            ["\"a\xd0\xf2b\"", 'a��b', 'invalid unicode sequence'],
            ['[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[33]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]', 'exception', 'Maximum stack depth exceeded']
        ];
    }

    /**
     * @dataProvider decode_test_cases
     */
    public function test_json_decode($input, $expected, $message): void {
        $reflection_class = new ReflectionClass(data_transfer_object_base::class);
        $method = $reflection_class->getMethod('json_decode');
        $method->setAccessible(true);

        if ($expected !== 'exception') {
            $this->assertEquals($expected, $method->invoke(null, $input), $message);
        } else {
            try {
                $result = $method->invoke(null, $input);
                $this->fail('Expected exception not thrown');
            } catch (Throwable $e) {
                $this->assertInstanceOf(ai_exception::class, $e);
                $this->assertStringContainsString($message, $e->getMessage());
            }
        }
    }
}
