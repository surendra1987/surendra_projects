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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Navjeet Singh <navjeet.singh@totara.com>
 * @author Ning Zhou <ning.zhou@totara.com>
 * @package mod_approval
 */

namespace mod_approval\rb\display;

use totara_reportbuilder\rb\display\base;

class assignment_is_default extends base {
    /**
     * @inheritDoc
     */
    public static function display($value, $format, \stdClass $row, \rb_column $column, \reportbuilder $report) {
        switch ($value) {
            case 0:
                return get_string('override', 'rb_source_approval_override_role_assignment');
            case 1:
                return get_string('default', 'rb_source_approval_override_role_assignment');
            default:
                throw new \coding_exception("Unknown assignment code");
        }
    }

    /**
     * @inheritDoc
     */
    public static function is_graphable(\rb_column $column, \rb_column_option $option, \reportbuilder $report) {
        return false;
    }
}