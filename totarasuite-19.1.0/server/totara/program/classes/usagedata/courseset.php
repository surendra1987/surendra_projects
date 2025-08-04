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
 * @package totara_program
 */

namespace totara_program\usagedata;

use core\orm\query\builder;
use tool_usagedata\export;
use totara_program\content\program_content;
use totara_program\entity\program;
use totara_program\entity\program_courseset;
use totara_program\entity\program_courseset_course;

class courseset implements export {

    /**
     * @inheritdoc
     */
    public function get_summary(): string {
        return get_string('program_courseset_summary', 'totara_program');
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
        $progs = builder::table(program_courseset::TABLE, 'pc')
            ->join([program::TABLE, 'p'], 'pc.programid', 'p.id')
            ->where_null('p.certifid')
            ->get();

        $prog_coursesets = builder::table(program_courseset::TABLE, 'pc')
            ->select_raw('pcc.coursesetid, COUNT(pcc.courseid) AS progcount')
            ->join([program::TABLE, 'p'], 'pc.programid', 'p.id')
            ->join([program_courseset_course::TABLE, 'pcc'], 'pc.id', 'pcc.coursesetid')
            ->where_null('p.certifid')
            ->group_by('pcc.coursesetid')
            ->get();

        return [
            'count_coursesets' =>  $progs->count(),
            'with_one_course' => $prog_coursesets
                ->filter(function ($prog_courseset) {
                    return $prog_courseset->progcount == 1;
                })->count(),
            'with_multiple_courses' => $prog_coursesets
                ->filter(function ($prog_courseset) {
                    return $prog_courseset->progcount > 1;
                })->count(),
            'progs_with_one_courseset' => builder::table(program_courseset::TABLE, 'pc')
                ->select_raw('pc.programid, COUNT(pc.programid) AS progcount')
                ->join([program::TABLE, 'p'], 'pc.programid', 'p.id')
                ->where_null('p.certifid')->group_by_raw('pc.programid')
                ->having_raw('COUNT(pc.programid) =1')
                ->count(),
            'legacy_recurring' => $progs->filter(function ($prog_assignment) {
                return $prog_assignment->contenttype == program_content::CONTENTTYPE_RECURRING;
            })->count(),
            'legacy_competency' => count(array_unique($progs->filter(function ($prog_assignment) {
                return $prog_assignment->contenttype == program_content::CONTENTTYPE_COMPETENCY;
            })->pluck('programid'))),
        ];
    }
}