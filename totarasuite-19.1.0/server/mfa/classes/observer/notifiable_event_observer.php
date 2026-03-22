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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package core_mfa
 */

namespace core_mfa\observer;

use core\event\base;
use core\event\mfa_instance_created as event_mfa_instance_created;
use core\event\mfa_instance_deleted as event_mfa_instance_deleted;
use core_mfa\model\instance;
use core_mfa\totara_notification\resolver\mfa_instance_created;
use core_mfa\totara_notification\resolver\mfa_instance_deleted;
use totara_notification\external_helper;

/**
 * Observers for MFA notifications.
 */
class notifiable_event_observer {
    /**
     * @param event_mfa_instance_created|event_mfa_instance_deleted $event
     * @return void
     */
    public static function mfa_instance(base $event): void {
        $is_create = $event instanceof event_mfa_instance_created;
        $is_delete = $event instanceof event_mfa_instance_deleted;

        if (!$is_create && !$is_delete) {
            // Not an event we want to observe;
            return;
        }

        if (!isset($event->other['factor_notify']) || !$event->other['factor_notify']) {
            return;
        }

        $data = [
            'subject_user_id' => $event->relateduserid,
            'factor_id' => $event->objectid,
            'factor_name' => $event->other['factor_name'],
            'date_created' => $event->other['date_created'],
        ];

        $resolver = $is_create ? mfa_instance_created::class : mfa_instance_deleted::class;
        external_helper::create_notifiable_event_queue(new $resolver($data));
    }
}
