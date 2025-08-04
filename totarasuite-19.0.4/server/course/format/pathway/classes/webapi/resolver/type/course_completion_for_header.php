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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Nathaniel Walmsley <nathaniel.walmsley@totara.com>
 * @package format_pathway
 */

namespace format_pathway\webapi\resolver\type;

use core\webapi\execution_context;
use core\webapi\type_resolver;

/**
 * This type resolver handles requests to the `get_course_completion` endpoint
 * for pathway courses.
 * The purpose of this resolver is to return the smallest possible amount of
 * data to the browser. The expected shape of this data is shown in
 * `get_course_completion.graphql`. The type resolver is passed a course
 * object by the query resolver of the same name.
 */
class course_completion_for_header extends type_resolver {

    /**
     * @inheritDoc
     */
    public static function resolve(string $field, $course, array $args, execution_context $ec) {
        global $USER;
        if ($field == 'completionenabled') {
            return $course->enablecompletion;
        }
        if ($field === 'completion') {
            if (!isset($course->id)) {
                // The user doesn't have access rights, so course ID isn't set
                return (object) [
                    'status' => '0',
                    'statuskey' => get_string('course_completion_for_header_statuskey_default', 'format_pathway'),
                    'progress' => 0
                ];
            }
            $completion = new \completion_completion(['userid'=> $USER->id,'course' => $course->id]);

            $data = new \stdClass();
            $data->status = $completion->status ?? '0';
            $data->statuskey = $completion->get_status($completion);
            $data->progress = $completion->get_percentagecomplete();
            return $data;
        }
        return null;
    }
}