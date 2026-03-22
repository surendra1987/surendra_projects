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
 * @package auth_ldap
 */


namespace auth_ldap;

use \moodle_exception;

/**
 * Complete user login callback
 */
class complete_user_login_callback {

    /**
     * Complete user login callback function
     *
     * @param $key
     * @param $plugin_config
     * @return void
     * @throws moodle_exception
     */
    public static function execute($key, $plugin_config) {
        global $USER, $CFG, $SESSION;
        // Cleanup the key to prevent reuse...
        // and to allow re-logins with normal credentials
        unset_cache_flag($plugin_config.'/ntlmsess', $key);

        // Redirection
        if (user_not_fully_set_up($USER, true)) {
            $urltogo = $CFG->wwwroot.'/user/edit.php';
            // We don't delete $SESSION->wantsurl yet, so we get there later
        } else if (isset($SESSION->wantsurl) and (strpos($SESSION->wantsurl, $CFG->wwwroot . '/') === 0)) {
            $urltogo = $SESSION->wantsurl;    // Because it's an address in this site
            unset($SESSION->wantsurl);
        } else {
            // No wantsurl stored or external - go to homepage
            $urltogo = $CFG->wwwroot.'/';
            unset($SESSION->wantsurl);
        }
        // We do not want to redirect if we are in a PHPUnit test.
        if (!PHPUNIT_TEST) {
            redirect($urltogo);
        }
    }
}