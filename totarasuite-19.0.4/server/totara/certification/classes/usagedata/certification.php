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

        $rep = program::repository()->where_not_null('certifid');
        return [
            'total_certifications' => $rep->count(),
            'program_completion_editor_setting' => isset($CFG->enableprogramcompletioneditor) ? $CFG->enableprogramcompletioneditor : self::NOTSET,
        ];
    }
}