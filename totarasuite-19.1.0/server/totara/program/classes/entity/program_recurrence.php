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

use core\entity\course;
use core\orm\entity\entity;
use core\orm\entity\relations\belongs_to;
use core\orm\entity\relations\has_one;

/**
 * An entity class that represents a row of table "prog_recurrence"
 *
 * Properties:
 * @property-read int $id Database record ID
 * @property int $programid
 * @property int $currentcourseid
 * @property int $nextcourseid
 *
 * Relationships:
 * @property-read course $current_course
 * @property-read course $next_course
 * @property-read program $program
 *
 * @method static program_recurrence_repository repository()
 */
class program_recurrence extends entity {

    public const TABLE = 'prog_recurrence';

    /**
     * @return belongs_to|program
     */
    public function program(): belongs_to {
        return $this->belongs_to(program::class, 'programid');
    }

    /**
     * @return has_one|course
     */
    public function current_course(): has_one {
        return $this->has_one(course::class, 'id', 'currentcourseid');
    }

    /**
     * @return has_one|course
     */
    public function next_course(): has_one {
        return $this->has_one(course::class, 'id', 'nextcourseid');
    }
}
