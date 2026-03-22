<?php
/**
 * This file is part of Totara Perform
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
 * @author Scott Davies <scott.davies@totara.com>
 * @package hierarchy_goal
 */

namespace hierarchy_goal\task;

use \core\task\adhoc_task;
use \core\task\manager;
global $CFG;
require_once($CFG->dirroot.'/totara/hierarchy/prefix/goal/lib.php');

/**
 * This task sets up the updating of user assignments so that the possibly long-running operation is not
 * executed immediately by a UI action. (This is to prevent duplicate records being inserted into the goal_record table.)
 */
class update_user_assignments_adhoc_task extends adhoc_task {
    /**
     * @param $goal
     * @param int $item
     * @param $assigntype
     * @param $relationship
     * @param $type
     * @return update_user_assignments_adhoc_task
     */
    public static function enqueue($goal, int $item, $assigntype, $relationship, $type): update_user_assignments_adhoc_task {
        $custom_data = [
            'goal' => $goal,
            'item' => $item,
            'assigntype' => $assigntype,
            'relationship' => $relationship,
            'type' => $type
        ];

        $task = new self();
        $task->set_custom_data($custom_data);
        $task->set_component('totara_hierarchy');
        // Block this adhoc task while the scheduled task "totara_hierarchy\task\update_goal_assignments_task" could be
        // running, so that they never try to execute at the same time.
        $task->set_blocking(true);

        manager::queue_adhoc_task($task, true);
        return $task;
    }

    /**
     * @return void
     */
    public function execute() {
        global $DB;
        // Since this is an adhoc task, it can run when there are no scheduled
        // update assignment tasks to run during this time
        $cdata = $this->get_custom_data();
        $goal = new \goal();
        $eventclass = "\\hierarchy_goal\\event\\assignment_{$cdata->type->fullname}_created";
        $goal->update_user_assignments($cdata->item, $cdata->assigntype, $cdata->relationship);
        $relationship = $DB->get_record($cdata->type->table, array('id' => $cdata->relationship->id));
        $eventclass::create_from_instance($relationship)->trigger();
    }
}
