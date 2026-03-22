<?php
/**
 * This file is part of Totara Core
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
 * @author Michael Ivanov <michael.ivanov@totara.com>
 * @package core
 */

namespace core\model;

use core\entity\enrol;
use core\entity\user;
use core\model\enrol as enrol_model;
use core\orm\entity\model;
use core\entity\user_enrolment as entity;

/**
 * @property int        $id
 * @property int        $status
 * @property int        $enrolid
 * @property int        $userid
 * @property int|null   $timestart
 * @property int|null   $timeend
 * @property int        $modifierid
 * @property int        $timecreated
 * @property int        $timemodified
 * @property-read enrol $enrol_instance
 * @property-read user  $modified_by
 * @property-read int   $time_enrolled
 */
class user_enrolment extends model {

    /**
     * @var string[]
     */
    protected $entity_attribute_whitelist = [
        'id',
        'status',
        'enrolid',
        'userid',
        'timestart',
        'timeend',
        'modifierid',
        'timecreated',
        'timemodified',
        'enrol_instance',
        'modified_by',
        'time_enrolled',
    ];

    /**
     * @var string[]
     */
    protected $model_accessor_whitelist = [
        'enrolment'
    ];

    /**
     * @return string
     */
    protected static function get_entity_class(): string {
        return entity::class;
    }

    /**
     * @return enrol_model
     */
    public function get_enrolment(): enrol_model {
        return enrol_model::load_by_entity($this->entity->enrol_instance);
    }

    /**
     * Find the user_enrolment, if any, for a given enrolment instance and user.
     *
     * @param int $instance_id
     * @param int $user_id
     * @return static|null
     */
    public static function find_by_instance_and_user(int $instance_id, int $user_id): ?static {
        $entity = entity::repository()
            ->where('enrolid', '=', $instance_id)
            ->where('userid', '=', $user_id)
            ->one(false);
        if ($entity) {
            return static::load_by_entity($entity);
        }
        return null;
    }
}