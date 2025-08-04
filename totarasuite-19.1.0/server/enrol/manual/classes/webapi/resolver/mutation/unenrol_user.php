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
 * @package enrol_manual
 */

namespace enrol_manual\webapi\resolver\mutation;

use container_course\interactor\course_interactor;
use core\entity\user;
use core\entity\user_enrolment;
use core\reference\user_record_reference;
use core\webapi\execution_context;
use core\webapi\middleware\require_authenticated_user;
use core\webapi\mutation_resolver;
use core_course\course_record_reference;
use core_user\external\user_interactor;
use enrol_manual\exception\unenrol_user_exception;
use required_capability_exception;

/**
 * Handles removing a learner from a course
 */
class unenrol_user extends mutation_resolver {

    /**
     * @param array $args
     * @param execution_context $ec
     * @return array
     */
    public static function resolve(array $args, execution_context $ec): array {
        global $CFG, $DB;
        require_once("{$CFG->dirroot}/lib/enrollib.php");

        $input = $args['input'] ?? [];
        $user_reference = $input['user'];
        $course_reference = $input['course'];

        if (empty($user_reference)) {
            throw new unenrol_user_exception('User reference is required.');
        }

        if (empty($course_reference)) {
            throw new unenrol_user_exception('Course reference is required..');
        }

        if (!enrol_is_enabled('manual')) {
            throw new unenrol_user_exception("Manual enrolment plugin is not enabled.");
        }
        $current_user = user::logged_in();
        $target_user = user_record_reference::load_for_viewer($user_reference, $current_user );
        $target_course = course_record_reference::load_for_viewer($course_reference);

        $course_interactor = course_interactor::from_course_id($target_course->id);
        $course_context = $course_interactor->get_course_context();
        if (!has_any_capability(['enrol/manual:manage', 'enrol/manual:unenrol'], $course_context)) {
            throw new unenrol_user_exception("You do not have permission to unenrol a target user.");
        }

        $instances = enrol_get_instances($target_course->id, true);
        $target_instance = null;
        foreach ($instances as $instance) {
            if ($instance->enrol === 'manual') {
                $target_instance = $instance;
                break;
            }
        }

        if (!$target_instance) {
            throw new unenrol_user_exception("Manual enrolment is not enabled in this course.");
        }

        // Check a target user is enrolled or not.
        if (!is_enrolled($course_interactor->get_course_context(), $target_user->id, '', true)) {
            return [
                'user' => $target_user,
                'course' => $target_course,
                'success' => true,
                'user_had_manual_enrolment' => false,
            ];
        }

        $manual_plugin = enrol_get_plugin('manual');
        // Manual plugin allow to unerol, just in case one day change manual plugin is false.
        if (!$manual_plugin->allow_unenrol($target_instance)) {
            throw new unenrol_user_exception("Manual enrolment does not allow to unenrol.");
        }

        if ($current_user->id == $target_user->id && !has_capability('enrol/manual:unenrolself', $course_context)) {
            throw new required_capability_exception($course_context, 'enrol/manual:unenrolself', 'nopermissions', '');
        }

        $trans = $DB->start_delegated_transaction();
        // User is enrolled by manual enrolments or not.
        $user_had_manual_enrolment = user_enrolment::repository()
            ->where('enrolid', $target_instance->id)
            ->where('userid', $target_user->id)
            ->exists();
        $manual_plugin->unenrol_user($target_instance, $target_user->id);

        $trans->allow_commit();
        return [
            'user' => $target_user,
            'course' => $target_course,
            'success' => true,
            'user_had_manual_enrolment' => $user_had_manual_enrolment
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
}