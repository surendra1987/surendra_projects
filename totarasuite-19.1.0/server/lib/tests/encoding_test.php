<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package core
 */

use core\encoding\base32;
use core_phpunit\testcase;

/**
 * Tests the encoding classes.
 */
class core_encoding_test extends testcase {
    /**
     * Assert we can encode both padded & unpadded data sets.
     *
     * @param string $raw
     * @param string $encoded
     * @param bool $padded
     * @return void
     * @dataProvider base32_data_provider
     * @covers       \core\encoding\base32::encode
     * @covers       \core\encoding\base32::decode
     */
    public function test_base32_encode_decode(string $raw, string $encoded, bool $padded): void {
        $newly_encoded = base32::encode($raw, $padded);
        $this->assertSame($encoded, $newly_encoded);

        // Decode it back
        $newly_raw = base32::decode($encoded, $padded);
        $this->assertSame($raw, $newly_raw);
    }

    /**
     * Assert decode will fail with a non-base32 string.
     *
     * @return void
     * @covers \core\encoding\base32::decode
     */
    public function test_base32_decode_fail_on_characters(): void {
        $this->expectExceptionMessage('Base32::doDecode() only expects characters in the correct base32 alphabet');
        $this->expectException(RangeException::class);
        $invalid_string = 'this is not a base 32 string';
        base32::decode($invalid_string);
    }

    /**
     * Assert decode will fail if padded incorrectly.
     *
     * @return void
     * @covers \core\encoding\base32::decode
     */
    public function test_base32_decode_invalid_padding(): void {
        $this->expectExceptionMessage('Incorrect padding');
        $this->expectException(RangeException::class);
        $invalid_string = 'ifbegrcfizdq=============';
        base32::decode($invalid_string);
    }

    /**
     * Assert that a padded string cannot be passed to a non-padde call.
     *
     * @return void
     * @covers \core\encoding\base32::decode
     */
    public function test_base32_decode_with_unexpected_padding(): void {
        $this->expectExceptionMessage('decodeNoPadding() doesn\'t tolerate padding');
        $this->expectException(InvalidArgumentException::class);
        $invalid_string = 'ifbegrcfizdq====';
        base32::decode($invalid_string);
    }

    /**
     * Test data for the base32 encode/decode tests
     *
     * @return array[]
     */
    public static function base32_data_provider(): array {
        return [
            ['ABCDE', 'ifbegrcf', false],
            ['ABCDE', 'ifbegrcf', true], // No padding either way
            ['ABCDEFG', 'ifbegrcfizdq', false],
            ['ABCDEFG', 'ifbegrcfizdq====', true],
            ['The Quick Brown Fox', 'krugkicrovuwg2zaijzg653oebdg66a', false],
            ['The Quick Brown Fox', 'krugkicrovuwg2zaijzg653oebdg66a=', true],
        ];
    }
}
