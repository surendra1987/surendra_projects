<?php
/*
 * This file is part of Totara LMS
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
 * @author Yi-Chin Chyi <yi-chin.chyi@totara.com>
 * @package totara_dashboard
 */

namespace totara_dashboard\rb\display;

use totara_reportbuilder\rb\display\base;
use totara_dashboard\lib;

/**
 * Class describing column display formatting.
 *
 * Translate public state into availability text.
 *
 * @author Yi-Chin Chyi <yi-chin.chyi@totara.com>
 * @package totara_dashboard
 */
class dashboard_availability extends base {

    public static function display($published, $format, \stdClass $row, \rb_column $column, \reportbuilder $report) {
        global $CFG, $OUTPUT, $USER;

        $extra = static::get_extrafields_row($row, $column);

        $available = '';
        switch ($published) {
            case \totara_dashboard::NONE:
                $available = get_string('availablenoneshort', 'totara_dashboard');
                break;
            case \totara_dashboard::AUDIENCE:
                $available = get_string('availableaudiencecnt', 'totara_dashboard', $extra->cnt ?: 0);
                break;
            case \totara_dashboard::ALL:
                $available = get_string('availableallshort', 'totara_dashboard');
                break;
            default:
                $available = get_string('availableunknownshort', 'totara_dashboard');
        }

        return $available;
    }

    public static function is_graphable(\rb_column $column, \rb_column_option $option, \reportbuilder $report) {
        return false;
    }
}
