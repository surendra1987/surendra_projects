<?php
/*
 * This file is part of Totara Core
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
 * @author Sam Hemelryk <sam.hemelryk@totara.com>
 * @package repository_url
 */

defined('MOODLE_INTERNAL') || die();

use core\check\result;

/**
 * Test the enabled security check.
 */
class repository_url_check_security_enabled_test extends \core_phpunit\testcase {

    public function test_security_repositoryurl() {
        $check = new \repository_url\check\security\enabled();
        self::assertIsString($check->get_name());
        self::assertSame('repository_url', $check->get_component());
        self::assertInstanceOf(\action_link::class, $check->get_action_link());
        $result = $check->get_result();
        self::assertIsString($result->get_summary());
        self::assertIsString($result->get_details());
        // The URL repository should be disabled by default.
        self::assertEquals(result::OK, $result->get_status());
    }
}