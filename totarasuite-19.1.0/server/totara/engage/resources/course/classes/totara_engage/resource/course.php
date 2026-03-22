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

namespace engage_course\totara_engage\resource;

use coding_exception;
use container_course\course as course_container;
use core\entity\course as course_entity;
use core\entity\course as entity;
use engage_course\event\course_reshared;
use engage_course\event\course_shared;
use moodle_url;
use totara_engage\access\access_manager;
use totara_engage\entity\engage_resource;
use totara_engage\resource\resource_item;
use totara_engage\share\share as share_model;

/**
 * Model for course resource.
 */
final class course extends resource_item {
    /**
     * @var course_entity
     */
    private $course;

    /**
     * Constructing a model of course.
     *
     * @param course_entity $course
     * @param engage_resource $entity
     *
     * @return course
     */
    public static function from_entity(course_entity $course, engage_resource $entity): course {
        $resourcetype = self::get_resource_type();
        if ($resourcetype != $entity->resourcetype) {
            throw new coding_exception("Invalid resource record that is used for different component");
        }

        if (!$entity->exists() || !$course->exists()) {
            throw new coding_exception("Either resource record or the course record is not being populated");
        } else if ($entity->instanceid != $course->id) {
            throw new coding_exception("Resource record is not meant for the course");
        }

        $resource = new self($entity);
        $resource->course = $course;

        return $resource;
    }

    /**
     * @param int $resourceid
     * @return course
     */
    public static function from_resource_id(int $resourceid): resource_item {
        /** @var course $resource */
        $resource = parent::from_resource_id($resourceid);

        if ($resource->get_resourcetype() !== static::get_resource_type()) {
            throw new coding_exception('Resource type is not meant for the course');
        }

        $instanceid = $resource->get_instanceid();
        $resource->course = new course_entity($instanceid);

        return $resource;
    }

    /**
     * There are no specific capabilities required for courses.
     *
     * @param int $userid
     * @return bool
     */
    public static function can_create(int $userid): bool {
        return true;
    }

    /**
     * @param array $data
     * @param engage_resource $entity
     * @param int $userid
     * @return int
     */
    protected static function do_create(array $data, engage_resource $entity, int $userid): int {
        if (isset($data['instanceid'])) {
            $entity = new entity($data['instanceid']);
            return $entity->id;
        } else {
            throw new coding_exception('Course not found!');
        }
    }

    /**
     * @return int
     */
    public function get_course_id(): int {
        return $this->course->id;
    }

    /**
     * Deleting is handled via unsharing instead.
     *
     * @param int $userid
     * @return bool
     */
    public function can_delete(int $userid): bool {
        return false;
    }

    /**
     * There is nothing to update on the course resource.
     *
     * @param int $userid
     * @return bool
     */
    public function can_update(int $userid): bool {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function can_share(int $userid): bool {
        $owner = $this->get_userid();

        if ($owner == $userid) {
            return true;
        }

        return access_manager::can_manage_engage($this->get_context(), $userid);
    }

    /**
     * @inheritDoc
     */
    public function shared(share_model $share): void {
        // Create a shared event.
        if (!$share->is_notified()) {
            $event = course_shared::from_share($share);
            $event->trigger();
        }
    }

    /**
     * @param int $userid
     */
    public function reshare(int $userid): void {
        $event = course_reshared::from_course($this, $userid);
        $event->trigger();
    }

    /**
     * @inheritDoc
     *
     * @param bool $reload
     * @return void
     */
    public function refresh(bool $reload = false): void {
        // Nothing that needs refreshing
    }

    /**
     * @return string
     */
    public function get_url(): string {
        return (new moodle_url('/totara/engage/resources/course/index.php', ['id' => $this->get_instanceid()]))->out(false);
    }

    /**
     * @inheritDoc
     */
    public function can_unshare(int $sharer_id, ?bool $is_container = false): bool {
        global $USER;

        // Check if current user is sharer.
        if ($USER->id == $sharer_id) {
            return true;
        }

        // Check if current user is the owner of the resource.
        if ($USER->id == $this->get_userid()) {
            return true;
        }

        return access_manager::can_manage_engage($this->get_context(), $USER->id);
    }

    /**
     * No action, we don't delete here.
     *
     * @param int $userid
     * @return bool
     */
    protected function do_delete(int $userid): bool {
        return true;
    }

    /**
     * No actions, we don't update here.
     *
     * @param array $data
     * @param int $userid
     * @return bool
     */
    protected function do_update(array $data, int $userid): bool {
        return true;
    }

    /**
     * Course can't be created via engage
     *
     * @return bool
     */
    public static function can_create_source_item(): bool {
        return false;
    }
}