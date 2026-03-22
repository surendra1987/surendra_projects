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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package core
 */

namespace core\webapi\resolver\query;

use container_course\interactor\course_interactor;
use container_course\non_interactive_enrolment;
use core\webapi\execution_context;
use core\webapi\middleware\require_login;
use core\webapi\query_resolver;
use core_enrol\enrolment_approval_helper;
use core_enrol\model\user_enrolment_application;
use mod_approval\controllers\application\edit;
use mod_approval\controllers\application\view;
use moodle_exception;

/**
 * Query to check if non interactive enrol that requires approval is pending for the target course.
 */
class non_interactive_enrol_pending_approval_info extends query_resolver {
    /**
     * @param array $args
     * @param execution_context $ec
     * @return array
     */
    public static function resolve(array $args, execution_context $ec): array {
        global $DB, $USER;

        $course_id = $args['course_id'];
        $course_interactor = course_interactor::from_course_id($course_id);

        if ($course_interactor->is_enrolled() && $course_interactor->is_enrolled_not_pending_approval()) {
            throw new moodle_exception('You are already enrolled.');
        }

        if (!$course_interactor->non_interactive_enrol_requires_approval()) {
            throw new moodle_exception('The course does not support non interactive enrol with approval requirement yet.');
        }

        $instances = non_interactive_enrolment::get_non_interactive_enrolment_instances($course_id, $USER->id);
        $enrol_instance = reset($instances);
        $user_enrolment = $DB->get_record('user_enrolments', ['userid' => $USER->id, 'enrolid' => $enrol_instance->id]);
        if ($user_enrolment && $user_enrolment->status == ENROL_USER_PENDING_APPLICATION) {
            $user_enrolment_application = user_enrolment_application::find_with_user_enrolment_id($user_enrolment->id);
            if ($user_enrolment_application) {
                $application = $user_enrolment_application->approval_application;

                return [
                    'pending' => true,
                    'button_name' => enrolment_approval_helper::get_approval_button_name($application, $USER->id),
                    'redirect_url' => $application->get_overall_progress() === 'DRAFT'? edit::get_url_for($application->id) : view::get_url_for($application->id),
                    'needs_create_new_application' => enrolment_approval_helper::needs_create_new_application($application)
                ];
            } else {
                return [
                    'pending' => true,
                    'button_name' => get_string('application:create_application', 'core_enrol'),
                    'redirect_url' => '',
                    'needs_create_new_application' => true
                ];
            }
        }

        return [
            'pending' => false,
            'button_name' => '',
            'redirect_url' => '',
            'needs_create_new_application' => false
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function get_middleware(): array {
        return [
            new require_login()
        ];
    }
}