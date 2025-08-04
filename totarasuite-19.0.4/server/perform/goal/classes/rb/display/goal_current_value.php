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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package perform_goal
 */

namespace perform_goal\rb\display;

use stdClass;
use rb_column;
use rb_column_option;
use reportbuilder;
use totara_reportbuilder\rb\display\base;

class goal_current_value extends base {

    /**
     * @inheritDoc
     */
    public static function display($value, $format, stdClass $row, rb_column $column, reportbuilder $report) {
        // $value is a percentage.
        $progress = round((float)$value);

        if ($format !== 'html') {
            return $progress;
        }

        return $progress . '%';
    }

    /**
     * @inheritDoc
     */
    public static function is_graphable(rb_column $column, rb_column_option $option, reportbuilder $report) {
        return true;
    }
}
