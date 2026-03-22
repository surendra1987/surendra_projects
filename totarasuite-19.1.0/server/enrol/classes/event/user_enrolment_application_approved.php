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

namespace core_enrol\event;

use core\event\base;
use core_enrol\model\user_enrolment_application;

class user_enrolment_application_approved extends base {

    /**
     * @return void
     */
    protected function init(): void {
        $this->data['objecttable'] = 'user_enrolments_application';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * @inheritDoc
     */
    public static function get_name(): string {
        return get_string('user_enrolment_application_approved', 'core_enrol');
    }

    /**
     * @inheritDoc
     */
    public function get_description(): string {
        return "User enrolment application has been approved for user enrolment with id {$this->other['user_enrolments_id']} and application with id {$this->other['approval_application_id']}";
    }

    /**
     * @inheritDoc
     */
    public function get_notification_event_data(): array {
        return [
            'user_enrolments_application_id' => $this->get_data()['objectid'],
            'user_enrolments_id' => $this->get_data()['other']['user_enrolments_id'],
            'approval_application_id' => $this->get_data()['other']['approval_application_id'],
        ];
    }

    /**
     * Given an application id, get a triggerable user_enrolment_application_approved event.
     *
     * @param int $approval_application_id
     * @return user_enrolment_application_approved
     */
    public static function create_from_application_id(int $approval_application_id): user_enrolment_application_approved {
        $user_enrolment_application = user_enrolment_application::find_with_application_id($approval_application_id);
        if (!$user_enrolment_application) {
            throw new \coding_exception('No user_enrolment_application record associated with that application');
        }

        return user_enrolment_application_approved::create([
            'objectid' => $user_enrolment_application->id,
            'other' => [
                'user_enrolments_id' => $user_enrolment_application->user_enrolments_id,
                'approval_application_id' => $user_enrolment_application->approval_application_id,
            ],
            'context' => \context_course::instance($user_enrolment_application->course->id),
        ]);
    }
}
