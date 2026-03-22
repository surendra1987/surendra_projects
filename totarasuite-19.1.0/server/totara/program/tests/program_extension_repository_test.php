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

use core\entity\user;
use core\testing\generator as data_generator;
use core_phpunit\testcase;
use totara_program\entity\program_extension;

class totara_program_program_extension_repository_test extends testcase {
    /** @var data_generator|null */
    private ?data_generator $generator;

    /** @var \totara_program\testing\generator*/
    private $program_generator;

    /** @var program */
    private $program1;

    /** @var user */
    private $user1;

    /**
     * @return void
     * @throws coding_exception
     */
    protected function setUp(): void {
        $this->generator = $this->getDataGenerator();
        $this->program_generator = $this->generator->get_plugin_generator('totara_program');

        // Creat a user.
        $this->user1 = $this->generator->create_user();

        // Create a program.
        $this->program1 = $this->program_generator->create_program();
    }

    /**
     * @return void
     * @throws dml_exception
     */
    public function test_find_by_program_id(): void {
        global $DB;

        // Create extension.
        $extension = new stdClass();
        $extension->programid = $this->program1->id;
        $extension->userid = $this->user1->id;
        $extension->extensiondate = strtotime('+1 day', time());
        $extension->extensionreason = "";
        $extension->status = 0;

       $DB->insert_record('prog_extension', $extension);

        // Use repository to get the extension.
        $rows = program_extension::repository()
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
        $this->program1 = null;
        $this->user1 = null;

        parent::tearDown();
    }
}