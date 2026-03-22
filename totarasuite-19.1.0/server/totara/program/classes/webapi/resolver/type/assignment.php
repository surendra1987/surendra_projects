<?php
/**
 * This file is part of Totara Learn
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
 * @author Rami Habib <rami.habib@totara.com>
 * @package totara_program
 */

namespace totara_program\webapi\resolver\type;

use coding_exception;
use context_program;
use core\date_format;
use core\webapi\execution_context;
use core\webapi\formatter\field\date_field_formatter;
use core\webapi\type_resolver;
use totara_program\assignments\assignments;
use totara_program\formatter\assignment_formatter;

use totara_program\assignment\base;
use totara_program\assignment\helper;
use totara_program\entity\program_assignment;
use totara_program\entity\program_user_assignment;
use totara_program\entity\program_completion;
use totara_program\utils;

/**
 * Program assignment type
 */
class assignment extends type_resolver {

    /**
     * Resolve assignment fields
     *
     * @param string $field
     * @param mixed $prog_assignment
     * @param array $args
     * @param execution_context $ec
     * @return mixed
     */
    public static function resolve(string $field, $prog_assignment, array $args, execution_context $ec) {
        global $USER;

        if (!is_a($prog_assignment, program_assignment::class)) {
            throw new coding_exception('Expected \totara_program\entity\program_assignment for $prog_assignment but received: ' . gettype($prog_assignment));
        }

        $type = $prog_assignment->assignmenttype;

        if ($field == 'type') {
            return helper::get_type_string($type);
        }

        $assignment = base::create_from_id($prog_assignment->id);
        $program_context = context_program::instance($prog_assignment->programid);

        if ($field == 'name') {
            return $assignment->get_name();
        }

        if ($field == 'description') {
            return $assignment->get_description();
        }

        if ($field == 'can_self_enrol') {
            return $assignment->can_user_self_enrol($USER->id);
        }

        if ($field == 'can_self_unenrol') {
            return $assignment->can_user_self_unenrol($USER->id);
        }

        if ($field == 'is_enrolled') {
            return program_user_assignment::repository()
                ->as('pua')
                ->select('pua.*')
                ->join([program_assignment::TABLE, 'pa'], 'assignmentid', 'id')
                ->where("pua.programid", $prog_assignment->programid)
                ->where('pua.userid', $USER->id)
                ->where('pa.assignmenttype', $type)
                ->where('pa.assignmenttypeid', $prog_assignment->assignmenttypeid)
                ->exists();
        }

        if ($field == 'due_date' || $field == 'actual_due_date' || $field == 'can_due_date_change') {
            $user_prog_completion = program_completion::repository()
                ->where('programid', $prog_assignment->programid)
                // We want the program's timedue (not any of its courses)
                ->where('coursesetid', 0)
                ->where('userid', $USER->id)
                ->get()
                ->first();

            // Current time due for this user
            $current_timedue = $user_prog_completion ? $user_prog_completion->timedue : -1;
            // Time due should be the actual timestamp if we can calculate it
            $new_timedue = $assignment->get_program()->make_timedue($USER->id, $assignment->get_record(), $current_timedue);
            $date_formatter = new date_field_formatter($args['format'] ?? date_format::FORMAT_DATE, $program_context);
            $duedate = $date_formatter->format($new_timedue);
            if ($field == 'actual_due_date') {
                return $duedate;
            } elseif ($field == 'due_date') {
                if (!$assignment->has_duedate()) {
                    return null;
                }
                if ($assignment->get_completionevent() == assignments::COMPLETION_EVENT_ENROLLMENT_DATE) {
                    $a = new \stdClass();
                    $a->num = $assignment->get_completionoffsetamount();
                    $a->period = get_string(utils::$timeallowancestrings[$assignment->get_completionoffsetunit()] .'_sentence', 'totara_program');
                    $a->duedate = $duedate;
                    $duedate = get_string('completewithinenrolmentdate', 'totara_program', $a);
                }
                return $duedate;
            } else {
                // The due date can change if the new time due for that assignment is later than the current time due
                return $new_timedue > $current_timedue;
            }
        }

        $format = $args['format'] ?? null;

        $formatter = new assignment_formatter($prog_assignment->to_record(), $program_context);
        return $formatter->format($field, $format);
    }
}
