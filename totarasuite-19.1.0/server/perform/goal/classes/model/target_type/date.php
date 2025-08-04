<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * @author Scott Davies <scott.davies@totara.com>
 * @package perform_goal
 */

namespace perform_goal\model\target_type;

use coding_exception;
use perform_goal\model\goal;

/**
 * The 'date' target_type for a goal will calculate the goal subject user's progress towards achieving their goal based on
 * the time elapsed between the start and target dates.
 */
class date extends target_type {

    /**
     * @param goal|null $goal
     */
    public function __construct(?goal $goal = null) {
        if ($goal) {
            $this->goal = $goal;
        }
    }

    /**
     * @return string
     */
    public static function get_label(): string {
        return get_string('target_type_date', 'perform_goal');
    }

    /**
     * @return string
     */
    public static function get_type() {
        return 'date';
    }

    /**
     * The goal's progress percent is calculated using the time elapsed between the start and target dates.
     * @return float
     */
    public function get_progress_percent() {
        if (!isset($this->goal)) {
            throw new coding_exception("The goal is missing or not set on the target_type: " . self::get_type() . ".");
        }
        // We can't do a calculation without these input values.
        $required_check = ['target_date' => $this->goal->target_date,
            'start_date' => $this->goal->start_date
        ];
        foreach ($required_check as $label => $check_field) {
            if (!isset($check_field) || empty($check_field)) {
                throw new coding_exception("The goal field '{$label}' is missing or not set.");
            }
        }

        // We have valid inputs, so start calculating.
        $current_time = time();
        $total_duration = $this->goal->target_date - $this->goal->start_date;
        if ($total_duration < 0) {
            throw new coding_exception('One of the date inputs is invalid, e.g. the target_date is before the start_date.');
        }

        $time_spent_so_far = $current_time - $this->goal->start_date;
        return ($time_spent_so_far / $total_duration) * 100.0;
    }

    /**
     * The goal's raw progress is the current timestamp.
     * @return int
     */
    public function get_progress_raw() {
        return time();
    }
}
