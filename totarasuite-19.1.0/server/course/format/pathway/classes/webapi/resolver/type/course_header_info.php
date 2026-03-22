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

use core\format;
use core\webapi\execution_context;
use core\webapi\formatter\field\string_field_formatter;
use core\webapi\type_resolver;

/**
 * This type resolver handles requests to the `get_course` endpoint
 * for pathway courses.
 * The purpose of this resolver is to return the smallest possible amount of
 * data to the browser. The expected shape of this data is shown in
 * `get_course.graphql`. The type resolver is provided a course object
 * by the query resolver of the same name.
 */
class course_header_info extends type_resolver {

    /**
     * @inheritDoc
     */
    public static function resolve(string $field, $course, array $args, execution_context $ec) {
        switch ($field) {
            case 'image_url':
                if (!isset($course->image)) {
                    $course->image = course_get_image($course);
                }

                if ($course->image instanceof \moodle_url) {
                    $course->image = $course->image->out();
                }
                return $course->image;
            case 'full_name':
                $formatter = new string_field_formatter(format::FORMAT_PLAIN, $ec->get_relevant_context());
                return $formatter->format($course->fullname);
            case 'id':
                return $course->id;
            default:
                return null;
        }
    }
}