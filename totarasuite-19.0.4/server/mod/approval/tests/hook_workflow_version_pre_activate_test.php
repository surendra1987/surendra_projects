<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package mod_approval
 */

use approvalform_enrol\installer;
use mod_approval\hook\workflow_version_pre_activate;
use mod_approval\model\workflow\workflow_version;

require_once(__DIR__ . '/testcase.php');

/**
 * @group approval_workflow
 * @coversDefaultClass mod_approval\hook\workflow_version_pre_activate
 */
class mod_approval_hook_workflow_version_pre_activate_test extends mod_approval_testcase {

    private function create_test_workflow_version(): workflow_version {
        // Create an un-published enrolment workflow.
        $workflow = $this->create_workflow_for_user(
            'enrol',
            [installer::class, 'configure_publishable_workflow'],
            'enrol',
            null,
            false
        );
        return $workflow->get_latest_version();
    }

    public function test_get_workflow_version() {
        $workflow_version = $this->create_test_workflow_version();
        $hook = new workflow_version_pre_activate($workflow_version);

        $returned_workflow_version = $hook->get_workflow_version();
        $this->assertInstanceOf(workflow_version::class, $returned_workflow_version);
        $this->assertEquals($workflow_version->id, $returned_workflow_version->id);
    }

    public function test_add_get_warnings() {
        $workflow_version = $this->create_test_workflow_version();
        $hook = new workflow_version_pre_activate($workflow_version);

        $this->assertCount(0, $hook->get_warnings());

        $hook->add_warning('foo');
        $this->assertCount(1, $hook->get_warnings());
        $this->assertEquals(['foo'], $hook->get_warnings());

        $hook->add_warning('bar');
        $this->assertCount(2, $hook->get_warnings());
        $this->assertEquals(['foo', 'bar'], $hook->get_warnings());

        // Warnings are not keyed/unique
        $hook->add_warning('foo');
        $this->assertCount(3, $hook->get_warnings());
        $this->assertEquals(['foo', 'bar', 'foo'], $hook->get_warnings());
    }

    public function test_ok_to_activate() {
        $workflow_version = $this->create_test_workflow_version();
        $hook = new workflow_version_pre_activate($workflow_version);

        $this->assertTrue($hook->ok_to_activate());

        $hook->do_not_activate();
        $this->assertFalse($hook->ok_to_activate());

        // Call it again.
        $hook->do_not_activate();
        $this->assertFalse($hook->ok_to_activate());
    }
}
