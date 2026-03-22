<?php
/**
 * This file is part of Totara Learn
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
 * @author Qingyang Liu <qingyang.liu@totaralearning.com>
 * @package totara_webapi
 */

use core_phpunit\testcase;
use totara_webapi\hook\external_pluginfile_allowed_hook;

class totara_webapi_external_pluginfile_allowed_hook_test extends testcase {

    public function test_external_pluginfile_allowed_hook(): void {
        $self = $this;
        $hook = function ($hook) use ($self) {
            $self->assertInstanceOf(external_pluginfile_allowed_hook::class, $hook);
            $hook->set_allowed(true);
            $hook->set_error_msg('set error');
        };

        $watchers = [
            [
                'hookname' => external_pluginfile_allowed_hook::class,
                'callback' => $hook,
            ],
        ];

        totara_core\hook\manager::phpunit_replace_watchers($watchers);

        $instance = new external_pluginfile_allowed_hook('user');
        $instance->execute();
        self::assertTrue($instance->allowed());
        self::assertTrue($instance->has_error());
        self::assertNotEmpty($instance->get_error_msg());
        self::assertEquals('user', $instance->get_component());

        totara_core\hook\manager::phpunit_reset();
    }
}