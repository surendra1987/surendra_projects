<?php
/*
 * This file is part of Totara Suite
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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package totara_core
 */

namespace core_course\local\version_specific;

use totara_core\version_specific\version_specific_setting_base;

/**
 * Course category pagination is an optional performance management enhancement.
 * In Totara 20, this will be enabled, and this setting will no longer exist
 */
class course_category_pagination extends version_specific_setting_base {

    public static function get_introduced_version(): string {
        return '19.1';
    }

    public static function get_display_name(): \lang_string {
        return new \lang_string("enable_course_category_pagination");
    }

    public static function get_help_text(): \lang_string {
        return new \lang_string("enable_course_category_pagination_help");
    }

}
