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
 * @author Katherine Galano <katherine.galano@totara.com>
 * @package totara_mobile
 */

namespace totara_mobile\dto;

class mobile_basic_response {
    /**
     * @var bool This variable returns true if successful, false otherwise.
     */
    public bool $success;
    /**
     * @var string This variable returns validation error messages, null otherwise.
     */
    public string $error;

    /**
     * Constructor with parameters initialized.
     *
     * @param string $error
     * @param bool $success
     */
    private function __construct(string $error, bool $success) {
        $this->error = $error;
        $this->success = $success;
    }

    /**
     * Create an error response with the given error.
     *
     * @param string $error
     * @return self
     */
    public static function create_error_response(string $error): self {
        return new mobile_basic_response(error: $error, success: false);
    }

    /**
     * Create a success response.
     *
     * @return self
     */
    public static function create_success_response(): self {
        return new mobile_basic_response(error: '', success: true);
    }

}
