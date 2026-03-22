<?php
/**
 * This file is part of Totara Engage
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package container_workspace
 */

namespace container_workspace\observer;

use totara_notification\external_helper;
use totara_notification\resolver\resolver_helper;
use container_workspace\event\user_role_changed;
use container_workspace\totara_notification\resolver\role_changed;

final class role_changed_observer {

    /**
     * Trigger 'User role changed' totara notification
     *
     * @param user_role_changed $event
     * @return void
     */
    public static function on_role_changed(user_role_changed $event): void {
        // Do not send notification to yourself.
        if ($event->userid === $event->relateduserid) {
            return;
        }

        $event_data = [
            'workspace_id' => $event->objectid,
            'actor_id' => $event->userid,
            'user_id' => $event->relateduserid,
            'role_id' => $event->other['role_id'],
        ];
        $resolver = resolver_helper::instantiate_resolver_from_class(role_changed::class, $event_data);
        external_helper::create_notifiable_event_queue($resolver);
    }
}
