<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @package totara_mobile
 */

namespace totara_mobile\webapi\resolver\query;

use core\webapi\query_resolver;
use core\webapi\execution_context;
use Exception;
use moodle_exception;
use totara_mobile\download\download_helper;
use totara_mobile\webapi\resolver\middleware\require_mobile_friendly_course;

/**
 * Query 'get_supported_offline_activities'
 */
class get_supported_offline_activities extends query_resolver {
    /**
     * @param array $args
     * @param execution_context $ec
     * @return array
     */
    public static function resolve(array $args, execution_context $ec): array {
        $course_id = $args['input']['course_id'];
        try {
            $course = get_course($course_id);
        } catch (Exception $exception) {
            throw new moodle_exception('invalidcourse');
        }

        \require_login($course, false, null, false, true);

        return download_helper::get_supported_offline_activities($course_id);
    }

    /**
     * @return array
     */
    public static function get_middleware(): array {
        return [
            new require_mobile_friendly_course('input.course_id')
        ];
    }
}