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
use totara_program\program as program_class;
use totara_program\entity\program;

class totara_program_program_entity_test extends testcase {
    /** @var \totara_program\testing\generator */
    private ?\totara_program\testing\generator $program_generator;

    /** @var program_class */
    private ?program_class $program1;

    /**
     * @return void
     * @throws coding_exception
     */
    protected function setUp(): void {
        $this->program_generator = totara_program\testing\generator::instance();

        // Create a program.
        $this->program1 = $this->program_generator->create_program();
    }

    /**
     * @return void
     * @throws coding_exception
     */
    public function test_entity(): void {
        /** @var program $program */
        $program = new program($this->program1->id);

        self::assertEquals($this->program1->id, $program->id);
        self::assertEquals($this->program1->fullname, $program->fullname);
    }

    /**
     * @return void
     */
    protected function tearDown(): void {
        $this->program_generator = null;
        $this->program1 = null;

        parent::tearDown();
    }
}