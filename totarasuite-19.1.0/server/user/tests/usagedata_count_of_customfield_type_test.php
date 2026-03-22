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
 * @package user
 * @category usagedata
 */

use core_phpunit\testcase;
use core_user\usagedata\count_of_customfield_type;

class core_user_usagedata_count_of_customfield_type_test extends testcase {
    public function test_export(): void {
        global $DB;
        $fields = [
            // 3 text
            ['shortname' => 'house', 'name' => 'House', 'required' => 1,
                'visible' => 1, 'locked' => 0, 'categoryid' => 1, 'datatype' => 'text'],
            ['shortname' => 'pet', 'name' => 'Pet', 'required' => 0,
                'visible' => 1, 'locked' => 0, 'categoryid' => 1, 'datatype' => 'text'],
            ['shortname' => 'secretid', 'name' => 'Secret ID', 'required' => 1,
                'visible' => 0, 'locked' => 0, 'categoryid' => 1, 'datatype' => 'text'],
            // 1 checkbox
            ['shortname' => 'muggleborn', 'name' => 'Muggle-born', 'required' => 1,
                'visible' => 1, 'locked' => 1, 'categoryid' => 1, 'datatype' => 'checkbox']
        ];
        foreach ($fields as $field) {
            $DB->insert_record('user_info_field', $field);
        }

        $results = (new count_of_customfield_type())->export();

        $this->assertEquals(3, $results['text']);
        $this->assertEquals(1, $results['checkbox']);
    }
}