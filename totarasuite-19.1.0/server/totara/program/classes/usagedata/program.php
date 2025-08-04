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

use tool_usagedata\export;
use totara_program\entity\program as entity;
use totara_program\entity\prog_group;
use totara_program\entity\program_assignment;
use totara_program\assignments\assignments;

class program implements export {

    /**
     * @inheritdoc
     */
    public function get_summary(): string {
        return get_string('program_summary', 'totara_program');
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

        $groups = fn() => entity::repository()
            ->where_null('certifid')
            ->join([program_assignment::TABLE, 'pa'], 'id', 'pa.programid')
            ->where('pa.assignmenttype', assignments::ASSIGNTYPE_GROUP)
            ->join([prog_group::TABLE, 'g'], 'pa.assignmenttypeid', 'g.id')
            ->group_by('id')
            ->select('id');

        return [
            'total_programs' => entity::repository()->where_null('certifid')->count(),
            'program_completion_editor_setting' => isset($CFG->enableprogramcompletioneditor) ? $CFG->enableprogramcompletioneditor : self::NOTSET,
            'legacy_program_content_setting' => isset($CFG->enablelegacyprogramcontent) ? $CFG->enablelegacyprogramcontent : self::NOTSET,
            'total_programs_self_enrol' => $groups()->where('g.can_self_enrol', '1')->count(),
        ];
    }
}
