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

use core\entity\course;
use core_phpunit\testcase;
use totara_program\program;
use totara_program\entity\program_recurrence;

class totara_program_program_entity_recurrence_test extends testcase {

    private ?stdClass $course1;
    private ?stdClass $course2;
    private ?program $program;

    /**
     * @return void
     */
    public function setUp(): void {
        $generator = totara_program\testing\generator::instance();
        $this->program = $generator->create_program(['fullname' => 'test_prog_fullname']);

        /** @var course $course1 */
        $this->course1 = $this->getDataGenerator()->create_course();
        /** @var course $course2 */
        $this->course2 = $this->getDataGenerator()->create_course();
        $generator->add_courses_and_courseset_to_program($this->program, [[$this->course1], [$this->course2]]);

        parent::setUp();
    }

    /**
     * @return void
     */
    protected function tearDown(): void {
        $this->course1 = null;
        $this->course2 = null;
        $this->program = null;
        parent::tearDown();
    }

    /**
     * @return void
     */
    public function test_entity(): void {
        $program_recurrence = new program_recurrence();
        $program_recurrence->programid = $this->program->id;
        $program_recurrence->currentcourseid = $this->course1->id;
        $program_recurrence->nextcourseid = $this->course2->id;
        $program_recurrence->save();

        $this->assertEquals($this->program->id, $program_recurrence->program->id);
        $this->assertEquals($this->course1->id, $program_recurrence->current_course->id);
        $this->assertEquals($this->course2->id, $program_recurrence->next_course->id);
    }

    /**
     * @return void
     */
    public function test_repository(): void {
        $program_recurrence = new program_recurrence();
        $program_recurrence->programid = $this->program->id;
        $program_recurrence->currentcourseid = $this->course1->id;
        $program_recurrence->nextcourseid = $this->course2->id;
        $program_recurrence->save();

        $retrieved = program_recurrence::repository()
            ->filter_by_program_id($this->program->id)
            ->one();
        $this->assertEquals($this->program->id, $retrieved->programid);
        $this->assertEquals($this->course1->id, $retrieved->currentcourseid);
        $this->assertEquals($this->course2->id, $retrieved->nextcourseid);
    }
}
