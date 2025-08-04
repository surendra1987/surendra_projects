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

use core\orm\entity\repository;

class program_courseset_course_repository extends repository {

    /**
     * @param int $course_id
     * @return $this
     */
    public function filter_by_course_id(int $course_id): self {
        return $this->where('courseid', $course_id);
    }

    /**
     * @param int $program_courseset_id
     * @return $this
     */
    public function filter_by_program_courseset_id(int $program_courseset_id): self {
        return $this->where('coursesetid', $program_courseset_id);
    }
}
