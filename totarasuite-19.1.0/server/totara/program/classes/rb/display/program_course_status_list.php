<?php
/*
 * This file is part of Totara Learn
 *
 * Copyright (C) 2018 onwards Totara Learning Solutions LTD
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
 * @author Simon Player <simon.player@totaralearning.com>
 * @package totara_program
 */

namespace totara_program\rb\display;
use totara_reportbuilder\rb\display\base;

/**
 * Display class intended for course statuses
 *
 * @author Simon Player <simon.player@totaralearning.com>
 * @package totara_program
 */
class program_course_status_list extends base {

    /**
     * Handles the display
     *
     * @param string $value
     * @param string $format
     * @param \stdClass $row
     * @param \rb_column $column
     * @param \reportbuilder $report
     * @return string
     */
    public static function display($value, $format, \stdClass $row, \rb_column $column, \reportbuilder $report) {
        global $CFG, $COMPLETION_STATUS;

        // Needed for the globals and defines.
        require_once($CFG->dirroot . '/completion/completion_completion.php');

        if (empty($value)) {
            return '';
        }

        $output = array();
        $uniquedelimiter = $report->src->get_uniquedelimiter();

        $items = explode($uniquedelimiter, $value);
        foreach ($items as $key => $item) {
            // $item is: $programid | $courseid | $status
            list(, , $status) = explode('|', $item);

            if ($status === '') {
                $status = (string)COMPLETION_STATUS_NOTYETSTARTED;
            }
            if (in_array($status, array_keys($COMPLETION_STATUS))) {
                $output[$key] = get_string('coursecompletion_'.$COMPLETION_STATUS[$status], 'rb_source_program_overview');
            } else {
                $output[$key] = get_string('coursecompletion_notyetstarted', 'rb_source_program_overview');
            }
        }

        return implode("\n", $output);
    }

    /**
     * Is this column graphable?
     *
     * @param \rb_column $column
     * @param \rb_column_option $option
     * @param \reportbuilder $report
     * @return bool
     */
    public static function is_graphable(\rb_column $column, \rb_column_option $option, \reportbuilder $report): bool {
        return false;
    }
}
