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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package format_pathway
 */

namespace format_pathway\webapi\resolver\query;

use coding_exception;
use container_course\course;
use container_course\interactor\course_interactor;
use core\webapi\execution_context;
use core\webapi\middleware\require_login;
use core\webapi\query_resolver;
use format_pathway\webapi\resolver\middleware\validate_format_pathway_course;

/**
 * This query resolver is to get course interactor
 */
class get_course_interactor extends query_resolver {
    /**
     * @param array $args
     * @param execution_context $ec
     * @return course_interactor
     */
    public static function resolve(array $args, execution_context $ec): course_interactor {
        if (!isset($args['course_id'])) {
            throw new coding_exception("Missing required field " . $args['course_id']);
        }

        return new course_interactor(course::from_record($args['course']));
    }

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            new require_login(),
            new validate_format_pathway_course('course_id')
        ];
    }
}