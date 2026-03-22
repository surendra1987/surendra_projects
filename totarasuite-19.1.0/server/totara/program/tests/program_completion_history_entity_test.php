<?php
/**
 * This file is part of Totara Learn
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author  Simon Player <simon.player@totara.com>
 * @package totara_program
 */

use core_phpunit\testcase;
use core\testing\generator as data_generator;
use totara_program\entity\program;
use totara_program\entity\program_assignment;
use totara_program\entity\program_completion;
use totara_program\entity\program_completion_history;
use totara_program\program as program_class;

class totara_program_program_completion_history_entity_test extends testcase {
    /** @var data_generator|null */
    private ?data_generator $generator;

    /** @var \totara_program\testing\generator */
    private ?\totara_program\testing\generator $program_generator;

    /** @var program_class */
    private ?program_class $program1;

    /**
     * @return void
     * @throws coding_exception
     */
    protected function setUp(): void {
        $this->generator = $this->getDataGenerator();
        $this->program_generator = totara_program\testing\generator::instance();

        // Create a program.
        $this->program1 = $this->program_generator->create_program();
    }

    /**
     * @return void
     * @throws dml_exception
     */
    public function test_entity_relation(): void {
        global $DB;

        $course1 = $this->generator->create_course();

        // Create a course set.
        $uniqueid = 'courseset1';
        $multicourseset1 = new totara_program\content\course_sets\multi_course_set($this->program1->id, null, $uniqueid);

        // Add course to courseset.
        $coursedata = new stdClass();
        $coursedata->{$uniqueid . 'courseid'} = $course1->id;
        $multicourseset1->add_course($coursedata);
        $multicourseset1->save_set();

        // Assign users to the program.
        $user1 = $this->generator->create_user();
        $user2 = $this->generator->create_user();
        $this->program_generator->assign_program($this->program1->id, [$user1->id, $user2->id]);
        self::assertEquals(2, $DB->count_records(program_assignment::TABLE));
        self::assertEquals(2, $DB->count_records(program_completion::TABLE));
        self::assertEquals(0, $DB->count_records(program_completion_history::TABLE));

        // Copy records to completion history table.
        $completion_records_history = $DB->get_recordset('prog_completion', []);
        foreach ($completion_records_history as $completion_record) {
            $completion_record->recurringcourseid = $course1->id;
            $DB->insert_record('prog_completion_history', $completion_record);
        }
        self::assertEquals(2, $DB->count_records(program_completion_history::TABLE));

        /** @var program_completion_history $row */
        $row = program_completion_history::repository()->where('programid', $this->program1->id)->get()->first();

        /** @var program $completion_history_program */
        $completion_history_program = $row->program()->get()->first();
        self::assertEquals($this->program1->id, $completion_history_program->id);
        self::assertEquals($this->program1->fullname, $completion_history_program->fullname);
    }

    /**
     * @return void
     */
    protected function tearDown(): void {
        $this->generator = null;
        $this->program_generator = null;
        $this->program1 = null;

        parent::tearDown();
    }
}