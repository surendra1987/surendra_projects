<?php
/*
 * This file is part of Totara Learn
 *
 * Copyright (C) 2019 onwards Totara Learning Solutions LTD
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
 * @author Petr Skoda <petr.skoda@totaralearning.com>
 * @package core
 */

namespace core\webapi;

abstract class mutation_resolver {
    /**
     * Mutation resolver.
     *
     * @param array $args
     * @param execution_context $ec
     * @return mixed
     */
    abstract public static function resolve(array $args, execution_context $ec);

    /**
     * Complexity points used when resolving this mutation. This can be overridden by individual resolvers if required.
     *
     * @return int
     */
    public static function cost_per_call(): int {
        return 10;
    }

    /**
     * Override in resolver with any middleware you want to be applied.
     *
     * @return array|middleware[]
     */
    public static function get_middleware(): array {
        return [];
    }

    /**
     * If your mutation just needs to return success = true, or success = false plus an error message and/or code, use
     * this helper to get a standard response.
     *
     * @param bool $success
     * @param string|null $message
     * @param string|null $code
     * @return array
     */
    protected static function success_result(bool $success, ?string $message = null, ?string $code = null): array {
        return [
            'success' => $success,
            'return_url' => null,
            'message' => $message,
            'code' => $code
        ];
    }

    /**
     * If your mutation just needs to return success = true, or success = false plus a return url
     * and an optional error message and/or code, use this helper to get a standard response.
     *
     * @param bool $success
     * @param string|null $return_url
     * @param string|null $message
     * @param string|null $code
     * @return array
     */
    protected static function success_url_result(bool $success, string $return_url = null, ?string $message = null, ?string $code = null): array {
        return [
            'success' => $success,
            'return_url' => $return_url,
            'message' => $message,
            'code' => $code
        ];
    }
}
