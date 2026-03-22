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
 * @author Navjeet Singh <navjeet.singh@totara.com>
 * @package core_mfa
 */

namespace core_mfa\check\security;

use core\check\check;
use core\check\result;
use core_mfa\mfa;

/**
 * Checks auth plugins are compatible with MFA
 */
class auth_plugin_compatibility extends check {

    /**
     * Get the short check name
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('check_auth_plugin_compatibility_name', 'mfa');
    }

    /**
     * A link to a place to action this
     *
     * @return \action_link|null
     */
    public function get_action_link(): ?\action_link {
        return new \action_link(
            new \moodle_url('/admin/settings.php?section=manageauths'),
            get_string('auth_plugins', 'mfa')
        );
    }

    /**
     * Return result
     * @return result
     */
    public function get_result(): result {
        if (mfa::has_incompatible_auth_plugins()) {
            $status = result::WARNING;
            $summary = get_string('error:auth_plugins_compatibility', 'mfa');
        } else {
            $status = result::OK;
            $summary = get_string('check_auth_plugin_compatibility', 'mfa');
        }

        $details = get_string('check_auth_plugin_compatibility_detail', 'mfa');

        return new result($status, $summary, $details);
    }
}