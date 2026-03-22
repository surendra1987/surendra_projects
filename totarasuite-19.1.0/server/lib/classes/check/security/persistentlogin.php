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
 * Verifies if the persistentloginenabled setting is enabled on this site.
 */
class persistentlogin extends check {

    /**
     * @inheritDoc
     */
    public function get_name(): string {
        return get_string('check_persistentlogin_name', 'admin');
    }

    /**
     * @inheritDoc
     */
    public function get_action_link(): ?\action_link {
        return new \action_link(
            new \moodle_url('/admin/settings.php?section=sitepolicies#admin-persistentloginenable'),
            get_string('sitepolicies', 'admin'));
    }

    /**
     * @inheritDoc
     */
    public function get_result(): result {
        global $CFG;
        if (empty($CFG->persistentloginenable)) {
            $status = result::OK;
            $summary = get_string('check_persistentlogin_ok', 'admin');
        } else {
            $status = result::WARNING;
            $summary = get_string('check_persistentlogin_warning', 'admin');
        }

        $details = get_string('check_persistentlogin_details', 'admin');

        return new result($status, $summary, $details);
    }
}

