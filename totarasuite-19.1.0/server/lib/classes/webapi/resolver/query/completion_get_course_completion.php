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
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package core
 */

namespace core\webapi\resolver\query;

use completion_completion;
use core\entity\user;
use core\reference\user_record_reference;
use core\webapi\execution_context;
use core\webapi\middleware\require_authenticated_user;
use core\webapi\query_resolver;
use core_course\course_record_reference;
use invalid_parameter_exception;

/**
 * Query to get the completion data for a user participating in a course.
 */
class completion_get_course_completion extends query_resolver {

    /**
     * @param array $args
     * @param execution_context $ec
     * @return array
     */
    public static function resolve(array $args, execution_context $ec): array {
        $query = $args['query'];
        $user_reference = $query['user'];
        $course_reference = $query['course'];
        $current_user = user::logged_in();

        // Load the specified target user - checks that the viewer can see the target user.
        if (empty($user_reference)) {
            throw new invalid_parameter_exception('User reference is required.');
        }
        $target_user = user_record_reference::load_for_viewer($user_reference, $current_user);

        // Load the specified course - checks that the viewer can see the target course.
        if (empty($course_reference)) {
            throw new invalid_parameter_exception('Course reference is required.');
        }
        $target_course = course_record_reference::load_for_viewer($course_reference, $current_user);

        // Load the completion record.
        $completion = new completion_completion(['userid' => $target_user->id, 'course' => $target_course->id]);

        // The completion loader returns an object even if the completion doesn't exist, so convert to null if needed.
        if (empty($completion->id)) {
            $completion = null;
        }

        return [
            'user' => $target_user,
            'course' => $target_course,
            'completion' => $completion,
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

