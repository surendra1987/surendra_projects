<?php
/*
 * This file is part of Totara Learn
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
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package totara_certification
 */

use core_phpunit\testcase;
use totara_program\assignment\individual;
use totara_program\assignments\assignments;
use totara_program\content\course_sets\multi_course_set;
use totara_program\testing\generator as program_generator;

class totara_certification_fixed_expiry_fixed_completion_auto_update_test extends testcase {

    public function test_totara_program_update_learner_assignments_logging() {
        global $DB;

        // Basic setup.
        self::setAdminUser();
        $program_generator = program_generator::instance();
        $user1 = self::getDataGenerator()->create_user();

        // Set up the cert.
        $cert1data = array(
            'learningcomptype' => CERTIFTYPE_PROGRAM,
            'activeperiod' => '365 day',
            'windowperiod' => '30 day',
            'minimumactiveperiod' => '90 day',
            'recertifydatetype' => CERTIFRECERT_FIXED,
        );
        $cert1 = $program_generator->create_certification($cert1data);

        // Set up a course set with a minimum time allowed.
        $courseset = new multi_course_set($cert1->id);
        $courseset->certifpath = CERTIFPATH_CERT;
        $courseset->timeallowed = DAYSECS * 30;
        $courseset->save_set();

        // Assign the user to the cert.
        $program_generator->assign_to_program($cert1->id, assignments::ASSIGNTYPE_INDIVIDUAL, $user1->id, null, true);
        $assignment = individual::create_from_record($DB->get_record('prog_assignment', ['programid' => $cert1->id]));

        // Update the assignment in the DB directly.
        $DB->update_record('prog_assignment', [
            'id' => $assignment->get_id(),
            'completionevent' => assignments::COMPLETION_EVENT_NONE,
            'completiontime' => time() + DAYSECS * 14,
        ]);

        // Before we do the test, clear the log.
        $DB->delete_records('prog_completion_log');

        // Run the test on totara_program\update_learner_assignment.
        $cert1->update_learner_assignments(true);

        // Check the results.
        $prog_completion_logs = $DB->get_records('prog_completion_log', [], 'id');
        self::assertCount(2, $prog_completion_logs);
        $adjustment_prog_completion_log = reset($prog_completion_logs);
        self::assertEquals($cert1->id, $adjustment_prog_completion_log->programid);
        self::assertEquals($user1->id, $adjustment_prog_completion_log->userid);
        self::assertEquals('Due date was pushed forward by 1 active periods to avoid time allowance exception', $adjustment_prog_completion_log->description);
    }

    public function test_totara_program_assignment_base_logging() {
        global $DB;

        // Basic setup.
        self::setAdminUser();
        $program_generator = program_generator::instance();
        $user1 = self::getDataGenerator()->create_user();

        // Set up the cert.
        $cert1data = array(
            'learningcomptype' => CERTIFTYPE_PROGRAM,
            'activeperiod' => '365 day',
            'windowperiod' => '30 day',
            'minimumactiveperiod' => '90 day',
            'recertifydatetype' => CERTIFRECERT_FIXED,
        );
        $cert1 = $program_generator->create_certification($cert1data);

        // Set up a course set with a minimum time allowed.
        $courseset = new multi_course_set($cert1->id);
        $courseset->certifpath = CERTIFPATH_CERT;
        $courseset->timeallowed = DAYSECS * 30;
        $courseset->save_set();

        // Assign the user to the cert.
        $program_generator->assign_to_program($cert1->id, assignments::ASSIGNTYPE_INDIVIDUAL, $user1->id, null, true);
        $assignment = individual::create_from_record($DB->get_record('prog_assignment', ['programid' => $cert1->id]));

        // Before we do the test, clear the log.
        $DB->delete_records('prog_completion_log');

        // Run the test on totara_program\assignment\base.
        $assignment->set_duedate(time() + DAYSECS * 14); // Due in 14 days.

        // Check the results.
        $prog_completion_logs = $DB->get_records('prog_completion_log', [], 'id');
        self::assertCount(2, $prog_completion_logs);
        $adjustment_prog_completion_log = reset($prog_completion_logs);
        self::assertEquals($cert1->id, $adjustment_prog_completion_log->programid);
        self::assertEquals($user1->id, $adjustment_prog_completion_log->userid);
        self::assertEquals('Due date was pushed forward by 1 active periods to avoid time allowance exception', $adjustment_prog_completion_log->description);
    }
}
