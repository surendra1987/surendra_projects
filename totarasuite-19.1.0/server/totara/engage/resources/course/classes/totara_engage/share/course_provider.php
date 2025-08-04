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

namespace engage_course\totara_engage\share;

use core\entity\course as entity;
use engage_course\totara_engage\resource\course;
use totara_engage\access\access;
use totara_engage\entity\engage_resource;
use totara_engage\share\provider;
use totara_engage\share\shareable;

class course_provider extends provider {
    /**
     * @param int $id Resource ID
     * @return shareable 
     */
    public function get_item_instance(int $id): shareable {
        return course::from_resource_id($id);
    }

    /**
     * Get item instance from instance ID (i.e. course ID)
     *
     * @param int $instance_id
     * @return shareable
     */
    public function get_item_instance_from_instance_id(int $instance_id): shareable {
        global $DB, $USER;
        $actor_id = $USER->id;

        // Course is special resource, so we need to check if it is existing.
        $resource_id = $DB->get_field(engage_resource::TABLE, 'id', [
            'instanceid' => $instance_id,
            'resourcetype' => course::get_resource_type(),
            'userid' => $actor_id
        ]);

        if ($resource_id) {
            return course::from_resource_id($resource_id);
        }

        $entity = new entity($instance_id);

        $data = [
            'name' => $entity->fullname,
            'instanceid' => $instance_id,
            'resourcetype' => course::get_resource_type(),
            'access' => access::PUBLIC,
        ];

        return course::create($data, $actor_id);
    }

    /**
     * Access of courses is never changed via the resource.
     *
     * @param shareable $instance
     * @param int $access
     * @param int $userid
     * @return void
     */
    public function update_access(shareable $instance, int $access, int $userid): void {
        // No action
    }

    /**
     * @return string
     */
    public function get_provider_type(): string {
        return 'course';
    }
}