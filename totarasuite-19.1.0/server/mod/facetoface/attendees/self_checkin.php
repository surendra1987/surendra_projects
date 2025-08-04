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

use core\output\notification;
use mod_facetoface\attendance\self_attendance_helper;
use mod_facetoface\seminar_event;
use mod_facetoface\signup;
use mod_facetoface\signup\state\fully_attended;
use mod_facetoface\signup_helper;

global $PAGE, $OUTPUT, $USER;
require_once __DIR__ . '/../../../config.php';

$s = required_param('s', PARAM_INT);
$token = required_param('token', PARAM_ALPHANUM);
$seminarevent = new seminar_event($s);
$seminar = $seminarevent->get_seminar();
$cm = $seminar->get_coursemodule();
$context = context_module::instance($cm->id);

require_login($seminar->get_course(), false, $cm);

$baseurl = new moodle_url('/mod/facetoface/attendees/self_checkin.php', ['s' => $s]);
$PAGE->set_cm($cm);
$PAGE->set_url($baseurl);
$PAGE->set_context($context);

// This is a redirect only page, ensure you always cater for the following redirection values otherwise below is the default
$redirect_url = new moodle_url('/mod/facetoface/eventinfo.php', ['s' => $s]);

$now = time();
if (self_attendance_helper::is_event_self_attendance_valid($seminarevent, $token, $now)) {
    $signup = signup::create($USER->id, $seminarevent);
    // Only when the learner booked the event.
    if (signup_helper::is_booked($signup, false)) {

        // If self attendance is not open.
        if (!$seminarevent->is_self_attendance_open($now)) {
            redirect($redirect_url, get_string('error:selfattendance_event', 'mod_facetoface'), null, notification::NOTIFY_ERROR);
        }

        // If the learner has not been marked by admin.
        if (!self_attendance_helper::attendance_has_been_marked($signup, $seminarevent)) {
            try {
                // Mark self attendance.
                if (self_attendance_helper::mark_self_attendance($signup, $seminarevent, $context)) {
                    redirect($redirect_url, get_string('eventselfattendance', 'mod_facetoface'), null, notification::NOTIFY_SUCCESS);
                }
            } catch (coding_exception $e) {
                debugging($e->getMessage(), DEBUG_DEVELOPER);
            }

            // Unexpected error.
            redirect($redirect_url, get_string('error:takeattendance', 'mod_facetoface'), null, notification::NOTIFY_ERROR);
        }

        // When the learner has been marked.
        redirect($redirect_url, get_string('attendancetaken_info', 'mod_facetoface'));
    }

    // Not booked.
    redirect($redirect_url, get_string('error:selfattendance_attendee', 'mod_facetoface'), null, notification::NOTIFY_ERROR);
}

// Link expired.
redirect($redirect_url, get_string('error:selfattendance', 'mod_facetoface'), null, notification::NOTIFY_ERROR);
