<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2021 onwards Totara Learning Solutions LTD
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
 * @author Riana Rossouw <riana.rossouw@totaralearning.com>
 * @package core_completion
 */

namespace core_completion\task;

use cache;
use core\entity\course as course_entity;
use core\entity\course_completion as course_completion_entity;
use core\orm\query\builder;
use core\task\adhoc_task;
use core_completion\helper as course_completion_helper;
use core_completion\model\course_completion as course_completion_model;

/**
 * Ad-hoc task to update completion due dates when course settings changed
 */
class update_completion_due_dates_task extends adhoc_task {

    public function execute() {
        global $DB;

        $data = $this->get_custom_data();
        if (empty($data->course_id)) {
            throw new \coding_exception('Missing course_id in update_completion_due_dates_task');
        }

        /** @var course_entity $course */
        $course = course_entity::repository()->find($data->course_id);
        if (!$course) {
            debugging('Course '.$data->course_id.' does not exist. Skipping.');
            return;
        }

        if (!$course->duedateoffsetunit) {
            // Due date is either not set, or set to a fixed date -
            // we can simply update all enrolled users' due dates to the duedate value
            $sql =
                "UPDATE {course_completions}
                    SET duedate = :duedate
                  WHERE course = :course_id";
            $params = ['course_id' => $course->id, 'duedate' => $course->duedate];
            $DB->execute($sql, $params);

            // Only delete the completion cache for the users in the course.
            // We could purge the whole completion cache but
            // on a site with a lot of courses this just puts additional
            // strain on the system recreating the cache afterward
            $cache = cache::make('core', 'coursecompletion');
            $completion_records = $DB->get_huge_recordset('course_completions', ['course' => $course->id]);
            foreach ($completion_records as $record) {
                $key = $record->userid . '_' . $record->course;
                $cache->delete($key);
            }

            course_completion_helper::save_completion_log(
                $course->id,
                null,
                "Due date updated in update_completion_due_dates_task to " .
                    course_completion_helper::format_log_date($course->duedate)
            );
        } else {
            // For relative due dates we need to update each user's completion record individually
            // Use huge_recordset to minimise memory usage on supported databases
            $completion_records = $DB->get_huge_recordset('course_completions', ['course' => $course->id]);
            $batch = [];
            foreach ($completion_records as $record) {
                $batch[] = $record;

                // Update a batch in a transaction to speed up the process
                if (count($batch) % 1000 === 0) {
                    builder::get_db()->transaction(function () use ($batch) {
                        foreach ($batch as $record) {
                            $entity = new course_completion_entity($record, false, true);
                            $model = course_completion_model::load_by_entity($entity);
                            $model->update_duedate();
                        }
                    });
                    $batch = [];
                }
            }

            // Deal with any leftovers
            if (!empty($batch)) {
                builder::get_db()->transaction(function () use ($batch) {
                    foreach ($batch as $record) {
                        $entity = new course_completion_entity($record, false, true);
                        $model = course_completion_model::load_by_entity($entity);
                        $model->update_duedate();
                    }
                });
            }
        }
    }

}
