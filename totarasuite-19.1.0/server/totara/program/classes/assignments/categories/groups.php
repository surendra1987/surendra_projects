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
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package totara_program
 */

namespace totara_program\assignments\categories;

use invalid_parameter_exception;
use totara_program\assignment\group;
use totara_program\assignments\assignments;
use totara_program\assignments\category;
use totara_program\entity\prog_group;
use totara_program\entity\prog_group_user;
use totara_program\entity\program_assignment;

/**
 * Group assignment category of program assignment.
 */
class groups extends category {

    public function __construct() {
        $this->id = assignments::ASSIGNTYPE_GROUP;
        $this->name = get_string('group', 'totara_program');
    }

    /**
     * @inheritdoc
     */
    public function build_table($programidorinstance): void {
        // This doesn't appear to be used by anything, so we're putting some placeholder stuff here as something is required.
        $this->headers = array('abc');
        $this->data = array(['123']);
    }

    /**
     * @inheritdoc
     */
    public function build_row($item, bool $canupdate = true): array {
        // This doesn't appear to be used by anything, so we're putting some placeholder stuff here as something is required.
        return [];
    }

    /**
     * @inheritdoc
     */
    public function get_js(int $programid): string {
        return "";
    }

    /**
     * @inheritdoc
     */
    public function user_affected_count($item): array|int {
        return $this->get_affected_users($item, 0, true);
    }

    /**
     * @inheritdoc
     */
    public function get_affected_users($item, int $userid = 0, bool $count = false) {
        $builder = prog_group_user::repository()
            ->select('user_id')
            ->where('prog_group_id', '=', $item->id)
            ->when($userid > 0, function ($builder) use ($userid) {
                $builder->where('user_id', '', $userid);
            });

        if ($count) {
            return $builder->count();
        } else {
            return $builder->get()->all();
        }
    }

    /**
     * @inheritdoc
     */
    public function get_affected_users_by_assignment($assignment, int $userid = 0) {
        if ($assignment->assignmenttype != assignments::ASSIGNTYPE_GROUP) {
            throw new invalid_parameter_exception();
        }

        return prog_group_user::repository()
            ->select('user_id AS id')
            ->where('prog_group_id', '=', $assignment->assignmenttypeid)
            ->when($userid > 0, function ($builder) use ($userid) {
                $builder->where('user_id', '', $userid);
            })
            ->get()
            ->all();
    }

    /**
     * @inheritdoc
     */
    public function get_includechildren($data, $object): int {
        // Assignment groups don't have inheritance.
        return 0;
    }

    /**
     * Deletes all prog_group records relating to a specified program.
     *
     * Prog_group_user child records are cascade deleted.
     *
     * @param int $programid
     * @return void
     */
    public static function delete_by_program(int $programid): void {
        prog_group::repository()
            ->where_exists(
                program_assignment::repository()
                    ->as('pa')
                    ->where('pa.programid', '=', $programid)
                    ->where('pa.assignmenttype', '=', group::ASSIGNTYPE_GROUP)
                    ->where_field('assignmenttypeid', '=', '{' . prog_group::TABLE .'}.id')
                    ->get_builder()
            )
            ->delete();
    }

    /**
     * Delete all records relating to a specified user.
     *
     * @param int $userid
     * @return void
     */
    public static function delete_by_user(int $userid): void {
        prog_group_user::repository()
            ->where('user_id', '=', $userid)
            ->delete();
    }

    /**
     * @inheritdoc
     *
     * For groups, an assignment is applicable if the learner can enrol.
     * We don't need to check for assignments where a learner can unenrol, because
     * the learner must already be enrolled in that case, and it is optional for
     * this function to include assignments where the user is already assigned.
     */
    public static function get_applicable_assignments(int $program_id, int $user_id): array {
        return program_assignment::repository()
            ->as('pa')
            ->join([prog_group::TABLE, 'pg'], 'pa.assignmenttypeid', '=', 'pg.id')
            ->where('pa.assignmenttype', '=', group::ASSIGNTYPE_GROUP)
            ->where('pa.programid', '=', $program_id)
            ->where('pg.can_self_enrol', '=', true)
            ->get()
            ->all();
    }
}