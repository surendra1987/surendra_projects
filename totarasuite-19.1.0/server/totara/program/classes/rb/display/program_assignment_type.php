<?php
/*
 * This file is part of Totara Learn
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Maxime Claudel <maxime.claudel@totara.com>
 * @package totara_program
 */

namespace totara_program\rb\display;

use totara_program\assignment\helper;
use totara_reportbuilder\rb\display\base;

global $CFG;
require_once $CFG->dirroot . '/totara/program/rb_sources/rb_source_program_membership.php';

/**
 * Displays a program assignment type
 */
class program_assignment_type extends base {

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
       return helper::get_type_string($value);
    }

    /**
     * Is this column graphable?
     *
     * @param \rb_column $column
     * @param \rb_column_option $option
     * @param \reportbuilder $report
     * @return bool
     */
    public static function is_graphable(\rb_column $column, \rb_column_option $option, \reportbuilder $report) {
        return false;
    }

    /**
     * Gets all the filter options
     * @return array
     */
    public static function get_options() {
        $types = [];
        foreach (helper::get_types() as $key => $type) {
            $types[$key] = helper::get_type_string($key);
        }
        asort($types);
        return $types;
    }
}
