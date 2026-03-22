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
 * @author Ning Zhou <ning.zhou@totara.com>
 * @package user
 * @category usagedata
 */

use core_phpunit\testcase;
use core_user\usagedata\count_by_status;

class core_user_usagedata_count_by_status_test extends testcase {
    public function test_export(): void {
        $export = (new count_by_status())->export();
        $this->assertEquals(2, $export['active']);
        $this->assertEquals(0, $export['suspended']);
        $this->assertEquals(0, $export['deleted']);

        $generator = $this->getDataGenerator();
        $user['active'][] = $generator->create_user(['suspended' => 0, 'deleted' => 0]);
        $user['active'][] = $generator->create_user(['suspended' => 0, 'deleted' => 0]);
        $user['active'][] = $generator->create_user(['suspended' => 0, 'deleted' => 0]);
        $user['suspended'][] = $generator->create_user(['suspended' => 1, 'deleted' => 0]);
        $user['suspended'][] = $generator->create_user(['suspended' => 1, 'deleted' => 0]);
        $user['suspended'][] = $generator->create_user(['suspended' => 1, 'deleted' => 0]);
        $user['suspended'][] = $generator->create_user(['suspended' => 1, 'deleted' => 0]);
        $user['deleted'][] = $generator->create_user(['suspended' => 0, 'deleted' => 1]);

        $export = (new count_by_status())->export();
        $this->assertEquals(count($user['active']) + 2, $export['active']);
        $this->assertEquals(count($user['suspended']), $export['suspended']);
        $this->assertEquals(count($user['deleted']), $export['deleted']);
    }
}