<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Sam Hemelryk <sam.hemelryk@totaralearning.com>
 * @package usagedata
 */

namespace core\usagedata;

use tool_usagedata\export;

class editor_order implements export {

    /**
     * @throws \coding_exception
     */
    public function get_summary(): string {
        return get_string('editor_order_summary');
    }

    /**
     * @inheritDoc
     */
    public function get_type(): int {
        return export::TYPE_OBJECT;
    }

    /**
     * @throws \dml_exception
     */
    public function export(): array {
        global $CFG;

        $editors_available = editors_get_available();
        $active_editors = explode(',', $CFG->texteditors);
        $active_editors = array_reverse($active_editors);

        $result = [];
        foreach ($active_editors as $key => $editor) {
            if (empty($editors_available[$editor])) {
                continue;
            }

            $result[$editor] = $key + 1;
        }

        return $result;
    }
}