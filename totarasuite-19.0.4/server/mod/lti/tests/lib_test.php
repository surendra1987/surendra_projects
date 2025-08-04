<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Unit tests for mod_lti lib
 *
 * @package    mod_lti
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */

defined('MOODLE_INTERNAL') || die();

use mod_approval\entity\assignment\assignment as assignment_entity;
use mod_lti\testing\generator as lti_generator;
use core_grades\testing\generator as grade_generator;

/**
 * Unit tests for mod_lti lib
 *
 * @package    mod_lti
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */
class mod_lti_lib_test extends \core_phpunit\testcase {

    /**
     * Prepares things before this test case is initialised
     * @return void
     */
    public static function setUpBeforeClass(): void {
        global $CFG;
        require_once($CFG->dirroot . '/mod/lti/lib.php');
    }

    /**
     * Test lti_view
     * @return void
     */
    public function test_lti_view() {
        global $CFG;

        $CFG->enablecompletion = 1;

        $this->setAdminUser();
        // Setup test data.
        $course = $this->getDataGenerator()->create_course(array('enablecompletion' => 1));
        $lti = $this->getDataGenerator()->create_module('lti', array('course' => $course->id),
                                                            array('completion' => 2, 'completionview' => 1));
        $context = context_module::instance($lti->cmid);
        $cm = get_coursemodule_from_instance('lti', $lti->id);

        // Trigger and capture the event.
        $sink = $this->redirectEvents();

        lti_view($lti, $course, $cm, $context);

        $events = $sink->get_events();
        // 2 additional events thanks to completion.
        $this->assertCount(4, $events); // Totara: There is one more event.
        $event = array_shift($events);

        // Checking that the event contains the expected values.
        $this->assertInstanceOf('\mod_lti\event\course_module_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $moodleurl = new \moodle_url('/mod/lti/view.php', array('id' => $cm->id));
        $this->assertEquals($moodleurl, $event->get_url());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());

        // Check completion status.
        $completion = new completion_info($course);
        $completiondata = $completion->get_data($cm);
        $this->assertEquals(1, $completiondata->completionstate);

    }

    /**
     * Test deleting LTI instance.
     */
    public function test_lti_delete_instance() {

        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course(array());
        $lti = $this->getDataGenerator()->create_module('lti', array('course' => $course->id));
        $cm = get_coursemodule_from_instance('lti', $lti->id);

        // Must not throw notices.
        course_delete_module($cm->id);
    }

    /**
     * Ensure that assign_get_completion_state reflects the correct status at each point.
     */
    public function test_lti_get_completion_state(): void
    {
        $generator = self::getDataGenerator();
        $course = $generator->create_course();
        $learner = $generator->create_and_enrol($course);

        $lti_generator = lti_generator::instance();
        $lti = $lti_generator->create_instance([
            'course' => $course->id,
            'gradepass' => 50,
            'completionpass' => 1,
        ]);

        $cm = get_coursemodule_from_instance('lti', $lti->id, $course->id);
        $grade_params = [
            'courseid' => $course->id,
            'itemtype' => 'mod',
            'itemmodule' => 'lti',
            'iteminstance' => $cm->instance,
            'itemnumber' => 0,
        ];
        $item = grade_item::fetch($grade_params);
        $grade_generator = grade_generator::instance();

        // With no grade set, completion should be false.
        self::assertFalse(lti_get_completion_state($course, $cm, $learner->id, false));

        // Submit a grade that is below the grade to pass.
        $grade_generator->new_grade_for_item($item->id, 30, $learner);
        self::assertFalse(lti_get_completion_state($course, $cm, $learner->id, false));

        // Submit a passing grade.
        $grade_generator->new_grade_for_item($item->id, 70, $learner);
        self::assertTrue(lti_get_completion_state($course, $cm, $learner->id, false));
    }


    /**
     * When completionpass setting is disabled, make sure completion is not affected.
     */
    public function test_lti_get_completion_state_when_completionpass_disabled(): void {
        $generator = self::getDataGenerator();
        $course = $generator->create_course();
        $learner = $generator->create_and_enrol($course);

        $lti_generator = lti_generator::instance();
        $lti = $lti_generator->create_instance([
            'course' => $course->id,
            'gradepass' => 50,
            'completionpass' => 0,
        ]);

        $cm = get_coursemodule_from_instance('lti', $lti->id, $course->id);
        $grade_params = [
            'courseid' => $course->id,
            'itemtype' => 'mod',
            'itemmodule' => 'lti',
            'iteminstance' => $cm->instance,
            'itemnumber' => 0,
        ];
        $item = grade_item::fetch($grade_params);
        $grade_generator = grade_generator::instance();

        // With no grade set, completion should be false.
        self::assertFalse(lti_get_completion_state($course, $cm, $learner->id, false));

        // Submit a grade that is below the grade to pass.
        $grade_generator->new_grade_for_item($item->id, 30, $learner);
        self::assertFalse(lti_get_completion_state($course, $cm, $learner->id, false));

        // Submit a passing grade, still not complete.
        $grade_generator->new_grade_for_item($item->id, 70, $learner);
        self::assertFalse(lti_get_completion_state($course, $cm, $learner->id, false));
    }
}
