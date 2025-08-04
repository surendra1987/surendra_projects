<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Angela Kuznetsova <angela.kuznetsova@totara.com>
 * @package totara_tenant
 */

namespace totara_tenant;

use totara_core\event\user_unsuspended;
use totara_core\event\user_undeleted;
use totara_tenant\local\util;

class totara_tenant_observer {
    /**
     * Check if unsuspended user member of tenant audience and add them if not.
     *
     * @param user_unsuspended $event
     */
    public static function user_unsuspended(user_unsuspended $event) {
        global $CFG;

        $user = $event->get_record_snapshot('user', $event->objectid);
        if (!empty($CFG->tenantsenabled) && !is_null($user->tenantid)) {
            util::refresh_audience_membership($user);
        }
    }

    /**
     * Check if undeleted user member of tenant audience and add them if not.
     *
     * @param user_undeleted $event
     */
    public static function user_undeleted(user_undeleted $event) {
        global $CFG;

        $user = $event->get_record_snapshot('user', $event->objectid);
        if (!empty($CFG->tenantsenabled) && !is_null($user->tenantid)) {
            util::refresh_audience_membership($user);
        }
    }
}
