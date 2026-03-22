<?php
/*
 * This file is part of Totara Talent Experience Platform
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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 */

use core_ai\configuration\option;
use core_phpunit\testcase;

/**
 * @group ai_integrations
*/
class core_ai_configuration_option_test extends testcase {
    public function test_is_secure() {
        $option = new option($this->createMock(admin_setting::class), false);
        $this->assertFalse($option->is_secure());

        $option = new option($this->createMock(admin_setting::class), true);
        $this->assertTrue($option->is_secure());
    }

    public function test_get_admin_setting() {
        $option = new option($this->createMock(admin_setting::class), false);
        $this->assertInstanceOf(admin_setting::class, $option->get_admin_setting());
    }
}

