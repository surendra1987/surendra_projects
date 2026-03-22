<?php
/**
 * This file is part of Totara Core
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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package engage_course
 */

namespace engage_course\webapi\middleware;

use container_course\course;
use core\entity\course as course_entity;
use core\webapi\resolver\payload;
use core\webapi\resolver\result;

/**
 * Will confirm the provided course ids are course ids that are visible to the active $USER.
 * Throws an exception otherwise.
 */
class validate_course_ids implements \core\webapi\middleware {
    /**
     * @var string Where in the payload to find the ids array
     */
    protected string $input_key;

    /**
     * @param string $input_key
     */
    public function __construct(string $input_key) {
        $this->input_key = $input_key;
    }

    /**
     * @param payload $payload
     * @param \Closure $next
     * @return result
     */
    public function handle(payload $payload, \Closure $next): result {
        global $USER;

        $course_ids = $payload->get_variable($this->input_key);
        if (empty($course_ids) || !is_array($course_ids)) {
            throw new \coding_exception('Argument must be an array of course ids');
        }

        [$sql_where, $sql_params] = totara_visibility_where($USER->id);
        $valid_course_ids = course_entity::repository()
            ->select('id')
            ->where_in('id', $course_ids)
            ->where_raw($sql_where, $sql_params)
            ->where('containertype', course::get_type())
            ->get()
            ->pluck('id');

        if (count($valid_course_ids) != count($course_ids)) {
            throw new \coding_exception('Invalid course_ids provided to add_to_playlist mutation');
        }

        return $next($payload);
    }
}

