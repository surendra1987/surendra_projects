<?php
/**
 * This file is part of Totara Core
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

namespace totara_dashboard\local\version_specific;
use totara_core\version_specific\version_specific_setting_base;
/*
 * A new layout for Manage dashboards that allows searching and editing display order.
 * In Totara 20, this will be enabled, and this setting will no longer exist
 */
class report extends version_specific_setting_base {
  public static function get_introduced_version(): string {
    return '19.1';
  }

  public static function get_display_name(): \lang_string {
    return new \lang_string("enable_totara_dashboard_report");
  }

  public static function get_help_text(): \lang_string {
    return new \lang_string("enable_totara_dashboard_report_help");
  }
}