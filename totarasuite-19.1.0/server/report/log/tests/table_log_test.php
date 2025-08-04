<?php
/**
 * This file is part of Totara Talent Experience Platform
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * @package report_log
 */

use core_phpunit\testcase;
use report_log\event\report_viewed;
use report_log\hook\modify_col_fullnameuser;

class report_log_table_log_test extends testcase {
    public function test_col_fullnameuser_hook() {
        $mock_event = report_viewed::create([
            'context' => context::instance_by_id(1),
            'relateduserid' => 1,
            'other' => [
                'groupid' => 1,
                'date' => time(),
                'modid' => 1,
                'modaction' => '',
                'logformat' => '',
            ]
        ]);

        $report_log_table = new report_log_table_log(1);

        // Let's first test without the hook
        $user_name = $report_log_table->col_fullnameuser($mock_event);
        $this->assertEquals(
            '-',
            $user_name
        );

        // Now, let's set up the mocked hook which will override the value
        $watchers = [
            [
                'hookname' => report_log\hook\modify_col_fullnameuser::class,
                'callback' => function (modify_col_fullnameuser $hook) {
                    $hook->set_override_value('overridden value!! :)');
                },
            ],
        ];
        totara_core\hook\manager::phpunit_replace_watchers($watchers);

        $user_name = $report_log_table->col_fullnameuser($mock_event);

        $this->assertEquals(
            'overridden value!! :)',
            $user_name
        );
    }

}

