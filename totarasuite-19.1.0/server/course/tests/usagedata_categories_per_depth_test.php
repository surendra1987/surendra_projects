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
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package core_course
 */

use core_course\usagedata\categories_per_depth;
use core_phpunit\testcase;

class core_course_usagedata_categories_per_depth_test extends testcase {

    /**
     * @throws moodle_exception
     */
    public function test_export() {
        // Depth 1
        $depth_1_id = coursecat::create(
            [
                'name' => "depth_1-1",
                'parent' => null,
            ]
        )->id;

        // Depth 2
        $depth_2_id = coursecat::create(
            [
                'name' => "depth_2-1",
                'parent' => $depth_1_id,
            ]
        )->id;
        coursecat::create(
            [
                'name' => "depth_2-2",
                'parent' => $depth_1_id,
            ]
        );

        // Depth 3
        $depth_3_id = coursecat::create(
            [
                'name' => "depth_3-1",
                'parent' => $depth_2_id,
            ]
        )->id;

        coursecat::create(
            [
                'name' => "depth_4-1",
                'parent' => $depth_3_id,
            ]
        );
        coursecat::create(
            [
                'name' => "depth_4-2",
                'parent' => $depth_3_id,
            ]
        );
        coursecat::create(
            [
                'name' => "depth_4-3",
                'parent' => $depth_3_id,
            ]
        );

        $results = (new categories_per_depth())->export();

        // Not checking depth '1' as there are initial categories in this depth
        $this->assertEquals(2, $results[2]);
        $this->assertEquals(1, $results[3]);
        $this->assertEquals(3, $results[4]);
    }
}