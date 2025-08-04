<?php
/**
 * This file is part of Totara Core
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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package tool_diagnostic
 */

use core_phpunit\testcase;
use tool_diagnostic\controllers\index;


/**
 * @group tool_diagnostic
 */
class tool_diagnostic_controller_index_test extends testcase {

    public function test_index_by_admin(): void {
        $this->setAdminUser();
        $index = (new index())->action();

        $this->assertEquals(get_string('support_diagnostics_tool', 'tool_diagnostic'), $index->get_title());
    }

    public function test_index_by_system_user(): void {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $this->expectException(moodle_exception::class);
        (new index())->action();
    }
}
