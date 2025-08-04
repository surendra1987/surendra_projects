<?php
/*
 * This file is part of Totara Learn
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
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package totara_program
 */

namespace totara_program\rb\display;

use totara_reportbuilder\rb\display\base;

defined('MOODLE_INTERNAL') || die();

/**
 * Display class intended for arbitrary text, one line per course
 *
 * @package totara_program
 *
 * Note that this class was previously marked for deprecation, but then we figured out that it is still needed.
 */
class program_course_newline extends base {

    /**
     * Handles the display
     * @param string $value
     * @param string $format
     * @param \stdClass $row
     * @param \rb_column $column
     * @param \reportbuilder $report
     * @return string|array
     */
    public static function display($value, $format, \stdClass $row, \rb_column $column, \reportbuilder $report) {

        if (empty($value)) {
            return '';
        }

        $uniquedelimiter = $report->src->get_uniquedelimiter();

        $items = explode($uniquedelimiter, $value);

        $newitems = array();
        foreach ($items as $key => $item) {
            // $item is: $programid | $courseid | $data
            list(, , $data) = explode('|', $item, 3);

            $data = trim($data);
            if (empty($data) || $data === '-') {
                $newitems[$key] = '-';
            } else {
                $newitems[$key] = format_string($data);
            }
        }

        $output = implode("\n", $newitems);

        if ($format !== 'html') {
            $output = static::to_plaintext($output);
        }

        // For excel and ods export, force cell type to be a string.
        if ($format == "excel" || $format == "ods") {
            return array('string', $output, null);
        }

        return $output;
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
