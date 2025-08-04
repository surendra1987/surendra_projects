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
 * @author  Gihan Hewaralalage <gihan.hewaralalage@totaralearning.com>
 * @package totara_program
 */

use core_phpunit\testcase;
use core\testing\generator as data_generator;
use totara_program\entity\program_courseset;

class totara_program_program_courseset_repository_test extends testcase {
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
     * @throws dml_exception
     */
    public function test_find_by_program_id(): void {
        global $DB;

        // Create a course set.
        $uniqueid = 'courseset1';
        $multicourseset1 = new totara_program\content\course_sets\multi_course_set($this->program1->id, null, $uniqueid);

        // Add course to courseset.
        $coursedata = new stdClass();
        $coursedata->{$uniqueid . 'courseid'} = $this->course1->id;
        $multicourseset1->add_course($coursedata);
        $multicourseset1->save_set();

        self::assertEquals(1, $DB->count_records(program_courseset::TABLE));

        // Use repository to get the courseset.
        $rows = program_courseset::repository()
            ->find_by_program_id($this->program1->id)
            ->get()
            ->to_array();

        self::assertCount(1, $rows);
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