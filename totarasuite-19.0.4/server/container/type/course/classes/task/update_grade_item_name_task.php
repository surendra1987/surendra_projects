<?php
/**
 * This file is part of Totara Perform
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package container_course
 */

namespace container_course\task;

defined('MOODLE_INTERNAL') || die();

use core\task\adhoc_task;
use core\task\manager as task_manager;

class update_grade_item_name_task extends adhoc_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() :string {
        return get_string('update_grade_item_name_task', 'container_course');
    }

    public function execute() {
        global $DB, $CFG;
        require_once($CFG->libdir . '/gradelib.php');

        $trace =  (defined('PHPUNIT_TEST') && PHPUNIT_TEST)
            ? new \null_progress_trace()
            : new \text_progress_trace();

        // Get custom data for adhoc task.
        $data = $this->get_custom_data();

        try {
            // Attempt to update the grade item if relevant;
            $grademodule = $DB->get_record($data->mod_name, ['id' => $data->instance_id]);
            $grademodule->cmidnumber = $data->idnumber;
            $grademodule->modname = $data->mod_name;

            grade_update_mod_grades($grademodule);

            $trace->output('Grade item name successfully updated');
        } catch (\Throwable $e) {
            // Is $data->instance_id already deleted somehow?
            task_manager::adhoc_task_complete($this);
            $trace->output('Unable to update grade item name.');
            $trace->output('Error message: ' . $e->getMessage());
        }
        $trace->finished();
    }
}
