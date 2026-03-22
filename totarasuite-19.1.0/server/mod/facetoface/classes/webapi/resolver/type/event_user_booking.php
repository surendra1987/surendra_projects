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
 * @author Stefenie Pickston <stefenie.pickston@totara.com>
 * @package mod_facetoface
 */

namespace mod_facetoface\webapi\resolver\type;

use coding_exception;
use core\entity\user;
use core\webapi\execution_context;
use core\webapi\type_resolver;
use core_user\access_controller as user_access_controller;
use mod_facetoface\signup;
use mod_facetoface\signup_helper;

/**
 * Event user booking type resolver
 *
 * The following execution_context ($ec) variables are utilised by this type resolver:
 * - `custom_fields_facetofacesignup` Used to fetch the custom fields for a booking. Will default to null if not given.
 * - `custom_fields_facetofacecancellation` Used to fetch the cancellation custom fields for a booking. Will default to null if not given.
 * - `context` Used by the custom fields resolver to determine the context to use
 */
class event_user_booking extends type_resolver {

    /**
     * @param string $field
     * @param signup $signup
     * @param array $args
     * @param execution_context $ec
     * @return array|mixed|null
     * @throws coding_exception
     */
    public static function resolve(string $field, $signup, array $args, execution_context $ec) {
        if (!$signup instanceof signup) {
            throw new coding_exception('Only signup instances are accepted. Instead, got: ' . gettype($signup));
        }

        if (!$signup->exists()) {
            throw new coding_exception('Signup provided does not exist.');
        }

        return match ($field) {
            'state' => $signup->get_state(),
            'booked' => signup_helper::is_booked($signup),
            'user' => (function () use ($signup) {
                $user = (new user($signup->get_userid()))->to_record();
                $course_id = $signup->get_seminar_event()->get_seminar()->get_course();
                if (user_access_controller::for($user, $course_id)->can_view_profile()) {
                    return $user;
                }
                return null;
            })(),
            'event' => $signup->get_seminar_event(),
            'custom_fields' => $ec->get_variable('custom_fields_facetofacesignup')[$signup->get_id()] ?? null,
            'cancellation_custom_fields' => $ec->get_variable('custom_fields_facetofacecancellation')[$signup->get_id()] ?? null,
        };
    }
}
