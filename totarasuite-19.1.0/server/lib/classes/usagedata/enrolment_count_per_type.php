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

class enrolment_count_per_type implements export {

    public const COMPONENT = 'core_enrol';

    /**
     * @throws \coding_exception
     */
    public function get_summary(): string {
        return get_string('enrolment_count_per_type_summary', 'enrol');
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
        global $DB;

        $sql = 'SELECT e.enrol, COUNT(ue.id)
                  FROM {user_enrolments} ue
                  JOIN {enrol} e ON e.id = ue.enrolid
              GROUP BY e.enrol';

        $enrolments = $DB->get_records_sql_menu($sql);

        $result = [];
        foreach ($enrolments as $enrolment => $count) {
            $result[$enrolment] = (int) $count;
        }
        return $result;
    }
}