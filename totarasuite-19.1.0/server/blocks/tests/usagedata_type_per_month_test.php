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

use core\orm\query\builder;
use core_block\usagedata\type_created_per_month;
use core_phpunit\testcase;
use tool_usagedata\helper\time;

class core_block_usagedata_type_per_month_test extends testcase {

    public function test_type_per_month(): void {
        $export = new type_created_per_month();
        $export_values = $export->export();
        $month_index = 1;

        $this->assertCount(12, $export_values);
        $this->assertEmpty($export_values[$month_index]['blocks']);

        $timestamps = time::get_timestamps_for_past_months();
        $creation_month_start = $timestamps[array_keys($timestamps)[$month_index]]['start'] + 1;

        // create a block instance in last month
        $object = [
            'blockname' => 'html',
            'parentcontextid' => context_system::instance()->id,
            'showinsubcontexts' => 0,
            'requiredbytheme' => 0,
            'pagetypepattern' => 'totara-dashboard-1',
            'defaultregion' => 'main',
            'defaultweight' => 1,
            'timecreated' => $creation_month_start,
            'timemodified' => $creation_month_start,
        ];
        builder::table('block_instances')->insert($object);
        $export_values = $export->export();
        $this->assertEquals([
            (object)[
                'blockname' => 'html',
                'blockcount' => 1
            ],
        ], $export_values[$month_index]['blocks']);
    }
}
