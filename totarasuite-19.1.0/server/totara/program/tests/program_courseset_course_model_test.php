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
use totara_program\model\program_courseset_course as program_courseset_course_model;

class totara_program_program_courseset_course_model_test extends testcase {
    /** @var data_generator|null */
    private ?data_generator $generator;

    /** @var \totara_program\testing\generator*/
    private $program_generator;

    /** @var stdClass|null */
    private ?stdClass $course1;

    /** @var program */
    private $program1;

    /**
     * @return void
     * @throws coding_exception
     */
    protected function setUp(): void {
        $this->generator = $this->getDataGenerator();
        $this->program_generator = $this->generator->get_plugin_generator('totara_program');

        // Create courses.
        $this->course1 = $this->generator->create_course();

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

        // Check $program_courseset1 has created.
        self::assertContains($program_courseset1->id, $coursesetids);

        // Add course to course set
        $program_courseset_course = program_courseset_course_model::create(
            $program_courseset1->id,
            $this->course1->id,
            0
        );

        // Check new course has been added to the course set
        $courseset_course = \totara_program\entity\program_courseset_course::repository()
            ->where('coursesetid', $program_courseset1->id)->get()->to_array();
        $courseset_course_ids =  array_column($courseset_course, 'courseid');

        // Check course exist in course set
        self::assertContains($this->course1->id, $courseset_course_ids);
    }

    /**
     * @return void
     */
    protected function tearDown(): void {
        $this->generator = null;
        $this->program_generator = null;
        $this->course1 = null;
        $this->program1 = null;

        parent::tearDown();
    }
}
