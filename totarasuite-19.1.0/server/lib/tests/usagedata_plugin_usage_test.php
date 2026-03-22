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
 * @package core
 * @category usagedata
 */

use core\usagedata\plugin_usage;
use core_phpunit\testcase;

class core_usagedata_plugin_usage_test extends testcase {
    public function test_export(): void {
        $result = (new plugin_usage())->export();

        foreach ($result as $plugin) {
            $this->assertEquals(['plugin_type', 'plugin_detail'], array_keys($plugin));
            $this->assertTrue(is_array($plugin['plugin_detail']) && !empty($plugin['plugin_detail']));
            if ($plugin['plugin_type'] === 'totara') {
                $this->assertTrue(count($plugin['plugin_detail']) > 40);
            }
        }
    }
}