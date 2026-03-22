<?php
/**
 * This file is part of Totara Learn
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
 * @author Greg Quigley <greg.quigley@totara.com>
 */
namespace totara_reportbuilder\hook;

use totara_core\hook\base;

/**
 * A hook for allowing modifications to a scheduled report.
 * The hook executes before the schedule is checked to see if it's time to run the report
 */
class scheduled_report_pre_process extends base {
    /**
     * Details about the report
     * @var \stdClass
     */
    public $report;

    /**
     * Schedule for the report
     * @var \scheduler
     */
    public $schedule;

    public function __construct(\stdClass $report, \scheduler $schedule) {
        $this->report = $report;
        $this->schedule = $schedule;
    }
}