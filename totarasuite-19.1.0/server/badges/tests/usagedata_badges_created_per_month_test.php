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
 * @package core_badges
 */

use core_phpunit\testcase;
use core_badges\usagedata\badges_created_per_month;
use tool_usagedata\helper\time;
class core_badges_usagedata_badges_created_per_month_test extends testcase {

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_export() {
        global $CFG;
        require_once($CFG->libdir . '/badgeslib.php');

        $user = $this->getDataGenerator()->create_user();

        $generator = $this->getDataGenerator()->get_plugin_generator('core_badges');

        $timestamps = time::get_timestamps_for_past_months();
        $timestamps_keys = array_keys($timestamps);

        // Timestamps[2] (Total: 3)
        $generator->create_badge($user->id, ['timecreated' => $timestamps[$timestamps_keys[2]]['start'] + 1000]);
        $generator->create_badge($user->id, ['timecreated' => $timestamps[$timestamps_keys[2]]['start'] + 1000]);
        $generator->create_badge($user->id, ['timecreated' => $timestamps[$timestamps_keys[2]]['start'] + 1000]);

        // Timestamps[5] (Total: 5)
        $generator->create_badge($user->id, ['timecreated' => $timestamps[$timestamps_keys[5]]['start'] + 1000]);
        $generator->create_badge($user->id, ['timecreated' => $timestamps[$timestamps_keys[5]]['start'] + 1000]);
        $generator->create_badge($user->id, ['timecreated' => $timestamps[$timestamps_keys[5]]['start'] + 1000]);
        $generator->create_badge($user->id, ['timecreated' => $timestamps[$timestamps_keys[5]]['start'] + 1000]);
        $generator->create_badge($user->id, ['timecreated' => $timestamps[$timestamps_keys[5]]['start'] + 1000]);

        // Timestamps[9] (Total: 1)
        $generator->create_badge($user->id, ['timecreated' => $timestamps[$timestamps_keys[9]]['start'] + 1000]);

        $results = (new badges_created_per_month())->export();

        $this->assertEquals(3, $results[$timestamps_keys[2]]);
        $this->assertEquals(5, $results[$timestamps_keys[5]]);
        $this->assertEquals(1, $results[$timestamps_keys[9]]);
    }
}