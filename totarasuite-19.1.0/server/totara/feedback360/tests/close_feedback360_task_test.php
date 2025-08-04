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

use core\task\manager;
use totara_core\advanced_feature;
use totara_feedback360\task\close_feedback360_task;

global $CFG;
require_once($CFG->dirroot.'/totara/feedback360/tests/feedback360_testcase.php');

class totara_feedback360_close_feedback360_task_test extends feedback360_testcase {

    public function test_execute() {
        advanced_feature::enable('feedback360');

        [$feedback360] = $this->prepare_feedback_with_users();
        $feedback360->validate();
        $this->assertTrue(feedback360::is_draft($feedback360));
        $feedback360->activate();
        $id = $feedback360->id;
        unset($feedback360);
        $feedback360 = new feedback360($id);
        $this->assertEquals(feedback360::STATUS_ACTIVE, $feedback360->status);

        close_feedback360_task::queue();

        $adhoc_tasks = manager::get_adhoc_tasks(close_feedback360_task::class);
        $this->assertNotEmpty($adhoc_tasks);

        // Test
        $this->executeAdhocTasks();
        unset($feedback360);
        $feedback360 = new feedback360($id);
        $this->assertEquals(feedback360::STATUS_CLOSED, $feedback360->status);

        advanced_feature::disable('feedback360');
    }
}