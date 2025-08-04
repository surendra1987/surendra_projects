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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package approvalform_enrol
 */

namespace approvalform_enrol\observer;

use approvalform_enrol\enrol;
use core_enrol\event\user_enrolment_application_approved;
use mod_approval\event\application_completed;
use mod_approval\model\application\application;

class application_completed_observer {

    /**
     * Observe application_completed events for workflows based on approvalform_enrol, and trigger an
     * enrolment_approved event when applicable.
     *
     * @param application_completed $event
     * @return void
     */
    public static function trigger_enrolment_approved(application_completed $event): void {
        // Make sure we should be handling this event.
        $data = $event->get_data();
        $application = application::load_by_id($data['objectid']);

        // Make sure this application belongs to an approvalform_enrol form.
        if ($application->get_approvalform_plugin()::class != enrol::class) {
            return;
        }

        // The stage name should be 'Approved'.
        if ($application->current_stage->name != enrol::APPROVED_END_STAGE) {
            return;
        }

        // Trigger user_enrolment_application_approved event here.
        $user_enrolment_application_approved = user_enrolment_application_approved::create_from_application_id($application->id);
        $user_enrolment_application_approved->trigger();
    }
}