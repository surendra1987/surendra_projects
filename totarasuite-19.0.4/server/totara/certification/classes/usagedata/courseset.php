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
use totara_program\entity\program;
use totara_program\entity\program_courseset;
use totara_program\entity\program_courseset_course;

class courseset implements export {

    /**
     * @inheritdoc
     */
    public function get_summary(): string {
        return get_string('certification_courseset_summary', 'totara_certification');
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
        $prog_coursesets = builder::table(program_courseset::TABLE, 'pc')
            ->select_raw('pcc.coursesetid, COUNT(pcc.courseid) AS coursecount')
            ->join([program::TABLE, 'p'], 'pc.programid', 'p.id')
            ->join([program_courseset_course::TABLE, 'pcc'], 'pc.id', 'pcc.coursesetid')
            ->where_not_null('p.certifid')
            ->group_by('pcc.coursesetid')
            ->get();

        return [
            'count_coursesets' => builder::table(program_courseset::TABLE, 'pc')
                ->join([program::TABLE, 'p'], 'pc.programid', 'p.id')
                ->where_not_null('p.certifid')
                ->count(),
            'with_one_course' => $prog_coursesets
                ->filter(function ($prog_courseset) {
                    return $prog_courseset->coursecount == 1;
                })->count(),
            'with_multiple_courses' => $prog_coursesets
                ->filter(function ($prog_courseset) {
                    return $prog_courseset->coursecount > 1;
                })->count(),
            'paths_with_one_courseset' => builder::table(program_courseset::TABLE, 'pc')
                ->select_raw('pc.programid, COUNT(pc.programid) AS programcount')
                ->join([program::TABLE, 'p'], 'pc.programid', 'p.id')
                ->where_not_null('p.certifid')
                ->group_by_raw('pc.programid')
                ->having_raw('COUNT(pc.programid)=1')
                ->count(),
        ];
    }
}