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
 * @author Ning Zhou <ning.zhou@totara.com>
 * @package usagedata
 */

namespace totara_job\usagedata;

use tool_usagedata\export;

class configuration_count implements export {

    public function get_summary(): string {
        return get_string('configuration_count_summary', 'totara_job');
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

        return [
            'total' => $DB->count_records('job_assignment'),
            'with_fullname' => $DB->count_records_select('job_assignment', 'fullname <> \'\' OR fullname IS NOT NULL'),
            'with_shortname' => $DB->count_records_select('job_assignment', 'shortname <> \'\' OR shortname IS NOT NULL'),
            'with_idnumber' => $DB->count_records_select('job_assignment', 'idnumber <> \'\''),
            'with_startdate' => $DB->count_records_select('job_assignment', 'startdate <> 0 OR startdate IS NOT NULL'),
            'with_enddate' => $DB->count_records_select('job_assignment', 'enddate <> 0 OR enddate IS NOT NULL'),
            'with_position' => $DB->count_records_select('job_assignment', 'positionid <> 0 OR positionid IS NOT NULL'),
            'with_organisation' => $DB->count_records_select('job_assignment', 'organisationid <> 0 OR organisationid IS NOT NULL'),
            'with_manager' => $DB->count_records_select('job_assignment', 'managerjaid <> 0 OR managerjaid IS NOT NULL'),
            'with_tempmanager' => $DB->count_records_select('job_assignment', 'tempmanagerjaid <> 0 OR tempmanagerjaid IS NOT NULL'),
            'with_appraiser' => $DB->count_records_select('job_assignment', 'appraiserid <> 0 OR appraiserid IS NOT NULL'),
        ];
    }

}
