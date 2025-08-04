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
 * @package totara_certification
 */

namespace totara_certification\usagedata;

use core\orm\query\builder;
use tool_usagedata\export;
use totara_program\entity\program as program_entity;
use totara_program\entity\program_assignment;
use totara_program\entity\program_user_assignment;
use totara_program\entity\prog_group;
use totara_program\entity\prog_group_user;
use totara_program\assignments\assignments;

class assignment implements export {

    /**
     * @inheritdoc
     */
    public function get_summary(): string {
        return get_string('certification_assignment_summary', 'totara_certification');
    }

    /**
     * @inheritDoc
     */
    public function get_type(): int {
        return export::TYPE_OBJECT;
    }

    /**
     * @inheritdoc
     */
    public function export(): array {
        $builder = fn() => builder::table(program_assignment::TABLE, 'pa')
            ->join([program_entity::TABLE, 'p'], 'pa.programid', 'p.id')
            ->join([program_user_assignment::TABLE, 'pua'], 'pua.assignmentid', 'pa.id')
            ->where_not_null('p.certifid');

        $dates = fn() => builder::table(program_assignment::TABLE, 'pa')
            ->join([program_entity::TABLE, 'p'], 'pa.programid', 'p.id')
            ->where_not_null('p.certifid');

        $groups = fn() => program_entity::repository()
            ->join([program_assignment::TABLE, 'pa'], 'id', 'pa.programid')
            ->where('pa.assignmenttype', assignments::ASSIGNTYPE_GROUP)
            ->join([prog_group::TABLE, 'g'], 'pa.assignmenttypeid', 'g.id')
            ->where_not_null('certifid');

        $assignments = fn($type) => program_entity::repository()
            ->join([program_assignment::TABLE, 'pa'], 'id', 'pa.programid')
            ->where('pa.assignmenttype', $type)
            ->where_not_null('certifid');

        return [
            'total_learners' => $builder()->count(),
            'count_learners_with_duedate_assignment' => $builder()
                ->where_raw('(pa.completiontime > -1 OR (pa.completionoffsetamount IS NOT NULL OR pa.completionoffsetunit IS NOT NULL))')
                ->count(),
            'count_fixed_due_date' => $dates()
                ->where_raw('pa.completiontime <> -1')
                ->count(),

            'count_relative_due_date' => $dates()
                ->where_raw('(pa.completionoffsetamount IS NOT NULL OR pa.completionoffsetunit IS NOT NULL)')
                ->count(),

            'groups_self_enrol' => $groups()->where('g.can_self_enrol', '1')->count(),
            'groups_self_unenrol' => $groups()->where('g.can_self_unenrol', '1')->count(),
            'groups_self_enrol_unenrol' => $groups()
                ->where('g.can_self_enrol', '1')
                ->where('g.can_self_unenrol', '1')
                ->count(),
            'users_self_enrol' => $groups()
                ->where('g.can_self_enrol', '1')
                ->join([prog_group_user::TABLE, 'u'], 'g.id', 'u.prog_group_id')
                ->count(),
            'audiences' => $assignments(assignments::ASSIGNTYPE_COHORT)->count(),
            'organisations' => $assignments(assignments::ASSIGNTYPE_ORGANISATION)->count(),
            'positions' => $assignments(assignments::ASSIGNTYPE_POSITION)->count(),
            'individuals' => $assignments(assignments::ASSIGNTYPE_INDIVIDUAL)->count(),
            'management_hierarchies' => $assignments(assignments::ASSIGNTYPE_MANAGERJA)->count(),
            'groups' => $assignments(assignments::ASSIGNTYPE_GROUP)->count(),
        ];
    }
}
