<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package totara_program
 */

use core_phpunit\testcase;

class totara_program_usagedata_program_test extends testcase {

    /**
     * @return void
     */
    public function test_export(): void {
        global $CFG;

        $CFG->enablelegacyprogramcontent = 1;
        $CFG->enableprogramcompletioneditor = 0;

        $generator = $this->getDataGenerator();
        $generator_program = $generator->get_plugin_generator('totara_program');
        $program_1 = $generator_program->create_program();
        $program_2 = $generator_program->create_program(['summary' => 'summary']);
        $program_3 = $generator_program->create_program(['summary' => 'summary1']);
        $generator_program->create_program(['summary' => '{"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"ddd"}]}]}']);
        $generator_program->create_program(['summary' => '{"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"ddd"}]}]}']);

        $generator_program->create_certification();
        $generator_program->create_certification();

        $generator_program->create_group_assignment(program: $program_1, can_self_enrol: true);
        $generator_program->create_group_assignment(program: $program_2, can_self_enrol: true);
        $generator_program->create_group_assignment(program: $program_3, can_self_enrol: false);

        $results = (new \totara_program\usagedata\program())->export();
        $this->assertFalse((bool)$results['program_completion_editor_setting']);
        $this->assertTrue((bool)$results['legacy_program_content_setting']);
        $this->assertEquals(5, $results['total_programs']);
        $this->assertEquals(2, $results['total_programs_self_enrol']);
    }
}
