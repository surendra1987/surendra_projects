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
 * @author Angela Kuznetsova <angela.kuznetsova@totara.com>
 * @package engage_course
 */

namespace engage_course\observer;

use core\event\course_deleted;
use core\event\course_updated;
use core\event\user_deleted;
use core\orm\query\builder;
use engage_course\totara_engage\resource\course;
use totara_engage\entity\engage_resource;
use core\entity\course as course_entity;

/**
 * Observer for course component
 */
class course_observer {

    /**
     * Triggered when course is deleted.
     *
     * @param course_deleted $event
     */
    public static function course_deleted_observer(course_deleted $event) {
        $course_id = $event->get_data()['objectid'] ?? null;
        if (!empty($course_id)) {
            $builder = builder::table(engage_resource::TABLE);
            $builder->where('resourcetype', course::get_resource_type());
            $builder->where('instanceid', $course_id)->delete();
        }
    }

    /**
     * Triggered when user is deleted.
     *
     * @param user_deleted $event
     */
    public static function user_deleted_observer(user_deleted $event) {
        $user_id = $event->get_data()['objectid'] ?? null;
        if (!empty($user_id)) {
            $builder = builder::table(engage_resource::TABLE);
            $builder->where('resourcetype', course::get_resource_type());
            $builder->where('userid', $user_id)->delete();
        }
    }

    /**
     * Observer for course_updated event.
     *
     * @param course_updated $event
     * @return void
     */
    public static function course_updated(course_updated $event): void {
        $course_id = $event->get_data()['objectid'] ?? null;

        if (empty($course_id)) {
            return;
        }

        $course_resources = engage_resource::repository()
            ->where('resourcetype', course::get_resource_type())
            ->where('instanceid', $course_id)
            ->get();

        if ($course_resources->count() <= 0) {
            return;
        }
        $course_entity = new course_entity($course_id);

        /** @var engage_resource $course_resource */

        foreach ($course_resources as $course_resource) {
            if ($course_resource->name != $course_entity->fullname) {
                try {
                    $course_resource->name = $course_entity->fullname;
                    $course_resource->save();
                } catch (\Exception $e) {
                    // Do nothing
                }
            }
        }
    }
}