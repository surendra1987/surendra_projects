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
 * @package core_badges
 */

namespace core_badges\usagedata;

use tool_usagedata\export;
use tool_usagedata\helper\time;
use core\dml\sql;

class badges_issued_per_month implements export {

    public function get_summary(): string {
        return get_string('badges_issued_per_month_summary', 'badges');
    }

    /**
     * @inheritDoc
     */
    public function get_type(): int {
        return export::TYPE_OBJECT;
    }

    /**
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function export(): array {
        global $DB;

        $results = [];
        foreach (time::get_timestamps_for_past_months() as $date => $timestamps) {
            $results[$date] = $DB->count_records_select(
                'badge_issued',
                new sql('dateissued > :start AND dateissued <= :end', $timestamps)
            );
        }

        return $results;
    }
}
