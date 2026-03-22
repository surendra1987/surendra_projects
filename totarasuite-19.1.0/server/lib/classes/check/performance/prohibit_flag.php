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
 * @author Sam Hemelryk <sam.hemelryk@totara.com>
 * @package core
 */

namespace core\check\performance;

defined('MOODLE_INTERNAL') || die();

use core\check\check;
use core\check\result;

class prohibit_flag extends check {

    /**
     * Get the short check name
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('check_prohibit_flag_name', 'admin');
    }

    /**
     * A link to a place to action this
     *
     * @return \action_link|null
     */
    public function get_action_link(): ?\action_link {
        return new \action_link(
            new \moodle_url('/admin/roles/manage.php'),
            get_string('manageroles', 'role'));
    }

    /**
     * Return result
     * @return result
     */
    public function get_result(): result {
        global $DB;

        $count_prohibit = $DB->count_records('role_capabilities', ['permission' => (string)CAP_PROHIBIT]);
        if ($count_prohibit === 0) {
            return new result(
                result::OK,
                get_string('check_prohibit_flag_status_ok', 'admin'),
                get_string('check_prohibit_flag_details', 'admin'),
            );
        }

        return new result(
            result::WARNING,
            get_string('check_prohibit_flag_status_warning', 'admin', $count_prohibit),
            get_string('check_prohibit_flag_details', 'admin'),
        );
    }
}

