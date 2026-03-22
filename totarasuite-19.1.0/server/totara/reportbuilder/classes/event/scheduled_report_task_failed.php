<?php
/**
 * This file is part of Totara Learn
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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package totara_reportbuilder
 */

namespace totara_reportbuilder\event;

use coding_exception;
use context_system;
use core\event\base;
use dml_exception;
use stdClass;

class scheduled_report_task_failed extends base {

    /**
     * @inheridDoc
     */
    protected function init(): void {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'report_builder_schedule';
    }

    /**
     * @inheridDoc
     */
    public static function get_name(): string {
        return get_string('event_scheduled_report_task_failed', 'totara_reportbuilder');
    }

    /**
     * @inheridDoc
     */
    public function get_description(): string {
        // report email attachment file size limit in megabytes
        $limit = get_config('totara_reportbuilder', 'filesizelimit');
        return "The scheduled report task with the scheduled report id '$this->objectid' has failed due to the maximum email attachment limit of {$limit}MB";
    }

    /**
     * Create instance of event.
     *
     * @param stdClass $scheduledtask scheduled report task details.
     * @return base
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function create_from_schedule(stdClass $scheduledtask): base {
        $data = [
            'context' => context_system::instance(),
            'objectid' => $scheduledtask->id,
        ];
        return static::create($data);
    }
}
