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
 * @author David Curry <david.curry@totara.com>
 * @package core
 */

namespace core\webapi\resolver\query;

use coursecat;
use core\webapi\execution_context;
use core\webapi\middleware\require_login;
use core\webapi\query_resolver;

class course_list extends query_resolver {

    public static function resolve(array $args, execution_context $ec) {
        global $DB;

        $courses = [];
        $course_ids = $args['course_ids'] ?? [];

        // Make sure we aren't including the site course in this.
        $key = array_search(SITEID, $course_ids);
        if ($key !== false) {
            unset($course_ids[$key]);
        }

        if (!empty($course_ids)) {
            // Generate the where/params for the IN().
            list($where, $params) = $DB->get_in_or_equal($course_ids, SQL_PARAMS_NAMED, 'list');

            // Use coursecat function that checks visibility to fetch records.
            $db_courses = coursecat::get_course_records('c.id ' . $where, $params, ['summary' => true], true);
            foreach ($db_courses as $course) {
                $course->image = course_get_image($course);

                $courses[] = $course;
            }
        }

        return $courses;
    }

    public static function get_middleware(): array {
        return [
            new require_login()
        ];
    }
}
