<?php
/**
 * This file is part of Totara Perform
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
 * @author Gihan Hewaralalage <gihan.hewaralalage@totara.com>
 * @package totara_program
 */

namespace totara_program\webapi\resolver\mutation;

use coding_exception;
use container_course\course;
use core\webapi\execution_context;
use core\webapi\middleware;
use core\webapi\middleware\require_login;
use core\webapi\middleware\require_prog_capability;
use core\webapi\mutation_resolver;
use core\webapi\param\text;
use moodle_exception;
use totara_program\content\course_set as content;
use totara_program\entity\program as program_entity;
use totara_program\model\program as program_model;
use totara_program\program as program_class;
use totara_program\content\course_set as content_cs;
use totara_program\content\program_content;
use ProgramException;
use totara_program\entity\program_courseset as program_courseset_entity;
use totara_program\model\program_courseset as program_courseset_model;

class save_courseset extends mutation_resolver {

    /**
     * @param array $args
     * @param execution_context $ec
     * @return program_courseset_entity|program_courseset_model
     * @throws coding_exception|ProgramException
     */
    public static function resolve(array $args, execution_context $ec) {
        global $CFG, $DB;

        // Include the certif lib to make sure CERTIFPATH_ constants are available.
        require_once($CFG->dirroot . '/totara/certification/lib.php');

        $is_courseset_exist = false;

        $input = $args['input'];
        $id = $input['id'];
        $program_id = $input['program_id'];
        $sort_order = $input['sort_order'] ?? 0;
        $completion_type = $input['completion_type'];
        $min_courses = $input['min_courses'] ?? 0;
        $course_sum_field = $input['course_sum_field'] ?? 0;
        $course_sum_field_total = $input['course_sum_field_total'] ?? 0;
        $time_allowed = $input['time_allowed'] ?? 0;
        $label = !empty($input['label']) ? text::parse_value($input['label']) : program_courseset_model::get_default_label();
        $course_ids = $input['course_ids'];

        // Validate the completion type.
        switch ($completion_type) {
            case 'ALL':
                $completion_type = content_cs::COMPLETIONTYPE_ALL;
                break;
            case 'ANY':
                $completion_type = content_cs::COMPLETIONTYPE_ANY;
                break;
            case 'SOME':
                $completion_type = content_cs::COMPLETIONTYPE_SOME;
                break;
            case 'OPTIONAL':
                $completion_type = content_cs::COMPLETIONTYPE_OPTIONAL;
                break;
            default:
                throw new \coding_exception("Invalid completion type.");
        }

        // Validate the program_id, check it corresponds to an existing program.
        $program = new program_class($program_id);

        if ($program->get_context()->tenantid && !empty($CFG->tenantsenabled)) {
            foreach ($course_ids as $course_id) {
                $course = course::from_id($course_id);
                if ($course->get_context()->tenantid) {
                    if ($course->get_context()->tenantid !== $program->get_context()->tenantid) {
                        throw new moodle_exception("Course {$course_id} can not be added into the program.");
                    }
                }
            }
        }

        if (!$program) {
            throw new \coding_exception("Invalid program.");
        }

        // Validate the id, check it corresponds to an existing courseset if not empty.
        if ($id) {
            $row = program_courseset_entity::repository()
                ->where('id', $id);
            $is_courseset_exist = program_courseset_entity::repository()->where_exists($row->get_builder());
        } else {
            // No course, no new courseset create.
            if (!($course_ids && is_array($course_ids))) {
                throw new \coding_exception("Invalid course set. The course set should have at least one course.");
            }
        }

        // Validate the coursesumfield, check it corresponds to a course custom field.
        if (!empty($course_sum_field)) {
            if (!$DB->record_exists('course_info_field', ['id' => $course_sum_field])) {
                throw new \coding_exception("Invalid custom field. The course sum field must be set to a valid course custom field id.");
            }
        }

        // Validate the course_sum_field_total, check it is a non-negative integer.
        if (!is_int($course_sum_field_total) || $course_sum_field_total < 0) {
            throw new \coding_exception("Invalid course sum field total. The field must be set to a non-negative integer");
        }

        // Validate the min_courses, check it is an achievable non-negative integer.
        $course_total = count($course_ids);
        if (!is_int($min_courses) || $course_total < $min_courses || $min_courses < 0) {
            throw new \coding_exception("Invalid min courses field total. The field must be set to a non-negative integer below the total number of courses in the courseset");
        }

        // Validate the time_allowed, check it is a non-negative integer.
        if (!is_int($time_allowed) || $time_allowed < 0) {
            throw new \coding_exception("Invalid time allowed. The field must be set to a non-negative integer");
        }

        // If the course_set does not exist, create a new one.
        if (!$is_courseset_exist) {
            $program_courseset = program_courseset_model::create(
                $program_id,
                $sort_order,
                $input['competency_id'] ?? 0,
                $input['next_set_operator'] ?? 0,
                $completion_type,
                $min_courses,
                $course_sum_field,
                $course_sum_field_total,
                $time_allowed,
                $input['recurrence_time'] ?? 0,
                $input['recur_create_time'] ?? 0,
                $input['content_type'] ?? program_content::CONTENTTYPE_MULTICOURSE,
                $label,
                $input['certif_path'] ?? CERTIFPATH_STD
            );

            // Set next operator to THEN on preceding course set.
            $courseset_entity = new program_courseset_entity($program_courseset->id);
            $courseset_model = program_courseset_model::from_entity($courseset_entity);
            list($previous_courseset_id) = $courseset_model->get_previous_and_next_courseset_ids();

            // Get course preceding course set.
            if ($previous_courseset_id) {
                $prev_course_set = new program_courseset_entity($previous_courseset_id);
                if ($prev_course_set->nextsetoperator == 0) {
                    $prev_course_set->nextsetoperator = content::NEXTSETOPERATOR_THEN;
                    $prev_course_set->save();
                }
            }
        } else {
            // If the course set exists, update the current course_set.
            /** @var program_courseset_entity $programe_courseset */
            $courseset = new program_courseset_entity($id);
            $courseset->programid = $program_id;
            $courseset->sortorder = $sort_order;
            if (isset($input['content_type'])) {
                $courseset->contenttype = $input['content_type'];
            }
            // If the current course set label is set to empty, set the previous label.
            if ($label != program_courseset_model::get_default_label()) {
                $courseset->label = $label;
            }
            if (isset($input['competency_id'])) {
                $courseset->competencyid = $input['competency_id'];
            }
            if (isset($input['next_set_operator'])) {
                $courseset->nextsetoperator = $input['next_set_operator'];
            }
            $courseset->completiontype = $completion_type;
            $courseset->mincourses = $min_courses;
            $courseset->coursesumfield = $course_sum_field;
            $courseset->coursesumfieldtotal = $course_sum_field_total;
            $courseset->timeallowed = $time_allowed;
            if (isset($input['recurrence_time'])) {
                $courseset->recurrencetime = $input['recurrence_time'];
            }
            if (isset($input['recur_create_time'])) {
                $courseset->recurcreatetime = $input['recur_create_time'];
            }
            if (isset($input['certif_path'])) {
                $courseset->certifpath = $input['certif_path'];
            }
            $courseset->save();

            // Get the model for the next part.
            $program_courseset = program_courseset_model::from_entity($courseset);
        }

        // Now we have saved the courseset, update its courses.
        $program_courseset->update_courses($course_ids);

        // Make sure that an array of course sets is in order in terms of each
        // set's sort order property and reset the sort order properties to ensure
        // that it begins from 1 and there are no gaps in the order.
        $program_entity = new program_entity($program_id);
        $program_model = program_model::from_entity($program_entity);
        $program_model->reset_coursesets_sortorder();

        // Trigger the content updated event.
        $program_model->trigger_content_updated_event();

        return $program_courseset;
    }

    /**
     * @return array|middleware[]
     */
    public static function get_middleware(): array {
        return [
            new require_login(),
            new require_prog_capability('program_id', 'totara/program:configurecontent')
        ];
    }
}
