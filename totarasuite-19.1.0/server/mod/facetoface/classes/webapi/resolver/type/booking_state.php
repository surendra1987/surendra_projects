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
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package mod_facetoface
 */

namespace mod_facetoface\webapi\resolver\type;

use coding_exception;
use core\format;
use core\webapi\execution_context;
use core\webapi\type_resolver;
use mod_facetoface\signup\state\state;

/**
 * Booking state type resolver.
 */
class booking_state extends type_resolver {

    /**
     * @param string $field
     * @param state $state
     * @param array $args
     * @param execution_context $ec
     * @return array|mixed|null
     * @throws coding_exception
     */
    public static function resolve(string $field, $state, array $args, execution_context $ec) {
        if (!is_subclass_of($state, state::class)) {
            throw new coding_exception('State must be a subclass of mod_facetoface\signup\state\state');
        }

        $formatter = new \mod_facetoface\formatter\booking_state($state, $ec->get_relevant_context());
        return $formatter->format($field, format::FORMAT_PLAIN);
    }
}
