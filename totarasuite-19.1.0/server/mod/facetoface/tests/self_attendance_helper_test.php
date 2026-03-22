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

use core_phpunit\testcase;
use mod_facetoface\attendance\attendance_helper;
use mod_facetoface\attendance\self_attendance_helper;
use mod_facetoface\seminar;
use mod_facetoface\seminar_event;
use mod_facetoface\seminar_session;
use mod_facetoface\signup;
use mod_facetoface\signup\state\booked;
use mod_facetoface\signup\state\fully_attended;
use mod_facetoface\signup\state\no_show;
use mod_facetoface\signup_status;

class mod_facetoface_self_attendance_helper_test extends testcase {

    public function test_success_mark_self_attendance_auto_grade(): void {
        $event = $this->create_seminar_event();
        $gen = $this->getDataGenerator();
        $user = $gen->create_user();
        $gen->enrol_user($user->id, $event->get_seminar()->get_course());
        global $USER;
        $USER->id = $user->id;

        $signup = signup::create($user->id, $event);
        $signup->save();
        $signup->switch_state(booked::class);

        // Start preparing the attendance test for taking attendance in event level.
        $seminar = $event->get_seminar();
        $cm = $seminar->get_coursemodule();
        $context = $seminar->get_contextmodule($cm->id);
        self::assertEquals(booked::class::get_code(), $signup->get_state()::get_code());
        self::assertNull(signup_status::from_current($signup)->get_grade());
        self::assertTrue(self_attendance_helper::mark_self_attendance($signup, $event, $context));
        self::assertEquals(fully_attended::class::get_code(), $signup->get_state()::get_code());
        self::assertEquals(100, signup_status::from_current($signup)->get_grade());
    }

    /**
     * Create an event with two sessions, where first session is going to be in the pass and the
     * second session is going to the future.
     *
     * @return seminar_event
     */
    private function create_seminar_event(): seminar_event {
        $gen = $this->getDataGenerator();
        $course = $gen->create_course();

        $f2fgen = $gen->get_plugin_generator('mod_facetoface');
        $f2f = $f2fgen->create_instance(['course' => $course->id]);

        $s = new seminar($f2f->id);
        $s->set_sessionattendance(0);
        $s->set_attendancetime(seminar::EVENT_ATTENDANCE_UNRESTRICTED);
        $s->save();

        $e = new seminar_event();
        $e->set_facetoface($s->get_id());
        $e->save();

        $time = time();
        $start_time = $time + 3600;
        $end_time = $time + (3600 * 2);
        $ss = new seminar_session();
        $ss->set_timefinish($end_time);
        $ss->set_timestart($start_time);
        $ss->set_sessionid($e->get_id());
        $ss->save();
        return $e;
    }

    public function test_successful_mark_self_attendance_manual_grade(): void {
        $event = $this->create_seminar_event();
        $gen = $this->getDataGenerator();
        $user = $gen->create_user();
        $gen->enrol_user($user->id, $event->get_seminar()->get_course());
        global $USER;
        $USER->id = $user->id;

        $signup = signup::create($user->id, $event);
        $signup->save();
        $signup->switch_state(booked::class);

        // Start preparing the attendance test for taking attendance in event level.
        $seminar = $event->get_seminar();
        $seminar->set_eventgradingmanual(1);
        $seminar->save();
        $context = $seminar->get_contextmodule($seminar->get_coursemodule()->id);
        self::assertEquals(booked::class::get_code(), $signup->get_state()::get_code());
        self::assertNull(signup_status::from_current($signup)->get_grade());
        self::assertTrue(self_attendance_helper::mark_self_attendance($signup, $event, $context));
        self::assertEquals(fully_attended::class::get_code(), $signup->get_state()::get_code());
        self::assertNull(signup_status::from_current($signup)->get_grade());
    }

    /**
     * Ensure we don't override already existing attendance.
     *
     * @return void
     * @throws \mod_facetoface\exception\signup_exception
     * @throws coding_exception
     */
    public function test_mark_self_attendance_no_override(): void {
        $event = $this->create_seminar_event();
        $gen = $this->getDataGenerator();
        $user = $gen->create_user();
        $gen->enrol_user($user->id, $event->get_seminar()->get_course());
        global $USER;
        $USER->id = $user->id;

        $signup = signup::create($user->id, $event);
        $signup->save();
        $signup->switch_state(booked::class);

        // Start preparing the attendance test for taking attendance in event level.
        $seminar = $event->get_seminar();
        $seminar->save();
        $context = $seminar->get_contextmodule($seminar->get_coursemodule()->id);
        $states = [$signup->get_id() => no_show::class::get_code()];
        $grades = [];
        $session = (object) ['id' => $event->get_id()];
        attendance_helper::process_event_attendance($event, $states, $grades, $session, $context);
        self::assertEquals(no_show::class::get_code(), $signup->get_state()::get_code());
        self::assertTrue(self_attendance_helper::mark_self_attendance($signup, $event, $context));
        self::assertEquals(no_show::class::get_code(), $signup->get_state()::get_code());
    }

    /**
     * Test we are validating max allowed time validity.
     *
     * @return void
     */
    public function test_validate_maxtime_allowed(): void {
        $start = time();
        $end = strtotime("+60 days", $start);
        self::assertFalse(self_attendance_helper::is_enddate_beyond_max_allowed($start, $end));
        $end = strtotime("+1 days", $start);
        self::assertFalse(self_attendance_helper::is_enddate_beyond_max_allowed($start, $end));
        $end = strtotime("+61 days", $start);
        self::assertTrue(self_attendance_helper::is_enddate_beyond_max_allowed($start, $end));
    }
}
