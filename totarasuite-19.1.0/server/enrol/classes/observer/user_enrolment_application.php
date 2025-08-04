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

use context_course;
use core\event\user_enrolment_created;
use core\model\user_enrolment;
use core_enrol\event\user_enrolment_application_approved;
use totara_core_observer;

class user_enrolment_application {

    /**
     * Catch user enrolment application approved event to update the enrolment to active.
     *
     * @param user_enrolment_application_approved $event
     * @return void
     */
    public static function approved(user_enrolment_application_approved $event): void {
        $user_enrolment = user_enrolment::load_by_id($event->get_data()['other']['user_enrolments_id']);

        // Set the user enrolment timestart to now only if moving it forward. If it is later than now then it
        // might be due to an enrolment plugin delaying access.
        $time_start = null;
        $now = time();
        if ($user_enrolment->timestart < $now) {
            $time_start = $now;
        }

        $instance = $user_enrolment->get_enrolment();
        $plugin = enrol_get_plugin($instance->enrol);
        $plugin->update_user_enrol((object)$instance->get_entity_copy()->to_array(), $user_enrolment->userid, ENROL_USER_ACTIVE, $time_start);

        /** @var user_enrolment_created $user_enrolment_created_event */
        $user_enrolment_created_event = user_enrolment_created::create(
            [
                'objectid' => $event->get_data()['other']['user_enrolments_id'],
                'courseid' => $instance->courseid,
                'context' => context_course::instance($instance->courseid),
                'relateduserid' => $user_enrolment->userid,
                'other' => [
                    'enrol' => 'cohort',
                    'containertype' => 'container_course',
                ],
            ]
        );
        // Was blocked due to pending status, creates course completion.
        totara_core_observer::user_enrolment($user_enrolment_created_event);
    }
}
