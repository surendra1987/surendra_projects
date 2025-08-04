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

use tool_usagedata\export;
use totara_program\entity\program;
use totara_program\entity\prog_group;
use totara_program\entity\program_assignment;
use totara_program\assignments\assignments;

class certification implements export {

    /**
     * @inheritdoc
     */
    public function get_summary(): string {
        return get_string('certification_summary', 'totara_certification');
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
        global $CFG;

        $rep = fn() => program::repository()->where_not_null('certifid');

        $groups = fn() => program::repository()
            ->where_not_null('certifid')
            ->join([program_assignment::TABLE, 'pa'], 'id', 'pa.programid')
            ->where('pa.assignmenttype', assignments::ASSIGNTYPE_GROUP)
            ->join([prog_group::TABLE, 'g'], 'pa.assignmenttypeid', 'g.id')
            ->group_by('id')
            ->select('id');

        return [
            'total_certifications' => $rep()->count(),
            'program_completion_editor_setting' => isset($CFG->enableprogramcompletioneditor) ? $CFG->enableprogramcompletioneditor : self::NOTSET,
            'total_certifications_self_enrol' => $groups()->where('g.can_self_enrol', '1')->count(),
        ];
    }
}
