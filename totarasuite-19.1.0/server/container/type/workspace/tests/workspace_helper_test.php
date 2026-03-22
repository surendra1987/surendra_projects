<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2020 onwards Totara Learning Solutions LTD
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
 * @author Kian Nguyen <kian.nguyen@totaralearning.com>
 * @package container_workspace
 */

defined('MOODLE_INTERNAL') || die();

use core\collection;
use core_phpunit\testcase;
use container_workspace\testing\generator;
use core\entity\user;
use core\format;
use core\files\file_helper;
use totara_webapi\phpunit\webapi_phpunit_helper;
use container_workspace\interactor\workspace\interactor as workspace_interactor;
use container_workspace\query\workspace\access;
use container_workspace\workspace;
use container_workspace\local\workspace_helper;
use container_workspace\member\member;

require_once(__DIR__.'/multi_owner_testcase.php');

/**
 * @group container_workspace
 */
class container_workspace_workspace_helper_test extends container_workspace_multi_owner_testcase {
    /**
     * @return void
     */
    public function test_update_workspace_time_stamp(): void {
        $generator = $this->getDataGenerator();
        $user_one = $generator->create_user();

        $this->setUser($user_one);

        /** @var \container_workspace\testing\generator $workspace_generator */
        $workspace_generator = $generator->get_plugin_generator('container_workspace');
        $workspace = $workspace_generator->create_workspace();

        // Update workspace time stamp
        $original_timestamp = $workspace->get_timestamp();
        workspace_helper::update_workspace_timestamp($workspace, $user_one->id, 100);

        $updated_timestamp = $workspace->get_timestamp();
        $this->assertNotEquals($original_timestamp, $updated_timestamp);
        $this->assertEquals(100, $updated_timestamp);
    }

    /**
     * @return void
     */
    public function test_update_workspace_timestamp_as_non_member(): void {
        $generator = $this->getDataGenerator();
        $user_one = $generator->create_user();

        $this->setUser($user_one);

        /** @var \container_workspace\testing\generator $workspace_generator */
        $workspace_generator = $generator->get_plugin_generator('container_workspace');
        $workspace = $workspace_generator->create_workspace();

        $user_two = $generator->create_user();
        workspace_helper::update_workspace_timestamp($workspace, $user_two->id, 100);

        $this->assertDebuggingCalled();
    }

    /**
     * @return void
     */
    public function test_update_timestamp_as_admin(): void {
        $generator = $this->getDataGenerator();
        $user_one = $generator->create_user();

        $this->setUser($user_one);

        /** @var \container_workspace\testing\generator $workspace_generator */
        $workspace_generator = $generator->get_plugin_generator('container_workspace');
        $workspace = $workspace_generator->create_workspace();

        $this->setAdminUser();
        workspace_helper::update_workspace_timestamp($workspace, null, 100);

        $timestamp = $workspace->get_timestamp();
        $this->assertEquals(100, $timestamp);
    }

    /**
     * @deprecated since Totara 19.0
     *
     * @return void
     */
    public function test_update_primary_owner_as_non_authority_user(): void {
        // Skipping this test as it is impossible to catch 2 exceptions,
        // instead of see test_remove_owners_as_non_authority_user()
        $this->markTestSkipped("deprecated since Totara 19.0");

        $generator = $this->getDataGenerator();
        $user_one = $generator->create_user();

        $this->setUser($user_one);

        /** @var \container_workspace\testing\generator $workspace_generator */
        $workspace_generator = $generator->get_plugin_generator('container_workspace');
        $workspace = $workspace_generator->create_workspace();

        // Log in as user two and check if user two is able to transfer the owner ship.
        $user_two = $generator->create_user();
        $this->setUser($user_two);

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage("Actor does not have ability to update workspace owner");

        workspace_helper::update_workspace_primary_owner($workspace, $user_two->id);
    }

    /**
     * @deprecated since Totara 19.0
     *
     * @return void
     */
    public function test_update_primary_owner_to_deleted_user(): void {
        // Skipping this test as it is impossible to catch 2 exceptions,
        // With new functionality this test is redundant
        $this->markTestSkipped("deprecated since Totara 19.0");

        $generator = $this->getDataGenerator();
        $user_one = $generator->create_user();
        $user_two = $generator->create_user();

        $this->setUser($user_one);

        /** @var \container_workspace\testing\generator $workspace_generator */
        $workspace_generator = $generator->get_plugin_generator('container_workspace');
        $workspace = $workspace_generator->create_workspace();

        // delete user two and assign it to the workspace's owner.
        delete_user($user_two);

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage(
            "Cannot update the workspace primary owner to user that had been suspended or deleted"
        );

        workspace_helper::update_workspace_primary_owner($workspace, $user_two->id);
    }

    /**
     * @return void
     */
    public function test_get_back_buttons(): void {
        $expected = [
            'url' => (new moodle_url(('/container/type/workspace/spaces.php')))->out(),
            'label' => get_string('find_spaces', 'container_workspace'),
            'url_param' => 'fw'
        ];

        $this->assertEquals($expected, workspace_helper::get_back_buttons('fw', null));

        $expected = [
            'url' => (new moodle_url(('/totara/catalog/explore.php')))->out(),
            'label' => get_string('back_button_explore', 'totara_catalog'),
            'url_param' => 'catalog'
        ];

        $this->assertEquals($expected, workspace_helper::get_back_buttons('catalog', null));
    }
}
