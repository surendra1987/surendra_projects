<?php
/**
 * This file is part of Totara LMS
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Navjeet Singh <naveet.singh@totara.com>
 * @package totara_reportbuilder
 */

namespace totara_reportbuilder\rb\display;

use core_ai\subsystem;

/**
 * get AI plugin name
 */
class ai_plugin_name extends base {

    /**
     * @param $value
     * @param $format
     * @param \stdClass $row
     * @param \rb_column $column
     * @param \reportbuilder $report
     * @return string
     */
    public static function display($value, $format, \stdClass $row, \rb_column $column, \reportbuilder $report) {
        $get_ai_plugins = subsystem::get_ai_plugins();
        $return_value = '';
        if (!empty($value)) {
            // defaults to the $value, so if the plugin can not be found, the raw value is displayed.
            $return_value = $value;
            if (isset($get_ai_plugins[$value])) {
                $return_value = $get_ai_plugins[$value]->displayname;
            }
            return $return_value;
        }
        return $return_value;
    }
}