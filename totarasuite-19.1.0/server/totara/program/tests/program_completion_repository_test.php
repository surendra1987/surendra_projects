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
use totara_program\program;
use totara_program\entity\program_assignment;
use totara_program\entity\program_completion;

class totara_program_program_completion_repository_test extends testcase {
    /** @var data_generator|null */
    private ?data_generator $generator;

    /** @var \totara_program\testing\generator */
    private ?\totara_program\testing\generator $program_generator;

    /** @var program */
    private ?program $program1;

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
    public function test_find_by_program_id(): void {
        global $DB;

        // Assign users to the program.
        $user1 = $this->generator->create_user();
        $user2 = $this->generator->create_user();
        $this->program_generator->assign_program($this->program1->id, [$user1->id, $user2->id]);
        self::assertEquals(2, $DB->count_records(program_assignment::TABLE));
        self::assertEquals(2, $DB->count_records(program_completion::TABLE));

        // Use repository to get the completions.
        $rows = program_completion::repository()
            ->find_by_program_id($this->program1->id)
            ->get()
            ->to_array();

        self::assertCount(2, $rows);
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