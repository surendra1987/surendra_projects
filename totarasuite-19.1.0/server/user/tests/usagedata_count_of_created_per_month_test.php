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
use core_user\usagedata\count_of_created_per_month;
use tool_usagedata\helper\time;

class core_user_usagedata_count_of_created_per_month_test extends testcase {
    public function test_export(): void {


        $timestamps = time::get_timestamps_for_past_months();
        $timestamp_keys = array_keys($timestamps);

        $generator = $this->getDataGenerator();
        // 3 in first timestamp
        $generator->create_user(['timecreated' => $timestamps[$timestamp_keys[1]]['start'] + 1000]);
        $generator->create_user(['timecreated' => $timestamps[$timestamp_keys[1]]['start'] + 1000]);
        $generator->create_user(['timecreated' => $timestamps[$timestamp_keys[1]]['start'] + 1000]);

        // 2 in second timestamp
        $generator->create_user(['timecreated' => $timestamps[$timestamp_keys[2]]['start'] + 1000]);
        $generator->create_user(['timecreated' => $timestamps[$timestamp_keys[2]]['start'] + 1000]);

        // 2 in fifth timestamp
        $generator->create_user(['timecreated' => $timestamps[$timestamp_keys[5]]['start'] + 1000]);
        $generator->create_user(['timecreated' => $timestamps[$timestamp_keys[5]]['start'] + 1000]);

        // 1 in eighth timestamp
        $generator->create_user(['timecreated' => $timestamps[$timestamp_keys[8]]['start'] + 1000]);

        // 4 in tenth timestamp
        $generator->create_user(['timecreated' => $timestamps[$timestamp_keys[10]]['start'] + 1000]);
        $generator->create_user(['timecreated' => $timestamps[$timestamp_keys[10]]['start'] + 1000]);
        $generator->create_user(['timecreated' => $timestamps[$timestamp_keys[10]]['start'] + 1000]);
        $generator->create_user(['timecreated' => $timestamps[$timestamp_keys[10]]['start'] + 1000]);

        // 2 in eleventh timestamp
        $generator->create_user(['timecreated' => $timestamps[$timestamp_keys[11]]['start'] + 1000]);
        $generator->create_user(['timecreated' => $timestamps[$timestamp_keys[11]]['start'] + 1000]);


        $results = (new count_of_created_per_month())->export();

        $this->assertEquals(3, $results[$timestamp_keys[1]]);
        $this->assertEquals(2, $results[$timestamp_keys[2]]);
        $this->assertEquals(2, $results[$timestamp_keys[5]]);
        $this->assertEquals(1, $results[$timestamp_keys[8]]);
        $this->assertEquals(4, $results[$timestamp_keys[10]]);
        $this->assertEquals(2, $results[$timestamp_keys[11]]);
    }
}