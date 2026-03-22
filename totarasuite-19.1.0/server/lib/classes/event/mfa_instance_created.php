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
 * @package core
 */

namespace core\event;

use core_mfa\entity\instance as instance_entity;
use core_mfa\model\instance;
use moodle_url;

/**
 * Event fired when a factor is registered.
 */
class mfa_instance_created extends base {
    /**
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = instance_entity::TABLE;
    }

    /**
     * @param instance_entity $entity
     * @return self
     */
    public static function create_event(instance_entity $entity): self {
        $factor = instance::load_by_entity($entity)->factor;
        $data = [
            'objectid' => $entity->id,
            'relateduserid' => $entity->user_id,
            'context' => \context_user::instance($entity->user_id),
            'other' => [
                'factor_name' => $factor->get_display_name(),
                'date_created' => $entity->created_at,
                'factor_notify' => $factor->notify_on_instance_create(),
            ]
        ];

        /** @var self $event */
        $event = static::create($data);
        return $event;
    }

    /**
     * Link in the event log report.
     *
     * @return moodle_url
     */
    public function get_url() {
        return new moodle_url('/mfa/user_preferences.php');
    }

    /**
     * Name shown in the event log report.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('event_instance_created', 'core_mfa');
    }

    /**
     * Description shown in the event log report.
     *
     * @return string
     */
    public function get_description(): string {
        return "The user with id '$this->userid' added new mfa instance '$this->objectid' for '$this->relateduserid'";
    }
}