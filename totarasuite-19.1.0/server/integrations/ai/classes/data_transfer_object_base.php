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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package core_ai
 */

namespace core_ai;

use JsonSerializable;

/**
 * Base class for request, response, prompt, or any other data transfer object that needs to
 * be a well-defined, JSON-serializable structure.
 *
 * @package core_ai
 */
abstract class data_transfer_object_base implements JsonSerializable {

    /**
     * Represent the object data as a JSON string.
     *
     * @return string
     */
    public function to_json(): string {
        return static::json_encode($this->to_array());
    }

    /**
     * Represent the object data as an array.
     *
     * @return array
     */
    abstract public function to_array(): array;

    /**
     * Helper method to encode JSON safely and consistently.
     *
     * @param mixed $data
     * @return string
     */
    public static function json_encode(mixed $data): string {
        try {
            $encoded = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE, 32);
        } catch (\JsonException $e) {
            throw new ai_exception('Error encoding data to JSON: ' . $e->getMessage());
        }
        return $encoded;
    }

    /**
     * Helper method to decode JSON safely and consistently.
     *
     * @param string $json
     * @return mixed
     */
    protected static function json_decode(string $json): mixed {
        try {
            $decoded = json_decode($json, true, 32, JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING | JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (\JsonException $e) {
            throw new ai_exception('Error decoding JSON string: ' . $e->getMessage());
        }
        return $decoded;
    }

    /**
     * Use the class's to_array() method when serializing.
     *
     * @return array
     */
    public function jsonSerialize(): array {
        return $this->to_array();
    }
}
