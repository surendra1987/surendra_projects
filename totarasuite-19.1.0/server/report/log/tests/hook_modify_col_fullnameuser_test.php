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

defined('MOODLE_INTERNAL') || die();

class report_log_hook_modify_col_fullnameuser_test extends testcase {

    private $mock_event;
    protected function setUp(): void {
        $this->mock_event = report_viewed::create([
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

        parent::setUp();
    }

    protected function tearDown(): void {
        $this->mock_event = null;

        parent::tearDown();
    }

    /**
     * @covers modify_col_fullnameuser::__construct
     * @covers modify_col_fullnameuser::get_event
     * @covers modify_col_fullnameuser::get_original_value
     * @covers modify_col_fullnameuser::get_override_value
     */
    public function test_getters() {
        $hook = new modify_col_fullnameuser(
            $this->mock_event,
            'Bob Serde'
        );

        $this->assertEquals(
            $this->mock_event,
            $hook->get_event()
        );

        $this->assertEquals(
            'Bob Serde',
            $hook->get_original_value()
        );

        $this->assertEmpty($hook->get_override_value());
    }

    /**
     * @covers modify_col_fullnameuser::set_override_value
     */
    public function test_set_override_value() {
        $hook = new modify_col_fullnameuser(
            $this->mock_event,
            'Bob Serde'
        );

        $this->assertEmpty($hook->get_override_value());

        $hook->set_override_value('This is an overridden value');

        $this->assertEquals(
            'This is an overridden value',
            $hook->get_override_value()
        );
    }

    public function test_modify_col_fullnameuser_hook() {
        $watchers = [
            [
                'hookname' => report_log\hook\modify_col_fullnameuser::class,
                'callback' => function (modify_col_fullnameuser $hook) {
                    $hook->set_override_value($hook->get_original_value() . ' [suffix]');
                },
            ],
        ];
        totara_core\hook\manager::phpunit_replace_watchers($watchers);

        $hook = new report_log\hook\modify_col_fullnameuser(
            $this->mock_event,
            'Bob Serde'
        );
        $hook->execute();

        $this->assertEquals(
            'Bob Serde [suffix]',
            $hook->get_override_value()
        );
    }
}