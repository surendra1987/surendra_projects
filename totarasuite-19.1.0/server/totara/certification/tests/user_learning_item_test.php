<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2017 onwards Totara Learning Solutions LTD
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
 * @package totara_certification
 */

use totara_program\content\course_set;
use totara_program\content\program_content;
use totara_program\program;
use totara_program\user_learning\item as program_item;

defined('MOODLE_INTERNAL') || die();

global $CFG;

class totara_certification_user_learning_item_test extends \core_phpunit\testcase {

    private $generator;
    private $program_generator, $completion_generator;
    private $course1, $course2, $course8;
    private $certification1;
    private $user1;

    protected function tearDown(): void {
        $this->generator = null;
        $this->program_generator = null;
        $this->completion_generator = null;
        $this->course1 = $this->course2 = $this->course8 = null;
        $this->certification1 = null;
        $this->user1 = null;
        parent::tearDown();
    }

    public function setUp(): void {
        parent::setUp();

        $this->generator = $this->getDataGenerator();
        $this->program_generator = \totara_program\testing\generator::instance();
        $this->completion_generator = \core_completion\testing\generator::instance();

        // Create some course.
        $this->course1 = $this->generator->create_course();
        $this->course2 = $this->generator->create_course();
        $this->course8 = $this->generator->create_course(array('audiencevisible'=>COHORT_VISIBLE_ENROLLED));

        // Create a certification.
        $certification1id = $this->program_generator->create_certification(array('fullname' => 'Certification 1'));
        $this->certification1 = new program($certification1id);

        $this->user1 = $this->getDataGenerator()->create_user(array('fullname' => 'user1'));
    }

    function test_is_single_course_true() {

        // Setup certification content.
        $certcontent = new program_content($this->certification1->id);
        $certcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);

        $coursesets = $certcontent->get_course_sets();

        $coursedata = new stdClass();
        $coursedata->{$coursesets[0]->get_set_prefix() . 'courseid'} = $this->course1->id;
        $certcontent->add_course(1, $coursedata);

        // Do some more setup.
        $coursesets[0]->nextsetoperator = course_set::NEXTSETOPERATOR_OR;

        // Set completion type.
        $coursesets[0]->completiontype = course_set::COMPLETIONTYPE_ALL;

        // Set certifpath.
        $coursesets[0]->certifpath = CERTIFPATH_CERT;

        // Save the sets
        $coursesets[0]->save_set();

        // Assign the user to the certification.
        $this->program_generator->assign_program($this->certification1->id, array($this->user1->id));

        // Get the certification and process the coursesets.
        $certification_item = \totara_certification\user_learning\item::one($this->user1->id, $this->certification1->id);

        $this->assertEquals($certification_item->is_single_course()->fullname, $this->course1->fullname);
        $this->assertEquals($certification_item->is_single_course()->id, $this->course1->id);
    }

    function test_is_single_course_false() {

        // Setup certification content.
        $certcontent = new program_content($this->certification1->id);
        $certcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);

        $coursesets = $certcontent->get_course_sets();

        $coursedata = new stdClass();
        $coursedata->{$coursesets[0]->get_set_prefix() . 'courseid'} = $this->course1->id;
        $certcontent->add_course(1, $coursedata);
        $coursedata->{$coursesets[0]->get_set_prefix() . 'courseid'} = $this->course2->id;
        $certcontent->add_course(1, $coursedata);

        // Do some more setup.
        $coursesets[0]->nextsetoperator = course_set::NEXTSETOPERATOR_AND;

        // Set completion type.
        $coursesets[0]->completiontype = course_set::COMPLETIONTYPE_ALL;

        // Set certifpath.
        $coursesets[0]->certifpath = CERTIFPATH_CERT;

        // Save the sets
        $coursesets[0]->save_set();

        // Assign the user to the certification.
        $this->program_generator->assign_program($this->certification1->id, array($this->user1->id));

        // Get the certification and process the coursesets.
        $certification_item = \totara_certification\user_learning\item::one($this->user1->id, $this->certification1->id);

        $this->assertFalse($certification_item->is_single_course());
    }

    public function test_export_for_template() {
        $progcontent = new program_content($this->certification1->id);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);

        $coursesets = $progcontent->get_course_sets();

        $coursedata = new stdClass();
        $coursedata->{$coursesets[0]->get_set_prefix() . 'courseid'} = $this->course1->id;
        $progcontent->add_course(1, $coursedata);

        $coursedata->{$coursesets[1]->get_set_prefix() . 'courseid'} = $this->course2->id;
        $progcontent->add_course(2, $coursedata);

        $progcontent->save_content();

        // Set the operator for Set 1 to be AND.
        $coursesets[0]->nextsetoperator = course_set::NEXTSETOPERATOR_AND;
        $coursesets[0]->save_set();

        // Assign user to the program.
        $this->program_generator->assign_program($this->certification1->id, array($this->user1->id));

        $program_item = \totara_certification\user_learning\item::one($this->user1->id, $this->certification1->id);

        $info = $program_item->export_for_template();

        $this->assertEquals($this->certification1->id, $info->id);
        $this->assertEquals($this->certification1->fullname, $info->fullname);
        $this->assertEquals('Certification', $info->component_name);
        $this->assertEquals($this->certification1->get_image(), $info->image);
    }

    public function test_export_for_template_with_enabled_audience_visibility() {
        global $CFG;
        $CFG->audiencevisibility = 1 ;

        $progcontent = new program_content($this->certification1->id);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);

        $coursesets = $progcontent->get_course_sets();
        $coursedata = new stdClass();
        $coursedata->{$coursesets[0]->get_set_prefix() . 'courseid'} = $this->course8->id;
        $progcontent->add_course(1, $coursedata);
        $progcontent->save_content();

        // Assign user to the program.
        $this->program_generator->assign_program($this->certification1->id, array($this->user1->id));
        $program_item = program_item::one($this->user1->id, $this->certification1->id);

        $info = $program_item->export_for_template();
        $this->assertEquals($this->certification1->id, $info->id);
        $this->assertEquals($this->certification1->fullname, $info->fullname);
        $this->assertStringContainsString('totara/program/required.php', $info->coursesets[0]->courses[0]->url_view);
    }

    public function test_export_for_template_with_disabled_audience_visibility() {
        $progcontent = new program_content($this->certification1->id);
        $progcontent->add_set(program_content::CONTENTTYPE_MULTICOURSE);

        $coursesets = $progcontent->get_course_sets();
        $coursedata = new stdClass();
        $coursedata->{$coursesets[0]->get_set_prefix() . 'courseid'} = $this->course8->id;
        $progcontent->add_course(1, $coursedata);
        $progcontent->save_content();

        // Assign user to the program.
        $this->program_generator->assign_program($this->certification1->id, array($this->user1->id));
        $program_item = program_item::one($this->user1->id, $this->certification1->id);

        $info = $program_item->export_for_template();
        $this->assertEquals($this->certification1->id, $info->id);
        $this->assertEquals($this->certification1->fullname, $info->fullname);
        $this->assertStringContainsString('course/view.php', $info->coursesets[0]->courses[0]->url_view);
    }

    public function test_current_learning_items(): void {
        global $DB;

        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $course3 = $this->getDataGenerator()->create_course();

        $cert1 = $this->program_generator->create_certification();
        $cert2 = $this->program_generator->create_certification();
        $cert3 = $this->program_generator->create_certification();

        $this->program_generator->assign_program($cert1->id, [$this->user1->id]);
        $this->program_generator->assign_program($cert2->id, [$this->user1->id]);
        $this->program_generator->assign_program($cert3->id, [$this->user1->id]);
        $this->program_generator->add_courses_and_courseset_to_program($cert1, [[$course1, $course2, $course3]]);

        $sql = 'SELECT pcc.courseid
          FROM {prog_courseset_course} pcc
          JOIN {prog_courseset} pc
            ON pcc.coursesetid = pc.id
          WHERE pc.programid = ' . $cert1->id;

        $records = $DB->get_records_sql($sql);
        self::assertEquals(3, count($records));

        $items = \totara_certification\user_learning\item::all($this->user1->id);
        self::assertEquals(6, count($items));
        $items = \totara_certification\user_learning\item::current($this->user1->id);
        self::assertEquals(3, count($items));

        foreach ($items as $item) {
            self::assertTrue(in_array($item->id, [$cert1->id, $cert2->id, $cert3->id]));
        }
    }
}