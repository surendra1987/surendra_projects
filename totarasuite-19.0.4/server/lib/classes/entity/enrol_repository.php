<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2021 onwards Totara Learning Solutions LTD
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
 * @author  Kian Nguyen <kian.nguyen@totaralearning.com>
 * @package core
 */
namespace core\entity;

use core\orm\collection;
use core\orm\entity\repository;

/**
 * Database repository class for enrol table.
 *
 * @method static enrol|null one(bool $strict)
 */
class enrol_repository extends repository {

    /**
     * Finds a single enrolment instance matching the plugin name (eg 'self') and course id.
     *
     * Warning, this may fail if there are multiple instances of the named enrolment plugin on the course.
     *
     * @param string $enrol_name
     * @param int    $course_id
     * @param bool   $strict
     *
     * @return enrol|null
     */
    public function find_enrol(string $enrol_name, int $course_id, bool $strict = false): ?enrol {
        $repository = enrol::repository();
        $repository->where('enrol',  $enrol_name);
        $repository->where('courseid', $course_id);

        return $repository->one($strict);
    }

    /**
     * Returns a (possibly empty) collection of enrolment instance entities for a given course and plugin.
     *
     * @param string $enrol_name
     * @param int $course_id
     * @return collection
     */
    public function find_by_enrol_and_course(string $enrol_name, int $course_id): collection {
        return $this->builder->where('courseid', '=', $course_id)
            ->where('enrol', '=', $enrol_name)
            ->get();
    }
}