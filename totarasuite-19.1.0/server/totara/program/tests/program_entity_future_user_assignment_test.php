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
use totara_program\program;
use totara_program\entity\program_future_user_assignment;

class totara_program_program_entity_future_user_assignment_test extends testcase {

    private ?program $program;
    private ?stdClass $user;
    private ?stdClass $assignment;

    /**
     * @return void
     * @throws dml_exception
     */
    public function setUp(): void {
        global $DB;

        $this->user = $this->getDataGenerator()->create_user();
        $program_generator = totara_program\testing\generator::instance();
        $this->program = $program_generator->create_program(['fullname' => 'test_prog_fullname']);
        $program_generator->assign_program($this->program->id, [$this->user->id]);

        // Get assignment.
        $this->assignment = $DB->get_record('prog_assignment', ['programid' => $this->program->id]);
        $this->program->create_future_assignment($this->program->id, $this->user->id, $this->assignment->id);

        // create a second future assignment for the same user with a different program
        $program2 = $program_generator->create_program(['fullname' => 'test_prog2_fullname']);
        $program_generator->assign_program($program2->id, [$this->user->id]);
        $assignment2 = $DB->get_record('prog_assignment', ['programid' => $program2->id]);
        $program2->create_future_assignment($program2->id, $this->user->id, $assignment2->id);

        parent::setUp();
    }

    /**
     * @return void
     */
    protected function tearDown(): void {
        $this->program = null;
        $this->user = null;
        $this->assignment = null;
        parent::tearDown();
    }

    /**
     * @return void
     */
    public function test_entity(): void {
        /** @var program_future_user_assignment $future_assignment */
        $future_assignment = program_future_user_assignment::repository()
            ->where('programid', '=', $this->program->id)
            ->get()
            ->first();

        $mapped_user = $future_assignment->user;
        $mapped_program = $future_assignment->program;
        $mapped_assignment = $future_assignment->program_assignment;
        $this->assertEquals($this->user->id, $mapped_user->id);
        $this->assertEquals($this->program->id, $mapped_program->id);
        $this->assertEquals($this->assignment->id, $mapped_assignment->id);
    }

    /**
     * @return void
     */
    public function test_repository(): void {
        $assignments = program_future_user_assignment::repository()
            ->filter_by_assignment_id($this->assignment->id)
            ->count();
        $this->assertSame(1, $assignments);

        $users_assignments = program_future_user_assignment::repository()
            ->filter_by_user_id($this->user->id)
            ->count();
        $this->assertSame(2, $users_assignments);

        $program_assignments = program_future_user_assignment::repository()
            ->filter_by_program_id($this->program->id)
            ->count();

        $this->assertSame(1, $program_assignments);
    }
}
