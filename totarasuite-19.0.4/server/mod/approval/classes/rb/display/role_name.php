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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Navjeet Singh <navjeet.singh@totara.com>
 * @author Ning Zhou <ning.zhou@totara.com>
 * @package mod_approval
 */

namespace mod_approval\rb\display;

use totara_reportbuilder\rb\display\base;

class role_name extends base {

    /**
     * @var string[]
     */
    private static $roles = [];

    /**
     * @inheritDoc
     */
    public static function display($value, $format, \stdClass $row, \rb_column $column, \reportbuilder $report) {
        $options = [];
        foreach (static::get_roles() as $role) {
            $options[$role->id] = $role->localname;
        }
        $extrafields = static::get_extrafields_row($row, $column);
        if (empty($value) && isset($options[$extrafields->base_roleid])) {
            return $options[$extrafields->base_roleid];
        }
        return $value;
    }

    /**
     * @inheritDoc
     */
    public static function is_graphable(\rb_column $column, \rb_column_option $option, \reportbuilder $report) {
        return false;
    }

    /**
     * Get roles
     * @return array|string[]
     */
    private static function get_roles() {
        if (empty(static::$roles)) {
            static::$roles = role_get_names();
        }
        return static::$roles;
    }
}