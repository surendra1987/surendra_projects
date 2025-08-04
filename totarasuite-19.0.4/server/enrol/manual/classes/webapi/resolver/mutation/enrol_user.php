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
 * @author Michael Ivanov <michael.ivanov@totara.com>
 * @package enrol_manual
 */

namespace enrol_manual\webapi\resolver\mutation;

use container_course\course;
use container_course\interactor\course_interactor;
use context_course;
use core\model\role;
use core\entity\user;
use core\model\user_enrolment as user_enrolment_model;
use core\entity\user_enrolment as user_enrolment_entity;
use core\orm\query\builder;
use core\reference\role_record_reference;
use core\reference\user_record_reference;
use core\webapi\execution_context;
use core\webapi\middleware\require_authenticated_user;
use core\webapi\mutation_resolver;
use core_course\course_record_reference;
use enrol_manual\exception\enrol_user_exception;

/**
 * GraphQL mutation to enrol user into the course manually
 */
class enrol_user extends mutation_resolver {

    protected const MANUAL_ENROL_PLUGIN_NAME = 'manual';

    /**
     * @param array $args
     * @param execution_context $ec
     * @return array
     */
    public static function resolve(array $args, execution_context $ec): array {
        global $CFG;
        require_once($CFG->dirroot."/lib/enrollib.php");

        $input = $args['input'] ?? [];
        $user_reference = $input['user'] ?? [];
        $course_reference = $input['course'] ?? [];
        $role_reference = $input['role'] ?? [];

        $current_user = user::logged_in();

        //Check if manual enrolment is enabled first
        $enrol_plugins = enrol_get_plugins(true);
        if (!isset($enrol_plugins[self::MANUAL_ENROL_PLUGIN_NAME])) {
            throw new enrol_user_exception('Manual enrolment plugin is not enabled.');
        }
        $manual_enrol_plugin = $enrol_plugins[self::MANUAL_ENROL_PLUGIN_NAME];

        $target_user = user_record_reference::load_for_viewer($user_reference);
        $target_course = course_record_reference::load_for_viewer($course_reference, $current_user);

        $course_interactor = new course_interactor(course::from_id($target_course->id), $target_user->id);
        $course_context = $course_interactor->get_course_context();

        if (empty($current_user->tenantid) && !$course_interactor->can_enrol_tenant_user_into_course()) {
            throw new enrol_user_exception('Can not manual enrol a target user into a target course.');
        }

        $enrol_instances = enrol_get_instances($target_course->id, true);
        $manual_enrol_instance = null;
        if (is_iterable($enrol_instances)) {
            foreach ($enrol_instances as $enrol_instance) {
                if ($enrol_instance->enrol ?? '' === self::MANUAL_ENROL_PLUGIN_NAME) {
                    $manual_enrol_instance = $enrol_instance;
                    break;
                }
            }
        }

        if (!$manual_enrol_instance) {
            throw new enrol_user_exception('Manual enrolment is not enabled in this course.');
        }

        if (empty($role_reference)) {
            $role_reference['id'] = $manual_enrol_instance->roleid;
        }
        $target_role = role_record_reference::load_for_viewer($role_reference, $current_user);

        self::check_assignable_roles($current_user, $course_context, $target_role);

        // We mimic get_enrolled_sql round(time(), -2) but always floor as we want users to always access their
        // courses once they are enrolled
        $now = floor(substr(time(), 0, 8) . '00') - 1;
        if (empty($input['timestart']) || ($input['timestart'] < $now && $input['timestart'] > $now - 86400)) {
            // Assume it is the same day as today
            $timestart = $now;
        } elseif ($input['timestart'] < $now) {
            throw new enrol_user_exception('The enrolment start date can not be in the past.');
        } else {
            $timestart = $input['timestart'];
        }

        if (empty($input['timeend'])) {
            $timeend = 0;
        } else {
            $timeend = $input['timeend'];
            if ($timeend < $timestart) {
                throw new enrol_user_exception('Enrolment end date can not be before the start date.');
            }
        }

        if (!$manual_enrol_plugin->allow_enrol($manual_enrol_instance)
            || !has_any_capability(['enrol/manual:enrol', 'enrol/manual:manage'], $course_context)
        ) {
            throw new enrol_user_exception('Not allowed to enrol the user into the course.');
        }

        $enrol_status = empty($input['suspended']) ? ENROL_USER_ACTIVE : ENROL_USER_SUSPENDED;

        // Check if the user is already enrolled.
        $was_already_enrolled = is_enrolled($course_context, $target_user->id);

        $manual_enrol_plugin->enrol_user($manual_enrol_instance, $target_user->id, $target_role->id, $timestart, $timeend, $enrol_status, 0);
        $user_enrolment = user_enrolment_entity::repository()
            ->where('enrolid', '=' ,$manual_enrol_instance->id)
            ->where('userid', '=', $target_user->id)
            ->order_by('id')
            ->first();

        return [
            'user' => $target_user,
            'course' => $target_course,
            'role' => role::load_by_id($target_role->id),
            'enrolment' => user_enrolment_model::load_by_id($user_enrolment->id),
            'success' => true,
            'was_already_enrolled' => $was_already_enrolled,
        ];
    }

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            new require_authenticated_user(),
        ];
    }

    /**
     * Check assignable roles based on target course context for the current logging user(api service account user).
     *
     * @param user $current_user
     * @param context_course $course_context
     * @param $target_role
     * @return void
     */
    protected static function check_assignable_roles(user $current_user, context_course $course_context, $target_role): void {
        $assignable_roles = get_assignable_roles($course_context);
        if (empty($assignable_roles)) {
            $sql = "SELECT r.id FROM {role} r 
                    JOIN {role_context_levels} rcl ON (rcl.contextlevel = :contextlevel AND r.id = rcl.roleid) 
                    LEFT JOIN {role_names} rn ON (rn.contextid = :coursecontext AND rn.roleid = r.id)
                    ORDER BY r.sortorder ASC";

            $has_assign_role = false;
            // Get all assignable roles.
            $assignable_roles = builder::get_db()->get_records_sql($sql,
                ['contextlevel' => $course_context->contextlevel, 'coursecontext' => $course_context->id]
            );

            foreach ($assignable_roles as $assignable_role) {
                if ($assignable_role->id == $target_role->id) {
                    $has_assign_role = true;
                }
            }

            if (!$has_assign_role || !has_capability('moodle/role:assign', $course_context, $current_user->id)) {
                throw new enrol_user_exception('This role can not be assigned to the target user on this course.');
            }

        } else {
            if (!isset($assignable_roles[$target_role->id])) {
                throw new enrol_user_exception('This role can not be assigned to the target user on this course.');
            }
        }
    }
}