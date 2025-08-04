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
use core_mfa\model\instance as instance;

/**
 * Event fired when an instance is deleted.
 */
class mfa_instance_deleted extends base {
    /**
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = instance_entity::TABLE;
    }

    /**
     * @param instance $model
     * @return self
     */
    public static function create_event(instance $model): self {
        $data = [
            'objectid' => $model->id,
            'relateduserid' => $model->user_id,
            'context' => \context_user::instance($model->user_id),
            'other' => [
                'factor_name' => $model->factor->get_display_name(),
                'date_created' => $model->created_at,
                'factor_notify' => $model->factor->notify_on_instance_delete(),
            ]
        ];

        /** @var self $event */
        $event = static::create($data);
        return $event;
    }

    /**
     * Name shown in the event log report.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('event_instance_deleted', 'core_mfa');
    }

    /**
     * Description shown in the event log report.
     *
     * @return string
     */
    public function get_description(): string {
        return "The MFA factor with id '$this->objectid' has been deleted by the user with id '$this->userid'";
    }
}