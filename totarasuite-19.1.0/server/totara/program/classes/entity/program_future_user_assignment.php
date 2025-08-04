<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2022 onwards Totara Learning Solutions LTD
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package totara_program
 */

namespace totara_program\entity;

use core\entity\user;
use core\orm\entity\entity;
use core\orm\entity\relations\belongs_to;

/**
 * An entity class that represents a row of table "prog_future_user_assignment"
 *
 * Properties:
 *
 * @property-read int $id Database record ID
 * @property int $programid
 * @property int $userid
 * @property int $assignmentid
 *
 * Relationships:
 * @property-read user $user
 * @property-read program $program
 * @property-read program_assignment $program_assignment
 *
 * @method static program_future_user_assignment_repository repository()
 *
 */
class program_future_user_assignment extends entity {

    public const TABLE = 'prog_future_user_assignment';

    /**
     * @return belongs_to|user
     */
    public function user(): belongs_to {
        return $this->belongs_to(user::class, 'userid');
    }

    /**
     * @return belongs_to|program
     */
    public function program(): belongs_to {
        return $this->belongs_to(program::class, 'programid');
    }

    /**
     * @return belongs_to|program_assignment
     */
    public function program_assignment(): belongs_to {
        return $this->belongs_to(program_assignment::class, 'assignmentid');
    }

}
