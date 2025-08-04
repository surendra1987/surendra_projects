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
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package core_enrol
 */

namespace core_enrol\observer;

use core\orm\query\builder;
use mod_approval\event\workflow_deleted;

class approval_workflow {

    /**
     * Deal with approval workflows being deleted.
     *
     * @param workflow_deleted $event
     * @return void
     */
    public static function approval_workflow_deleted(workflow_deleted $event): void {
        // Find all enrolment instances which use the workflow.
        $enrol_instance_ids = builder::table('enrol')
            ->select('id')
            ->where('workflow_id', '=', $event->objectid)
            ->get()
            ->keys();

        if (empty($enrol_instance_ids)) {
            return;
        }

        // Deal with the in-progress user enrolments.
        builder::table('user_enrolments')
            ->where_in('enrolid', $enrol_instance_ids)
            ->where('status', '=', ENROL_USER_PENDING_APPLICATION)
            ->update([
                'status' => ENROL_USER_ACTIVE,
            ]);

        // Update the enrolment instances.
        builder::table('enrol')
            ->where('workflow_id', '=', $event->objectid)
            ->update([
                'workflow_id' => null,
            ]);
    }
}
