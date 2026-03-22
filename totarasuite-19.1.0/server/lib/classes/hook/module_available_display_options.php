<?php
/*
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
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package mod_resource
 */

namespace core\hook;

use core\entity\course;
use totara_core\hook\base;

class module_available_display_options extends base {
    /**
     * @var string
     */
    public string $module_name;

    /**
     * @var array
     */
    public array $available_options;

    /**
     * @var int
     */
    public int $course_id;

    /**
     * @param array $available_options
     * @param string $module_name
     * @param int $course_id
     */
    public function __construct(array $available_options, string $module_name, int $course_id) {
        $this->module_name = $module_name;
        $this->available_options = $available_options;
        $this->course_id = $course_id;
    }

    /**
     * @return course
     */
    public function get_course(): course {
        return new course($this->course_id);
    }

    /**
     * @return string
     */
    public function get_course_format(): string {
        return $this->get_course()->format;
    }
}
