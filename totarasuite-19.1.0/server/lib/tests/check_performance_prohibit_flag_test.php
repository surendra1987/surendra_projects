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
 * @author Sam Hemelryk <sam.hemelryk@totara.com>
 * @package core
 */

use core\check\result;
use core\check\performance\prohibit_flag;

/**
 * Tests the CAP_PROHIBIT flag performance check
 *
 * @group check_api
 */
class core_check_performance_prohibit_flag_test extends \core_phpunit\testcase {
    public function test_get_name() {
        $check = new prohibit_flag();
        self::assertIsString($check->get_name());
        self::assertEquals(get_string('check_prohibit_flag_name', 'admin'), $check->get_name());
    }
    public function test_get_action_link() {
        $check = new prohibit_flag();
        $link = $check->get_action_link();
        self::assertInstanceOf(action_link::class, $link);
        self::assertStringEndsWith('/admin/roles/manage.php', $link->url->get_path());
        self::assertEmpty($link->url->params());
    }
    public function test_get_result_no_use() {
        $check = new prohibit_flag();
        $result = $check->get_result();

        self::assertSame(result::OK, $result->get_status());
        self::assertIsString($result->get_summary());
        self::assertEquals(get_string('check_prohibit_flag_status_ok', 'admin'), $result->get_summary());
        self::assertIsString($result->get_details());
        self::assertEquals(get_string('check_prohibit_flag_details', 'admin'), $result->get_details());
        self::assertSame('core/check_result', $result->get_template_name());
    }
    public function test_get_result_flag_used() {
        $role = $this->getDataGenerator()->create_role();
        assign_capability('moodle/site:config', CAP_PROHIBIT, $role, context_system::instance());

        $check = new prohibit_flag();
        $result = $check->get_result();

        self::assertSame(result::WARNING, $result->get_status());
        self::assertIsString($result->get_summary());
        self::assertEquals(get_string('check_prohibit_flag_status_warning', 'admin', 1), $result->get_summary());
        self::assertIsString($result->get_details());
        self::assertEquals(get_string('check_prohibit_flag_details', 'admin'), $result->get_details());
        self::assertSame('core/check_result', $result->get_template_name());
    }
}