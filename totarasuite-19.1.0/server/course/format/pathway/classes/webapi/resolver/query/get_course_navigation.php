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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package format_pathway
 */

namespace format_pathway\webapi\resolver\query;

use context_course;
use core\webapi\execution_context;
use core\webapi\query_resolver;
use format_pathway\webapi\resolver\middleware\validate_format_pathway_course;

/**
 * This query resolver responds to the `get_course_navigation` GraphQL query for pathway courses.
 * Middleware is not used for this resolver because of the way that pathway courses are
 * loaded by the front-end.
 * We return a course object, and rely on the `course_navigation` and its nested type resolvers to
 * limit the information that is returned.
 */
class get_course_navigation extends query_resolver {
    public static function resolve(array $args, execution_context $ec) {
        global $CFG;
        require_once($CFG->dirroot . '/course/lib.php');

        if (!isset($args['course'])) {
            throw new \moodle_exception('invalidcourse');
        }

        $course = $args['course'];

        try {
            // Always prevent redirects for GraphQL requests
            // and we do not need to set the wantsurl to the current url
            \require_login($course, false, null, false, true);
        } catch (\require_login_exception $rle) {
            // See `get_course_navigation.graphql` to see what fields the query will try to access
            $empty_course = new \stdClass();
            $empty_course->id = null;
            return $empty_course;
        }

        // Set execution context context.
        $context = context_course::instance($course->id);
        $ec->set_relevant_context($context);

        return $course;
    }

    /**
     * @return array
     */
    public static function get_middleware(): array {
        return [
            new validate_format_pathway_course('course_id')
        ];
    }
}