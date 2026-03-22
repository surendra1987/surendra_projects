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
 * @author Alastair Munro <alastair.munro@totaralearning.com>
 * @package totara_program
 */

defined('MOODLE_INTERNAL') || die();

use totara_core\user_learning\item_helper as learning_item_helper;
use totara_program\program;
use totara_program\user_learning\item as program_item;
use totara_program\content\course_set;
use totara_program\content\program_content;

global $CFG;

class totara_program_user_learning_item_test extends \core_phpunit\testcase {

    private $generator;
    private $program_generator, $completion_generator;
    private $course1, $course2, $course3, $course4, $course5, $course6, $course7, $course8;
    private $program1, $program2, $program3, $program4;
    private $user1;

    protected function tearDown(): void {
        $this->generator = null;
        $this->program_generator = null;
        $this->completion_generator = null;
        $this->course1 = null;
        $this->course2 = null;
        $this->course3 = null;
        $this->course4 = null;
        $this->course5 = null;
        $this->course6 = null;
        $this->course7 = null;
        $this->course8 = null;
        $this->program1 = null;
        $this->program2 = null;
        $this->program3 = null;
        $this->program4 = null;
        $this->user1 = null;

        parent::tearDown();
    }

    public function setUp(): void {
        global $DB;

        parent::setUp();

        $this->generator = $this->getDataGenerator();
        $this->program_generator = $this->generator->get_plugin_generator('totara_program');
        $this->completion_generator = $this->getDataGenerator()->get_plugin_generator('core_completion');

        // Create some course.
        $this->course1 = $this->generator->create_course(array('enablecompletion' => true));
        $this->course2 = $this->generator->create_course(array('enablecompletion' => true));
        $this->course3 = $this->generator->create_course(array('enablecompletion' => true));
        $this->course4 = $this->generator->create_course(array('enablecompletion' => true));
        $this->course5 = $this->generator->create_course(array('enablecompletion' => true));
        $this->course6 = $this->generator->create_course(array('enablecompletion' => true));
        $this->course7 = $this->generator->create_course(array('enablecompletion' => true));
        $this->course8 = $this->generator->create_course(array('audiencevisible'=>COHORT_VISIBLE_ENROLLED));

        // Create some programs.
        $this->program1 = $this->program_generator->create_program(array('fullname' => 'Program 1'));
        $this->program2 = $this->program_generator->create_program(array('fullname' => 'Program 2'));
        $this->program3 = $this->program_generator->create_program(array('fullname' => 'Program 3'));
        $this->program4 = $this->program_generator->create_program(array('fullname' => 'Program 4', 'visible' => false));

        // Reload courses to get accurate data.
        // See note in totara/program/tests/program_content_test.php for more info.
        $this->course1 = $DB->get_record('course', array('id' => $this->course1->id));
        $this->course2 = $DB->get_record('course', array('id' => $this->course2->id));
        $this->course3 = $DB->get_record('course', array('id' => $this->course3->id));
        $this->course4 = $DB->get_record('course', array('id' => $this->course4->id));
        $this->course5 = $DB->get_record('course', array('id' => $this->course5->id));
        $this->course6 = $DB->get_record('course', array('id' => $this->course6->id));

        // Enable completion for courses.
        $this->completion_generator->enable_completion_tracking($this->course1);
        $this->completion_generator->enable_completion_tracking($this->course2);
        $this->completion_generator->enable_completion_tracking($this->course3);
        $this->completion_generator->enable_completion_tracking($this->course4);
        $this->completion_generator->enable_completion_tracking($this->course5);
        $this->completion_generator->enable_completion_tracking($this->course6);

        $this->user1 = $this->getDataGenerator()->create_user(array('fullname' => 'user1'));
    }

    public function test_all_programs() {
        global $CFG;

        // Assign user to 3 programs.
        $this->program_generator->assign_program($this->program1->id, array($this->user1->id));
        $this->program_generator->assign_program($this->program2->id, array($this->user1->id));
        $this->program_generator->assign_program($this->program3->id, array($this->user1->id));

        $program_items = program_item::all($this->user1->id);

        // Ensure we get the right number of programs.
        $this->assertCount(3, $program_items);

        // We need to manually include the file for reflection.
        require_once($CFG->dirroot . '/totara/program/classes/user_learning/item.php');

        // Reflection property so we can access program.
        $rp = new ReflectionProperty('totara_program\user_learning\item', 'program');
        $rp->setAccessible(true);

        $results = array();
        foreach ($program_items as $item) {
            $results[$rp->getValue($item)->id] = $rp->getValue($item)->fullname;
        }

        $this->assertEquals('Program 1', $results[$this->program1->id]);
        $this->assertEquals('Program 2', $results[$this->program2->id]);
        $this->assertEquals('Program 3', $results[$this->program3->id]);
    }

    public function test_one_program() {
        global $CFG;

        // Assign user to 3 programs.
        $this->program_generator->assign_program($this->program1->id, array($this->user1->id));
        $this->program_generator->assign_program($this->program2->id, array($this->user1->id));

        $program_item = program_item::one($this->user1->id, $this->program2->id);;

        // Make sure we only have one program.
        $this->assertNotEmpty($program_item);

        // Reflection property so we can access program.
        $rp = new ReflectionProperty('totara_program\user_learning\item', 'program');
        $rp->setAccessible(true);

        $prog = $rp->getValue($program_item);
        $this->assertEquals($this->program2->id, $prog->id);
        $this->assertEquals($this->program2->fullname, $prog->fullname);
    }

    public function test_all_programs_with_hidden_program() {
        global $CFG;

        // Assign user to 3 programs.
        $this->program_generator->assign_program($this->program1->id, array($this->user1->id));
        $this->program_generator->assign_program($this->program2->id, array($this->user1->id));
        $this->program_generator->assign_program($this->program3->id, array($this->user1->id));
        // Program 4 is not visible.
        $this->program_generator->assign_program($this->program4->id, array($this->user1->id));

        $program_items = program_item::all($this->user1->id);

        // Ensure we only get 3 programs.
        $this->assertCount(3, $program_items);

        // Reflection property so we can access program.
        $rp = new ReflectionProperty('totara_program\user_learning\item', 'program');
        $rp->setAccessible(true);

        $results = array();
        foreach ($program_items as $item) {
            $results[$rp->getValue($item)->id] = $rp->getValue($item)->fullname;
        }

        // Make sure we have the correct 3 programs (not program 4).
        $this->assertEquals('Program 1', $results[$this->program1->id]);
        $this->assertEquals('Program 2', $results[$this->program2->id]);
        $this->assertEquals('Program 3', $results[$this->program3->id]);
    }


    /**
     *
     */
    public function test_get_courseset_courses_multicourseset() {
        global $CFG;

        // Add content to program 1.
        $progcontent = new program_content($this->program1->id);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);

        $coursesets = $progcontent->get_course_sets();

        $coursedata = new stdClass();
        $coursedata->{$coursesets[0]->get_set_prefix() . 'courseid'} = $this->course1->id;
        $progcontent->add_course(1, $coursedata);

        $coursedata->{$coursesets[0]->get_set_prefix() . 'courseid'} = $this->course2->id;
        $progcontent->add_course(1, $coursedata);

        $coursedata->{$coursesets[1]->get_set_prefix() . 'courseid'} = $this->course3->id;
        $progcontent->add_course(2, $coursedata);

        $coursedata->{$coursesets[1]->get_set_prefix() . 'courseid'} = $this->course4->id;
        $progcontent->add_course(2, $coursedata);

        $progcontent->save_content();

        // Assign user to the program.
        $this->program_generator->assign_program($this->program1->id, array($this->user1->id));

        // Get the program item.
        $program_item = program_item::one($this->user1->id, $this->program1->id);

        $rm = new ReflectionMethod('totara_program\user_learning\item', 'get_courseset_courses');
        $rm->setAccessible(true);

        $courses = $rm->invoke($program_item);

        // We should have 4 courses, 2 in each courseset.
        $this->assertCount(4, $courses);
    }

    public function test_get_courseset_courses_recurrning_course() {
        // Add content to program 1.
        $progcontent = new program_content($this->program1->id);
        $progcontent->add_set(program_content::CONTENTTYPE_RECURRING);

        $coursesets = $progcontent->get_course_sets();

        // Program contains a single recurring course set with course1.
        $coursesets[0]->course = $this->course1;
        $progcontent->save_content();

        // Assign user to the program.
        $this->program_generator->assign_program($this->program1->id, array($this->user1->id));

        // Get the program item.
        $program_item = program_item::one($this->user1->id, $this->program1->id);

        $rm = new ReflectionMethod('totara_program\user_learning\item', 'get_courseset_courses');
        $rm->setAccessible(true);

        $courses = $rm->invoke($program_item);

        // We should have 1 course.
        $this->assertCount(1, $courses);


    }

    public function test_get_courseset_courses_competency_set() {
        // Add content to program 1.
        $progcontent = new program_content($this->program1->id);
        $progcontent->add_set(program_content::CONTENTTYPE_COMPETENCY);

        $coursesets = $progcontent->get_course_sets();

        $hierarchygenerator = $this->generator->get_plugin_generator('totara_hierarchy');
        $competencyframework = $hierarchygenerator->create_comp_frame(array());
        $competencydata = array('frameworkid' => $competencyframework->id);
        $competency = $hierarchygenerator->create_comp($competencydata);

        // Completions for courses 1,2 and 3 will be assigned to this competency.
        $course1evidenceid = $hierarchygenerator->assign_linked_course_to_competency($competency, $this->course1);
        $course2evidenceid = $hierarchygenerator->assign_linked_course_to_competency($competency, $this->course2);
        $course3evidenceid = $hierarchygenerator->assign_linked_course_to_competency($competency, $this->course3);

        // Add a competency to the competency courseset.
        $compdata = new stdClass();
        $compdata->{$coursesets[0]->get_set_prefix() . 'competencyid'} = $competency->id;
        $progcontent->add_competency(1, $compdata);

        $progcontent->save_content();

        // Assign user to the program.
        $this->program_generator->assign_program($this->program1->id, array($this->user1->id));

        // Get the program item.
        $program_item = program_item::one($this->user1->id, $this->program1->id);

        $rm = new ReflectionMethod('totara_program\user_learning\item', 'get_courseset_courses');
        $rm->setAccessible(true);

        $courses = $rm->invoke($program_item);

        // We should have 3 courses.
        $this->assertCount(3, $courses);
    }

    public function test_ensure_program_loaded() {
        // Assign user to the program.
        $this->program_generator->assign_program($this->program1->id, array($this->user1->id));

        // Get the program item.
        $program_item = program_item::one($this->user1->id, $this->program1->id);

        $rp = new ReflectionProperty('totara_program\user_learning\item', 'program');
        $rp->setAccessible(true);
        // Force to be null, so we can test the function.
        $rp->setValue($program_item, null);

        $this->assertEmpty($rp->getValue($program_item));

        $rm = new ReflectionMethod('totara_program\user_learning\item', 'ensure_program_loaded');
        $rm->setAccessible(true);

        $rm->invoke($program_item);

        // Check that it is now not empty.
        $this->assertNotEmpty($rp->getValue($program_item));

    }

    public function test_ensure_course_sets_loaded() {
        // Add some coursesets to the program.
        $progcontent = new program_content($this->program1->id);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);

        $coursesets = $progcontent->get_course_sets();

        $coursedata = new stdClass();
        $coursedata->{$coursesets[0]->get_set_prefix() . 'courseid'} = $this->course1->id;
        $progcontent->add_course(1, $coursedata);

        $coursedata->{$coursesets[1]->get_set_prefix() . 'courseid'} = $this->course2->id;
        $progcontent->add_course(2, $coursedata);

        $progcontent->save_content();

        // Assign user to the program.
        $this->program_generator->assign_program($this->program1->id, array($this->user1->id));

        // Get the program item.
        $program_item = program_item::one($this->user1->id, $this->program1->id);

        $rp = new ReflectionProperty('totara_program\user_learning\item', 'coursesets');
        $rp->setAccessible(true);
        // Force to be null, so we can test the function.
        $rp->setValue($program_item, null);

        $rm = new ReflectionMethod('totara_program\user_learning\item', 'ensure_course_sets_loaded');
        $rm->setAccessible(true);

        $rm->invoke($program_item);

        // Check that it is now not empty.
        $this->assertNotEmpty($rp->getValue($program_item));
    }

    public function test_ensure_completion_loaded() {
        global $CFG;

        set_config('enablecompletion', 0);

        // Assign user to the program.
        $this->program_generator->assign_program($this->program1->id, array($this->user1->id));

        // Get the program item.
        $program_item = program_item::one($this->user1->id, $this->program1->id);

        $progress_canbecompleted = new ReflectionProperty('totara_program\user_learning\item', 'progress_canbecompleted');
        $progress_canbecompleted->setAccessible(true);

        $progress_percentage = new ReflectionProperty('totara_program\user_learning\item', 'progress_percentage');
        $progress_percentage->setAccessible(true);

        // Check they are all empty.
        $this->assertEmpty($progress_canbecompleted->getValue($program_item));
        $this->assertEmpty($progress_percentage->getValue($program_item));

        $rm = new ReflectionMethod('totara_program\user_learning\item', 'ensure_completion_loaded');
        $rm->setAccessible(true);

        $rm->invoke($program_item);

        // Completion is turned off by default so this should not get set.
        $this->assertFalse($progress_canbecompleted->getValue($program_item));
        $this->assertEmpty($progress_percentage->getValue($program_item));

        // Lets turn on completion and try again.
        set_config('enablecompletion', 1);
        $rm = new ReflectionMethod('totara_program\user_learning\item', 'ensure_completion_loaded');
        $rm->setAccessible(true);

        $rm->invoke($program_item);

        // We should have some values this time (even if there is no progress).
        $this->assertTrue($progress_canbecompleted->getValue($program_item));
        $this->assertEquals(0, $progress_percentage->getValue($program_item));
    }

    public function test_ensure_duedate_loaded() {
        global $CFG;

        $CFG->enablecompletion = true;

        // Assign user to the program.
        $this->program_generator->assign_program($this->program1->id, array($this->user1->id));
        // Set completion time.
        $this->program1->set_timedue($this->user1->id, 1475190000);

        // Get the program item.
        $program_item = program_item::one($this->user1->id, $this->program1->id);

        $rp = new ReflectionProperty('totara_program\user_learning\item', 'duedate');
        $rp->setAccessible(true);

        $this->assertEmpty($rp->getValue($program_item));

        $rm = new ReflectionMethod('totara_program\user_learning\item', 'ensure_duedate_loaded');
        $rm->setAccessible(true);

        $rm->invoke($program_item);

        $this->assertEquals('1475190000', $rp->getValue($program_item));

    }

    public function test_get_progress_percentage() {
        global $CFG, $DB;

        $CFG->enablecompletion = true;

        $progcontent = new program_content($this->program1->id);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);

        $coursesets = $progcontent->get_course_sets();

        $coursedata = new stdClass();
        $coursedata->{$coursesets[0]->get_set_prefix() . 'courseid'} = $this->course1->id;
        $progcontent->add_course(1, $coursedata);

        $coursedata->{$coursesets[1]->get_set_prefix() . 'courseid'} = $this->course2->id;
        $progcontent->add_course(2, $coursedata);

        $progcontent->save_content();

        $coursesets[0]->certifpath = 1;
        $coursesets[0]->save_set();

        $coursesets[1]->certifpath = 1;
        $coursesets[1]->save_set();

        // Assign user to the program.
        $this->program_generator->assign_program($this->program1->id, array($this->user1->id));
        $this->completion_generator->complete_course($this->course1, $this->user1);

        $completiontime = time() - (2 * WEEKSECS);
        $completionsettings = array(
            'status'        => program::STATUS_COURSESET_COMPLETE,
            'timecompleted' => $completiontime
        );
        $coursesets[0]->update_courseset_complete($this->user1->id, $completionsettings);

        // Reload the program to get all the courseset info.
        $this->program1 = new program($this->program1->id);

        // Get the program item.
        $program_item = program_item::one($this->user1->id, $this->program1->id);
        $percentage = $program_item->get_progress_percentage();

        $this->assertEquals('50', $percentage);
    }

    public function test_export_progress_for_template() {
        global $CFG;

        $CFG->enablecompletion = true;

        $progcontent = new program_content($this->program1->id);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);

        $coursesets = $progcontent->get_course_sets();

        $coursedata = new stdClass();
        $coursedata->{$coursesets[0]->get_set_prefix() . 'courseid'} = $this->course1->id;
        $progcontent->add_course(1, $coursedata);

        $coursedata->{$coursesets[1]->get_set_prefix() . 'courseid'} = $this->course2->id;
        $progcontent->add_course(2, $coursedata);

        $progcontent->save_content();

        $coursesets[0]->certifpath = 1;
        $coursesets[0]->save_set();

        $coursesets[1]->certifpath = 1;
        $coursesets[1]->save_set();

        $completion_generator = $this->getDataGenerator()->get_plugin_generator('core_completion');

        // Assign user to the program.
        $this->program_generator->assign_program($this->program1->id, array($this->user1->id));

        // Reload the program to get all the courseset info.
        $this->program1 = new program($this->program1->id);

        // Get the program item.
        $program_item = program_item::one($this->user1->id, $this->program1->id);
        $progress_info = $program_item->export_progress_for_template();

        $this->assertEquals('0', $progress_info->pbar['progress']);

        // Next we make some progress and make sure it changes.
        $this->completion_generator->complete_course($this->course1, $this->user1);

        $completiontime = time() - (2 * WEEKSECS);
        $completionsettings = array(
            'status'        => program::STATUS_COURSESET_COMPLETE,
            'timecompleted' => $completiontime
        );
        $coursesets[0]->update_courseset_complete($this->user1->id, $completionsettings);

        $this->program1 = new program($this->program1->id);
        $program_item = program_item::one($this->user1->id, $this->program1->id);
        $progress_info = $program_item->export_progress_for_template();

        $this->assertEquals('50', $progress_info->pbar['progress']);
    }

    public function test_export_dueinfo_for_template() {
        // Assign user to the program.
        $this->program_generator->assign_program($this->program1->id, array($this->user1->id));

        $program_item = program_item::one($this->user1->id, $this->program1->id);

        $rp = new ReflectionProperty('totara_program\user_learning\item', 'duedate');
        $rp->setAccessible(true);

        $now = time();

        // Set a date in the future.
        $futuredate = $now + (2 * WEEKSECS);
        $rp->setvalue($program_item, $futuredate);

        $dueinfo = $program_item->export_dueinfo_for_template();

        $duedateformatted = userdate($futuredate, get_string('strftimedateshorttotara', 'langconfig'));
        $tooltipdate = userdate($futuredate, get_string('strftimedatetimeon', 'langconfig'));
        $duetooltipformat = get_string('programduex', 'totara_program', $tooltipdate);

        $expected = new stdClass();
        $expected->duetext = get_string('userlearningdueonx', 'totara_core', $duedateformatted);
        $expected->tooltip = \totara_core\strftime::format($duetooltipformat, $futuredate);

        $this->assertEquals($expected->duetext, $dueinfo->duetext);
        $this->assertEquals($expected->tooltip, $dueinfo->tooltip);


        // Set a date in the past (overdue).
        $pastdate = $now - (2 * WEEKSECS);
        $rp->setvalue($program_item, $futuredate);

        $dueinfo = $program_item->export_dueinfo_for_template();

        $duedateformatted = userdate($futuredate, get_string('strftimedateshorttotara', 'langconfig'));

        $expected = new stdClass();
        $expected->duetext = get_string('userlearningoverduesincex', 'totara_core', $duedateformatted);
        $expected->tooltip = \totara_core\strftime::format($duetooltipformat, $pastdate);
    }

    public function test_export_for_template() {
        global $DB;

        $progcontent = new program_content($this->program1->id);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);

        $coursesets = $progcontent->get_course_sets();

        $coursedata = new stdClass();
        $coursedata->{$coursesets[0]->get_set_prefix() . 'courseid'} = $this->course1->id;
        $progcontent->add_course(1, $coursedata);

        $coursedata->{$coursesets[1]->get_set_prefix() . 'courseid'} = $this->course2->id;
        $progcontent->add_course(2, $coursedata);

        $coursedata->{$coursesets[1]->get_set_prefix() . 'courseid'} = $this->course3->id;
        $progcontent->add_course(2, $coursedata);

        $progcontent->save_content();

        // Set the operator for Set 1 to be AND.
        $coursesets[0]->nextsetoperator = course_set::NEXTSETOPERATOR_AND;
        $coursesets[0]->save_set();

        // Assign user to the program.
        $this->program_generator->assign_program($this->program1->id, array($this->user1->id));

        $program_item = program_item::one($this->user1->id, $this->program1->id);

        $info = $program_item->export_for_template();

        $this->assertEquals($this->program1->id, $info->id);
        $this->assertEquals($this->program1->fullname, $info->fullname);
        $this->assertEquals('Program', $info->component_name);
        $this->assertEquals($this->program1->get_image(), $info->image);
        $this->assertCount(2, $info->coursesets);

        // Test some coursesets properties.
        $this->assertEquals($coursesets[0]->label, $info->coursesets[0]->name);

        // Course set 1.
        $this->assertEquals('and', $info->coursesets[0]->nextsetoperator);
        $this->assertCount(1, $info->coursesets[0]->courses);
        $this->assertEquals($this->course2->fullname, $coursesets[1]->courses[0]->fullname);

        // Course set 2.
        $this->assertEquals($coursesets[1]->label, $info->coursesets[1]->name);
        $this->assertCount(2, $info->coursesets[1]->courses);
        $this->assertEquals($this->course2->fullname, $coursesets[1]->courses[0]->fullname);
        $this->assertEquals($this->course3->fullname, $coursesets[1]->courses[1]->fullname);
    }

    public function test_export_for_template_with_enabled_audience_visibility() {
        global $CFG;
        $CFG->audiencevisibility = 1 ;

        $progcontent = new program_content($this->program1->id);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);

        $coursesets = $progcontent->get_course_sets();
        $coursedata = new stdClass();
        $coursedata->{$coursesets[0]->get_set_prefix() . 'courseid'} = $this->course8->id;
        $progcontent->add_course(1, $coursedata);
        $progcontent->save_content();

        // Assign user to the program.
        $this->program_generator->assign_program($this->program1->id, array($this->user1->id));
        $program_item = program_item::one($this->user1->id, $this->program1->id);

        $info = $program_item->export_for_template();
        $this->assertEquals($this->program1->id, $info->id);
        $this->assertEquals($this->program1->fullname, $info->fullname);
        $this->assertStringContainsString('totara/program/required.php', $info->coursesets[0]->courses[0]->url_view);
    }

    public function test_export_for_template_with_disabled_audience_visibility() {
        $progcontent = new program_content($this->program1->id);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);

        $coursesets = $progcontent->get_course_sets();
        $coursedata = new stdClass();
        $coursedata->{$coursesets[0]->get_set_prefix() . 'courseid'} = $this->course8->id;
        $progcontent->add_course(1, $coursedata);
        $progcontent->save_content();

        // Assign user to the program.
        $this->program_generator->assign_program($this->program1->id, array($this->user1->id));
        $program_item = program_item::one($this->user1->id, $this->program1->id);

        $info = $program_item->export_for_template();
        $this->assertEquals($this->program1->id, $info->id);
        $this->assertEquals($this->program1->fullname, $info->fullname);
        $this->assertStringContainsString('course/view.php', $info->coursesets[0]->courses[0]->url_view);
    }

    /**
     * Tests course sets with AND, OR, THEN pattern, by completing the OR course set.
     */
    public function test_process_coursesets_1() {
        // Setup program content.
        $progcontent = new program_content($this->program1->id);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);

        $coursesets = $progcontent->get_course_sets();

        $coursedata = new stdClass();
        $coursedata->{$coursesets[0]->get_set_prefix() . 'courseid'} = $this->course1->id;
        $progcontent->add_course(1, $coursedata);

        $coursedata->{$coursesets[1]->get_set_prefix() . 'courseid'} = $this->course2->id;
        $progcontent->add_course(2, $coursedata);

        $coursedata->{$coursesets[2]->get_set_prefix() . 'courseid'} = $this->course3->id;
        $progcontent->add_course(3, $coursedata);

        $coursedata->{$coursesets[3]->get_set_prefix() . 'courseid'} = $this->course4->id;
        $progcontent->add_course(4, $coursedata);

        $progcontent->save_content();

        // Do some more setup.
        $coursesets[0]->nextsetoperator = course_set::NEXTSETOPERATOR_AND;
        $coursesets[1]->nextsetoperator = course_set::NEXTSETOPERATOR_OR;
        $coursesets[2]->nextsetoperator = course_set::NEXTSETOPERATOR_THEN;
        $coursesets[3]->nextsetoperator = course_set::NEXTSETOPERATOR_AND;

        // Set completion type.
        $coursesets[0]->completiontype = course_set::COMPLETIONTYPE_ALL;
        $coursesets[1]->completiontype = course_set::COMPLETIONTYPE_ALL;
        $coursesets[2]->completiontype = course_set::COMPLETIONTYPE_ALL;
        $coursesets[3]->completiontype = course_set::COMPLETIONTYPE_ALL;

        // Set certifpath.
        $coursesets[0]->certifpath = CERTIFPATH_STD;
        $coursesets[1]->certifpath = CERTIFPATH_STD;
        $coursesets[2]->certifpath = CERTIFPATH_STD;
        $coursesets[3]->certifpath = CERTIFPATH_STD;

        // Save the sets
        $coursesets[0]->save_set();
        $coursesets[1]->save_set();
        $coursesets[2]->save_set();
        $coursesets[3]->save_set();

        // User has completed course 3, which was in coursesets[2] with next operator THEN,
        // so they will need to complete coursesets[3] with course 4 in it.
        $this->completion_generator->complete_course($this->course3, $this->user1);

        $this->getDataGenerator()->enrol_user($this->user1->id, $this->course3->id, null, 'manual');

        // Assign the user to the program.
        $this->program_generator->assign_program($this->program1->id, array($this->user1->id));

        // Get the program and process the coursesets.
        $program_item = program_item::one($this->user1->id, $this->program1->id);

        $rp = new ReflectionProperty('totara_program\user_learning\item', 'coursesets');
        $rp->setAccessible(true);

        $resultset = $program_item->process_coursesets($rp->getvalue($program_item));

        // Check we have the correct sets now.
        $this->assertEquals('Course set 4', $resultset->sets[0]->name);

        $this->assertCount(1, $resultset->sets);

        $this->assertEquals(1, $resultset->completecount);
        $this->assertEquals(0, $resultset->unavailablecount);
    }

    /**
     * Tests course sets with OR OR OR pattern by completing one of the courses, so no other courses in current learning.
     */
    public function test_process_coursesets_2() {
        // Setup program content.
        $progcontent = new program_content($this->program1->id);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);

        $coursesets = $progcontent->get_course_sets();

        $coursedata = new stdClass();
        $coursedata->{$coursesets[0]->get_set_prefix() . 'courseid'} = $this->course1->id;
        $progcontent->add_course(1, $coursedata);

        $coursedata->{$coursesets[1]->get_set_prefix() . 'courseid'} = $this->course2->id;
        $progcontent->add_course(2, $coursedata);

        $coursedata->{$coursesets[2]->get_set_prefix() . 'courseid'} = $this->course3->id;
        $progcontent->add_course(3, $coursedata);

        $coursedata->{$coursesets[3]->get_set_prefix() . 'courseid'} = $this->course4->id;
        $progcontent->add_course(4, $coursedata);

        $progcontent->save_content();

        // Do some more setup.
        $coursesets[0]->nextsetoperator = course_set::NEXTSETOPERATOR_OR;
        $coursesets[1]->nextsetoperator = course_set::NEXTSETOPERATOR_OR;
        $coursesets[2]->nextsetoperator = course_set::NEXTSETOPERATOR_OR;
        $coursesets[3]->nextsetoperator = course_set::NEXTSETOPERATOR_OR;

        // Set completion type.
        $coursesets[0]->completiontype = course_set::COMPLETIONTYPE_ALL;
        $coursesets[1]->completiontype = course_set::COMPLETIONTYPE_ALL;
        $coursesets[2]->completiontype = course_set::COMPLETIONTYPE_ALL;
        $coursesets[3]->completiontype = course_set::COMPLETIONTYPE_ALL;

        // Set certifpath.
        $coursesets[0]->certifpath = CERTIFPATH_STD;
        $coursesets[1]->certifpath = CERTIFPATH_STD;
        $coursesets[2]->certifpath = CERTIFPATH_STD;
        $coursesets[3]->certifpath = CERTIFPATH_STD;

        // Save the sets
        $coursesets[0]->save_set();
        $coursesets[1]->save_set();
        $coursesets[2]->save_set();
        $coursesets[3]->save_set();

        // Assign the user to the program.
        $this->program_generator->assign_program($this->program1->id, array($this->user1->id));

        $this->getDataGenerator()->enrol_user($this->user1->id, $this->course3->id, null, 'manual');

        $this->completion_generator->complete_course($this->course3, $this->user1);

        // Get user current learning items.
        $items = learning_item_helper::get_users_current_learning_items($this->user1->id);

        // Make sure we have no program item.
        $this->assertEmpty($items);

        // Nothing else to do.
    }

    /**
     * Tests course sets with AND AND AND pattern; completing one course, but there are still three more to do.
     */
    public function test_process_coursesets_3() {

        // Setup program content.
        $progcontent = new program_content($this->program1->id);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);

        $coursesets = $progcontent->get_course_sets();

        $coursedata = new stdClass();
        $coursedata->{$coursesets[0]->get_set_prefix() . 'courseid'} = $this->course1->id;
        $progcontent->add_course(1, $coursedata);

        $coursedata->{$coursesets[1]->get_set_prefix() . 'courseid'} = $this->course2->id;
        $progcontent->add_course(2, $coursedata);

        $coursedata->{$coursesets[2]->get_set_prefix() . 'courseid'} = $this->course3->id;
        $progcontent->add_course(3, $coursedata);

        $coursedata->{$coursesets[3]->get_set_prefix() . 'courseid'} = $this->course4->id;
        $progcontent->add_course(4, $coursedata);

        $progcontent->save_content();

        // Do some more setup.
        $coursesets[0]->nextsetoperator = course_set::NEXTSETOPERATOR_AND;
        $coursesets[1]->nextsetoperator = course_set::NEXTSETOPERATOR_AND;
        $coursesets[2]->nextsetoperator = course_set::NEXTSETOPERATOR_AND;
        $coursesets[3]->nextsetoperator = course_set::NEXTSETOPERATOR_AND;

        // Set completion type.
        $coursesets[0]->completiontype = course_set::COMPLETIONTYPE_ALL;
        $coursesets[1]->completiontype = course_set::COMPLETIONTYPE_ALL;
        $coursesets[2]->completiontype = course_set::COMPLETIONTYPE_ALL;
        $coursesets[3]->completiontype = course_set::COMPLETIONTYPE_ALL;

        // Set certifpath.
        $coursesets[0]->certifpath = CERTIFPATH_STD;
        $coursesets[1]->certifpath = CERTIFPATH_STD;
        $coursesets[2]->certifpath = CERTIFPATH_STD;
        $coursesets[3]->certifpath = CERTIFPATH_STD;

        // Save the sets
        $coursesets[0]->save_set();
        $coursesets[1]->save_set();
        $coursesets[2]->save_set();
        $coursesets[3]->save_set();

        // User has completed on course, in one of the course sets. But they need to complete all four.
        $this->completion_generator->complete_course($this->course3, $this->user1);

        $this->getDataGenerator()->enrol_user($this->user1->id, $this->course3->id, null, 'manual');

        // Assign the user to the program.
        $this->program_generator->assign_program($this->program1->id, array($this->user1->id));

        // Get the program and process the coursesets.
        $program_item = program_item::one($this->user1->id, $this->program1->id);

        $rp = new ReflectionProperty('totara_program\user_learning\item', 'coursesets');
        $rp->setAccessible(true);

        $resultset = $program_item->process_coursesets($rp->getvalue($program_item));

        // Check we have the correct sets now.
        $this->assertEquals('Course set 1', $resultset->sets[0]->name);
        $this->assertEquals('Course set 2', $resultset->sets[1]->name);
        $this->assertEquals('Course set 4', $resultset->sets[2]->name);

        $this->assertCount(3, $resultset->sets);

        $this->assertEquals(1, $resultset->completecount);
        $this->assertEquals(0, $resultset->unavailablecount);
    }

    /**
     * Tests course sets with OR OR OR AND OR OR OR pattern. If you complete course 4, you must complete course5; or you
     * can complete 1, 2, 3, 6, or 7 instead.
     */
    public function test_process_coursesets_4() {

        // Setup program content.
        $progcontent = new program_content($this->program1->id);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);

        $coursesets = $progcontent->get_course_sets();

        $coursedata = new stdClass();
        $coursedata->{$coursesets[0]->get_set_prefix() . 'courseid'} = $this->course1->id;
        $progcontent->add_course(1, $coursedata);

        $coursedata->{$coursesets[1]->get_set_prefix() . 'courseid'} = $this->course2->id;
        $progcontent->add_course(2, $coursedata);

        $coursedata->{$coursesets[2]->get_set_prefix() . 'courseid'} = $this->course3->id;
        $progcontent->add_course(3, $coursedata);

        $coursedata->{$coursesets[3]->get_set_prefix() . 'courseid'} = $this->course4->id;
        $progcontent->add_course(4, $coursedata);

        $coursedata->{$coursesets[4]->get_set_prefix() . 'courseid'} = $this->course5->id;
        $progcontent->add_course(5, $coursedata);

        $coursedata->{$coursesets[5]->get_set_prefix() . 'courseid'} = $this->course6->id;
        $progcontent->add_course(6, $coursedata);

        $coursedata->{$coursesets[6]->get_set_prefix() . 'courseid'} = $this->course7->id;
        $progcontent->add_course(7, $coursedata);

        $progcontent->save_content();

        // Do some more setup.
        $coursesets[0]->nextsetoperator = course_set::NEXTSETOPERATOR_OR; // course1 OR
        $coursesets[1]->nextsetoperator = course_set::NEXTSETOPERATOR_OR; // course2 OR
        $coursesets[2]->nextsetoperator = course_set::NEXTSETOPERATOR_OR; // course3; OR
        $coursesets[3]->nextsetoperator = course_set::NEXTSETOPERATOR_AND; // course4 AND
        $coursesets[4]->nextsetoperator = course_set::NEXTSETOPERATOR_OR; // course5; OR
        $coursesets[5]->nextsetoperator = course_set::NEXTSETOPERATOR_OR; // course6 OR
        $coursesets[6]->nextsetoperator = course_set::NEXTSETOPERATOR_OR; // course7

        // Set completion type.
        $coursesets[0]->completiontype = course_set::COMPLETIONTYPE_ALL;
        $coursesets[1]->completiontype = course_set::COMPLETIONTYPE_ALL;
        $coursesets[2]->completiontype = course_set::COMPLETIONTYPE_ALL;
        $coursesets[3]->completiontype = course_set::COMPLETIONTYPE_ALL;
        $coursesets[4]->completiontype = course_set::COMPLETIONTYPE_ALL;
        $coursesets[5]->completiontype = course_set::COMPLETIONTYPE_ALL;
        $coursesets[6]->completiontype = course_set::COMPLETIONTYPE_ALL;

        // Set certifpath.
        $coursesets[0]->certifpath = CERTIFPATH_STD;
        $coursesets[1]->certifpath = CERTIFPATH_STD;
        $coursesets[2]->certifpath = CERTIFPATH_STD;
        $coursesets[3]->certifpath = CERTIFPATH_STD;
        $coursesets[4]->certifpath = CERTIFPATH_STD;
        $coursesets[5]->certifpath = CERTIFPATH_STD;
        $coursesets[6]->certifpath = CERTIFPATH_STD;

        // Save the sets
        $coursesets[0]->save_set();
        $coursesets[1]->save_set();
        $coursesets[2]->save_set();
        $coursesets[3]->save_set();
        $coursesets[4]->save_set();
        $coursesets[5]->save_set();
        $coursesets[6]->save_set();

        // User has completed course4. They need to complete course5 also; or they could complete any of course1, 2, 3, 6, or 7.
        $this->completion_generator->complete_course($this->course4, $this->user1);

        $this->getDataGenerator()->enrol_user($this->user1->id, $this->course3->id, null, 'manual');

        $coursesets = array();

        // Assign the user to the program.
        $this->program_generator->assign_program($this->program1->id, array($this->user1->id));

        // Get the program and process the coursesets.
        $program_item = program_item::one($this->user1->id, $this->program1->id);
        $this->assertNotEmpty($program_item);

        $rp = new ReflectionProperty('totara_program\user_learning\item', 'coursesets');
        $rp->setAccessible(true);

        $resultset = $program_item->process_coursesets($rp->getvalue($program_item));

        // Check we have the correct sets now.
        $this->assertEquals('Course set 1', $resultset->sets[0]->name);
        $this->assertEquals('Course set 2', $resultset->sets[1]->name);
        $this->assertEquals('Course set 3', $resultset->sets[2]->name);
        $this->assertEquals('Course set 5', $resultset->sets[3]->name);
        $this->assertEquals('Course set 6', $resultset->sets[4]->name);
        $this->assertEquals('Course set 7', $resultset->sets[5]->name);

        $this->assertCount(6, $resultset->sets);

        $this->assertEquals(1, $resultset->completecount);
        $this->assertEquals(0, $resultset->unavailablecount);
    }

    /**
     * Tests course sets with OR AND AND OR OR pattern; complete two of the latter courses but there is still one
     * left to do at the beginning.
     */
    public function test_process_coursesets_5() {

        // Setup program content.
        $progcontent = new program_content($this->program1->id);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);

        $coursesets = $progcontent->get_course_sets();

        $coursedata = new stdClass();
        $coursedata->{$coursesets[0]->get_set_prefix() . 'courseid'} = $this->course1->id;
        $progcontent->add_course(1, $coursedata);

        $coursedata->{$coursesets[1]->get_set_prefix() . 'courseid'} = $this->course2->id;
        $progcontent->add_course(2, $coursedata);

        $coursedata->{$coursesets[2]->get_set_prefix() . 'courseid'} = $this->course3->id;
        $progcontent->add_course(3, $coursedata);

        $coursedata->{$coursesets[3]->get_set_prefix() . 'courseid'} = $this->course4->id;
        $progcontent->add_course(4, $coursedata);

        $coursedata->{$coursesets[4]->get_set_prefix() . 'courseid'} = $this->course5->id;
        $progcontent->add_course(5, $coursedata);

        $progcontent->save_content();

        // Do some more setup.
        $coursesets[0]->nextsetoperator = course_set::NEXTSETOPERATOR_OR; // course1 OR
        $coursesets[1]->nextsetoperator = course_set::NEXTSETOPERATOR_AND; // course2 AND
        $coursesets[2]->nextsetoperator = course_set::NEXTSETOPERATOR_AND; // course3 AND
        $coursesets[3]->nextsetoperator = course_set::NEXTSETOPERATOR_OR; // course4; OR
        $coursesets[4]->nextsetoperator = course_set::NEXTSETOPERATOR_OR; // course5

        // Set completion type.
        $coursesets[0]->completiontype = course_set::COMPLETIONTYPE_ALL;
        $coursesets[1]->completiontype = course_set::COMPLETIONTYPE_ALL;
        $coursesets[2]->completiontype = course_set::COMPLETIONTYPE_ALL;
        $coursesets[3]->completiontype = course_set::COMPLETIONTYPE_ALL;
        $coursesets[4]->completiontype = course_set::COMPLETIONTYPE_ALL;

        // Set certifpath.
        $coursesets[0]->certifpath = CERTIFPATH_STD;
        $coursesets[1]->certifpath = CERTIFPATH_STD;
        $coursesets[2]->certifpath = CERTIFPATH_STD;
        $coursesets[3]->certifpath = CERTIFPATH_STD;
        $coursesets[4]->certifpath = CERTIFPATH_STD;

        // Save the sets
        $coursesets[0]->save_set();
        $coursesets[1]->save_set();
        $coursesets[2]->save_set();
        $coursesets[3]->save_set();
        $coursesets[4]->save_set();

        // User has completed course3 and course4, but still has to complete course1 or course2; or course 5.
        $this->completion_generator->complete_course($this->course3, $this->user1);
        $this->completion_generator->complete_course($this->course4, $this->user1);

        $this->getDataGenerator()->enrol_user($this->user1->id, $this->course3->id, null, 'manual');
        $this->getDataGenerator()->enrol_user($this->user1->id, $this->course4->id, null, 'manual');

        // Assign the user to the program.
        $this->program_generator->assign_program($this->program1->id, array($this->user1->id));

        // Get the program and process the coursesets.
        $program_item = program_item::one($this->user1->id, $this->program1->id);

        $rp = new ReflectionProperty('totara_program\user_learning\item', 'coursesets');
        $rp->setAccessible(true);

        $resultset = $program_item->process_coursesets($rp->getvalue($program_item));

        // Check we have the correct sets now.
        $this->assertEquals('Course set 1', $resultset->sets[0]->name);
        $this->assertEquals('Course set 2', $resultset->sets[1]->name);
        $this->assertEquals('Course set 5', $resultset->sets[2]->name);

        // Make sure we only have 3 sets (the others shouldn't be included).
        $this->assertCount(3, $resultset->sets);

        $this->assertEquals(2, $resultset->completecount);
        $this->assertEquals(0, $resultset->unavailablecount);
    }

    /**
     * Tests course sets with OR AND THEN THEN AND pattern. Complete course 1, now course4 must be completed (the
     * first THEN set), and the second THEN course should not be available yet.
     */
    public function test_process_coursesets_6() {

        // Setup program content.
        $progcontent = new program_content($this->program1->id);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);

        $coursesets = $progcontent->get_course_sets();

        $coursedata = new stdClass();
        $coursedata->{$coursesets[0]->get_set_prefix() . 'courseid'} = $this->course1->id;
        $progcontent->add_course(1, $coursedata);

        $coursedata->{$coursesets[1]->get_set_prefix() . 'courseid'} = $this->course2->id;
        $progcontent->add_course(2, $coursedata);

        $coursedata->{$coursesets[2]->get_set_prefix() . 'courseid'} = $this->course3->id;
        $progcontent->add_course(3, $coursedata);

        $coursedata->{$coursesets[3]->get_set_prefix() . 'courseid'} = $this->course4->id;
        $progcontent->add_course(4, $coursedata);

        $coursedata->{$coursesets[4]->get_set_prefix() . 'courseid'} = $this->course5->id;
        $progcontent->add_course(5, $coursedata);

        $progcontent->save_content();

        // Do some more setup.
        $coursesets[0]->nextsetoperator = course_set::NEXTSETOPERATOR_OR; // course1 OR
        $coursesets[1]->nextsetoperator = course_set::NEXTSETOPERATOR_AND; // course2 AND
        $coursesets[2]->nextsetoperator = course_set::NEXTSETOPERATOR_THEN; // course3; THEN
        $coursesets[3]->nextsetoperator = course_set::NEXTSETOPERATOR_THEN; // course4; THEN
        $coursesets[4]->nextsetoperator = course_set::NEXTSETOPERATOR_AND; // course5

        // Set completion type.
        $coursesets[0]->completiontype = course_set::COMPLETIONTYPE_ALL;
        $coursesets[1]->completiontype = course_set::COMPLETIONTYPE_ALL;
        $coursesets[2]->completiontype = course_set::COMPLETIONTYPE_ALL;
        $coursesets[3]->completiontype = course_set::COMPLETIONTYPE_ALL;
        $coursesets[4]->completiontype = course_set::COMPLETIONTYPE_ALL;

        // Set certifpath.
        $coursesets[0]->certifpath = CERTIFPATH_STD;
        $coursesets[1]->certifpath = CERTIFPATH_STD;
        $coursesets[2]->certifpath = CERTIFPATH_STD;
        $coursesets[3]->certifpath = CERTIFPATH_STD;
        $coursesets[4]->certifpath = CERTIFPATH_STD;

        // Save the sets
        $coursesets[0]->save_set();
        $coursesets[1]->save_set();
        $coursesets[2]->save_set();
        $coursesets[3]->save_set();
        $coursesets[4]->save_set();

        // User has completed course1, skipping the next two course sets, to land at course4.
        $this->completion_generator->complete_course($this->course1, $this->user1);

        $this->getDataGenerator()->enrol_user($this->user1->id, $this->course1->id, null, 'manual');

        // Assign the user to the program.
        $this->program_generator->assign_program($this->program1->id, array($this->user1->id));

        // Get the program and process the coursesets.
        $program_item = program_item::one($this->user1->id, $this->program1->id);
        $this->assertNotEmpty($program_item);

        $rp = new ReflectionProperty('totara_program\user_learning\item', 'coursesets');
        $rp->setAccessible(true);

        $resultset = $program_item->process_coursesets($rp->getvalue($program_item));

        // Check we have the correct set now.
        $this->assertEquals('Course set 4', $resultset->sets[0]->name);

        // Make sure we only have 1 set (the others shouldn't be included).
        $this->assertCount(1, $resultset->sets);

        $this->assertEquals(1, $resultset->completecount);
        $this->assertEquals(1, $resultset->unavailablecount);
    }

    public function test_is_single_course_true() {

        // Setup program content.
        $progcontent = new program_content($this->program1->id);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);

        $coursesets = $progcontent->get_course_sets();

        $coursedata = new stdClass();
        $coursedata->{$coursesets[0]->get_set_prefix() . 'courseid'} = $this->course1->id;
        $progcontent->add_course(1, $coursedata);

        // Do some more setup.
        $coursesets[0]->nextsetoperator = course_set::NEXTSETOPERATOR_OR;

        // Set completion type.
        $coursesets[0]->completiontype = course_set::COMPLETIONTYPE_ALL;

        // Set certifpath.
        $coursesets[0]->certifpath = CERTIFPATH_STD;

        // Save the sets
        $coursesets[0]->save_set();

        // Assign the user to the program.
        $this->program_generator->assign_program($this->program1->id, array($this->user1->id));

        // Get the program and process the coursesets.
        $program_item = program_item::one($this->user1->id, $this->program1->id);

        $this->assertEquals($program_item->is_single_course()->fullname, $this->course1->fullname);
        $this->assertEquals($program_item->is_single_course()->id, $this->course1->id);
    }

    public function test_is_single_course_false() {

        // Setup program content.
        $progcontent = new program_content($this->program1->id);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);

        $coursesets = $progcontent->get_course_sets();

        $coursedata = new stdClass();
        $coursedata->{$coursesets[0]->get_set_prefix() . 'courseid'} = $this->course1->id;
        $progcontent->add_course(1, $coursedata);
        $coursedata->{$coursesets[0]->get_set_prefix() . 'courseid'} = $this->course2->id;
        $progcontent->add_course(1, $coursedata);

        // Do some more setup.
        $coursesets[0]->nextsetoperator = course_set::NEXTSETOPERATOR_AND;

        // Set completion type.
        $coursesets[0]->completiontype = course_set::COMPLETIONTYPE_ALL;

        // Set certifpath.
        $coursesets[0]->certifpath = CERTIFPATH_STD;

        // Save the sets
        $coursesets[0]->save_set();

        // Assign the user to the program.
        $this->program_generator->assign_program($this->program1->id, array($this->user1->id));

        // Get the program and process the coursesets.
        $program_item = program_item::one($this->user1->id, $this->program1->id);

        $this->assertFalse($program_item->is_single_course());
    }

    public function test_current_learning_items(): void {
        global $DB;

        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $course3 = $this->getDataGenerator()->create_course();

        $this->program_generator->assign_program($this->program1->id, [$this->user1->id]);
        $this->program_generator->assign_program($this->program2->id, [$this->user1->id]);
        $this->program_generator->add_courses_and_courseset_to_program($this->program1, [[$course1, $course2, $course3]]);
        $this->program_generator->add_courses_and_courseset_to_program($this->program2, [[$course2, $course3]]);


        $sql = 'SELECT pcc.courseid
          FROM {prog_courseset_course} pcc
          JOIN {prog_courseset} pc
            ON pcc.coursesetid = pc.id
          WHERE pc.programid = ' . $this->program1->id;

        $records = $DB->get_records_sql($sql);
        self::assertEquals(3, count($records));

        $items = program_item::all($this->user1->id);
        self::assertEquals(7, count($items));
        $items = program_item::current($this->user1->id);
        self::assertEquals(2, count($items));

        foreach ($items as $item) {
            self::assertTrue(in_array($item->id, [$this->program1->id, $this->program2->id]));
        }
    }

    public function test_manual_and_program_enrolment_with_conditions() {
        global $DB, $CFG;

        /**
         * Audience-based visibility is enabled.
         * Program enrolment and Manual enrolment methods are enabled.
         *
         * Create learner user, do not add the user to an audience
         * Create a course
         * * for Audience-based visibility set it to either 'Enrolled users only' or 'Enrolled users and members of the selected audiences'.
         * * enrol the learner user as individual through 'manual' enrolment plugin and set the enrolment start and end dates as days in the past.
         * * test 'is_enrolled' return false, because of the past dates
         *
         * Create a program.
         * * add the course to the program.
         * * assign as individual the learner users in the program
         * * test for correct url
         */

        $CFG->audiencevisibility = 1;

        $student_role = $DB->get_record('role', ['shortname' => 'student']);

        /** @var \enrol_plugin */
        $manual_enrol_plugin = enrol_get_plugin('manual');
        $this->assertNotEmpty($manual_enrol_plugin);

        $enrolment = $DB->get_record('enrol', ['courseid' => $this->course8->id, 'enrol' => 'manual'], '*', MUST_EXIST);
        $timestart = time() - DAYSECS * 3;
        $timeend = time() - DAYSECS;
        $manual_enrol_plugin->enrol_user($enrolment, $this->user1->id, $student_role->id, $timestart, $timeend);
        $coursecontext = \context_course::instance($this->course8->id);

        $trace = new null_progress_trace();
        $manual_enrol_plugin->sync($trace, $this->course8->id);

        self::assertFalse(is_enrolled($coursecontext, $this->user1, '', true));

        $progcontent = new program_content($this->program1->id);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);

        $coursesets = $progcontent->get_course_sets();
        $coursedata = new stdClass();
        $coursedata->{$coursesets[0]->get_set_prefix() . 'courseid'} = $this->course8->id;
        $progcontent->add_course(1, $coursedata);
        $progcontent->save_content();

        // Set as a single course, requires to test program_item::one()
        $coursesets[0]->nextsetoperator = course_set::NEXTSETOPERATOR_OR;
        $coursesets[0]->completiontype = course_set::COMPLETIONTYPE_ALL;
        $coursesets[0]->certifpath = CERTIFPATH_STD;
        $coursesets[0]->save_set();

        // Assign user to the program.
        $this->program_generator->assign_program($this->program1->id, array($this->user1->id));

        self::setUser($this->user1);

        // Get the program
        $program_item = program_item::one($this->user1->id, $this->program1->id);
        $expected_url = new \moodle_url('/totara/program/required.php');
        self::assertStringContainsString($expected_url->out(), $program_item->url_view->out());
    }
}