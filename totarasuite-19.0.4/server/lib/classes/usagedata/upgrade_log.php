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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package core
 */

namespace core\usagedata;

use tool_usagedata\export;

class upgrade_log implements export {

    /**
     * @throws \coding_exception
     */
    public function get_summary(): string {
        return get_string('upgrade_log_summary');
    }

    /**
     * @inheritDoc
     */
    public function get_type(): int {
        return export::TYPE_ARRAY;
    }

    /**
     * @throws \dml_exception
     */
    public function export(): array {
        global $DB;

        $records = $DB->get_records_unkeyed(
            'upgrade_log',
            [
                'plugin' => 'core'
            ],
            'timemodified DESC',
            'id, type, plugin, timemodified, version, targetversion, info',
            0,
            30
        );

        return  array_map(
            fn($record) => (array)$record,
            $records
        );
    }
}