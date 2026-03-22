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
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package tool_usagedata
 */

namespace tool_usagedata\task;

use core\task\scheduled_task;
use Exception;
use tool_usagedata\config;
use tool_usagedata\exporter;

class export_task extends scheduled_task {

    /**
     * @return string
     * @throws \coding_exception
     */
    public function get_name(): string {
        return get_string('export_task_name', 'tool_usagedata');
    }

    /**
     * Will attempt to run the task
     * Will not run if opt_out is enabled for tool_usagedata
     * @return bool
     * @throws Exception
     */
    public function execute(): bool {
        global $CFG;
        require_once "$CFG->dirroot/$CFG->admin/registerlib.php";

        // First, let's check that we can run the exporter and, if we can't, let's make the output nice and neat
        if ($CFG->sitetype !== 'production' || is_registration_exempted()) {
            mtrace('Site type is not production or registration is exempted.');
            return false;
        }

        if (config::is_opt_out_enabled()) {
            mtrace('opt_out enabled, disable opt_out to continue.');
            return false;
        }

        $export_success = (new exporter())->export_to_data_collector();
        if (!$export_success) {
            mtrace('Usage data export skipped - collection URL not set');
            return false;
        }

        mtrace('Usage data exported successfully.');
        return true;
    }
}