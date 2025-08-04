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
 * @package auth_shibboleth
 */

namespace auth_shibboleth;

/**
 * Complete user login callback
*/
class complete_user_login_callback {

    /**
     * Complete login callback function.
     *
     * @return void
     */
    public static function execute() {
        global $CFG, $USER;

        if (user_not_fully_set_up($USER, true)) {
            $urltogo = $CFG->wwwroot.'/user/edit.php?id='.$USER->id.'&amp;course='.SITEID;
            // We don't delete $SESSION->wantsurl yet, so we get there later

        } else if (isset($SESSION->wantsurl) and (strpos($SESSION->wantsurl, $CFG->wwwroot . '/') === 0)) {
            $urltogo = $SESSION->wantsurl;    /// Because it's an address in this site
            unset($SESSION->wantsurl);

        } else {
            $urltogo = $CFG->wwwroot.'/';      /// Go to the standard home page
            unset($SESSION->wantsurl);         /// Just in case
        }

        // If the url to go to is the same as the site page, check for default homepage.
        if ($urltogo === $CFG->wwwroot or $urltogo === $CFG->wwwroot.'/' or $urltogo === $CFG->wwwroot.'/index.php') {
            if (get_home_page() == HOMEPAGE_TOTARA_DASHBOARD) {
                $urltogo = $CFG->wwwroot.'/totara/dashboard/';
            } else if (get_home_page() == HOMEPAGE_TOTARA_GRID_CATALOG) {
                $urltogo = $CFG->wwwroot.'/totara/catalog/';
            }
        }

        redirect($urltogo);

        exit;
    }
}