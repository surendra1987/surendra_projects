<?php
/*
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package totara_core
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

global $CFG;
require_once($CFG->dirroot . '/totara/core/pagelib.php');

use core_phpunit\testcase;

class totara_core_pagelib_test extends testcase {

    public function test_get_javascript_code(): void {
        global $_SERVER;

        // Switch to ajax.
        $orig_server_HTTP_X_REQUESTED_WITH = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? 'test_notset';
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';

        // Set up the manager and make the function invokable.
        $manager = new totara_page_requirements_manager();
        $class = new ReflectionClass($manager);
        $method = $class->getMethod('get_javascript_code');
        $method->setAccessible(true);

        // Add some js.
        $manager->js_function_call('test_js_call_normal', ['test_one', 'test_two'], false, 10); // Note false.
        $manager->js_function_call('test_js_calls_on_dom_ready', null, true); // Note true.

        // When on an ajax page and dom is ready.
        self::assertEquals('', $method->invokeArgs($manager, array(true)));

        // When on an ajax page and dom is not ready.
        self::assertStringContainsString(
            'setTimeout(function() { test_js_call_normal("test_one", "test_two"); }, 10000);',
            $method->invokeArgs($manager, array(false))
        );
        self::assertStringContainsString('test_js_calls_on_dom_ready();', $method->invokeArgs($manager, array(false)));

        // Set the server back to normal.
        if ($orig_server_HTTP_X_REQUESTED_WITH == 'test_notset') {
            unset($_SERVER['HTTP_X_REQUESTED_WITH']);
        } else {
            $_SERVER['HTTP_X_REQUESTED_WITH'] = $orig_server_HTTP_X_REQUESTED_WITH;
        }
    }
}
