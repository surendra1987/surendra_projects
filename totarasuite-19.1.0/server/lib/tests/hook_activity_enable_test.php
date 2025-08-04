<?php
/*
 *  This file is part of Totara TXP
 *
 *  Copyright (C) 2023 onwards Totara Learning Solutions LTD
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  @author Simon Coggins <simon.coggins@totara.com>
 *
 */

use core\hook\activity_enable;

defined('MOODLE_INTERNAL') || die();

class core_hook_activity_enable_test extends \core_phpunit\testcase {

    public function test_basic_operation() {
        $hook = new activity_enable('abc');

        self::assertSame('abc', $hook->mod);
        self::assertFalse($hook->prevent_enable);
        self::assertEmpty($hook->prevent_enable_reason);

        $hook->prevent_enable = true;
        $hook->prevent_enable_reason = 'Some reason';

        self::assertTrue($hook->prevent_enable);
        self::assertSame('Some reason', $hook->prevent_enable_reason);
    }

}