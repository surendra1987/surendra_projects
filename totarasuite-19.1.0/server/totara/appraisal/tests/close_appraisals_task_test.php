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

use core\task\manager;
use totara_core\advanced_feature;
use totara_appraisal\task\close_appraisals_task;

global $CFG;
require_once($CFG->dirroot.'/totara/appraisal/tests/appraisal_testcase.php');

class totara_appraisal_close_appraisals_task_test extends appraisal_testcase {

    public function test_execute() {
        advanced_feature::enable('appraisals');

        [$appraisal] = $this->prepare_appraisal_with_users();
        $appraisal->activate();
        $id = $appraisal->id;
        unset($appraisal);
        $appraisal = new appraisal($id);
        $this->assertEquals(appraisal::STATUS_ACTIVE, $appraisal->status);

        close_appraisals_task::queue();

        $adhoc_tasks = manager::get_adhoc_tasks(close_appraisals_task::class);
        $this->assertNotEmpty($adhoc_tasks);

        $this->executeAdhocTasks();
        unset($appraisal);
        $appraisal = new appraisal($id);
        $this->assertEquals(appraisal::STATUS_CLOSED, $appraisal->status);

        advanced_feature::disable('appraisals');
    }
}
