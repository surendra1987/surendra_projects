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

namespace mod_scorm\check\security;

defined('MOODLE_INTERNAL') || die();

use core\check\check;
use core\check\result;

/**
 * Verifies if the sessionkeepalive setting in scorm is enabled on this site.
 */
class sessionkeepalive extends check {

    /**
     * @inheritDoc
     */
    public function get_name(): string {
        return get_string('check_sessionkeepalive_name', 'scorm');
    }

    /**
     * @inheritDoc
     */
    public function get_action_link(): ?\action_link {
        return new \action_link(
            new \moodle_url('/admin/settings.php?section=modsettingscorm#admin-sessionkeepalive'),
            get_string('modulename', 'scorm'));
    }

    /**
     * @inheritDoc
     */
    public function get_result(): result {
        if (!get_config('scorm', 'sessionkeepalive')) {
            $status = result::OK;
            $summary = get_string('check_sessionkeepalive_ok', 'scorm');
        } else {
            $status = result::WARNING;
            $summary = get_string('check_sessionkeepalive_warning', 'scorm');
        }

        $details = get_string('check_sessionkeepalive_details', 'scorm');

        return new result($status, $summary, $details);
    }
}

