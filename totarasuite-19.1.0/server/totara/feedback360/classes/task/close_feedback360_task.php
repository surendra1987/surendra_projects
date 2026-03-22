<?php
/**
 * This file is part of Totara Perform
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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package totara_feedback360
 */

namespace totara_feedback360\task;

use core\task\manager;
use core\orm\query\builder;
use core\task\adhoc_task;

defined('MOODLE_INTERNAL') || die();

class close_feedback360_task extends adhoc_task {

    /**
     * Trigger adhoc task to close all active feedback360
     *
     * @return void
     */
    public static function queue() {
        $task = new close_feedback360_task();
        manager::queue_adhoc_task($task, true);
    }

    /**
     * @inheritDoc
     */
    public function execute() {
        global $CFG;
        require_once($CFG->dirroot . '/totara/feedback360/lib.php');

        if (defined('PHPUNIT_TEST')) {
            $trace = new \null_progress_trace();
        } else {
            $trace = new \text_progress_trace();
        }

        $items = builder::table('feedback360')
            ->select('id')
            ->where('status', \feedback360::STATUS_ACTIVE)
            ->get();

        foreach ($items as $item) {
            $trace->output('Closing feedback360 with id: ' . $item->id);
            try {
                $feedback360 = new \feedback360($item->id);
                $feedback360->close();
            } catch (\Exception $e) {
                $trace->output('Unable to close feedback360 with id ' . $item->id);
                $trace->output('Error message: ' . $e->getMessage());
            }
        }
        $trace->finished();
    }
}