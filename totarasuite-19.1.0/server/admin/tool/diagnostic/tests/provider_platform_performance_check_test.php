<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Maria Torres <maria.torres@totara.com>
 * @package tool_diagnostic
 */

use tool_diagnostic\provider\platform_performance_check;

/**
 * @group tool_diagnostic
 */
class tool_diagnostic_provider_platform_performance_check_test extends \core_phpunit\testcase {
    public function test_content(): void {
        $provider = new platform_performance_check(["enabled" => true]);
        $data = html_to_text($provider->get_content()->get_data());

        $this->assertStringContainsString(get_string('check_backup', 'admin'), $data);
        $this->assertStringContainsString(get_string('cachejs', 'admin'), $data);
        $this->assertStringContainsString(get_string('debug', 'admin'), $data);
        $this->assertStringContainsStringIgnoringCase(get_string('status'), $data);
        $this->assertStringContainsStringIgnoringCase(get_string('check'), $data);
        $this->assertStringContainsStringIgnoringCase(get_string('summary'), $data);
        $this->assertStringContainsStringIgnoringCase(get_string('details'), $data);
    }
}
