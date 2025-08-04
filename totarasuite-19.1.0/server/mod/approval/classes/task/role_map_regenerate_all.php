<?php
/*
 * This file is part of Totara Learn
 *
 * Copyright (C) 2022 onwards Totara Learning Solutions LTD
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
 * @author Chris Snyder <chris.snyder@totaralearning.com>
 * @package mod_approval
 */

namespace mod_approval\task;

use cache;
use core\lock\lock_config;
use core\task\scheduled_task;
use mod_approval\data_provider\application\role_map\role_map_controller;
use totara_core\advanced_feature;

defined('MOODLE_INTERNAL') || die();

/**
 * Regenerate role capability maps used to optimise building user capability maps.
 */
class role_map_regenerate_all extends scheduled_task {

    /**
     * Queues this scheduled task to ensure it is run when CRON runs next.
     */
    public static function queue() {
        global $DB;
        $sql = "UPDATE {task_scheduled}
                   SET nextruntime = 0, lastruntime = CASE WHEN lastruntime < :now THEN lastruntime ELSE 0 END
                 WHERE classname = :classname AND nextruntime <> 0";
        $params = [
            'now' => time(),
            'classname' => '\\' . __CLASS__
        ];
        $DB->execute($sql, $params);
    }

    /**
     * A description of what this task does for administrators.
     *
     * @return string
     */
    public function get_name() {
        return get_string('task_role_map_regenerate_all', 'mod_approval');
    }

    /**
     * Regenerate maps.
     */
    public function execute() {
        // Check on/off switch.
        if (advanced_feature::is_disabled('approval_workflows')) {
            return;
        }

        // Suppress output during tests.
        $quiet = PHPUNIT_TEST;
        if (!$quiet) {
            mtrace('Updating role capability maps at ' . time() . ' ...');
        }

        // Acquire a 1 hour lock for the execution of this task.
        $lock_factory = lock_config::get_lock_factory('mod_approval');
        $lock = $lock_factory->get_lock(get_class($this), 0, HOURSECS);

        // Prevent multiple processes from recalculating at the same time
        // Failure to acquire lock means the task is executing in another process
        if (empty($lock)) {
            if (!$quiet) {
                mtrace('... but maps are already being recalculated.');
            }
            return;
        }

        $start = microtime(true);
        foreach (role_map_controller::get_all_maps() as $map) {
            $map_start = microtime(true);

            // Re-acquire lock if it has expired to prevent the adhoc task from running.
            if ($map_start - $start > HOURSECS) {
                $lock = $lock_factory->get_lock(get_class($this), 0, HOURSECS);

                // Failed to re-acquire the lock.
                if (!$lock) {
                    $message = 'Lost the lock, it\'s been acquired by another process running this task, stopping execution';
                    debugging($message);
                    mtrace($message);
                    return;
                }
            }

            if (!$quiet) {
                mtrace('    updating ' . get_class($map), ' ... ');
            }
            $map->recalculate_complete_map();
            if (!$quiet) {
                mtrace(' done in ' . (microtime(true) - $map_start) . 's');
            }
        }

        // Set the clean flag.
        $role_cache = cache::make('mod_approval', 'role_map');
        $role_cache->set('maps_clean', 1);

        // release the lock
        $lock->release();

        $end = microtime(true);
        if (!$quiet) {
            mtrace('Complete at ' . time() . ' in ' . ceil($end - $start) . 's');
        }
    }
}
