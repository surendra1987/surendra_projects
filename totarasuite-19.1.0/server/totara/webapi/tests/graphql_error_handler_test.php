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
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package totara_webapi
 */

defined('MOODLE_INTERNAL') || die();

class totara_webapi_graphql_error_handler_test extends \core_phpunit\testcase {

    /**
     * Check that normal mode removes the full path and replaces with dirroot
     * from setuplib's get_exception_info
     *
     * @return void
     */
    public function test_new_client_aware_exception_with_normal_mode(): void {
        $ex_message = __DIR__;
        $error = new Exception($ex_message, 0, new exception($ex_message));
        $expected_debug_message = $this->replace_dirroot($error);
        $scrubbed_errors = \totara_webapi\local\util::graphql_error_handler([$error], function($errors){
            static $debug = 1;
            return GraphQL\Error\FormattedError::prepareFormatter(null, $debug);
        });
        $exception_output = (reset($scrubbed_errors))($error);
        $this->assertSame("Internal server error", $exception_output['message']);
        $this->assertNotEquals($ex_message, $exception_output['message']);
        $this->assertSame($expected_debug_message, $exception_output['extensions']['debugMessage']);
    }

    /**
     * Check that none mode removes the full path and replaces with dirroot
     * from setuplib's get_exception_info
     *
     * @return void
     */
    public function test_new_client_aware_exception_with_none_mode(): void {
        $ex_message = __DIR__;
        $error = new Exception($ex_message, 0, new exception($ex_message));
        $scrubbed_errors = \totara_webapi\local\util::graphql_error_handler([$error], function($errors){
            static $debug = 0;
            return GraphQL\Error\FormattedError::prepareFormatter(null, $debug);
        });
        $exception_output = (reset($scrubbed_errors))($error);
        $this->assertSame("Internal server error", $exception_output['message']);
        $this->assertArrayNotHasKey('debugMessage', $exception_output);
    }

    /**
     * Check that debug mode doesn't replace and leaves the full path in
     *
     * @return void
     */
    public function test_new_client_aware_exception_with_debug_mode(): void {
        $ex_message = __DIR__;
        $error = new Exception($ex_message, 0, new exception($ex_message));
        $scrubbed_errors = \totara_webapi\local\util::graphql_error_handler([$error], function($errors){
            static $debug = 3;
            return GraphQL\Error\FormattedError::prepareFormatter(null, $debug);
        });
        $exception_output = (reset($scrubbed_errors))($error);
        $this->assertSame("Internal server error", $exception_output['message']);
        $this->assertArrayHasKey('trace', $exception_output['extensions']);
        $this->assertSame($ex_message, $exception_output['extensions']['debugMessage']);
    }

    private function replace_dirroot(Exception $exception): string {
        global $CFG;
        $search = "";
        $replace = "";
        if (property_exists($CFG, 'dirroot')) {
            $search = $CFG->dirroot;
            $replace = "[dirroot]";
        }
        return str_replace($search, $replace, $exception->getMessage());
    }
}
