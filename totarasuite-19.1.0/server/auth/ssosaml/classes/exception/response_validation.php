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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package auth_ssosaml
 */

namespace auth_ssosaml\exception;

use moodle_exception;

/**
 * Base error thrown when a SAML response is invalid.
 */
abstract class response_validation extends moodle_exception {
    /**
     * @param string|null $debug_info
     * @param string|null $a
     */
    protected function __construct(?string $debug_info = null, ?string $a = null) {
        parent::__construct($this->get_error_code(), 'auth_ssosaml', null, $a, $debug_info);
    }

    /**
     * @param string|null $debug_info
     * @param string|null $a
     * @return static
     */
    public static function make(?string $debug_info = null, ?string $a = null): response_validation {
        return new static($debug_info, $a);
    }

    /**
     * @return string The error code language string.
     */
    abstract protected function get_error_code(): string;
}