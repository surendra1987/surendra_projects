<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Ning Zhou <ning.zhou@totara.com>
 * @package core_block
 * @category usagedata
 */

use core_block\usagedata\type_per_contextlevel;
use core_phpunit\testcase;

class core_block_usagedata_type_per_contextlevel_test extends testcase {

    public function test_type_per_contextlevel(): void {
        $export = new type_per_contextlevel();
        $export_values = $export->export();
        $this->assertCount(9, $export_values);

        $expect = [
            'system',
            'tenant',
            'user',
            'coursecat',
            'program',
            'course',
            'module',
            'block',
            'other',
        ];
        $actual = array_map(function ($value){ return $value['context_level']; }, $export_values);
        $this->assertEquals($expect, $actual);
    }
}
