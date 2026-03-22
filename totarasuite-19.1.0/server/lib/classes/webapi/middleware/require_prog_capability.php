<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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
 * @author David Curry <david.curry@totara.com>
 * @package core
 */

namespace core\webapi\middleware;

use Closure;
use core\webapi\middleware;
use core\webapi\resolver\payload;
use core\webapi\resolver\result;
use invalid_parameter_exception;

/**
 * Interceptor to confirm if a user has a capability within a given program
 */
class require_prog_capability implements middleware {

    /** @var string */
    protected string $program_id;

    /** @var string */
    protected string $capability;

    /**
     * require_prog_capability constructor.
     *
     * @param string $program_id
     * @param string $capability
     */
    public function __construct(string $program_id, string $capability) {
        $this->capability = $capability;
        $this->program_id = $program_id;
    }

    /**
     * @inheritDoc
     */
    public function handle(payload $payload, Closure $next): result {

        $input = $payload->get_variable('input') ?: [];
        if (empty($input[$this->program_id])) {
            throw new invalid_parameter_exception('invalid program id');
        }

        $program_id = (int)$input[$this->program_id];
        $context = \context_program::instance($program_id, MUST_EXIST);
        require_capability($this->capability, $context);

        return $next($payload);
    }
}
