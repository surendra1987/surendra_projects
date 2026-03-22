<?php
/*
 * This file is part of Totara LMS
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
 * @author Tatsuhiro Kirihara <tatsuhiro.kirihara@totaralearning.com>
 * @package mod_facetoface
 */

namespace mod_facetoface\signup\condition;

use mod_facetoface\signup;
use mod_facetoface\signup_helper;

defined('MOODLE_INTERNAL') || die();

/**
 * Class event_taking_attendance
 */
class event_taking_attendance extends condition {
    const UNLOCKED_SECS_PRIOR_TO_START = 900; // 15 minutes

    /**
     * Is condition passing
     *
     * @return bool
     */
    public function pass() : bool {
        global $USER;
        $event = $this->signup->get_seminar_event();

        $token = optional_param('token', '', PARAM_ALPHANUM);

        // Check self check in is open or not for the logged-in user.  Considered code execution time, -1.
        if ((!empty($token) && $token == $event->get_token()) &&
            $event->is_self_attendance_open(time() - 1) &&
            signup_helper::is_booked(signup::create($USER->id, $event), false)
        ) {
            return true;
        }

        return $event->is_attendance_open();
    }

    /**
     * Get description of condition
     *
     * @return string
     */
    public static function get_description() : string {
        return get_string('state_eventtakingattendance_desc', 'mod_facetoface');
    }

    /**
     * Return explanation why condition has not passed
     *
     * @return array of strings
     */
    public function get_failure() : array {
        return ['event_taking_attendance' => get_string('state_eventtakingattendance_fail', 'mod_facetoface')];
    }
}
