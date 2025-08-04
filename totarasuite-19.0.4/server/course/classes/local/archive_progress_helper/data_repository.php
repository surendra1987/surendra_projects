<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2021 onwards Totara Learning Solutions LTD
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
 * @author Kunle Odusan <kunle.odusan@totaralearning.com>
 */

namespace core_course\local\archive_progress_helper;

use context_helper;

/**
 * This is the data access layer used in resetting enrolled user course progress.
 */
class data_repository {

    /**
     * Check if the user has progress on the specified course.
     *
     * @param int $course_id
     * @param int $user_id
     * @return bool
     */
    public static function user_has_progress(int $course_id, int $user_id): bool {
        global $DB;
        $status = array(
            COMPLETION_STATUS_INPROGRESS,
            COMPLETION_STATUS_COMPLETE,
            COMPLETION_STATUS_COMPLETEVIARPL,
        );

        list($status_sql, $params) = $DB->get_in_or_equal($status, SQL_PARAMS_NAMED, 'status');
        $sql = "SELECT COUNT(DISTINCT cc.userid) as total
                  FROM {course_completions} cc
                 WHERE cc.course = :courseid
                   AND cc.userid = :userid
                   AND cc.status {$status_sql}";
        $params['courseid'] = $course_id;
        $params['userid'] = $user_id;
        $user_count = $DB->get_field_sql($sql, $params);

        return (int)$user_count > 0;
    }

    /**
     * Get number of users that have completed the course.
     *
     * @deprecated since Totara 19.0.1
     *
     * @return int
     */
    public static function get_course_completed_users_count(int $course_id): int {

        debugging(
            __METHOD__ . ' has been deprecated; please use count(get_users_for_archive_reset()) instead',
            DEBUG_DEVELOPER
        );

        return count(static::get_users_for_archive_reset($course_id));
    }

    /**
     * Get ids of users that have completions in the course.
     *
     * @return array
     */
    public static function get_course_completed_users(int $course_id): array {
        global $DB;
        $status = array(
            COMPLETION_STATUS_COMPLETE,
            COMPLETION_STATUS_COMPLETEVIARPL
        );

        list($statussql, $params) = $DB->get_in_or_equal($status, SQL_PARAMS_NAMED, 'status');
        $sql = "SELECT DISTINCT cc.userid
                  FROM {course_completions} cc
                 WHERE cc.course = :courseid
                   AND cc.status {$statussql}
              ORDER BY cc.userid ASC";
        $params['courseid'] = $course_id;
        $userids = $DB->get_records_sql_menu($sql, $params);

        return array_keys($userids);
    }

    /**
     * Get programs and certifications linked to a course
     *
     * @param int $course_id
     * @return array[]
     */
    public static function get_linked_programs_and_certifications_names(int $course_id): array {
        global $DB;

        $ctxfields = context_helper::get_preload_record_columns_sql('ctx');
        $sql = "SELECT DISTINCT p.id, p.fullname, p.certifid, {$ctxfields}
                  FROM {prog_courseset_course} pcc
                  JOIN {prog_courseset} pc ON pcc.coursesetid = pc.id
                  JOIN {prog} p ON pc.programid = p.id
                  JOIN {context} ctx ON ctx.instanceid = p.id AND ctx.contextlevel = :ctxlevel
                 WHERE pcc.courseid = :courseid";
        $params = [
            'courseid' => $course_id,
            'ctxlevel' => CONTEXT_PROGRAM
        ];

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Can the course progress be reset for a single user enrolled into course and/or have program user assignment
     *
     * @param int $course_id
     * @param int $user_id
     * @return bool
     */
    public static function user_confirming_reset(int $course_id, int $user_id): bool {
        $confirming_reset = static::user_has_progress($course_id, $user_id);
        if (static::allow_course_completion_reset_for_program_courses()) {
            return $confirming_reset;
        }

        return $confirming_reset && !static::user_has_program_and_certification_assignments($course_id, $user_id);
    }

    /**
     * Can the course progress be reset for the users enrolled into course and/or have program user assignment
     *
     * @param int $course_id
     * @return bool
     */
    public static function course_confirming_reset(int $course_id): bool {
        return count(static::get_users_for_archive_reset($course_id)) > 0;
    }

    /**
     * Get user's programs and certifications linked to a course
     *
     * @param int $course_id
     * @param int $user_id
     * @return array[]
     */
    public static function get_user_linked_programs_certifications(int $course_id, int $user_id): array {
        global $DB;

        $ctxfields = context_helper::get_preload_record_columns_sql('ctx');
        $sql = "SELECT DISTINCT p.id, p.fullname, p.certifid, {$ctxfields}
                  FROM {prog_courseset_course} pcc
                  JOIN {prog_courseset} pc ON pcc.coursesetid = pc.id
                  JOIN {prog} p ON pc.programid = p.id
                  JOIN {prog_user_assignment} pua ON pua.programid = p.id
                  JOIN {context} ctx ON ctx.instanceid = p.id AND ctx.contextlevel = :ctxlevel
                 WHERE pcc.courseid = :courseid
                   AND pua.userid = :userid";
        $params = [
            'courseid' => $course_id,
            'ctxlevel' => CONTEXT_PROGRAM,
            'userid' => $user_id
        ];

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Return true/false if a user's programs and certifications linked to a course
     *
     * @param int $course_id
     * @param int $user_id
     * @return bool
     */
    public static function user_has_program_and_certification_assignments(int $course_id, int $user_id): bool {
        return count(static::get_user_linked_programs_certifications($course_id, $user_id)) > 0;
    }

    /**
     * Load the user ids excluding ids from program/certification user assignments
     *
     * @param int $course_id
     * @return array
     */
    public static function get_users_for_archive_reset(int $course_id): array {
        global $DB;

        $course_user_ids = static::get_course_completed_users($course_id);

        if (static::allow_course_completion_reset_for_program_courses()) {
            return $course_user_ids;
        }

        $sql = "SELECT DISTINCT pua.userid
                  FROM {prog_courseset_course} pcc
                  JOIN {prog_courseset} pc ON pcc.coursesetid = pc.id
                  JOIN {prog} p ON pc.programid = p.id
                  JOIN {prog_user_assignment} pua ON pua.programid = p.id
                  JOIN {context} ctx ON ctx.instanceid = p.id AND ctx.contextlevel = :ctxlevel
                 WHERE pcc.courseid = :courseid
                 ORDER BY pua.userid ASC";
        $params = [
            'courseid' => $course_id,
            'ctxlevel' => CONTEXT_PROGRAM
        ];
        $prog_user_ids = array_keys($DB->get_records_sql_menu($sql, $params));
        // We must only return users who are NOT assign to a programs and/or certifications.
        return array_diff($course_user_ids, $prog_user_ids);
    }

    /**
     * Hidden configuration to allow an admin to revert to the Totara 15.1 behaviour
     *
     * @return bool
     */
    private static function allow_course_completion_reset_for_program_courses(): bool {
        global $CFG;
        /** @var \core_config $CFG */
        return (isset($CFG->allow_course_completion_reset_for_program_courses)
            && $CFG->allow_course_completion_reset_for_program_courses === true);
    }
}
