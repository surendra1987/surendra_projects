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

class assignments_per_user implements export {

    public function get_summary(): string {
        return get_string('assignments_per_user_summary', 'totara_job');
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

        $sql = 'SELECT x.jobcount, COUNT(x.id) AS usercount
                FROM (
                    SELECT u.id, COUNT(ja.id) AS jobcount
                        FROM {user} AS u
                        LEFT JOIN {job_assignment} AS ja ON ja.userid = u.id
                        GROUP BY u.id
                ) x GROUP BY x.jobcount';
        $data = $DB->get_records_sql($sql);

        $results = [
            'nil' => 0,
            '1' => 0,
            '2' => 0,
            '3' => 0,
            '4' => 0,
            '5' => 0,
            '6' => 0,
            '7' => 0,
            '8' => 0,
            '9' => 0,
            '10+' => 0,
        ];

        foreach ($data as $job_count) {
            $job_count->jobcount = (int) $job_count->jobcount;
            $job_count->usercount = (int) $job_count->usercount;

            if ($job_count->jobcount === 0) {
                $results['nil'] += $job_count->usercount;
                continue;
            }

            if ($job_count->jobcount <= 1) {
                $results['1'] += $job_count->usercount;
                continue;
            }

            if ($job_count->jobcount <= 2) {
                $results['2'] += $job_count->usercount;
                continue;
            }

            if ($job_count->jobcount <= 3) {
                $results['3'] += $job_count->usercount;
                continue;
            }

            if ($job_count->jobcount <= 4) {
                $results['4'] += $job_count->usercount;
                continue;
            }

            if ($job_count->jobcount <= 5) {
                $results['5'] += $job_count->usercount;
                continue;
            }

            if ($job_count->jobcount <= 6) {
                $results['6'] += $job_count->usercount;
                continue;
            }

            if ($job_count->jobcount <= 7) {
                $results['7'] += $job_count->usercount;
                continue;
            }

            if ($job_count->jobcount <= 8) {
                $results['8'] += $job_count->usercount;
                continue;
            }

            if ($job_count->jobcount <= 9) {
                $results['9'] += $job_count->usercount;
                continue;
            }

            $results['10+'] += $job_count->usercount;
        }

        return $results;
    }
}
