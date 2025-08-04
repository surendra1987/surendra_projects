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
 * @package totara_certification
 */

use core_phpunit\testcase;
use totara_certification\usagedata\certification;

class totara_certification_usagedata_certification_test extends testcase {

    /**
     * @return void
     */
    public function test_export(): void {
        $generator = $this->getDataGenerator();
        $generator_program = $generator->get_plugin_generator('totara_program');
        $generator_program->create_certification();
        $generator_program->create_certification(['summary' => 'summary']);
        $generator_program->create_certification(['summary' => 'summary1']);
        $generator_program->create_certification(['summary' => '{"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"ddd"}]}]}']);
        $generator_program->create_certification(['summary' => '{"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"ddd"}]}]}']);

        $generator_program->create_program();
        $generator_program->create_program();

        $results = (new certification())->export();
        $this->assertEquals(5, $results['total_certifications']);
        $this->assertTrue((bool)$results['program_completion_editor_setting']);
    }
}
