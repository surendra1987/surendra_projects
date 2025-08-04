<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2021 onwards Totara Learning Solutions LTD
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
 * @author Riana Rossouw <riana.rossouw@totaralearning.com>
 * @package core_completion
 */

use \container_course\course as course_container;
use core\entity\course as course_entity;
use core\entity\course_completion as course_completion_entity;
use core_completion\model\course_completion as course_completion_model;
use core_phpunit\testcase;
use totara_program\content\program_content;
use totara_program\content\course_set;
use totara_program\testing\generator as program_generator;

defined('MOODLE_INTERNAL') || die();

class core_completion_course_completion_model_test extends testcase {

    public function test_update_duedate(): void {
        global $CFG;
        require_once($CFG->dirroot . '/totara/certification/lib.php');

        $generator = self::getDataGenerator();
        $programgen = program_generator::instance();

        // Create users
        $user = $generator->create_user();

        // Create course with completion enabled to ensure the course_completion records are created
        // Using completionstartonenrol to have a known enrolment datetime
        $course = $generator->create_course(['enablecompletion' => 1, 'completionstartonenrol' => 1]);

        // Create program and assign the course to the program
        $program = $programgen->create_program(['fullname' => 'Program1']);
        $coursesetdata = [
            [
                'type' => program_content::CONTENTTYPE_MULTICOURSE,
                'nextsetoperator' => course_set::NEXTSETOPERATOR_THEN,
                'completiontype' => course_set::COMPLETIONTYPE_ALL,
                'certifpath' => CERTIFPATH_CERT,
                'courses' => [$course],
            ],
        ];
        $programgen->legacy_add_coursesets_to_program($program, $coursesetdata);

        // Enrol the user in the course
        $now = time();

        $generator->enrol_user($user->id, $course->id, null, 'manual', $now);
        $generator->enrol_user($user->id, $course->id, null, 'totara_program', strtotime('-1 week', $now));

        // course completion is created on first enrolment and not overwritten
        // There should be only 1
        $completion_entity = course_completion_entity::repository()
            ->where('userid', $user->id)
            ->where('course', $course->id)
            ->one();
        self::assertEquals($now, $completion_entity->timeenrolled);
        self::assertNull($completion_entity->duedate);

        // Course has no duedate set, update should have no effect
        $model = course_completion_model::load_by_entity($completion_entity);
        $model->update_duedate();

        $completion_entity = course_completion_entity::repository()
            ->where('userid', $user->id)
            ->where('course', $course->id)
            ->one();
        self::assertNull($completion_entity->duedate);

        // Update the course's due date to a fixed date
        $date_in_future = strtotime('+22 days');
        $course_entity = new course_entity($course->id);
        $course_entity->duedate = $date_in_future;
        $course_entity->save();

        $completion_model = course_completion_model::load_by_entity($completion_entity);
        $completion_model->update_duedate();

        $completion_entity = course_completion_entity::repository()
            ->where('userid', $user->id)
            ->where('course', $course->id)
            ->one();
        self::assertEquals($date_in_future, $completion_entity->duedate);

        // Update the course's due date to a relative date
        $course_entity->duedate = null;
        $course_entity->duedateoffsetunit = course_container::DUEDATEOFFSETUNIT_MONTHS;
        $course_entity->duedateoffsetamount = 2;
        $course_entity->save();

        // Ensure we get the updated course
        $completion_model = course_completion_model::load_by_entity($completion_entity);
        $completion_model->update_duedate();

        $completion_entity = course_completion_entity::repository()
            ->where('userid', $user->id)
            ->where('course', $course->id)
            ->one();
        $expected = strtotime('+2 months', $now);
        self::assertEquals($expected, $completion_entity->duedate);

        // Disable completion tracking on a system level - has no immediate effect on the saved value
        $CFG->enablecompletion = COMPLETION_DISABLED;
        $completion_model = course_completion_model::load_by_entity($completion_entity);
        $completion_model->update_duedate();

        $completion_entity = course_completion_entity::repository()
            ->where('userid', $user->id)
            ->where('course', $course->id)
            ->one();
        self::assertEquals($expected, $completion_entity->duedate);
    }

    public function test_set_correct_relative_duedate(): void {
        $generator = self::getDataGenerator();
        $programgen = program_generator::instance();

        // Create users
        $user = $generator->create_user();

        // Create course with completion enabled to ensure the course_completion records are created
        // Using completionstartonenrol to have a known enrolment datetime
        $course = $generator->create_course(['enablecompletion' => 1, 'completionstartonenrol' => 1]);
        $course_entity = new course_entity($course->id);
        $course_entity->duedate = null;
        $course_entity->duedateoffsetunit = course_container::DUEDATEOFFSETUNIT_MONTHS;
        $course_entity->duedateoffsetamount = 2;
        $course_entity->save();

        // Create program and assign the course to the program
        $program = $programgen->create_program(['fullname' => 'Program1']);
        $coursesetdata = [
            [
                'type' => program_content::CONTENTTYPE_MULTICOURSE,
                'nextsetoperator' => course_set::NEXTSETOPERATOR_THEN,
                'completiontype' => course_set::COMPLETIONTYPE_ALL,
                'certifpath' => CERTIFPATH_CERT,
                'courses' => [$course],
            ],
        ];

        $programgen->add_courses_and_courseset_to_program($program, [$coursesetdata[0]['courses']], CERTIFPATH_CERT);
        $programgen->assign_program($program->id, array($user->id));

        // Course completion in real life created before enrolment
        $ccdetails = [
            'course' => $course->id,
            'userid' => $user->id,
        ];
        $cc = new completion_completion($ccdetails, true);
        $cc->get_progressinfo();

        // Check if course_completion record does not have incorrect duedate
        $completion_entity = course_completion_entity::repository()
            ->where('userid', $user->id)
            ->where('course', $course->id)
            ->one();
        self::assertNull($completion_entity->duedate);

        // Enroll user
        $now = time();
        $generator->enrol_user($user->id, $course->id, null, 'totara_program', $now);

        // Now we have correct duedate
        $completion_entity = course_completion_entity::repository()
            ->where('userid', $user->id)
            ->where('course', $course->id)
            ->one();

        $expected = strtotime('+2 months', $now);
        // Allow for a 1 second discrepancy that can cause an intermittent unit test failure.
        self::assertLessThanOrEqual(1, abs($expected - $completion_entity->duedate));
        self::assertEquals($now, $completion_entity->timeenrolled);

        // Course has duedate set, update should have no effect
        $model = course_completion_model::load_by_entity($completion_entity);
        $model->update_duedate();

        $completion_entity = course_completion_entity::repository()
            ->where('userid', $user->id)
            ->where('course', $course->id)
            ->one();
        self::assertEquals($expected, $completion_entity->duedate);

        // Update the course's due date to a fixed date
        $date_in_future = strtotime('+22 days');
        $course_entity = new course_entity($course->id);
        $course_entity->duedate = $date_in_future;
        $course_entity->save();

        $completion_model = course_completion_model::load_by_entity($completion_entity);
        $completion_model->update_duedate();

        $completion_entity = course_completion_entity::repository()
            ->where('userid', $user->id)
            ->where('course', $course->id)
            ->one();
        self::assertEquals($date_in_future, $completion_entity->duedate);
    }

    /**
     * @return void
     */
    public function test_load_by_course_id(): void {
        $generator = self::getDataGenerator();
        $user = $generator->create_user();
        $user1 = $generator->create_user();
        $user2 = $generator->create_user();
        $course = $generator->create_course(['enablecompletion' => 1, 'completionstartonenrol' => 1]);
        $course1 = $generator->create_course(['enablecompletion' => 1, 'completionstartonenrol' => 1]);

        $generator->enrol_user($user->id, $course->id);
        $generator->enrol_user($user1->id, $course->id);
        $generator->enrol_user($user2->id, $course->id);
        $generator->enrol_user($user1->id, $course1->id);
        $generator->enrol_user($user2->id, $course1->id);

        $course_completions = \core_completion\model\course_completion::load_by_course_id($course->id);

        self::assertEquals(3, count($course_completions));

        foreach ($course_completions as $course_completion) {
            self::assertInstanceOf(\core_completion\model\course_completion::class, $course_completion);
        }

    }
}
