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
        $builder = builder::table(program_assignment::TABLE, 'pa')
            ->join([program_entity::TABLE, 'p'], 'pa.programid', 'p.id')
            ->join([program_user_assignment::TABLE, 'pua'], 'pua.assignmentid', 'pa.id')
            ->where_not_null('p.certifid');

        return [
            'total_learners' => $builder->count(),
            'count_learners_with_duedate_assignment' => $builder
                ->where_raw('(pa.completiontime > -1 OR (pa.completionoffsetamount IS NOT NULL OR pa.completionoffsetunit IS NOT NULL))')
                ->count(),
            'count_fixed_due_date' => builder::table(program_assignment::TABLE, 'pa')
                ->join([program_entity::TABLE, 'p'], 'pa.programid', 'p.id')
                ->where_not_null('p.certifid')
                ->where_raw('pa.completiontime <> -1')
                ->count(),

            'count_relative_due_date' => builder::table(program_assignment::TABLE, 'pa')
                ->join([program_entity::TABLE, 'p'], 'pa.programid', 'p.id')
                ->where_not_null('p.certifid')
                ->where_raw('(pa.completionoffsetamount IS NOT NULL OR pa.completionoffsetunit IS NOT NULL)')
                ->count(),
        ];
    }
}