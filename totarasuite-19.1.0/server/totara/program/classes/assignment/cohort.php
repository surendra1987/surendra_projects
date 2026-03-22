<?php
/*
 * This file is part of Totara Learn
 *
 * Copyright (C) 2019 onwards Totara Learning Solutions LTD
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
 * @author Alastair Munro <alastair.munro@totaralearning.com>
 * @package totara_program
 */

namespace totara_program\assignment;
use totara_cohort\event\enrolled_program_item_added;
use totara_cohort\event\enrolled_program_item_deleted;

class cohort extends base {

    const ASSIGNTYPE_COHORT = 3;

    public function get_type(): int {
        return self::ASSIGNTYPE_COHORT;
    }

    public function get_name() : string {
        global $DB;

        $audience = $DB->get_record('cohort', ['id' => $this->instanceid]);

        return format_string($audience->name);
    }

    /**
     * Number of users in the audience
     *
     * @return int
     */
    public function get_user_count(): int {
        global $DB;

        // Get the count of cohort members.
        $count = $DB->count_records('cohort_members', ['cohortid' => $this->instanceid]);

        return $count;
    }

    /**
     * @inheritdoc
     */
    protected function trigger_assignment_event(): void {
        parent::trigger_assignment_event();
        global $DB;
        $instance_id = $this->instanceid;
        $audience_record = $DB->get_record('cohort', ['id' => $instance_id], '*', MUST_EXIST);
        $event = enrolled_program_item_added::create_from_data($instance_id, $audience_record);
        $event->trigger();
    }

    /**
     * @inheritdoc
     */
    protected function trigger_unassigned_event(): void {
        parent::trigger_unassigned_event();
        global $DB;
        $instance_id = $this->instanceid;
        $audience_record = $DB->get_record('cohort', ['id' => $instance_id]);
        if (!$audience_record) {
            return;
        }
        $event = enrolled_program_item_deleted::create_from_data($instance_id, $audience_record);
        $event->trigger();
    }
}
