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

namespace totara_program\assignments\categories;

use totara_program\assignments\assignments;
use totara_program\assignments\category;

/**
 * Learning plans category of program assignment.
 */
class plans extends category {

    public function __construct() {
        $this->id = assignments::ASSIGNTYPE_PLAN;
        $this->name = get_string('plan', 'totara_program');
    }

    /**
     * Should the assignment type be used via the user interface?
     *
     * @return bool
     */
    public static function show_in_ui() : bool {
        return false;
    }

    /**
     * Builds table for displaying within assignment category.
     * Not required for plans as this assignment type intentionally has no UI.
     *
     * @inheritdoc
     */
    public function build_table($programidorinstance): void { }

    /**
     * Create row to be added to this assignment category's table.
     * Not required for plans as this assignment type intentionally has no UI.
     */
    public function build_row($item, bool $canupdate = true): array {
        return array();
    }

    /**
     * @inheritdoc
     */
    public function user_affected_count($item) {
        return $this->get_affected_users($item, 0, true);
    }

    /**
     * @inheritdoc
     */
    public function get_affected_users($item, int $userid = 0, bool $count = false) {
        global $DB;

        $select = $count ? 'COUNT(u.id)' : 'u.id';

        $sql = "SELECT $select
                  FROM {dp_plan} p
                  JOIN {dp_plan_program_assign} ppa on ppa.planid = p.id
                  JOIN {user} u ON u.id = p.userid
                 WHERE p.id = :assignmenttypeid
                   AND ppa.programid = :programid
                   AND p.status = :status_approved
                   AND p.status != :status_complete
                   AND ppa.approved = :approved
                   AND u.deleted = 0";

        $params = [
            'programid' => $item->programid,
            'assignmenttypeid' => $item->assignmenttypeid,
            'status_approved' => DP_PLAN_STATUS_APPROVED,
            'status_complete' => DP_PLAN_STATUS_COMPLETE,
            'approved' => DP_APPROVAL_APPROVED,
        ];

        if ($count) {
            return $DB->count_records_sql($sql, $params);
        } else {
            return $DB->get_records_sql($sql, $params);
        }
    }

    /**
     * @inheritdoc
     */
    public function get_affected_users_by_assignment($assignment, int $userid = 0) {
        return $this->get_affected_users($assignment, $userid);
    }

    /**
     * Unused by the plans category, so just return zero
     *
     * @inheritdoc
     */
    public function get_includechildren($data, $object): int {
        return 0;
    }

    /**
     * Unused by the plans category, so just return empty string
     *
     * @inheritdoc
     */
    public function get_js($programid): string {
        return '';
    }

    /**
     * Add assignment hook
     *
     * @param $object
     * @return bool
     */
    protected function _add_assignment_hook($object): bool {
        return true;
    }

    /**
     * Delete assignment hook
     *
     * @param $object
     * @return bool
     */
    protected function _delete_assignment_hook($object): bool {
        return true;
    }
}