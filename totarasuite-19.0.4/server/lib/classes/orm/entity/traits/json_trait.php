<?php
/**
 * This file is part of Totara Perform
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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package core_orm
 */

namespace core\orm\entity\traits;

use stdClass;

/**
 * Enables consistent encoding and decoding of JSON data throughout the model.
 */
trait json_trait {

    /**
     * Encodes JSON data up to 32 levels deep, in a format suitable for database storage.
     *
     * @param mixed $data
     * @return string
     */
    public static function json_encode($data): string {
        return json_encode($data, JSON_THROW_ON_ERROR | JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE, 32);
    }

    /**
     * Decodes JSON data from storage to a stdClass instance.
     *
     * @param string $json
     * @return stdClass
     * @throws \JsonException
     */
    public static function json_decode(string $json): stdClass {
        return (object)self::json_decode_internal($json);
    }

    /**
     * Decodes JSON data from storage to an array.
     *
     * @param string $json
     * @return array
     * @throws \JsonException
     */
    public static function json_decode_to_array(string $json): array {
        return (array)self::json_decode_internal($json, true);
    }

    /**
     * Decodes JSON string to either an object or array, depending on params.
     *
     * @param string $json
     * @param false $to_array
     * @return mixed
     * @throws \JsonException
     */
    protected static function json_decode_internal(string $json, $to_array = false) {
        return json_decode($json, $to_array, 32, JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING | JSON_INVALID_UTF8_SUBSTITUTE);
    }
}