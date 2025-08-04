<?php
/**
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package core
 */

use core\login\complete_login_callback;
use core_phpunit\testcase;

require_once ( __DIR__ . '/fixtures/complete_login_callback_fixtures.php');

/**
 * @coversDefaultClass core\login\complete_login_callback
 */
class core_login_complete_login_callback_test extends testcase {

    public function test_creating_a_callback_with_a_static_method() {
        global $CFG;
        $callback = complete_login_callback::create(
            [sample_callback::class, 'test_call'],
            [4]
        );

        $this->assertEquals(
            [sample_callback::class, 'test_call'],
            $callback->get_function()
        );
        $this->assertEquals([4], $callback->get_args());

        try {
            $callback->execute();
            $this->fail('Callback static method not executed');
        } catch (moodle_exception $exception) {
            $this->assertEquals('redirecterrordetected', $exception->errorcode);
            $this->assertEquals($CFG->wwwroot . "?no=4", $exception->link);
        }
    }

    public function test_executing_callback_with_a_function() {
        global $CFG;

        $callback = complete_login_callback::create(
            'sample_cb_redirect_function_tester',
            [1]
        );

        $this->assertEquals(
            'sample_cb_redirect_function_tester',
            $callback->get_function()
        );
        $this->assertEquals([1], $callback->get_args());

        try {
            $callback->execute();
            $this->fail('Callback function not executed');
        } catch (moodle_exception $exception) {
            $this->assertEquals('redirecterrordetected', $exception->errorcode);
            $this->assertEquals($CFG->wwwroot . "?no=1", $exception->link);
        }
    }
}
