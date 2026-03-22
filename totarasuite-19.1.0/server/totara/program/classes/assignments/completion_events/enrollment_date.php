<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * @author Simon Player <simon.player@totara.com>
 * @package totara_program
 */

namespace totara_program\assignments\completion_events;

use totara_program\assignments\assignments;
use totara_program\assignments\completion_event;

/**
 * Enrolment date completion event.
 */
class enrollment_date extends completion_event {

    /** @var array */
    private $timestamps;

    /**
     * @inheritdoc
     */
    public function get_id(): int {
        return assignments::COMPLETION_EVENT_ENROLLMENT_DATE;
    }

    /**
     * @inheritdoc
     */
    public function get_name(): string {
        return get_string('programenrollmentdate', 'totara_program');
    }

    /**
     * @inheritdoc
     */
    public function get_script(): string {
        return "
            totaraDialogs['completionevent'].clear();
        ";
    }

    /**
     * @inheritdoc
     */
    public function get_item_name(int $instanceid): string {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function get_completion_string(): string {
        return get_string('programenrollmentdate', 'totara_program');
    }

    /**
     * @inheritdoc
     */
    public function get_timestamp(int $userid, $assignobject): int {
        global $DB;

        // First time calling this function.
        if (!isset($this->timestamps)) {
            $this->timestamps = array();
        }

        // First time calling this function for this assignmentid.
        $assignmentid = $assignobject->id;
        if (!isset($this->timestamps[$assignmentid])) {
            $params = array('assignmentid' => $assignmentid);
            $this->timestamps[$assignmentid] = $DB->get_records('prog_user_assignment', $params, '', 'userid, timeassigned');
        }

        // Get the specific user assignment record.
        if (isset($this->timestamps[$assignmentid][$userid])) {
            return $this->timestamps[$assignmentid][$userid]->timeassigned;
        }

        // The only reason we would be trying to get this timestamp and it doesn't exist is if the record
        // is just about to be created, so just return the current time.
        return time();
    }
}