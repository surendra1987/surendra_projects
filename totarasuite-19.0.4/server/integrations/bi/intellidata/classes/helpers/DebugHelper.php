<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This plugin provides access to Moodle data in form of analytics and reports in real time.
 *
 *
 * @package    bi_intellidata
 * @copyright  2020 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @website    http://intelliboard.net/
 */

namespace bi_intellidata\helpers;

class DebugHelper {

    /**
     * @return bool
     * @throws \dml_exception
     */
    public static function debugenabled() {
        return SettingsHelper::get_setting('debugenabled');
    }

    /**
     * @param $errorstring
     * @throws \dml_exception
     */
    public static function error_log($errorstring) {
        if (self::debugenabled()) {
            syslog(LOG_ERR, 'IntelliData Debug: ' . $errorstring);
        }
    }

    /**
     * @throws \dml_exception
     */
    public static function enable_moodle_debug () {
        global $CFG;

        if (self::debugenabled()) {
            $CFG->debug = DEBUG_DEVELOPER;
            $CFG->debugdeveloper = true;
            $CFG->debugdisplay = true;
        }
    }

    /**
     * @throws \dml_exception
     */
    public static function disable_moodle_debug () {
        global $CFG;

        if (self::debugenabled()) {
            $CFG->debug = 0;
            $CFG->debugdeveloper = false;
            $CFG->debugdisplay = false;
        }
    }

    /**
     * Returns whether request coming from the external API. If so, we don't normally want to insert log output into the response.
     * @return bool
     */
    public static function is_api_request(): bool {
        $is_api_request = false;
        if (isset($_SERVER['REQUEST_SCHEME']) || (defined('PHPUNIT_TEST') && PHPUNIT_TEST)) {
            $is_api_request = true;
        }
        return $is_api_request;
    }

    /**
     * Is the request coming from the external API? If so, we don't normally want to insert log output into the response.
     * @return void
     */
    public static function log_if_not_api_request(string $log_message): void {
        if (!self::is_api_request()) {
            mtrace($log_message);
        }
    }
}
