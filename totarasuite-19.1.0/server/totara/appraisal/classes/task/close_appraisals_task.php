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
 * @package totara_appraisal
 */

namespace totara_appraisal\task;

use core\task\manager;
use core\orm\query\builder;
use core\task\adhoc_task;
use totara_core\advanced_feature;

defined('MOODLE_INTERNAL') || die();

class close_appraisals_task extends adhoc_task {

    /**
     * Trigger adhoc task to close all active appraisals
     *
     * @return void
     */
    public static function queue() {
        $task = new close_appraisals_task();
        manager::queue_adhoc_task($task, true);
    }

    /**
     * @inheritDoc
     */
    public function execute() {
        global $CFG;
        require_once($CFG->dirroot . '/totara/appraisal/lib.php');

        if (defined('PHPUNIT_TEST')) {
            $trace = new \null_progress_trace();
        } else {
            $trace = new \text_progress_trace();
        }

        $items = builder::table('appraisal')
            ->select('id')
            ->where('status', \appraisal::STATUS_ACTIVE)
            ->get();

        foreach ($items as $item) {
            $trace->output('Closing appraisal with id: ' . $item->id);
            try {
                $appraisal = new \appraisal($item->id);
                $appraisal->close();
            } catch (\Exception $e) {
                $trace->output('Unable to close appraisal with id ' . $item->id);
                $trace->output('Error message: ' . $e->getMessage());
            }
        }
        $trace->finished();
    }
}