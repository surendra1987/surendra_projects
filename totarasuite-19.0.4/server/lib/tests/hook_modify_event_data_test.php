<?php
/**
 * This file is part of Totara LMS
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Ning Zhou <ning.zhou@totara.com>
 * @package core
 */

use core\event\user_loggedin;
use core\hook\modify_event_data;
use core_phpunit\testcase;

/**
 * tests for the modify_event_data hook
 */
class core_hook_modify_event_data_test extends testcase {

    /**
     *  Test the hooks count when a event has been created
     *
     * @return void
     * @throws coding_exception
     */
    public function test_modify_event_data_hook_count(): void {
        $hook_sink = $this->redirectHooks();
        $this->assertEquals(0, $hook_sink->count());
        $this->assertEmpty($hook_sink->get_hooks());
        $hook_sink->clear();

        $this->setAdminUser();
        user_loggedin::create(['other' => ['username' => 'admin']]);
        $hooks = $hook_sink->get_hooks();
        $this->assertCount(1, $hooks);
        $this->assertEquals(modify_event_data::class, get_class($hooks[0]));
    }

    public function test_modify_event_data(): void {
        $expect = [
            'key1' => 'What a nice day',
            'key2' => 'You\'re right !',
        ];
        $watcher = [
            [
                'hookname' => modify_event_data::class,
                'callback' => function (modify_event_data $hook) use ($expect) {
                    $data = $hook->get_event_data();
                    $data['expect'] = $expect;
                    $hook->set_event_data($data);
                }
            ]
        ];

        totara_core\hook\manager::phpunit_replace_watchers($watcher);
        $event = user_loggedin::create(['other' => ['username' => 'admin']]);

        $this->assertEquals($expect, $event->get_data()['expect']);
    }
}
