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

namespace engage_course\event;

use coding_exception;
use context_user;
use core\entity\course as course_entity;
use core\event\base;
use engage_course\totara_engage\resource\course;

final class course_reshared extends base {
    /**
     * @return string
     */
    public static function get_name() {
        return get_string('coursereshared', 'engage_course');
    }

    /**
     * @param course $resource
     * @param int|null $actorid
     *
     * @return base
     */
    public static function from_course(course $resource, int $actorid = null): base {
        global $USER;

        if (!$resource->is_exists(true)) {
            throw new coding_exception("Unable to create an event for the not-existing course");
        }

        if (null == $actorid) {
            $actorid = $USER->id;
        }

        $ownerid = $resource->get_userid();
        $context = context_user::instance($ownerid);

        $data = [
            'objectid' => $resource->get_instanceid(),
            'context' => $context,
            'userid' => $actorid,
            'relateduserid' => $ownerid,
            'other' => [
                'name' => $resource->get_name(false),
                'resourceid' => $resource->get_id(),
                'owner_id' => $ownerid,
                'is_public' => $resource->is_public()
            ]
        ];

        /** @var base $event */
        return self::create($data);
    }

    /**
     * @return void
     */
    protected function init(): void {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = course_entity::TABLE;
    }
}