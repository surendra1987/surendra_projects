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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Navjeet Singh <navjeet.singh@totara.com>
 * @package auth_ssosaml
 */

namespace auth_ssosaml\task;

use auth_ssosaml\entity\session as idp_session;

defined('MOODLE_INTERNAL') || die();

/**
 * Session cleanup task.
 */
class session_cleanup_task extends \core\task\scheduled_task {

    /**
     * @inheritDoc
     *
     * @return string
     */
    public function get_name() {
        return get_string('cleanup_task_name', 'auth_ssosaml');
    }

    /**
     * Do the job.
     */
    public function execute() {
        if (!is_enabled_auth('ssosaml')) {
            idp_session::repository()->delete();
            return;
        }

        idp_session::repository()
            ->where('status', idp_session::STATUS_INITIATED)
            ->where_null('name_id')
            ->where_null('session_index')
            ->where('created_at', '<', time() - DAYSECS)
            ->delete();

        // Test sessions will stick around for an hour
        idp_session::repository()
            ->where('test', 1)
            ->where('created_at', '<', time() - HOURSECS)
            ->delete();
    }
}
