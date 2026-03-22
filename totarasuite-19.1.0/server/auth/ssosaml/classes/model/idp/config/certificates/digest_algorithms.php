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
 * @package auth_ssosaml
 */

namespace auth_ssosaml\model\idp\config\certificates;

/**
 * The available algorithms used for certificate signings.
 */
class digest_algorithms {
    public const ALGORITHM_SHA1 = 'SHA1';
    public const ALGORITHM_SHA256 = 'SHA256';
    public const ALGORITHM_SHA384 = 'SHA384';
    public const ALGORITHM_SHA512 = 'SHA512';

    /**
     * @return static
     */
    public static function make(): self {
        return new self();
    }

    /**
     * The list of available algorithms.
     *
     * @return array
     */
    public function list(): array {
        return [
            self::ALGORITHM_SHA1 => get_string('sha1', 'auth_ssosaml'),
            self::ALGORITHM_SHA256 => get_string('sha256', 'auth_ssosaml'),
            self::ALGORITHM_SHA384 => get_string('sha384', 'auth_ssosaml'),
            self::ALGORITHM_SHA512 => get_string('sha512', 'auth_ssosaml'),
        ];
    }

    /**
     * Confirm the provided algorithm is a valid option.
     *
     * @param string $key
     * @return bool
     */
    public function valid(string $key): bool {
        return array_key_exists($key, $this->list());
    }
}

