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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Ning Zhou <ning.zhou@totara.com>
 * @package core
 */

namespace core\check\security;

defined('MOODLE_INTERNAL') || die();

use core\check\check;
use core\check\result;
use core\orm\query\builder;

class csv extends check {

    /**
     * @inheritDoc
     */
    public function get_name(): string {
        return get_string('check_csv_export_name', 'admin');
    }

    /**
     * @inheritDoc
     */
    public function get_result(): result {
        $status = result::WARNING;

        // Check the general report settings
        $export_options = explode(',', get_config('reportbuilder', 'exportoptions'));
        if (in_array('csv', $export_options)) {
            $summary = get_string('check_csv_export_setting_warning', 'admin');
        } else {
            $status = result::OK;
        }

        // Check the override report settings
        if ($status === result::OK) {
            // If general setting disable the csv export, then we need to make sure no override option enable it in all reports
            $override_options = builder::table('report_builder')->as('rb')
                ->left_join(['report_builder_settings', 'rbs'], 'rb.id', 'rbs.reportid')
                ->where('rb.overrideexportoptions', 1)
                ->where('rbs.type', 'exportoption')
                ->where('rbs.name', 'csv')
                ->where('rbs.value', 1)
                ->exists();
            if ($override_options) {
                $status = result::WARNING;
                $summary = get_string('check_csv_export_override_setting_warning', 'admin');
            }
        }

        // Check the schedule report export setting
        if ($status === result::OK) {
            $schedule_report_option = builder::table('report_builder_schedule')
                ->where('format', 'csv')
                ->exists();
            if ($schedule_report_option) {
                $status = result::WARNING;
                $summary = get_string('check_csv_export_schedule_warning', 'admin');
            }
        }

        if ($status === result::OK) {
            $summary = get_string('check_csv_export_ok', 'admin');
        }

        $details = get_string('check_csv_export_details', 'admin');

        return new result($status, $summary, $details);
    }
}