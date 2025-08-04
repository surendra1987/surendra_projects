<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2022 onwards Totara Learning Solutions LTD
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package totara_program
 */

use core_phpunit\testcase;
use totara_program\content\course_set;
use totara_program\entity\program_courseset;
use totara_program\entity\program_courseset_course;

class totara_program_program_entity_courseset_course_test extends testcase {

    private ?int $courseset1;
    private ?int $courseset2;
    private ?stdClass $course1;

    /**
     * @return void
     * @throws dml_exception
     */
    public function setUp(): void {
        global $DB;

        $generator = totara_program\testing\generator::instance();
        $program1 = $generator->create_program();
        $program2 = $generator->create_program();

        $courseset1_data = [
            'programid' => $program1->id,
            'sortorder' => 1,
            'label' => 'Courseset One',
            'nextsetoperator' => course_set::NEXTSETOPERATOR_AND,
            'completiontype' => 1,
            'mincourses' => 0,
            'coursesumfield' => 0,
            'coursesumfieldtotal' => 0,
            'timeallowed' => 86400,
            'recurrancetime' => 0,
            'recurrancecreatetime' => 0,
            'contenttype' => 1,
        ];
        $courseset2_data = $courseset1_data;
        $courseset2_data['programid'] = $program2->id;

        $this->courseset1 = $DB->insert_record('prog_courseset', (object) $courseset1_data);
        $this->courseset2 = $DB->insert_record('prog_courseset', (object) $courseset2_data);

        $this->course1 = $this->getDataGenerator()->create_course(['fullname' => 'cs1course']);
        $DB->insert_record('prog_courseset_course', (object) ['coursesetid' => $this->courseset1, 'courseid' => $this->course1->id, 'sortorder' => 1]);
        $DB->insert_record('prog_courseset_course', (object) ['coursesetid' => $this->courseset2, 'courseid' => $this->course1->id, 'sortorder' => 1]);

        parent::setUp();
    }

    /**
     * @return void
     */
    protected function tearDown(): void {
        $this->course1 = null;
        $this->courseset1 = null;
        $this->courseset2 = null;

        parent::tearDown();
    }

    /**
     * @return void
     */
    public function test_entity(): void {
        $mapped_rows = program_courseset_course::repository()
            ->where('coursesetid', $this->courseset1)
            ->count();
        $this->assertSame(1, $mapped_rows);

        $course = $this->getDataGenerator()->create_course();
        /** @var program_courseset_course $prog_courseset_course */
        $mapping = new program_courseset_course();
        $mapping->coursesetid = $this->courseset1;
        $mapping->courseid = $course->id;
        $mapping->save();

        $mapped_rows = program_courseset_course::repository()
            ->where('coursesetid', $this->courseset1)
            ->count();
        $this->assertSame(2, $mapped_rows);

        $second_mapping = program_courseset_course::repository()
            ->where('coursesetid', $this->courseset2)
            ->count();
        $this->assertSame(1, $second_mapping);

        /** @var program_courseset_course $courseset_course_ut */
        $courseset_course_ut = program_courseset_course::repository()
            ->where('coursesetid', $this->courseset2)
            ->one();

        /** @var program_courseset $expected_courseset */
        $expected_courseset = program_courseset::repository()->find($this->courseset2);
        $this->assertSame($expected_courseset->program->id, $courseset_course_ut->program_courseset->program->id);
    }

    /**
     * @return void
     */
    public function test_repository(): void {
        $repo = program_courseset_course::repository();
        $found_by_course = $repo->filter_by_course_id($this->course1->id)
            ->count();
        $this->assertSame(2, $found_by_course);

        $found_by_courseset = $repo->filter_by_program_courseset_id($this->courseset1)
            ->count();
        $this->assertSame(1, $found_by_courseset);

        /** @var program_courseset_course $course */
        $course = $repo->filter_by_program_courseset_id($this->courseset1)
            ->one();
        $this->assertEquals($this->course1->id, $course->course->id);
    }
}
