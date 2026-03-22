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
 * @author Angela Kuznetsova <angela.kuznetsova@totara.com>
 * @package mod_approval
 */

namespace mod_approval\rb\display;

defined('MOODLE_INTERNAL') || die();

use mod_approval\model\assignment\approver_type\user as user_approver;
use mod_approval\model\assignment\assignment_approver_type;
use totara_reportbuilder\rb\display\base;

/**
 * Class display approver name
 */
class approver_name extends base {

    /**
     * @inheritDoc
     */
    public static function display($value, $format, \stdClass $row, \rb_column $column, \reportbuilder $report) {
        $extrafields = static::get_extrafields_row($row, $column);
        if ($value) {
            if ($extrafields->approver_type_code == user_approver::TYPE_IDENTIFIER) {
                return $value;
            } else {
                return assignment_approver_type::get_instance($extrafields->approver_type_code)->entity_name((int) $value);
            }
        }
        return get_string('no_approvers_left', 'mod_approval');
    }

    /**
     * @inheritDoc
     */
    public static function is_graphable(\rb_column $column, \rb_column_option $option, \reportbuilder $report) {
        return false;
    }
}