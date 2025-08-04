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
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package tool_usagedata
 */

namespace tool_usagedata\helper;

class time {

    /**
     * @param $num_of_months
     * @return array
     */
    public static function get_timestamps_for_past_months($num_of_months = 12): array {
        $now = time();
        $month_start_timestamp = mktime(0, 0, 0, date('m', $now), 1, date('Y', $now));
        $month_start = (new \DateTime())->setTimestamp($month_start_timestamp);
        $month_interval = new \DateInterval('P1M');

        $return = [];
        for ($i = 0; $i < $num_of_months; $i++) {
            $month_end = (clone $month_start)->add($month_interval)->sub(new \DateInterval('PT1S'));
            $return[$month_start->format('Y-m')] = [
                'start' => $month_start->getTimestamp(),
                'end' => $month_end->getTimestamp(),
            ];
            $month_start->sub($month_interval);
        }
        return $return;
    }
}