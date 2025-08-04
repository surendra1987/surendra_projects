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
 * @package core
 */

namespace core\webapi\resolver\query;

use container_course\interactor\course_interactor;
use core\reference\user_record_reference;
use core\webapi\middleware\require_authenticated_user;
use core\webapi\query_resolver;
use core\webapi\execution_context;
use core_course\course_record_reference;
use core_course\exception\course_is_user_enrolled_exception;
use core_user\external\user_interactor;

/**
 * Handles the "core_course_is_user_enrolled" GraphQL query.
 */
class course_is_user_enrolled extends query_resolver {
    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec): array {
        global $CFG, $USER;
        require_once("{$CFG->dirroot}/lib/enrollib.php");

        $user_reference = $args['user'];
        $course_reference = $args['course'];

        if (empty($user_reference)) {
            throw new course_is_user_enrolled_exception('User reference is required.');
        }

        if (empty($course_reference)) {
            throw new course_is_user_enrolled_exception('Course reference is required.');
        }

        $target_user = user_record_reference::load_for_viewer($user_reference);
        $target_course = course_record_reference::load_for_viewer($course_reference);

        if (!(new user_interactor($USER->id, $target_user->id))->can_view()) {
            throw new course_is_user_enrolled_exception('You do not have capabilities to view a target user.');
        }

        $course_interactor = course_interactor::from_course_id($target_course->id, $USER->id);
        if (!$course_interactor->can_view_enrol()) {
            throw new course_is_user_enrolled_exception('You do not have capabilities to view a target course.');
        }

        return [
            'user_currently_enrolled' => is_enrolled($course_interactor->get_course_context(), $target_user->id, '', true)
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function get_middleware(): array {
        return [
            new require_authenticated_user()
        ];
    }
}