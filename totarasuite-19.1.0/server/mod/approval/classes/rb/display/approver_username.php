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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package mod_approval
 */

namespace mod_approval\rb\display;

use mod_approval\model\assignment\approver_type\relationship;
use totara_reportbuilder\rb\display\base;

/**
 * Approver username display column
 */
class approver_username extends base {
    /**
     * @inheritdoc
     */
    public static function display($value, $format, \stdClass $row, \rb_column $column, \reportbuilder $report) {
        $extrafields = static::get_extrafields_row($row, $column);
        if ($extrafields->approver_type_code == relationship::get_code()) {
            return strtolower((new relationship())->entity_name((int)$value));
        }
        return $value;
    }

    /**
     * @inheritdoc
     */
    public static function is_graphable(\rb_column $column, \rb_column_option $option, \reportbuilder $report) {
        return false;
    }
}
