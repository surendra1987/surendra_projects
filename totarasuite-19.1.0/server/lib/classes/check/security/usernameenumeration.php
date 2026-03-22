<?php
/**
 * This file is part of Totara Core
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
 * @author Sam Hemelryk <sam.hemelryk@totaralearning.com>
 * @package core
 */

namespace core\check\security;

defined('MOODLE_INTERNAL') || die();

use core\check\result;
use core\check\check;

/**
 * Test if registerauth has been enabled or if protect usernames has been turned off and warn about possible user enumeration.
 */
class usernameenumeration extends check {

    /**
     * @inheritDoc
     */
    public function get_name(): string {
        return get_string('check_usernameenumeration_name', 'admin');
    }

    /**
     * @inheritDoc
     */
    public function get_action_link(): ?\action_link {
        return new \action_link(
            new \moodle_url('/admin/settings.php?section=manageauths'),
            get_string('authsettings', 'admin'));
    }

    /**
     * @inheritDoc
     */
    public function get_result(): result {
        global $CFG;
        // We only check registerauth, we don't check that if its set the plugin actually facilitates it.
        // This is because the plugin may have hidden logic, really if registerauth is set we can presume that "someone" can signup.
        // We also check $CFG->protectusernames because that allows username enumeration as well.
        if (empty($CFG->registerauth) && !empty($CFG->protectusernames)) {
            $status = result::OK;
            $summary = get_string('check_usernameenumeration_ok', 'admin');
        } else {
            $status = result::WARNING;
            $summary = get_string('check_usernameenumeration_warning', 'admin');
        }

        $details = get_string('check_usernameenumeration_details', 'admin');

        return new result($status, $summary, $details);
    }
}

