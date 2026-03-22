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
 * @package approvalform_enrol
 */

use approvalform_enrol\installer;
use approvalform_enrol\watcher\workflow_version;

require_once(__DIR__ . '/../../../tests/testcase.php');

/**
 * @coversDefaultClass \approvalform_enrol\watcher\workflow_version
 *
 * @group approval_workflow
 */
class approvalform_enrol_watcher_workflow_version_test extends mod_approval_testcase {

    /**
     * @covers ::handle_workflow_version_pre_activate
     */
    public function test_handle_workflow_version_pre_activate_other_plugin() {
        $workflow = $this->create_workflow_for_user();
        $hook = new \mod_approval\hook\workflow_version_pre_activate($workflow->get_latest_version());

        // Watch the hook.
        workflow_version::handle_workflow_version_pre_activate($hook);

        // approvalform_simple isn't going to object to the structure.
        $this->assertTrue($hook->ok_to_activate());
        $this->assertEmpty($hook->get_warnings());
    }

    /**
     * @covers ::handle_workflow_version_pre_activate
     */
    public function test_handle_workflow_version_pre_activate_no_warnings() {
        // Create an un-published, publishable enrolment workflow.
        $workflow = $this->create_workflow_for_user(
            'enrol',
            [installer::class, 'configure_publishable_workflow'],
            'enrol',
            null,
            false
        );
        // Create a hook.
        $hook = new \mod_approval\hook\workflow_version_pre_activate($workflow->get_latest_version());

        // Watch the hook.
        workflow_version::handle_workflow_version_pre_activate($hook);

        $this->assertTrue($hook->ok_to_activate());
        $this->assertEmpty($hook->get_warnings());
    }

    /**
     * @covers ::handle_workflow_version_pre_activate
     */
    public function test_handle_workflow_version_pre_activate_with_warnings() {
        // Create an un-published, publishable enrolment workflow.
        $workflow = $this->create_workflow_for_user(
            'enrol',
            [installer::class, 'configure_publishable_workflow'],
            'enrol',
            null,
            false
        );

        // Hack it to remove the approved end stage.
        \mod_approval\form\approvalform_core_enrol_base::get_approved_stage($workflow->latest_version)->delete();

        // Create a hook.
        $hook = new \mod_approval\hook\workflow_version_pre_activate($workflow->get_latest_version());

        // Watch the hook.
        workflow_version::handle_workflow_version_pre_activate($hook);

        $this->assertFalse($hook->ok_to_activate());
        $this->assertNotEmpty($hook->get_warnings());
    }
}