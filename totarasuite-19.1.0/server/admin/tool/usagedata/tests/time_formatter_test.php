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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package tool_usagedata
 */

use core_phpunit\testcase;
use tool_usagedata\helper\time;

class tool_usagedata_time_formatter_test extends testcase {

    /**
     * @return void
     */
    public function test_get_timestamps_for_past_months(): void {
        // Check default number of months count
        $timestamps = time::get_timestamps_for_past_months();
        $this->assertCount(12, $timestamps);

        // Check customize number of months count and the values
        $number_of_month = rand(1, 20);
        $timestamps_subset = time::get_timestamps_for_past_months($number_of_month);
        $this->assertCount($number_of_month, $timestamps_subset);
        foreach ($timestamps_subset as $date => $value) {
            if (isset($timestamps[$date])) {
                $this->assertEquals($timestamps[$date], $value);
            }
        }

        // Check the first month value.
        $now = time();
        $index = date('Y-m', $now);
        $start = mktime(0, 0, 0, date('m', $now), 1, date('Y', $now));
        $expect = [
            'start' => $start,
            'end' => strtotime('+1 month', $start) - 1,
        ];
        $this->assertEquals($expect, $timestamps_subset[$index]);
    }
}
