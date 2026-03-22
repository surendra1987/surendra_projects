<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2016 onwards Totara Learning Solutions LTD
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
 * @author Brendan Cox <brendan.cox@totaralearning.com>
 * @package totara_core
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

global $CFG;
require_once($CFG->dirroot . '/totara/core/classes/observer.php');
require_once($CFG->dirroot . '/completion/criteria/completion_criteria.php');
require_once($CFG->dirroot . '/completion/criteria/completion_criteria_course.php');

use core\entity\course_completion;
use core\event\user_enrolment_created;
use core\model\user_enrolment;

class totara_core_observer_test extends \core_phpunit\testcase {

    /** @var  \core\testing\generator $data_generator */
    protected $generator;

    /** @var \core_completion\testing\generator $completion_generator */
    protected $completion_generator;

    protected function tearDown(): void {
        $this->generator = null;
        $this->completion_generator = null;
        parent::tearDown();
    }

    protected function setUp(): void {
        parent::setup();

        set_config('enablecompletion', '1');

        $this->generator = $this->getDataGenerator();
        $this->completion_generator = $this->getDataGenerator()->get_plugin_generator('core_completion');
    }

    public function test_course_criteria_review() {

        $course1 = $this->generator->create_course(array('enablecompletion' => 1));
        $course2 = $this->generator->create_course(array('enablecompletion' => 1));

        $user1 = $this->generator->create_user();
        $user2 = $this->generator->create_user();

        // Set up completion of course 1 as a completion criteria for course 2.
        $this->completion_generator->set_course_criteria_course_completion($course2,
            array($course1->id), COMPLETION_AGGREGATION_ALL);

        // Enrol both users in both courses.
        $this->generator->enrol_user($user1->id, $course1->id);
        $this->generator->enrol_user($user2->id, $course1->id);
        $this->generator->enrol_user($user1->id, $course2->id);
        $this->generator->enrol_user($user2->id, $course2->id);

        // Ensure that neither course1 or course 2 are complete yet.
        $course1info = new completion_info($course1);
        $this->assertFalse($course1info->is_course_complete($user1->id));
        $this->assertFalse($course1info->is_course_complete($user2->id));
        $course2info = new completion_info($course2);
        $this->assertFalse($course2info->is_course_complete($user1->id));
        $this->assertFalse($course2info->is_course_complete($user2->id));

        // We'll mark user2 complete for course 1.
        $this->completion_generator->complete_course($course1, $user2);

        // User1 should not be complete, but user2 should be, for course 1.
        $this->assertFalse($course1info->is_course_complete($user1->id));
        $this->assertTrue($course1info->is_course_complete($user2->id));

        // User2 should also be complete for course 2 now. User1 should not be.
        $this->assertFalse($course2info->is_course_complete($user1->id));
        $this->assertTrue($course2info->is_course_complete($user2->id));
    }

    public function test_user_enrolment_when_user_enrolment_is_pending(): void {
        global $DB;

        // Enrol a user in a course.
        $user1 = $this->generator->create_user();
        $course1 = $this->generator->create_course();
        $this->generator->enrol_user($user1->id, $course1->id, null, 'manual', 0, 0, ENROL_USER_PENDING_APPLICATION);

        // Find the user enrolment that was created.
        $enrol = $DB->get_record('enrol', array('enrol' => 'manual', 'courseid' => $course1->id));
        $user_enrolment_record = $DB->get_record('user_enrolments', ['userid' => $user1->id, 'enrolid' => $enrol->id]);

        // Create an event.
        /** @var user_enrolment_created $user_enrolment_created_event */
        $user_enrolment_created_event = user_enrolment_created::create(
            [
                'objectid' => $user_enrolment_record->id,
                'courseid' => $course1->id,
                'context' => context_course::instance($course1->id),
                'relateduserid' => $user1->id,
                'other' => [
                    'enrol' => 'cohort',
                    'containertype' => 'container_course',
                ],
            ]
        );

        // Run the observer.
        totara_core_observer::user_enrolment($user_enrolment_created_event);

        // Check that the course completion was not created.
        $this->assertEquals(0, course_completion::repository()->count());
    }
}