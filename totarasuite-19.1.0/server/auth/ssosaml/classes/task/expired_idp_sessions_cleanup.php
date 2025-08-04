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
 * @package auth_ssosaml
 */

namespace auth_ssosaml\task;

use auth_ssosaml\entity\session as idp_session;
use core\collection;
use core\session\manager as session_manager;
use core\task\scheduled_task;

/**
 * Cleans up expired idp sessions as stated in the SessionNotOnOrAfter attribute of the SAML login response
 */
class expired_idp_sessions_cleanup extends scheduled_task {

    /**
     * @inheritDoc
     */
    public function get_name() {
        return get_string('expired_idp_sessions_cleanup_task_name', 'auth_ssosaml');
    }

    /**
     * @inheritDoc
     */
    public function execute() {
        /** @var collection|idp_session[] $expired_user_sessions*/
        $expired_user_sessions = idp_session::repository()
            ->where_not_null('user_id')
            ->where_not_null('session_not_on_or_after')
            ->where('session_not_on_or_after', '<=', time())
            ->where('status', idp_session::STATUS_COMPLETED)
            ->get();

        foreach ($expired_user_sessions as $user_session) {
            session_manager::kill_session($user_session->session_id);
        }

        idp_session::repository()
            ->where_in('id', $expired_user_sessions->pluck('id'))
            ->delete();
    }
}
