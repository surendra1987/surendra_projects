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

use core\entity\cohort;
use core\entity\course;
use core\orm\entity\model;
use core\entity\enrol as enrol_entity;
use mod_approval\entity\workflow\workflow;

/**
 * @property int            $id
 * @property string         $enrol
 * @property int            $status
 * @property int            $courseid
 * @property int|null       $workflow_id
 * @property int            $sortorder
 * @property string|null    $name
 * @property int            $enrolperiod
 * @property int            $enrolstartdate
 * @property int            $enrolenddate
 * @property int            $expirynotify
 * @property int            $expirythreshold
 * @property int            $notifyall
 * @property string|null    $password
 * @property string|null    $cost
 * @property string|null    $currency
 * @property int            $roleid
 * @property int|null       $customint1
 * @property int|null       $customint2
 * @property int|null       $customint3
 * @property int|null       $customint4
 * @property int|null       $customint5
 * @property int|null       $customint6
 * @property int|null       $customint7
 * @property int|null       $customint8
 * @property string|null    $customchar1
 * @property string|null    $customchar2
 * @property string|null    $customchar3
 * @property string|null    $customdec1
 * @property string|null    $customdec2
 * @property string|null    $customtext1
 * @property string|null    $customtext2
 * @property string|null    $customtext3
 * @property string|null    $customtext4
 * @property int            $timecreated
 * @property int            $timemodified
 * @property-read  cohort|null $cohort
 *
 * Relationships:
 * @property-read course $course
 * @property-read user_enrolment[] $user_enrolments
 */
class enrol extends model {

    /**
     * @var string[]
     */
    protected $entity_attribute_whitelist = [
        'id',
        'enrol',
        'status',
        'courseid',
        'workflow_id',
        'sortorder',
        'name',
        'enrolperiod',
        'enrolstartdate',
        'enrolenddate',
        'expirynotify',
        'expirythreshold',
        'notifyall',
        'password',
        'cost',
        'currency',
        'roleid',
        'customint1',
        'customint2',
        'customint3',
        'customint4',
        'customint5',
        'customint6',
        'customint7',
        'customint8',
        'customchar1',
        'customchar2',
        'customchar3',
        'customdec1',
        'customdec2',
        'customtext1',
        'customtext2',
        'customtext3',
        'customtext4',
        'timecreated',
        'timemodified',
        'cohort',
        'course',
    ];

    /**
     * @var string[]
     */
    protected $model_accessor_whitelist = [
        'user_enrolments',
    ];

    /**
     * @return string
     */
    protected static function get_entity_class(): string {
        return enrol_entity::class;
    }

    /**
     * Get the user_enrolments associated with this enrolment instance.
     *
     * @return array of user_enrolment models
     */
    public function get_user_enrolments(): array {
        return $this->entity->user_enrolments()->load_for_entity()->map_to(user_enrolment::class)->all();
    }
}