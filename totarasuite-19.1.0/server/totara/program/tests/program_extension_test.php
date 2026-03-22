<?php
/*
 * This file is part of Totara Learn
 *
 * Copyright (C) 2018 onwards Totara Learning Solutions LTD
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
 * @author Carl Anderson <carl.anderson@totaralearning.com>
 * @package totara_program
 */

use totara_program\assignments\assignments;
use totara_program\program;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/totara/program/lib.php');
require_once($CFG->dirroot . '/totara/reportbuilder/tests/reportcache_advanced_testcase.php');

/**
 * Lib test for prog_process_extension to ensure correct behaviour of extensions
 */
class totara_program_program_extension_test extends reportcache_advanced_testcase {
    private $user, $course, $module;


    protected function setUp(): void {
        global $DB, $CFG;

        parent::setUp();
        $CFG->enablecompletion = true;

        $job_generator = \totara_job\testing\generator::instance();

        $this->course = \core\testing\generator::instance()->create_course(array('enablecompletion' => true));
        $this->user = $job_generator->create_user_and_job([])[0];
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $this->assertNotEmpty($studentrole);

        // Get manual enrolment plugin and enrol user.
        require_once($CFG->dirroot.'/enrol/manual/locallib.php');
        $manplugin = enrol_get_plugin('manual');
        $maninstance = $DB->get_record('enrol', array('courseid' => $this->course->id, 'enrol' => 'manual'), '*', MUST_EXIST);
        $manplugin->enrol_user($maninstance, $this->user->id, $studentrole->id);
        $this->assertEquals(1, $DB->count_records('user_enrolments'));

        $completionsettings = array('completion' => COMPLETION_TRACKING_AUTOMATIC, 'completionview' => 1);
        $this->module = $this->getDataGenerator()->create_module('forum', array('course' => $this->course->id), $completionsettings);
    }

    protected function tearDown(): void {
        $this->user = null;
        $this->course = null;
        $this->module = null;
        parent::tearDown();
    }

    public function test_granting_extension_extends_due_date() {
        global $DB;

        $this->setAdminUser();

        $program_generator = \totara_program\testing\generator::instance();

        //Create a program
        $programid = \totara_program\testing\generator::instance()->legacy_create_program()->id;

        //Assign User to program
        $program_generator->assign_to_program($programid, assignments::ASSIGNTYPE_INDIVIDUAL, $this->user->id);

        //Create a program completion
        $duedate = time();
        $prog_completion = new stdClass();
        $prog_completion->programid = $programid;
        $prog_completion->userid = $this->user->id;
        $prog_completion->status = program::STATUS_PROGRAM_INCOMPLETE;
        $prog_completion->timedue = $duedate;
        $prog_completion->timecompleted = 0;
        $prog_completion->organisationid = 0;
        $prog_completion->positionid = 0;

        prog_write_completion($prog_completion);

        //Request extension for user
        $extensiondate = strtotime('+1 day', $duedate);

        $extension = new stdClass;
        $extension->programid = $programid;
        $extension->userid = $this->user->id;
        $extension->extensiondate = $extensiondate;
        $extension->extensionreason = ""; //Ehhh we don't need a reason
        $extension->status = 0;

        $extensionid = $DB->insert_record('prog_extension', $extension);
        $output1 = prog_process_extensions(array($extensionid => program::PROG_EXTENSION_GRANT), array($extensionid => 'works'));
        $prog_completion = prog_load_completion($extension->programid, $extension->userid);
        $extension = $DB->get_record('prog_extension', array('id' => $extensionid));

        $this->assertEquals($extensiondate, $prog_completion->timedue);
        $this->assertEquals('works', $extension->reasonfordecision);
        $this->assertEquals(program::PROG_EXTENSION_GRANT, $extension->status);
        $this->assertEquals(1, $output1['total']);
        $this->assertEquals(0, $output1['failcount']);
        $this->assertEquals(0, $output1['updatefailcount']);

        // If we try to do the same update again, it will fail and the data will not be updated.
        $extension->extensiondate = $extensiondate + DAYSECS * 10;
        $DB->update_record('prog_extension', $extension);
        $output2 = prog_process_extensions(array($extensionid => program::PROG_EXTENSION_DENY), array($extensionid => 'fails'));
        $prog_completion = prog_load_completion($extension->programid, $extension->userid);
        $extension = $DB->get_record('prog_extension', array('id' => $extensionid));

        // Completion due date, extension status and reasonofdecision have not been changed from earlier.
        $this->assertEquals($extensiondate, $prog_completion->timedue);
        $this->assertEquals('works', $extension->reasonfordecision);
        $this->assertEquals(program::PROG_EXTENSION_GRANT, $extension->status);
        $this->assertEquals(0, $output2['total']);
        $this->assertEquals(1, $output2['failcount']);
        $this->assertEquals(1, $output2['updatefailcount']);

    }
}