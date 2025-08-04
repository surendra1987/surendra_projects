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

use cache;
use totara_reportbuilder\rb\display\base;
use totara_program\assignment\base as assignment_base;
use totara_program\assignment\individual;

global $CFG;
require_once $CFG->dirroot . '/totara/program/rb_sources/rb_source_program_membership.php';

/**
 * Displays a program assignment name
 */
class program_assignment_name extends base {

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
        $extrafields = static::get_extrafields_row($row, $column);
        $key = $extrafields->programid . '/' . $extrafields->assignmenttype . '/' . $value;
        $cache = cache::make('totara_program', 'rb_assignment_name');
        if (!$name = $cache->get($key)) {
            // get returns false if it's not there and can't be loaded.
            $name = assignment_base::create_from_instance_id($extrafields->programid, $extrafields->assignmenttype, $value)->get_name();
            // No need to cache individual assignments, they are unique anyway
            if ($extrafields->assignmenttype != individual::ASSIGNTYPE_INDIVIDUAL) {
                $cache->set($key, $name);
            }
        }
        return $name;
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
}
