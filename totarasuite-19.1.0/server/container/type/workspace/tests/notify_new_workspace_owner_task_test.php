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

use container_workspace\task\notify_new_workspace_owner_task;
use container_workspace\output\transfer_ownership_notification;

/**
 * @deprecated since Totara 19.0
 */
class container_workspace_notify_new_workspace_owner_task_test extends \core_phpunit\testcase {
    use \core_phpunit\language_pack_faker_trait;
    /**
     * @return void
     */
    public function test_execute_task_without_workspace_id(): void {
        $this->markTestSkipped("deprecated since Totara 19.0");

        $task = new notify_new_workspace_owner_task();

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage("No workspace's id or author's id was set");
        $task->execute();
    }

    /**
     * @return void
     */
    public function test_execute_task_without_user_id(): void {
        $this->markTestSkipped("deprecated since Totara 19.0");

        $task = new notify_new_workspace_owner_task();

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage("No workspace's id or author's id was set");
        $task->execute();
    }

    /**
     * @return void
     */
    public function test_execute_task(): void {
        $this->markTestSkipped("deprecated since Totara 19.0");

        global $OUTPUT;
        $generator = $this->getDataGenerator();
        $user_one = $generator->create_user();

        $this->setUser($user_one);

        /** @var \container_workspace\testing\generator $workspace_generator */
        $workspace_generator = $generator->get_plugin_generator('container_workspace');
        $workspace = $workspace_generator->create_workspace();

        $admin_user = get_admin();

        $task = notify_new_workspace_owner_task::from_workspace($workspace->get_id(), $admin_user->id);

        $sink = $this->redirectMessages();
        $sink->clear();

        $task->execute();
        $messages = $sink->get_messages();

        $this->assertCount(1, $messages);
        $message = reset($messages);

        $this->assertObjectHasProperty('useridto', $message);
        $this->assertEquals($workspace->owners()->first()?->id, $message->useridto);

        $this->assertObjectHasProperty('subject', $message);
        $this->assertEquals(get_string('transfer_ownership_title', 'container_workspace'), $message->subject);

        $this->assertObjectHasProperty('fullmessage', $message);
        $this->assertObjectHasProperty('fullmessageformat', $message);
        $this->assertObjectHasProperty('fullmessagehtml', $message);

        $template = transfer_ownership_notification::create($workspace, $admin_user->id);
        $rendered_content = $OUTPUT->render($template);

        $this->assertEquals($rendered_content, $message->fullmessagehtml);
    }

    /**
     * @return void
     */
    public function test_execute_task_when_user_is_empty(): void {
        $this->markTestSkipped("deprecated since Totara 19.0");

        $generator = $this->getDataGenerator();
        $user_one = $generator->create_user();

        $this->setUser($user_one);

        /** @var \container_workspace\testing\generator $workspace_generator */
        $workspace_generator = $generator->get_plugin_generator('container_workspace');
        $workspace = $workspace_generator->create_workspace();

        $workspace->remove_user();

        $admin_user = get_admin();
        $task = notify_new_workspace_owner_task::from_workspace($workspace->get_id(), $admin_user->id);

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage("Workspace does not have owner record");

        $task->execute();
    }

    public function test_recipients_language_setting_is_observed(): void {
        $this->markTestSkipped("deprecated since Totara 19.0");

        $generator = self::getDataGenerator();
        $fake_language = 'xo_ox';
        $this->add_fake_language_pack(
            $fake_language,
            [
                'container_workspace' => [
                    'transfer_ownership_title' => 'Fake language subject string'
                ]
            ]
        );

        $user_one = $generator->create_user(['lang' => $fake_language]);
        self::setUser($user_one);

        /** @var \container_workspace\testing\generator $workspace_generator */
        $workspace_generator = $generator->get_plugin_generator('container_workspace');
        $workspace = $workspace_generator->create_workspace();

        $admin_user = get_admin();

        $task = notify_new_workspace_owner_task::from_workspace($workspace->get_id(), $admin_user->id);

        $sink = $this->redirectMessages();
        $sink->clear();

        $task->execute();
        $messages = $sink->get_messages();

        self::assertCount(1, $messages);
        $message = reset($messages);

        self::assertEquals($user_one->id, $message->useridto);
        self::assertEquals('Fake language subject string', $message->subject);
    }

}