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
use tool_diagnostic\provider\performance_io;

/**
 * @group tool_diagnostic
 */
class tool_diagnostic_provider_performance_io_test extends testcase {

    public function test_content(): void {
        $provider = new performance_io(["enabled" => true]);
        $data = $provider->get_content()->get_data();

        $this->assertStringContainsString('Testing dataroot file write speed:', $data);
        $this->assertStringContainsString('Testing cachedir file write speed:', $data);
        $this->assertStringContainsString('Testing dataroot file read speed:', $data);
        $this->assertStringContainsString('Testing cachedir file read speed:', $data);
    }
}