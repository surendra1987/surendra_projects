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
 * @author Simon Chester <simon.chester@totara.com>
 * @package core_mfa
 */

namespace core_mfa\webapi\middleware;

use Closure;
use core\webapi\middleware;
use core\webapi\resolver\payload;
use core\webapi\resolver\result;
use core_mfa\framework;

/**
 * Validate that the current user can register auth methods.
 */
class require_can_register implements middleware {

    /** @inheritDoc */
    public function handle(payload $payload, Closure $next): result {
        global $USER;

        if (!(new framework($USER->id))->user_can_register()) {
            throw new \moodle_exception('nopermissions', 'error', '', get_string('register_a_new_factor', 'mfa'));
        }

        return $next($payload);
    }
}
