<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * @author  Gihan Hewaralalage <gihan.hewaralalage@totara.com>
 * @package totara_program
 */

use core_phpunit\testcase;
use core\testing\generator as data_generator;
use totara_program\content\course_set as content_cs;
use totara_program\content\course_sets\multi_course_set;
use totara_program\content\program_content;
use totara_program\entity\program as program;
use totara_program\entity\program_courseset;
use totara_program\model\program_courseset as program_courseset_model;

class totara_program_program_courseset_model_test extends testcase {
    /** @var data_generator|null */
    private ?data_generator $generator;

    /** @var \totara_program\testing\generator*/
    private $program_generator;

    /** @var stdClass|null */
    private ?stdClass $course1;
    private ?stdClass $course2;
    private ?stdClass $course3;

    /** @var program */
    private $program1;

    /**
     * @return void
     * @throws coding_exception
     */
    protected function setUp(): void {
        $this->generator = $this->getDataGenerator();
        $this->program_generator = $this->generator->get_plugin_generator('totara_program');

        // We'll need some sort of completion setup on these courses.
        $completion_generator = $this->generator->get_plugin_generator('core_completion');
        $completioncriteria = [COMPLETION_CRITERIA_TYPE_SELF => 1];

        // Create courses.
        $this->course1 = $this->generator->create_course();
        $completion_generator->enable_completion_tracking($this->course1);
        $completion_generator->set_completion_criteria($this->course1, $completioncriteria);

        $this->course2 = $this->generator->create_course();
        $completion_generator->enable_completion_tracking($this->course2);
        $completion_generator->set_completion_criteria($this->course2, $completioncriteria);

        $this->course3 = $this->generator->create_course();
        $completion_generator->enable_completion_tracking($this->course3);
        $completion_generator->set_completion_criteria($this->course3, $completioncriteria);

        // Create a program.
        $this->program1 = $this->program_generator->create_program();
    }

    /**
     * @return void
     */
    public function test_create(): void {
        // Create a course set.
        $uniqueid = 'courseset1';
        $multicourseset1 = new multi_course_set($this->program1->id, null, $uniqueid);

        // Add course to courseset.
        $coursedata = new stdClass();
        $coursedata->{$uniqueid . 'courseid'} = $this->course1->id;
        $multicourseset1->add_course($coursedata);
        $multicourseset1->save_set();

        /** @var program_courseset $row */
        $row = program_courseset::repository()->where('programid', $this->program1->id)->one();

        /** @var program $courseset_program */
        $courseset_program = $row->program()->get()->first();
        self::assertEquals($this->program1->id, $courseset_program->id);
        self::assertEquals($this->program1->fullname, $courseset_program->fullname);

        // Create course set
        $program_courseset1 = program_courseset_model::create(
            $this->program1->id,
            0,
            0,
            0,
            content_cs::COMPLETIONTYPE_ALL,
            0,
            0,
            0,
            0,
            0,
            0,
            program_content::CONTENTTYPE_MULTICOURSE,
            'Course set1',
            0
        );

        // Get all course sets in the program
        $coursesets = program_courseset::repository()->where('programid', $this->program1->id)->get()->to_array();
        $coursesetids = array_column($coursesets, 'id');

        // Check program_courseset1 has created.
        self::assertContains($program_courseset1->id, $coursesetids);
    }

    /**
     * @return void
     * @throws coding_exception
     */
    public function test_create_with_empty_label(): void {
        // Create a course set.
        $uniqueid = 'courseset1';
        $multicourseset1 = new multi_course_set($this->program1->id, null, $uniqueid);

        // Add course to courseset.
        $coursedata = new stdClass();
        $coursedata->{$uniqueid . 'courseid'} = $this->course1->id;
        $multicourseset1->add_course($coursedata);
        $multicourseset1->save_set();

        /** @var program_courseset $row */
        $row = program_courseset::repository()->where('programid', $this->program1->id)->one();

        /** @var program $courseset_program */
        $courseset_program = $row->program()->get()->first();
        self::assertEquals($this->program1->id, $courseset_program->id);
        self::assertEquals($this->program1->fullname, $courseset_program->fullname);

        // Create course set
        $program_courseset1 = program_courseset_model::create(
            $this->program1->id,
            0,
            0,
            0,
            content_cs::COMPLETIONTYPE_ALL,
            0,
            0,
            0,
            0,
            0,
            0,
            program_content::CONTENTTYPE_MULTICOURSE,
            program_courseset_model::get_default_label(),
            0
        );

        // Get all course sets in the program
        $coursesets = program_courseset::repository()->where('programid', $this->program1->id)->get()->to_array();
        $coursesetids = array_column($coursesets, 'id');

        // Check program_courseset1 has created.
        self::assertContains($program_courseset1->id, $coursesetids);
        // Check program_courseset label.
        self::assertEquals($program_courseset1->label, 'Course set 1');
    }

    /**
     * @return void
     * @throws coding_exception
     */
    public function test_courseset_delete(): void {
        global $DB;

        self::assertCount(0, $DB->get_records('prog_courseset'));
        self::assertCount(0, $DB->get_records('prog_courseset_course'));

        // Create an empty course set.
        $cs1 = program_courseset_model::create(
            $this->program1->id,
            1,
            0,
            content_cs::NEXTSETOPERATOR_THEN,
            content_cs::COMPLETIONTYPE_ALL,
            0,
            0,
            0,
            0,
            0,
            0,
            program_content::CONTENTTYPE_MULTICOURSE,
            'p1cs1',
            0
        );

        self::assertCount(1, $DB->get_records('prog_courseset'));
        self::assertCount(0, $DB->get_records('prog_courseset_course'));

        // Add courses to the course set.
        $content = [$this->course1->id, $this->course2->id];
        $cs1->update_courses($content);

        self::assertCount(1, $DB->get_records('prog_courseset'));
        self::assertCount(2, $DB->get_records('prog_courseset_course'));

        // Delete course set
        $cs1->delete();

        self::assertCount(0, $DB->get_records('prog_courseset'));
        self::assertCount(0, $DB->get_records('prog_courseset_course'));
    }

    /**
     * Test update content of course set.
     *
     * @return void
     * @throws coding_exception
     */
    public function test_courseset_update_content() {
        global $DB;

        $program_plugin = enrol_get_plugin('totara_program');

        self::assertCount(0, $DB->get_records('prog_courseset'));
        self::assertCount(0, $DB->get_records('prog_courseset_course'));

        // Create an empty course set.
        $cs1 = program_courseset_model::create(
            $this->program1->id,
            1,
            0,
            content_cs::NEXTSETOPERATOR_THEN,
            content_cs::COMPLETIONTYPE_ALL,
            0,
            0,
            0,
            0,
            0,
            0,
            program_content::CONTENTTYPE_MULTICOURSE,
            'p1cs1',
            0
        );

        self::assertCount(1, $DB->get_records('prog_courseset'));
        self::assertCount(0, $DB->get_records('prog_courseset_course'));

        $content = [$this->course1->id, $this->course2->id];
        $cs1->update_courses($content);

        self::assertCount(1, $DB->get_records('prog_courseset'));
        self::assertCount(2, $DB->get_records('prog_courseset_course'));

        // Program enrolment instances should have been added to course 1&2  but not 3
        $instance1 = $program_plugin->get_instance_for_course($this->course1->id);
        self::assertNotEmpty($instance1);

        $instance2 = $program_plugin->get_instance_for_course($this->course2->id);
        self::assertNotEmpty($instance2);

        $instance3 = $program_plugin->get_instance_for_course($this->course3->id);
        self::assertEmpty($instance3);

        // Now add course 1 to a different program.
        $program2 = $this->program_generator->create_program();
        $multicourseset1 = new multi_course_set($program2->id, null, 'p2cs2');
        // Add course to courseset.
        $coursedata = new stdClass();
        $coursedata->p2cs2courseid = $this->course1->id;
        $multicourseset1->add_course($coursedata);
        $multicourseset1->save_set();

        self::assertCount(2, $DB->get_records('prog_courseset'));
        self::assertCount(3, $DB->get_records('prog_courseset_course'));

        // Update the first programs ocntent, removing courses 1 & 2, but adding course 3.
        $content = [$this->course3->id];
        $cs1->update_courses($content);

        self::assertCount(2, $DB->get_records('prog_courseset'));
        self::assertCount(2, $DB->get_records('prog_courseset_course'));

        // Check course 1 which is still associated with a program, kept the plugin.
        $instance1 = $program_plugin->get_instance_for_course($this->course1->id);
        self::assertNotEmpty($instance1);

        // Check course 2 which is no longer associated with any programs removed the plugin.
        $instance2 = $program_plugin->get_instance_for_course($this->course2->id);
        self::assertEmpty($instance2);

        // And check course 3 which is newly associated has added the plugin.
        $instance3 = $program_plugin->get_instance_for_course($this->course3->id);
        self::assertNotEmpty($instance3);
    }

    /**
     * Test the courseset function that fetches a nextset operator constant
     *
     * @return void
     */
    public function test_courseset_get_nextset_operator_constant(): void {
        // First lets create a courseset and use it.
        $cs1 = program_courseset_model::create(
            $this->program1->id,
            1,
            0,
            content_cs::NEXTSETOPERATOR_THEN,
            content_cs::COMPLETIONTYPE_ALL,
            0,
            0,
            0,
            0,
            0,
            0,
            program_content::CONTENTTYPE_MULTICOURSE,
            'p1cs1',
            0
        );

        $operator = program_courseset_model::get_nextset_operator_constant($cs1->nextsetoperator);
        self::assertEquals('NEXTSETOPERATOR_THEN', $operator);

        // Looks good so lets just mock the rest.
        $operator = program_courseset_model::get_nextset_operator_constant(content_cs::NEXTSETOPERATOR_OR);
        self::assertEquals('NEXTSETOPERATOR_OR', $operator);

        $operator = program_courseset_model::get_nextset_operator_constant(content_cs::NEXTSETOPERATOR_AND);
        self::assertEquals('NEXTSETOPERATOR_AND', $operator);

        // Now check null.
        $operator = program_courseset_model::get_nextset_operator_constant(null);
        self::assertEquals('NEXTSETOPERATOR_THEN', $operator);
    }

    /**
     * Test the courseset function that updates the nextset operator for a courseset.
     *
     * @return void
     */
    public function test_courseset_update_nextset_operator(): void {
        global $DB;

        // First lets create a THEN courseset to start with.
        $cs1 = program_courseset_model::create(
            $this->program1->id,
            1,
            0,
            content_cs::NEXTSETOPERATOR_THEN,
            content_cs::COMPLETIONTYPE_ALL,
            0,
            0,
            0,
            0,
            0,
            0,
            program_content::CONTENTTYPE_MULTICOURSE,
            'p1cs1',
            0
        );

        // Quick check on the nextset operator.
        self::assertEquals(content_cs::NEXTSETOPERATOR_THEN, $cs1->nextsetoperator);

        // Now we update the nextset operator and check it.
        $cs1->update_nextset_operator('NEXTSETOPERATOR_OR');
        self::assertEquals(content_cs::NEXTSETOPERATOR_OR, $cs1->nextsetoperator);

        // Lets do a quick check on the $DB to make sure it's worked as expected.
        $cs_data = $DB->get_record('prog_courseset', ['id' => $cs1->id]);
        self::assertEquals(content_cs::NEXTSETOPERATOR_OR, $cs_data->nextsetoperator);

        // Things are looking good, lets try the other 2 expected values.
        $cs1->update_nextset_operator('NEXTSETOPERATOR_AND');
        self::assertEquals(content_cs::NEXTSETOPERATOR_AND, $cs1->nextsetoperator);

        $cs1->update_nextset_operator('NEXTSETOPERATOR_THEN');
        self::assertEquals(content_cs::NEXTSETOPERATOR_THEN, $cs1->nextsetoperator);

        // Righto the happy paths are working, now lets test a failure.
        $this->expectException(\coding_exception::class);
        $this->expectExceptionMessage('invalid nextset operator: NEXTSETOPERATOR_NONEXISTANT');

        $cs1->update_nextset_operator('NEXTSETOPERATOR_NONEXISTANT');
    }

    /**
     * Test the courseset function that course set exists.
     *
     * @return void
     */
    public function test_courseset_is_courseset_exists():void {
        // First lets create a course set.
        $cs1 = program_courseset_model::create(
            $this->program1->id,
            1,
            0,
            content_cs::NEXTSETOPERATOR_THEN,
            content_cs::COMPLETIONTYPE_ALL,
            0,
            0,
            0,
            0,
            0,
            0,
            program_content::CONTENTTYPE_MULTICOURSE,
            'p1cs1',
            0
        );

        $course_set_id = $cs1->id;

        // Course set should exist
        self::assertTrue(program_courseset_model::is_courseset_exists($course_set_id));

        // Course set should not exist
        self::assertFalse(program_courseset_model::is_courseset_exists($cs1->id * 3));
    }


    /**
     * @return void
     */
    protected function tearDown(): void {
        $this->generator = null;
        $this->program_generator = null;
        $this->course1 = null;
        $this->course2 = null;
        $this->course3 = null;
        $this->program1 = null;

        parent::tearDown();
    }
}
