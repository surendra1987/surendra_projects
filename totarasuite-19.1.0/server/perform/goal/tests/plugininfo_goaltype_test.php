<?php
/**
 * This file is part of Totara Perform
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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package perform_goal
 */

use core_phpunit\testcase;
use perform_goal\plugininfo\goaltype as goaltype_plugininfo;

/**
 * @group perform_goal
 * @coversDefaultClass perform_goal\plugininfo\goaltype
 */
class perform_goal_plugininfo_goaltype_test extends testcase {

    public function test_is_not_enabled() {
        set_config('enableperform_goals', false);
        $plugininfo = new goaltype_plugininfo();

        $this->assertFalse($plugininfo->is_enabled());
    }

    public function test_is_enabled() {
        // Enabled by default.
        $plugininfo = new goaltype_plugininfo();

        $this->assertTrue($plugininfo->is_enabled());
    }
}