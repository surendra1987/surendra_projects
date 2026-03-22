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
 * @package mod_facetoface
 */

namespace mod_facetoface\attendance;

use coding_exception;
use context_module;
use mod_facetoface\attendees_helper;
use mod_facetoface\seminar_event;
use mod_facetoface\signup;
use mod_facetoface\signup\state\attendance_state;
use mod_facetoface\signup\state\booked;
use mod_facetoface\signup\state\fully_attended;
use mod_facetoface\signup\state\no_show;
use mod_facetoface\signup\state\partially_attended;
use mod_facetoface\signup\state\unable_to_attend;

class self_attendance_helper {
    // 60 days
    public const MAX_ALLOWED_VALIDITY = 60 * DAYSECS;

    /**
     * Returns true if this user is valid to do self attendance for the given seminar event, false otherwise.
     *
     * @param seminar_event $seminarevent
     * @param string $token
     * @param int $now
     *
     * @return bool
     */
    public static function is_event_self_attendance_valid(seminar_event $seminarevent, string $token, int $now): bool {
        $is_token_valid = ($token === $seminarevent->get_token());
        return $seminarevent->is_valid_self_attendance_period($now) && $is_token_valid;
    }

    /**
     *
     *  Returns true if the user's attendance for the given seminar event is
     *   1. Already marked or
     *   2. Successfully marked by this function as "fully attended"
     *  , false otherwise.
     *
     *
     * @param signup $signup
     * @param seminar_event $seminarevent
     * @param context_module $context
     *
     * @return bool
     * @throws coding_exception
     */
    public static function mark_self_attendance(signup $signup, seminar_event $seminarevent, context_module $context): bool {
        if ($signup->get_state()::get_code() === booked::class::get_code()) {
            $states = [$signup->get_id() => fully_attended::get_code()];
            $session = (object) ['id' => $seminarevent->get_id()];
            $grades = [];
            return attendance_helper::process_event_attendance($seminarevent, $states, $grades, $session, $context);
        }
        return true;
    }

    /**
     *
     * Check learner attendance has been marked by teacher or not.
     *
     * @param signup $signup
     * @param seminar_event $seminarevent
     *
     * @return bool
     */
    public static function attendance_has_been_marked(signup $signup, seminar_event $seminarevent): bool {
        $status_codes = attendance_state::get_all_attendance_code_with([
            fully_attended::class,
            partially_attended::class,
            unable_to_attend::class,
            no_show::class,
        ]);
        $helper = new attendees_helper($seminarevent);
        $attendees = $helper->get_attendees_with_codes($status_codes);
        foreach ($attendees as $attendee) {
            if ($attendee->get_signupid() == $signup->get_id()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns true if the given $enddate is greater than $startdate plus {@see self::SIXTY_DAYS}
     *
     * @param int $startdate
     * @param int $enddate
     * @return bool
     */
    public static function is_enddate_beyond_max_allowed(int $startdate, int $enddate): bool {
        $maxtime = $startdate + self::MAX_ALLOWED_VALIDITY;
        return $enddate > $maxtime;
    }
}
