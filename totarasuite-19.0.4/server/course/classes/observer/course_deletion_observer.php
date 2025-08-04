<?php
/**
 * This file is part of Totara Learn
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
 * @author Fabian Derschatta <fabian.derschatta@totara.com>
 * @package core_course
 */

namespace core_course\observer;

use core\event\course_deleted;
use totara_notification\external_helper;

class course_deletion_observer {

    /**
     * Catch course deleted events to deal with potential notification leftovers
     *
     * @param course_deleted $event
     * @return void
     */
    public static function cleanup_notifications(course_deleted $event) {
        // We have to make sure the preferences are deleted and all queued notifications with it
        external_helper::remove_notification_preferences_by_context($event->get_context());
    }
}
