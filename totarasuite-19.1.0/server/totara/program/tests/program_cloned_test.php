<?php

use totara_program\event\program_cloned;

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

class totara_program_program_cloned_test extends \core_phpunit\testcase {
    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_cloning_operation(): void {
        $this->setAdminUser();
        $program_generator = \totara_program\testing\generator::instance();
        $program = $program_generator->create_program();

        $clone_user = $this->getDataGenerator()->create_user();
        $this->setUser($clone_user);
        $cloned_program = $program->clone_program_details();

        $sink = $this->redirectEvents();
        $sink->clear();

        $event = program_cloned::create_for_operation($program, $cloned_program);
        $event->trigger();

        $events = $sink->get_events();
        $triggered_event = reset($events);

        $expected_description = "Program #{$cloned_program->id} was cloned from Program #{$program->id} by user {$clone_user->id}";
        $this->assertSame($expected_description, $triggered_event->get_description());

        $this->assertSame($triggered_event->get_context()->id, $cloned_program->get_context()->id);
    }
}
