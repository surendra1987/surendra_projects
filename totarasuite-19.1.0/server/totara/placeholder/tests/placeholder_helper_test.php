<?php
/**
 * This file is part of Totara LMS
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
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package totara_placeholder
 */


use core_phpunit\testcase;

use totara_placeholder\placeholder_helper;

(defined('MOODLE_INTERNAL') && PHPUNIT_TEST) || die();

class totara_placeholder_placeholder_helper_test extends testcase {
    public function test_is_valid_placeholder_provider() {
        global $CFG;
        require_once("{$CFG->dirroot}/totara/placeholder/tests/fixtures/test_placeholder_provider.php");
        require_once("{$CFG->dirroot}/totara/placeholder/tests/fixtures/test_non_placeholder_provider.php");
        // test a valid placeholder provider
        $this->assertTrue(placeholder_helper::is_valid_placeholder_provider(\test_placeholder_provider::class));
        // test a non-valid placeholder provider
        $this->assertFalse(placeholder_helper::is_valid_placeholder_provider(\test_non_placeholder_provider::class));
        // test an unknown class
        $this->assertFalse(placeholder_helper::is_valid_placeholder_provider('unknown_class'));
    }
}