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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package core
 */

namespace core\login;

use coding_exception;

/**
 * Post login callback.
 */
class complete_login_callback {

    /**
     * Callback function.
     *
     * @var string|array|callable
     */
    private $function;

    /**
     * Callback arguments.
     *
     * @var array
     */
    private $args;

    /**
     * @param $function_name
     * @param array $args
     */
    private function __construct($function_name, array $args = []) {
        $this->function = $function_name;
        $this->args = $args;
    }

    /**
     * Create a complete_login callback.
     *
     * @param array|string|callable $function_name Method name or function name to call after user login is completed.
     * @param array $args Arguments to call with the function
     * @return self
     * @throws coding_exception
     */
    public static function create($function_name, array $args = []): self {
        if (!is_callable($function_name)) {
            throw new coding_exception("Function/method is not callable");
        }
        return new self($function_name, $args);
    }

    /**
     * Get callback function.
     *
     * @return array|string|callable
     */
    public function get_function() {
        return $this->function;
    }

    /**
     * Execute the callback.
     *
     * @return void
     */
    public function execute() {
        if (is_callable($this->function)) {
            call_user_func($this->function, ...$this->args);
        }
    }

    /**
     * Get the callback arguments.
     *
     * @return array
     */
    public function get_args(): array {
        return $this->args;
    }
}
