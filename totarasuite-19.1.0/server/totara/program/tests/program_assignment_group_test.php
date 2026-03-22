<?php
/**
 * This file is part of Totara Learn
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
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package totara_program
 */

use core\collection;
use core\entity\user;
use core\event\base as event;
use core_phpunit\event_sink;
use core_phpunit\testcase;
use totara_program\assignment\base;
use totara_program\assignment\cohort;
use totara_program\assignment\group;
use totara_program\assignments\assignments;
use totara_program\content\program_content;
use totara_program\content\course_sets\multi_course_set;
use totara_program\entity\program_user_assignment;
use totara_program\event\program_assigned;
use totara_program\program;
use totara_program\task\user_assignments_task;
use totara_program\testing\generator;
use totara_program\utils;

/**
 * @group totara_program
 */
class totara_program_program_assignment_group_test extends testcase {

    private $generator = null;
    private $programgenerator = null;
    private $programs;
    private $users;
    private $groups;
    private $group_users;

    protected function tearDown(): void {
        $this->generator = null;
        $this->programgenerator = null;

        $this->programs = null;
        $this->groups = null;
        $this->users = null;

        parent::tearDown();
    }

    private function create_group_data() {
        $this->generator = $this->getDataGenerator();
        $this->programgenerator = $this->generator->get_plugin_generator('totara_program');
        $this->programs[1] = $this->programgenerator->create_program();
        $this->users[1] = $this->generator->create_user();
        $this->groups[1] = $this->programgenerator->create_group();
        $this->group_users[1] = $this->programgenerator->create_group_user($this->users[1]->id, $this->groups[1]->id);
    }

    public function test_show_in_ui() {
        $this->create_group_data();
        $assignment = base::create_from_instance_id($this->programs[1]->id, assignments::ASSIGNTYPE_GROUP, $this->groups[1]->id);
        self::assertTrue($assignment::show_in_ui());
    }

    public function test_can_be_updated() {
        // Admin can update group assignments.
        self::setAdminUser();
        $this->create_group_data();
        $assignment = base::create_from_instance_id($this->programs[1]->id, assignments::ASSIGNTYPE_GROUP, $this->groups[1]->id);
        self::assertTrue($assignment::can_be_updated($this->programs[1]->id));

        // Random user cannot update group assignments.
        self::setUser($this->users[1]);
        self::assertFalse($assignment::can_be_updated($this->programs[1]->id));
    }

    public function test_create_from_instance_id() {
        global $DB;

        $this->setAdminUser();

        $this->create_group_data();

        $assignment = base::create_from_instance_id($this->programs[1]->id, assignments::ASSIGNTYPE_GROUP, $this->groups[1]->id);
        $assignment->save();

        $this->assertInstanceOf(group::class, $assignment);

        $reflection = new ReflectionClass(group::class);
        $property = $reflection->getProperty('instanceid');
        $property->setAccessible(true);
        $this->assertEquals($this->groups[1]->id, $property->getValue($assignment));

        $assignment_record = $DB->get_record('prog_assignment', ['programid' => $this->programs[1]->id, 'assignmenttype' => assignments::ASSIGNTYPE_GROUP, 'assignmenttypeid' => $this->groups[1]->id]);
        $this->assertEquals('-1', $assignment_record->completiontime);
        $this->assertEquals(0, $assignment_record->completionevent);
        $this->assertEquals(0, $assignment_record->completioninstance);

        $completion_record = $DB->get_records('prog_completion', ['programid' => $this->programs[1]->id, 'userid' => $this->users[1]->id, 'coursesetid' => 0]);
        $record = reset($completion_record);
        $this->assertEquals('-1', $record->timedue);
        $this->assertEquals(0, $record->status);
    }

    public function test_create_from_group_id() {
        global $DB;

        $this->setAdminUser();
        $this->create_group_data();

        $type = assignments::ASSIGNTYPE_GROUP;
        $assignment = base::create_from_instance_id(
            $this->programs[1]->id, $type, $this->groups[1]->id
        );
        $assignment->save();

        $group_id = $assignment->get_instanceid();
        $group = group::create_from_group_id($group_id);

        self::assertEquals($assignment->get_id(), $group->get_id());
        self::assertEquals($group_id, $group->get_instanceid());
        self::assertEquals($type, $group->get_type());
    }

    public function test_create_from_id() {
        global $DB;

        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $programgenerator = $generator->get_plugin_generator('totara_program');

        $user1 = $generator->create_user();
        $program1 = $programgenerator->create_program();

        $programgenerator->assign_to_program($program1->id, assignments::ASSIGNTYPE_GROUP, $user1->id);

        // There should be only one record
        $this->assertEquals(1, $DB->count_records('prog_assignment'));

        $assignments = $DB->get_records('prog_assignment', ['programid' => $program1->id]);
        $record = reset($assignments);

        $assignment = group::create_from_id($record->id);

        $this->assertInstanceOf(group::class, $assignment);

        $reflection = new ReflectionClass(group::class);
        $property = $reflection->getProperty('typeid');
        $property->setAccessible(true);
        $this->assertEquals(assignments::ASSIGNTYPE_GROUP, $property->getValue($assignment));

        $property = $reflection->getProperty('instanceid');
        $property->setAccessible(true);
        $this->assertEquals($user1->id, $property->getValue($assignment));

        // Fix once generator is fixed
        $completion_record = $DB->get_records('prog_completion');
    }

    public function test_get_type() {
        global $DB;

        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $programgenerator = $generator->get_plugin_generator('totara_program');

        $user1 = $generator->create_user();
        $program1 = $programgenerator->create_program();

        $programgenerator->assign_to_program($program1->id, assignments::ASSIGNTYPE_GROUP, $user1->id);

        $assignments = $DB->get_records('prog_assignment', ['programid' => $program1->id]);
        $record = reset($assignments);
        $assignment = group::create_from_id($record->id);
        $this->assertEquals(assignments::ASSIGNTYPE_GROUP, $assignment->get_type());
    }

    public function test_get_name() {
        global $DB;

        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $programgenerator = $generator->get_plugin_generator('totara_program');

        $group1 = $programgenerator->create_group();
        $program1 = $programgenerator->create_program();

        $programgenerator->assign_to_program($program1->id, assignments::ASSIGNTYPE_GROUP, $group1->id);

        $assignments = $DB->get_records('prog_assignment', ['programid' => $program1->id]);
        $record = reset($assignments);
        $assignment = group::create_from_id($record->id);

        $this->assertEquals($group1->name, $assignment->get_name());
    }

    public function test_get_programid() {
        global $DB;

        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $programgenerator = $generator->get_plugin_generator('totara_program');

        $group1 = $programgenerator->create_group();
        $program1 = $programgenerator->create_program();

        $programgenerator->assign_to_program($program1->id, assignments::ASSIGNTYPE_GROUP, $group1->id);

        $assignments = $DB->get_records('prog_assignment', ['programid' => $program1->id]);
        $record = reset($assignments);
        $assignment = group::create_from_id($record->id);

        $this->assertEquals($program1->id, $assignment->get_programid());
    }

    public function test_includechildren() {
        global $DB;

        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $programgenerator = $generator->get_plugin_generator('totara_program');

        $group1 = $programgenerator->create_group();
        $program1 = $programgenerator->create_program();

        $programgenerator->assign_to_program($program1->id, assignments::ASSIGNTYPE_GROUP, $group1->id);

        $assignments = $DB->get_records('prog_assignment', ['programid' => $program1->id]);
        $record = reset($assignments);
        $assignment = group::create_from_id($record->id);

        $this->assertEquals($program1->id, $assignment->get_programid());
    }

    /**
     *
     */
    public function test_get_duedate_fixed_date() {
        global $DB;

        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $programgenerator = $generator->get_plugin_generator('totara_program');

        $group1 = $programgenerator->create_group();
        $program1 = $programgenerator->create_program();

        $programgenerator->assign_to_program($program1->id, assignments::ASSIGNTYPE_GROUP, $group1->id, null, true);

        $hour = 13;
        $minute = 30;
        $timedue = new DateTime('2 weeks'); // 2 weeks from now
        $timedue->setTime($hour, $minute); // Set time to 1:30pm

        // Set completion values.assignment->
        $completiontime = $timedue->getTimestamp();
        $completiontimestring = $timedue->format('d/m/Y');
        $completionevent = assignments::COMPLETION_EVENT_NONE;
        $completioninstance = 0;
        $includechildren = null;

        $grouptype = 8;

        $data = new stdClass();
        $data->id = $program1->id;
        $data->item = array($grouptype => array($group1->id => 1));
        $data->completiontime = array($grouptype => array($group1->id => $completiontimestring));
        $data->completiontimehour = array($grouptype => array($group1->id => $hour));
        $data->completiontimeminute = array($grouptype => array($group1->id => $minute));
        $data->completionevent = array($grouptype => array($group1->id => $completionevent));
        $data->completioninstance = array($grouptype => array($group1->id => $completioninstance));
        $data->includechildren = array ($grouptype => array($group1->id => $includechildren));

        $assignmenttoprog = assignments::factory($grouptype);
        $assignmenttoprog->update_assignments($data, false);

        // Set time due in completion record
        $program1->set_timedue($group1->id, $completiontime);

        $program = new program($program1->id);
        $program->update_learner_assignments(true);

        // Get assignment
        $assignments = $DB->get_records('prog_assignment', ['programid' => $program1->id]);
        $record = reset($assignments);
        $assignment = group::create_from_id($record->id);

        $completiontimestring = userdate($timedue->getTimestamp(), get_string('strfdateattime', 'langconfig'));
        $expectedstring = get_string('completebytime', 'totara_program', $completiontimestring);

        $result = $assignment->get_duedate();

        $expected = new \stdClass();
        $expected->string = $expectedstring;
        $expected->changeable = true;
        $this->assertEquals($expected, $result);
    }

    public function test_get_duedate_first_login() {
        global $DB;

        $this->setAdminUser();

        $this->create_group_data();

        $this->programgenerator->assign_to_program($this->programs[1]->id, assignments::ASSIGNTYPE_GROUP, $this->users[1]->id);

        $assignment_record = $DB->get_record('prog_assignment', ['programid' => $this->programs[1]->id, 'assignmenttype' => assignments::ASSIGNTYPE_GROUP, 'assignmenttypeid' => $this->users[1]->id]);

        $grouptype = assignments::ASSIGNTYPE_GROUP;
        $completionevent = assignments::COMPLETION_EVENT_FIRST_LOGIN;
        $completiontime = '2 ' . utils::TIME_SELECTOR_WEEKS;

        $data = new stdClass();
        $data->id = $this->programs[1]->id;
        $data->item = array($grouptype => array($this->users[1]->id => 1));
        $data->completiontime = array($grouptype => array($this->users[1]->id => $completiontime));
        $data->completionevent = array($grouptype => array($this->users[1]->id => $completionevent));

        $assignmenttoprog = assignments::factory($grouptype);
        $assignmenttoprog->update_assignments($data, false);

        $program = new program($this->programs[1]->id);
        $program->update_learner_assignments(true);

        $assignment = base::create_from_id($assignment_record->id);
        // We need to trim result since first login doesn't have and instance

        $result = $assignment->get_duedate();
        $result->string = trim($result->string);

        $expected = new \stdClass();
        $expected->string = 'Complete within 2 Week(s) of First login';
        $expected->changeable = true;
        $this->assertEquals($expected, $result);
    }

    public function test_set_duedate_static_date() {
        global $DB;

        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $programgenerator = $generator->get_plugin_generator('totara_program');

        $user1 = $generator->create_user();
        $group1 = $programgenerator->create_group();
        $program1 = $programgenerator->create_program();
        $group_user1 = $programgenerator->create_group_user($user1->id, $group1->id);

        $programgenerator->assign_to_program($program1->id, assignments::ASSIGNTYPE_GROUP, $group1->id, null, true);

        $timedue = new DateTime('2 weeks'); // 2 weeks from now
        $timedue->setTime(13, 30); // Set time to 1:30pm

        // Set completion values.
        $completiontime = $timedue->getTimestamp();

        // Get assignment
        $assignments = $DB->get_records('prog_assignment', ['programid' => $program1->id]);
        $record = reset($assignments);
        $assignment = group::create_from_id($record->id);

        $progassign_record = $DB->get_record('prog_assignment', ['id' => $assignment->get_id()]);
        $progcompletion_record = $DB->get_record('prog_completion', ['programid' => $program1->id, 'userid' => $user1->id, 'coursesetid' => 0]);
        $this->assertEquals(0, $progassign_record->completionevent);
        $this->assertEquals(0, $progassign_record->completioninstance);
        $this->assertEquals(-1, $progassign_record->completiontime);

        // Set fixed due date first
        $assignment->set_duedate($completiontime);

        $progassign_record = $DB->get_record('prog_assignment', ['id' => $assignment->get_id()]);
        $progcompletion_record = $DB->get_record('prog_completion', ['programid' => $program1->id, 'userid' => $user1->id, 'coursesetid' => 0]);
        $this->assertEquals(0, $progassign_record->completionevent);
        $this->assertEquals(0, $progassign_record->completioninstance);
        $this->assertEquals($completiontime, $progassign_record->completiontime);

        $this->assertEquals($completiontime, $progcompletion_record->timedue);


        // Set relative date on Course completion
        $course1 = $generator->create_course();

        $assignment->set_duedate($completiontime, 4, $course1->id);
        $progassign_record = $DB->get_record('prog_assignment', ['id' => $assignment->get_id()]);
        $progcompletion_record = $DB->get_record('prog_completion', ['programid' => $program1->id, 'userid' => $user1->id, 'coursesetid' => 0]);

        $this->assertEquals(4, $progassign_record->completionevent);
        $this->assertEquals($course1->id, $progassign_record->completioninstance);
        // Check the completion record for the user
        $this->assertEquals($completiontime, $progcompletion_record->timedue);
    }

    public function test_set_due_date_based_on_first_login() {
        global $DB;

        $this->setAdminUser();
        $this->create_group_data();

        $this->programgenerator->assign_to_program($this->programs[1]->id, assignments::ASSIGNTYPE_GROUP, $this->groups[1]->id, null, true);

        $completionperiod = 1209600; // Duration of 2 weeks
        $completionevent = assignments::COMPLETION_EVENT_FIRST_LOGIN;

        $assignment_record = $DB->get_record('prog_assignment', ['programid' => $this->programs[1]->id, 'assignmenttype' => assignments::ASSIGNTYPE_GROUP, 'assignmenttypeid' => $this->groups[1]->id]);
        $assignment = group::create_from_id($assignment_record->id);

        $this->assertEquals(-1, $assignment_record->completiontime);
        $this->assertEquals(0, $assignment_record->completionevent);
        $this->assertEquals(0, $assignment_record->completioninstance);

        $completion_record = $DB->get_record('prog_completion', ['programid' => $this->programs[1]->id, 'userid' => $this->users[1]->id, 'coursesetid' => 0]);
        $this->assertEquals(-1, $completion_record->timedue);

        $assignment->set_duedate($completionperiod, $completionevent);

        $assignment_record = $DB->get_record('prog_assignment', ['programid' => $this->programs[1]->id, 'assignmenttype' => assignments::ASSIGNTYPE_GROUP, 'assignmenttypeid' => $this->groups[1]->id]);
        $this->assertEquals($completionperiod, $assignment_record->completiontime);
        $this->assertEquals(assignments::COMPLETION_EVENT_FIRST_LOGIN, $assignment_record->completionevent);
        $this->assertEquals(0, $assignment_record->completioninstance);

        $completion_record = $DB->get_record('prog_completion', ['programid' => $this->programs[1]->id, 'userid' => $this->users[1]->id, 'coursesetid' => 0]);
        $this->assertEquals(-1, $completion_record->timedue);

        // Create a second program this time with some content
        $this->programs[2] = $this->programgenerator->create_program();
        $this->groups[2] = $this->programgenerator->create_group();
        $this->group_users[2] = $this->programgenerator->create_group_user($this->users[1]->id, $this->groups[2]->id);

        $course1 = $this->generator->create_course();
        $course2 = $this->generator->create_course();

        $progcontent = new program_content($this->programs[2]->id);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);

        $coursesets = $progcontent->get_course_sets();

        $coursedata = new stdClass();
        $coursedata->{$coursesets[0]->get_set_prefix() . 'courseid'} = $course1->id;
        $progcontent->add_course(1, $coursedata);
        $coursedata->{$coursesets[0]->get_set_prefix() . 'courseid'} = $course2->id;
        $progcontent->add_course(1, $coursedata);

        $coursesets[0]->timeallowed = 1209600; // 2 Weeks
        $coursesets[0]->save_set();

        $this->programgenerator->assign_to_program($this->programs[2]->id, assignments::ASSIGNTYPE_GROUP, $this->groups[2]->id, null, true);

        $completionperiod = 604800; // 1 Week
        $completionevent = assignments::COMPLETION_EVENT_FIRST_LOGIN;

        $assignment_record = $DB->get_record('prog_assignment', ['programid' => $this->programs[2]->id, 'assignmenttype' => assignments::ASSIGNTYPE_GROUP, 'assignmenttypeid' => $this->groups[2]->id]);
        $assignment = group::create_from_id($assignment_record->id);

        $this->assertEquals(-1, $assignment_record->completiontime);
        $this->assertEquals(0, $assignment_record->completionevent);
        $this->assertEquals(0, $assignment_record->completioninstance);

        $completion_record = $DB->get_record('prog_completion', ['programid' => $this->programs[2]->id, 'userid' => $this->users[1]->id, 'coursesetid' => 0]);
        $this->assertEquals(-1, $completion_record->timedue);

        $assignment->set_duedate($completionperiod, $completionevent);

        $assignment_record = $DB->get_record('prog_assignment', ['programid' => $this->programs[2]->id, 'assignmenttype' => assignments::ASSIGNTYPE_GROUP, 'assignmenttypeid' => $this->groups[2]->id]);
        $this->assertEquals($completionperiod, $assignment_record->completiontime);
        $this->assertEquals(assignments::COMPLETION_EVENT_FIRST_LOGIN, $assignment_record->completionevent);
        $this->assertEquals(0, $assignment_record->completioninstance);

        $completion_record = $DB->get_record('prog_completion', ['programid' => $this->programs[2]->id, 'userid' => $this->users[1]->id, 'coursesetid' => 0]);
        $this->assertEquals(-1, $completion_record->timedue);
    }

    public function test_set_due_date_based_on_prog_enrolment() {
        global $DB;

        $this->setAdminUser();
        $this->create_group_data();

        $this->programgenerator->assign_to_program($this->programs[1]->id, assignments::ASSIGNTYPE_GROUP, $this->groups[1]->id, null, true);

        $completionperiod_amount = 1;
        $completionperiod_unit = utils::TIME_SELECTOR_DAYS;
        $completionevent = assignments::COMPLETION_EVENT_ENROLLMENT_DATE;

        $assignment_record = $DB->get_record('prog_assignment', ['programid' => $this->programs[1]->id, 'assignmenttype' => assignments::ASSIGNTYPE_GROUP, 'assignmenttypeid' => $this->groups[1]->id]);
        $assignment = group::create_from_id($assignment_record->id);

        $course1 = $this->generator->create_course();
        $course2 = $this->generator->create_course();

        $progcontent = new program_content($this->programs[1]->id);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);

        $uniqueid = 'multiset';
        $multicourseset1 = new totara_program\content\course_sets\multi_course_set($this->programs[1]->id, null, $uniqueid);

        $coursedata = new stdClass();
        $coursedata->{$uniqueid . 'courseid'} = $course1->id;
        $multicourseset1->add_course($coursedata);
        $coursedata->{$uniqueid . 'courseid'} = $course2->id;
        $multicourseset1->add_course($coursedata);

        // Set certifpath so exceptions are calculated correctly
        $multicourseset1->certifpath = CERTIFPATH_STD;
        $multicourseset1->timeallowed = 1209600; // 2 Weeks
        $multicourseset1->save_set();

        $user_assignment_record = $DB->get_record('prog_user_assignment', ['programid' => $this->programs[1]->id, 'userid' => $this->users[1]->id, 'assignmentid' => $assignment->get_id()]);

        $assignment->set_duedate(0, $completionevent, 0, $completionperiod_amount, $completionperiod_unit);

        $assignment_record = $DB->get_record('prog_assignment', ['programid' => $this->programs[1]->id, 'assignmenttype' => assignments::ASSIGNTYPE_GROUP, 'assignmenttypeid' => $this->groups[1]->id]);
        $user_assignment_record = $DB->get_record('prog_user_assignment', ['programid' => $this->programs[1]->id, 'userid' => $this->users[1]->id, 'assignmentid' => $assignment_record->id]);
        $completion_record = $DB->get_record('prog_completion', ['programid' => $this->programs[1]->id, 'userid' => $this->users[1]->id, 'coursesetid' => 0]);
        $coursesets = $DB->get_record('prog_courseset', ['programid' => $this->programs[1]->id]);

        $this->assertEquals(1, $user_assignment_record->exceptionstatus);
    }

    public function test_remove() {
        global $DB;

        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $programgenerator = $generator->get_plugin_generator('totara_program');

        $user1 = $generator->create_user();
        $user2 = $generator->create_user();
        $group1 = $programgenerator->create_group();
        $group2 = $programgenerator->create_group();
        $group_user1 = $programgenerator->create_group_user($user1->id, $group1->id);
        $group_user2 = $programgenerator->create_group_user($user2->id, $group2->id);
        $program1 = $programgenerator->create_program();

        $programgenerator->assign_to_program($program1->id, assignments::ASSIGNTYPE_GROUP, $group1->id, null, true);
        $programgenerator->assign_to_program($program1->id, assignments::ASSIGNTYPE_GROUP, $group2->id, null, true);

        $group1_prog_assignment = $DB->get_record('prog_assignment', ['programid' => $program1->id, 'assignmenttype' => assignments::ASSIGNTYPE_GROUP, 'assignmenttypeid' => $group1->id]);
        $group1_assignment = group::create_from_id($group1_prog_assignment->id);
        $group2_prog_assignment = $DB->get_record('prog_assignment', ['programid' => $program1->id, 'assignmenttype' => assignments::ASSIGNTYPE_GROUP, 'assignmenttypeid' => $group2->id]);
        $group2_assignment = group::create_from_id($group2_prog_assignment->id);

        // Check we have some db records
        $this->assertCount(2, $DB->get_records('prog_assignment', ['programid' => $program1->id]));
        $this->assertCount(2, $DB->get_records('prog_completion', ['programid' => $program1->id]));
        $this->assertCount(2, $DB->get_records('prog_group'));
        $this->assertCount(2, $DB->get_records('prog_group_user'));

        $group1_assignment->remove();

        // Check the right records have been deleted.
        $this->assertCount(1, $DB->get_records('prog_assignment', ['programid' => $program1->id]));
        $this->assertCount(1, $DB->get_records('prog_assignment', ['assignmenttypeid' => $group2->id]));
        $this->assertCount(1, $DB->get_records('prog_completion', ['programid' => $program1->id]));
        $this->assertCount(1, $DB->get_records('prog_group'));
        $this->assertCount(1, $DB->get_records('prog_group', ['id' => $group2->id]));
        $this->assertCount(1, $DB->get_records('prog_group_user'));
        $this->assertCount(1, $DB->get_records('prog_group_user', ['id' => $group_user2->id]));
    }


    public function test_update_user_assignments() {
        global $DB;

        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $programgenerator = $generator->get_plugin_generator('totara_program');

        $user1 = $generator->create_user();
        $user2 = $generator->create_user();
        $group1 = $programgenerator->create_group();
        $group2 = $programgenerator->create_group();
        $group_user1 = $programgenerator->create_group_user($user1->id, $group1->id);
        $group_user2 = $programgenerator->create_group_user($user2->id, $group2->id);
        $program1 = $programgenerator->create_program();

        $programgenerator->assign_to_program($program1->id, assignments::ASSIGNTYPE_GROUP, $group1->id, null, true);
        $programgenerator->assign_to_program($program1->id, assignments::ASSIGNTYPE_GROUP, $group2->id, null, true);

        $completion_record = $DB->get_record('prog_completion', ['programid' => $program1->id, 'coursesetid' => 0, 'userid' => $user1->id]);

        $this->assertNotFalse($completion_record);
        $this->assertEquals(-1, $completion_record->timedue);
    }

    public function test_user_assignment_records() {
        global $DB;

        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $programgenerator = $generator->get_plugin_generator('totara_program');

        $user1 = $generator->create_user();
        $group1 = $programgenerator->create_group();
        $group_user1 = $programgenerator->create_group_user($user1->id, $group1->id);
        $program1 = $programgenerator->create_program();

        $programgenerator->assign_to_program($program1->id, assignments::ASSIGNTYPE_GROUP, $group1->id);

        $record = $DB->get_record('prog_assignment', ['programid' => $program1->id, 'assignmenttype' => assignments::ASSIGNTYPE_GROUP, 'assignmenttypeid' => $group1->id]);
        $assignment = group::create_from_id($record->id);

        $this->assertCount(0, $DB->get_records('prog_completion', ['programid' => $program1->id]));

        $reflection = new ReflectionClass(group::class);
        $method = $reflection->getMethod('create_user_assignment_records');
        $method->setAccessible(true);
        $method->invokeArgs($assignment, []);

        $this->assertCount(1, $DB->get_records('prog_completion', ['programid' => $program1->id]));

        // The function assign_learners_bulk will die in a fire if called a second
        // time so we can't test this function will run again without issue.
    }

    public function test_ensure_category_loaded() {
        global $DB, $CFG;

        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $programgenerator = $generator->get_plugin_generator('totara_program');

        $group1 = $programgenerator->create_group();
        $program1 = $programgenerator->create_program();

        $programgenerator->assign_to_program($program1->id, assignments::ASSIGNTYPE_GROUP, $group1->id);

        $record = $DB->get_record('prog_assignment', ['programid' => $program1->id, 'assignmenttype' => assignments::ASSIGNTYPE_GROUP, 'assignmenttypeid' => $group1->id]);
        $assignment = group::create_from_id($record->id);

        $reflection = new ReflectionClass(group::class);
        $property = $reflection->getProperty('program');
        $property->setAccessible(true);
        $this->assertNull($property->getValue($assignment));

        $reflection = new ReflectionClass(group::class);
        $method = $reflection->getMethod('ensure_program_loaded');
        $method->setAccessible(true);
        $method->invokeArgs($assignment, []);

        $actual = $property->getValue($assignment);
        $this->assertNotNull($actual);
        $this->assertEquals($program1->id, $actual->id);
        $this->assertEquals($program1->fullname, $actual->fullname);
    }

    public function test_ensure_program_loaded() {
        global $DB, $CFG;

        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $programgenerator = $generator->get_plugin_generator('totara_program');

        $user1 = $generator->create_user();
        $group1 = $programgenerator->create_group();
        $group_user1 = $programgenerator->create_group_user($user1->id, $group1->id);
        $program1 = $programgenerator->create_program();

        $programgenerator->assign_to_program($program1->id, assignments::ASSIGNTYPE_GROUP, $group1->id);

        $record = $DB->get_record('prog_assignment', ['programid' => $program1->id, 'assignmenttype' => assignments::ASSIGNTYPE_GROUP, 'assignmenttypeid' => $group1->id]);
        $assignment = group::create_from_id($record->id);

        $reflection = new ReflectionClass(group::class);
        $property = $reflection->getProperty('category');
        $property->setAccessible(true);
        $this->assertNull($property->getValue($assignment));

        $reflection = new ReflectionClass(group::class);
        $method = $reflection->getMethod('ensure_category_loaded');
        $method->setAccessible(true);
        $method->invokeArgs($assignment, []);

        $actual = $property->getValue($assignment);
        $this->assertNotNull($actual);
        $this->assertInstanceOf('totara_program\assignments\categories\groups', $actual);

        $assignment = group::create_from_id($record->id);

        $this->assertEquals(1, $assignment->get_user_count());
        $this->assertEquals(assignments::ASSIGNTYPE_GROUP, $actual->id);
    }

    public function test_get_user_count() {
        global $DB;

        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $programgenerator = $generator->get_plugin_generator('totara_program');

        $user1 = $generator->create_user();
        $user2 = $generator->create_user();
        $user3 = $generator->create_user();
        $group1 = $programgenerator->create_group();
        $group2 = $programgenerator->create_group();
        $group_user1 = $programgenerator->create_group_user($user1->id, $group1->id);
        $group_user2 = $programgenerator->create_group_user($user2->id, $group2->id);
        $group_user3 = $programgenerator->create_group_user($user3->id, $group2->id);
        $program1 = $programgenerator->create_program();

        $programgenerator->assign_to_program($program1->id, assignments::ASSIGNTYPE_GROUP, $group1->id);
        $programgenerator->assign_to_program($program1->id, assignments::ASSIGNTYPE_GROUP, $group2->id);

        $assignid = $DB->get_field('prog_assignment', 'id', ['programid' => $program1->id, 'assignmenttype' => assignments::ASSIGNTYPE_GROUP, 'assignmenttypeid' => $group2->id]);
        $assignment = group::create_from_id($assignid);

        $this->assertEquals(2, $assignment->get_user_count());
    }

    public function test_group_assignment_delete() {
        global $DB;

        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $programgenerator = $generator->get_plugin_generator('totara_program');

        $user1 = $generator->create_user();
        $user2 = $generator->create_user();
        $user3 = $generator->create_user();
        $group1 = $programgenerator->create_group();
        $group2 = $programgenerator->create_group();
        $group_user1 = $programgenerator->create_group_user($user1->id, $group1->id);
        $group_user2 = $programgenerator->create_group_user($user2->id, $group2->id);
        $group_user3 = $programgenerator->create_group_user($user3->id, $group2->id);
        $program1 = $programgenerator->create_program();

        $programgenerator->assign_to_program($program1->id, assignments::ASSIGNTYPE_GROUP, $group1->id);
        $programgenerator->assign_to_program($program1->id, assignments::ASSIGNTYPE_GROUP, $group2->id);

        // Run cron
        $task = new user_assignments_task();
        $task->execute();

        $assignments = $DB->get_records('prog_assignment', ['programid' => $program1->id]);
        $this->assertCount(2, $assignments);
        $user_assignments = $DB->get_records('prog_user_assignment');
        $this->assertCount(3, $user_assignments);

        $assignment = $DB->get_record('prog_assignment', ['assignmenttype' => assignments::ASSIGNTYPE_GROUP, 'assignmenttypeid' => $group1->id]);
        $assignment = cohort::create_from_id($assignment->id);
        $assignment->remove();

        $assignments = $DB->get_records('prog_assignment', ['programid' => $program1->id]);
        $this->assertCount(1, $assignments);
        $user_assignments = $DB->get_records('prog_user_assignment');
        $this->assertCount(2, $user_assignments);
    }

    public function test_group_unassign_users() {
        global $DB;

        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $programgenerator = $generator->get_plugin_generator('totara_program');

        $user1 = $generator->create_user();
        $user2 = $generator->create_user();
        $user3 = $generator->create_user();
        // User4 exists in both groups
        $user4 = $generator->create_user();
        $group1 = $programgenerator->create_group();
        $group2 = $programgenerator->create_group();
        $programgenerator->create_group_user($user1->id, $group1->id);
        $programgenerator->create_group_user($user2->id, $group1->id);
        $programgenerator->create_group_user($user3->id, $group2->id);
        $programgenerator->create_group_user($user4->id, $group1->id);
        $programgenerator->create_group_user($user4->id, $group2->id);
        $program1 = $programgenerator->create_program();
        $programgenerator->assign_to_program($program1->id, assignments::ASSIGNTYPE_GROUP, $group1->id);
        $programgenerator->assign_to_program($program1->id, assignments::ASSIGNTYPE_GROUP, $group2->id);

        // Run cron
        $task = new user_assignments_task();
        $task->execute();
        $user_assignments = $DB->get_records('prog_user_assignment');
        $this->assertCount(5, $user_assignments);
        $user4_assignments = $DB->get_records('prog_user_assignment', ['userid' => $user4->id, 'programid' => $program1->id]);
        $this->assertCount(2, $user4_assignments);

        $group1_assignment = $DB->get_record('prog_assignment', ['assignmenttype' => assignments::ASSIGNTYPE_GROUP, 'assignmenttypeid' => $group1->id, 'programid' => $program1->id]);

        /** @var group $group1_model */
        $group1_model = group::create_from_record($group1_assignment);
        // Remove user1 and user4 from group1
        $group1_model->remove_users([$user1->id, $user4->id]);

        $user_assignments = $DB->get_records('prog_user_assignment');
        $this->assertCount(3, $user_assignments);
        $user4_assignments =  $DB->get_records('prog_user_assignment', ['userid' => $user4->id, 'programid' => $program1->id]);
        $this->assertCount(1, $user4_assignments);

        $group1_users = $DB->get_records('prog_group_user', ['prog_group_id' => $group1->id]);
        $this->assertCount(1, $group1_users);
        $group2_users = $DB->get_records('prog_group_user', ['prog_group_id' => $group2->id]);
        $this->assertCount(2, $group2_users);
    }

    public function test_group_assign_users() {
        $this->setAdminUser();

        $generator = generator::instance();
        $program = $generator->create_program();
        $group = $generator->create_group_assignment($program);
        self::assertEquals(0, $group->get_user_count());

        $new_user = fn(): user => new user(
            self::getDataGenerator()->create_user()
        );

        $uids_in_program = fn(): array => program_user_assignment::repository()
            ->where('programid', $program->id)
            ->where('assignmentid', $group->get_id())
            ->get()
            ->pluck('userid');

        // 1. add a brand new set of members.
        $users = collection::new(range(0, 5))->map(fn(int $i): user => $new_user());
        $expected_uids = $users->pluck('id');

        self::assertEqualsCanonicalizing(
            $expected_uids, $group->add_users($users)->get_users()->pluck('id')
        );

        self::assertEqualsCanonicalizing($expected_uids, $uids_in_program());

        // 2. add new users with already existing members.
        $users->append($new_user());
        $expected_uids = $users->pluck('id');

        self::assertEqualsCanonicalizing(
            $expected_uids, $group->add_users($users)->get_users()->pluck('id')
        );

        self::assertEqualsCanonicalizing($expected_uids, $uids_in_program());

        // 3. add another set of new users.
        $user = $new_user();
        $expected_uids[] = $user->id;

        self::assertEqualsCanonicalizing(
            $expected_uids, $group->add_users($user)->get_users()->pluck('id')
        );

        self::assertEqualsCanonicalizing($expected_uids, $uids_in_program());
    }

    public function test_can_user_self_enrol() {
        global $DB;

        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $programgenerator = $generator->get_plugin_generator('totara_program');

        $user1 = $generator->create_user();
        $group1 = $programgenerator->create_group();
        /** @var program $program1 */
        $program1 = $programgenerator->create_program(['availablefrom' => (new DateTime('2 weeks'))->getTimestamp()]);

        $programgenerator->assign_to_program($program1->id, assignments::ASSIGNTYPE_GROUP, $group1->id, null, true);

        $timedue = new DateTime('2 weeks'); // 2 weeks from now
        $timedue->setTime(13, 30); // Set time to 1:30pm

        // Get assignment
        $assignments = $DB->get_records('prog_assignment', ['programid' => $program1->id]);
        $record = reset($assignments);
        $assignment = group::create_from_id($record->id);

        // Program it not available
        $this->assertFalse($assignment->can_user_self_enrol($user1->id));
        $programentity = new totara_program\entity\program($program1->id);
        $programentity->availablefrom = (new DateTime('-2 weeks'))->getTimestamp();
        $programentity->available = program::AVAILABILITY_TO_STUDENTS;
        $programentity->save();
        $assignment = group::create_from_id($record->id);

        // Set future due date
        $assignment->set_duedate($timedue->getTimestamp());

        $this->assertTrue($assignment->can_user_self_enrol($user1->id));

        // Set past due date
        $timedue = new DateTime('-1 week');
        $assignment->set_duedate($timedue->getTimestamp());
        $this->assertFalse($assignment->can_user_self_enrol($user1->id));

        // Set relative date on Course completion
        $course1 = $generator->create_course();
        $assignment->set_duedate(0, assignments::COMPLETION_EVENT_COURSE_COMPLETION, $course1->id);
        $this->assertFalse($assignment->can_user_self_enrol($user1->id));

        // Test when there's not enough time
        $uniqueid = 'multiset';
        $multicourseset1 = new multi_course_set($program1->id, null, $uniqueid);
        $coursedata = new stdClass();
        $coursedata->{$uniqueid . 'courseid'} = $course1->id;
        $multicourseset1->add_course($coursedata);
        $multicourseset1->timeallowed = 20 * DAYSECS;
        $multicourseset1->set_certifpath(CERTIFPATH_STD);
        $multicourseset1->save_set();
        // reload assignment
        $assignment = group::create_from_id($record->id);
        $timedue = new DateTime('2 weeks'); // 2 weeks from now
        $assignment->set_duedate($timedue->getTimestamp());
        $this->assertFalse($assignment->can_user_self_enrol($user1->id));

        // The group doesn't allow self enrolment
        $group1->can_self_enrol = false;
        $group1->save();
        $multicourseset1->timeallowed = 2 * DAYSECS;
        $multicourseset1->save_set();
        $this->assertFalse($assignment->can_user_self_enrol($user1->id));
    }

    public function test_can_user_self_unenrol() {
        global $DB;

        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $programgenerator = $generator->get_plugin_generator('totara_program');

        $user1 = $generator->create_user();
        $group1 = $programgenerator->create_group();
        $programgenerator->create_group_user($user1->id, $group1->id);
        /** @var program $program1 */
        $program1 = $programgenerator->create_program(['availablefrom' => (new DateTime('2 weeks'))->getTimestamp()]);

        $programgenerator->assign_to_program($program1->id, assignments::ASSIGNTYPE_GROUP, $group1->id, null, true);

        $timedue = new DateTime('2 weeks'); // 2 weeks from now
        $timedue->setTime(13, 30); // Set time to 1:30pm

        // Get assignment
        $assignments = $DB->get_records('prog_assignment', ['programid' => $program1->id]);
        $record = reset($assignments);
        $assignment = group::create_from_id($record->id);

        // Program it not available
        $this->assertFalse($assignment->can_user_self_unenrol($user1->id));
        $programentity = new totara_program\entity\program($program1->id);
        $programentity->availablefrom = (new DateTime('-2 weeks'))->getTimestamp();
        $programentity->available = program::AVAILABILITY_TO_STUDENTS;
        $programentity->save();
        $assignment = group::create_from_id($record->id);

        $this->assertTrue($assignment->can_user_self_unenrol($user1->id));

        $programentity->availableuntil = (new DateTime('-1 weeks'))->getTimestamp();
        $programentity->available = program::AVAILABILITY_NOT_TO_STUDENTS;
        $programentity->save();
        // reload assignment
        $assignment = group::create_from_id($record->id);
        $this->assertFalse($assignment->can_user_self_unenrol($user1->id));

        // The group doesn't allow self enrolment
        $programentity->availablefrom = 0;
        $programentity->available = 0;
        $programentity->availableuntil = 0;
        $programentity->save();
        $group1->can_self_unenrol = false;
        $group1->save();
        $this->assertFalse($assignment->can_user_self_unenrol($user1->id));
    }
}
