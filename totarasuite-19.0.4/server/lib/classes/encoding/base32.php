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

namespace core\encoding;

use ParagonIE\ConstantTime\Base32 as constant_time_base32;

/**
 * Helper class wrapped around Constant Time Encoding's base32 implementation.
 * Offers base32 encode/decode.
 *
 * See libraries/required/paragonie/constant_time_encoding/README.md
 */
class base32 {
    /**
     * Encode the provided string data into base32.
     *
     * @param string $data The raw data to encode.
     * @param bool $padded Whether padding is added. Defaults to false (no padding).
     * @return string The base32 encoded string.
     */
    public static function encode(string $data, bool $padded = false): string {
        if ($padded) {
            return constant_time_base32::encode($data);
        }
        return constant_time_base32::encodeUnpadded($data);
    }

    /**
     * Decode the provided base32 string into the raw result.
     *
     * @param string $base32_string The encoded string.
     * @param bool $padded Whether the string was padded. Defaults to false (no padding).
     * @return string The original string.
     */
    public static function decode(string $base32_string, bool $padded = false): string {
        if ($padded) {
            return constant_time_base32::decode($base32_string);
        }

        return constant_time_base32::decodeNoPadding($base32_string);
    }
}

