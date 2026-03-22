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
use core_badges\usagedata\badges_issued_per_month;
use tool_usagedata\helper\time;

class core_badges_usagedata_badges_issued_per_month_test extends testcase {

    /**
     * @throws coding_exception
     * @throws moodle_exception
     * @throws dml_exception
     */
    public function test_export() {
        $user_creator = $this->getDataGenerator()->create_user();
        $user_awarded = $this->getDataGenerator()->create_user();

        $timestamps = time::get_timestamps_for_past_months();
        $timestamps_keys = array_keys($timestamps);

        // Timestamps[3] (total 1)
        $this->create_and_issue_badge($user_creator, $user_awarded, $timestamps[$timestamps_keys[3]]['start'] + 1000);

        // Timestamps[5] (Total: 3)
        $this->create_and_issue_badge($user_creator, $user_awarded, $timestamps[$timestamps_keys[5]]['start'] + 1000);
        $this->create_and_issue_badge($user_creator, $user_awarded, $timestamps[$timestamps_keys[5]]['start'] + 1000);
        $this->create_and_issue_badge($user_creator, $user_awarded, $timestamps[$timestamps_keys[5]]['start'] + 1000);

        // Timestamps[8] (Total: 2)
        $this->create_and_issue_badge($user_creator, $user_awarded, $timestamps[$timestamps_keys[8]]['start'] + 1000);
        $this->create_and_issue_badge($user_creator, $user_awarded, $timestamps[$timestamps_keys[8]]['start'] + 1000);

        $results = (new badges_issued_per_month())->export();

        $this->assertEquals(1, $results[$timestamps_keys[3]]);
        $this->assertEquals(3, $results[$timestamps_keys[5]]);
        $this->assertEquals(2, $results[$timestamps_keys[8]]);
    }

    /**
     * @throws coding_exception
     * @throws moodle_exception
     * @throws dml_exception
     */
    public function create_and_issue_badge($user_creator, $user_awarded, $timestamp): void {
        global $DB, $CFG;
        require_once($CFG->libdir . '/badgeslib.php');

        $generator = $this->getDataGenerator()->get_plugin_generator('core_badges');

        $badge_id = $generator->create_badge($user_creator->id);
        $badge = new badge($badge_id);

        $badge->issue($user_awarded->id, true);

        // Override the 'dateissued' to our expected timestamp
        $DB->set_field('badge_issued', 'dateissued', $timestamp, ['userid' => $user_awarded->id, 'badgeid' => $badge_id]);
    }
}