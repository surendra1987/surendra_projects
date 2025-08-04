<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package theme_inspire
 */

namespace theme_inspire\webapi\resolver\mutation;

use core\webapi\execution_context;
use core\webapi\mutation_resolver;
use moodle_exception;

/**
 * Mutation for setting inspire theme navigation state.
 */
class set_navigation_state extends mutation_resolver {
    /**
     * @var array
     */
    private const NAVIGATION_STATES = [
        'collapsed',
        'expanded',
    ];

    /**
     * @param array $args
     * @param execution_context $ec
     * @return bool
     */
    public static function resolve(array $args, execution_context $ec): bool {
        global $SESSION;
        $input = $args['input'];

        if (isset($input['state'])) {
            $state = $input['state'];
            if (!in_array($state , self::NAVIGATION_STATES, true)) {
                throw new moodle_exception('Invalid navigation state.');
            }

            $SESSION->theme_inspire_navigation_state = $state;
        } else {
            // If null passed, set it to null;
            if (isset($SESSION->theme_inspire_navigation_state)) {
                $SESSION->theme_inspire_navigation_state = null;
            }
        }

        return true;
    }
}
