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

use core_enrol\event\pre_user_enrolment_deleted;
use core_enrol\event\pre_user_enrolment_bulk_deleted;
use core_enrol\model\user_enrolment_application;
use mod_approval\form\approvalform_core_enrol_base;
use mod_approval\model\workflow\stage_type\finished;

class user_enrolment {

    /**
     * Catch pre user enrolment deleted and mark application archived or delete if draft.
     *
     * @param pre_user_enrolment_deleted $event
     * @return void
     */
    public static function pre_user_enrolment_deleted(pre_user_enrolment_deleted $event): void {
        $user_enrolment_application = user_enrolment_application::find_with_user_enrolment_id($event->get_data()['objectid']);

        if (!$user_enrolment_application) {
            return;
        }

        $application = $user_enrolment_application->get_approval_application();

        if ($application->get_current_state()->is_draft()) {
            $application->delete();
            return;
        }

        if ($application->completed || $application->current_state->is_stage_type(finished::get_code())) {
            return;
        }

        $archived_stage = approvalform_core_enrol_base::get_archived_stage($application->workflow_version);
        $application->set_current_state($archived_stage->state_manager->get_initial_state());
    }

    /**
     * Catch pre user enrolment bulk deleted and mark all applications archived or delete if draft.
     *
     * @param pre_user_enrolment_bulk_deleted $event
     * @return void
     */
    public static function pre_user_enrolment_bulk_deleted(pre_user_enrolment_bulk_deleted $event): void {
        $user_enrolment_applications = user_enrolment_application::find_with_user_enrolment_ids($event->get_data()['other']['user_enrolment_ids']);

        foreach ($user_enrolment_applications as $user_enrolment_application) {
            $application = $user_enrolment_application->get_approval_application();

            if ($application->get_current_state()->is_draft()) {
                $application->delete();
                continue;
            }

            if ($application->completed || $application->current_state->is_stage_type(finished::get_code())) {
                continue;
            }

            $archived_stage = approvalform_core_enrol_base::get_archived_stage($application->workflow_version);
            $application->set_current_state($archived_stage->state_manager->get_initial_state());
        }
    }
}
