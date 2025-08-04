<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package core_enrol
 */

namespace core_enrol\entity;

use core\entity\user_enrolment;
use core\orm\entity\entity;
use core\orm\entity\relations\belongs_to;
use mod_approval\entity\application\application;

/**
 * Entity for table "user_enrolments_application"
 *
 * @property int        $id
 * @property int        $user_enrolments_id
 * @property int        $approval_application_id
 * @property int        $timecreated
 * @property int        $timemodified
 * @property-read user_enrolment $user_enrolment
 * @property-read application $approval_application
 */
class user_enrolment_application extends entity {
    /**
     * @var string
     */
    public const TABLE = 'user_enrolments_application';

    /**
     * @var string
     */
    public const CREATED_TIMESTAMP = 'timecreated';

    /**
     * @var string
     */
    public const UPDATED_TIMESTAMP = 'timemodified';

    /**
     * Get user enrolment
     *
     * @return belongs_to
     */
    public function user_enrolment(): belongs_to {
        return $this->belongs_to(user_enrolment::class, 'user_enrolments_id');
    }

    /**
     * Get approval workflow application
     *
     * @return belongs_to
     */
    public function approval_application(): belongs_to {
        return $this->belongs_to(application::class, 'approval_application_id');
    }
}