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
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package core
 */

use core\usagedata\session_handling;
use core_phpunit\testcase;
use tool_usagedata\export;

class core_usagedata_session_handling_test extends testcase {

    public function test_export() {
        global $CFG;

        set_config('session_handler_class', '\core\session\database');
        set_config('sessioncookie', 'test');
        set_config('sessioncookiepath', 'test');
        set_config('sessioncookiedomain', 'test');
        set_config('cookiesecure', true);
        set_config('cookiehttponly', true);

        $export_result = (new session_handling())->export();

        $this->assertEquals('\core\session\database', $export_result['session_handler_class']);
        $this->assertEquals($CFG->dbsessions, $export_result['dbsessions']);
        $this->assertEquals($CFG->sessiontimeout, $export_result['sessiontimeout']);
        $this->assertTrue($export_result['uses_sessioncookie']);
        $this->assertTrue($export_result['uses_sessioncookiepath']);
        $this->assertTrue($export_result['uses_sessioncookiedomain']);
        $this->assertEquals(1, $export_result['cookiesecure']);
        $this->assertEquals(1, $export_result['cookiehttponly']);

        // Now let's test the conditionals (based on empty state)
        unset_config('session_handler_class');
        unset_config('sessioncookie');
        unset_config('sessioncookiepath');
        unset_config('sessioncookiedomain');

        $export_result = (new session_handling())->export();

        $this->assertEquals(export::NOTSET, $export_result['session_handler_class']);
        $this->assertFalse($export_result['uses_sessioncookie']);
        $this->assertFalse($export_result['uses_sessioncookiepath']);
        $this->assertFalse($export_result['uses_sessioncookiedomain']);
    }
}