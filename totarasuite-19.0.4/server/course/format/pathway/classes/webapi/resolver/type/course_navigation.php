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
 * This type resolver is top layer in the series of resolvers
 * that handle the `get_course_navigation` query for pathway courses.
 * The purpose of this resolver is to return the smallest possible amount of
 * data to the browser.
 * This resolver is responsible for handling information that will be shown
 * in the left-hand navigation menu
 * The expected shape of the return data is shown in `get_course_navigation.graphql`.
 */
class course_navigation extends type_resolver {

    /**
     * @inheritDoc
     */
    public static function resolve(string $field, $course, array $args, execution_context $ec) {
        global $USER;

        if ($course->id === null) {
            return [];
        }

        if ($field !== 'sections') {
            throw new \coding_exception(
                'The course navigation query resolver only has the "sections" option. Provided here: ',
                $field
            );
        }

        $modinfo = \course_modinfo::instance($course->id, $USER->id);
        return $modinfo->get_section_info_all();
    }
}
