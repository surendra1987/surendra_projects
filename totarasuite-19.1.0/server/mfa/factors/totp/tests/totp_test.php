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
 * @package mfa_totp
 */

use core_phpunit\testcase;
use core\encoding\base32;
use mfa_totp\totp;

/**
 * @coversDefaultClass totp
 * @group core_mfa
 * @group mfa_totp
 */
class mfa_totp_totp_test extends testcase {
    /**
     * Assert that the token generates expected values.
     *
     * This list comes from the specification ({@link https://datatracker.ietf.org/doc/html/rfc6238#appendix-B})
     *
     * @param int $timestamp
     * @param string $digest
     * @param string $expected_token
     * @return void
     * @testWith [59, "SHA1", "94287082"]
     *           [59, "SHA256", "46119246"]
     *           [59, "SHA512", "90693936"]
     *           [1111111109, "SHA1", "07081804"]
     *           [1111111109, "SHA256", "68084774"]
     *           [1111111109, "SHA512", "25091201"]
     *           [1111111111, "SHA1", "14050471"]
     *           [1111111111, "SHA256", "67062674"]
     *           [1111111111, "SHA512", "99943326"]
     *           [1234567890, "SHA1", "89005924"]
     *           [1234567890, "SHA256", "91819424"]
     *           [1234567890, "SHA512", "93441116"]
     *           [2000000000, "SHA1", "69279037"]
     *           [2000000000, "SHA256", "90698825"]
     *           [2000000000, "SHA512", "38618901"]
     *           [20000000000, "SHA1", "65353130"]
     *           [20000000000, "SHA256", "77737706"]
     *           [20000000000, "SHA512", "47863826"]
     */
    public function test_generates_valid_tokens(int $timestamp, string $digest, string $expected_token): void {
        $secrets = [
            'SHA1' => base32::encode('12345678901234567890'),
            'SHA256' => base32::encode('12345678901234567890123456789012'),
            'SHA512' => base32::encode('1234567890123456789012345678901234567890123456789012345678901234'),
        ];
        $digits = 8;
        $totp = new totp($secrets[$digest], $digest, $digits);
        $token = $totp->generate($timestamp);
        $this->assertEquals($expected_token, $token);

        // Assert the validator
        $valid = $totp->validate($timestamp, $expected_token);
        $this->assertTrue($valid);

        // Assert the validator can catch failures
        $invalid = $totp->validate(123456789, $expected_token);
        $this->assertFalse($invalid);
    }
}
