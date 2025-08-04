<?php
/**
 * This file is part of Totara Talent Experience Platform
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package core_cipher
 */

use core\cipher\testing\mock_cipher_models;
use core\cipher\testing\mock_key_manager;
use core\task\update_encrypted_models_task;
use core_cipher\fixtures\model\cipher_mock_model;
use core_phpunit\testcase;

/**
 * This test uses custom models for testing so relies on the orm_entity_testcases.
 *
 * @group core_cipher
 */
class core_cipher_update_encrypted_models_task_test extends testcase {
    use mock_cipher_models, mock_key_manager;

    /**
     * @return void
     */
    public function test_task_validation(): void {
        $task = new update_encrypted_models_task();
        $task->set_model_class('not-real');

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Model not-real did not have configured encryption details.');

        $task->execute();
    }

    /**
     * @return void
     */
    public function test_execution(): void {
        $key_manager = $this->setup_mock_key_manager();
        $rollover_manager = $this->create_mock_setup();
        $first_key = $key_manager->add_key();

        // Up-to-date model
        $model = $this->create_model('plain', 'abc');
        $entity = $model->get_entity();

        $task = new update_encrypted_models_task();
        $task->set_model_class(cipher_mock_model::class);

        // Confirm we see no records
        ob_start(); // Start a buffer to catch all the mtraces in the task.
        $task->execute();
        ob_end_clean();

        // Add a new key & confirm the model is out of date
        $this->waitForSecond();
        $second_key = $key_manager->add_key();

        // Model uses old key
        $this->assertStringStartsWith($first_key, $entity->encrypted);

        // Task is run
        ob_start(); // Start a buffer to catch all the mtraces in the task.
        $task->execute();
        ob_end_clean();
        $entity->refresh();

        // Model uses the new key
        $this->assertStringStartsWith($second_key, $entity->encrypted);

        $this->reset_mock_models();
        $this->reset_mock_key_manager();
    }

    /**
     * Test the ad-hoc task completes when queued.
     *
     * @return void
     */
    public function test_queue_execution(): void {
        $key_manager = $this->setup_mock_key_manager();
        $rollover_manager = $this->create_mock_setup();
        $first_key = $key_manager->add_key();

        // Up-to-date model
        $model = $this->create_model('plain', 'abc');
        $entity = $model->get_entity();
        $this->assertStringStartsWith($first_key, $entity->encrypted);

        $this->waitForSecond();
        $second_key = $key_manager->add_key();

        // Queue our job up
        update_encrypted_models_task::enqueue_one(cipher_mock_model::class);

        ob_start(); // Start a buffer to catch all the mtraces in the task.
        $this->executeAdhocTasks();
        ob_end_clean();

        $entity->refresh();
        $this->assertStringStartsWith($second_key, $entity->encrypted);

        $this->waitForSecond();
        $third_key = $key_manager->add_key();

        // Queue our job up
        update_encrypted_models_task::enqueue_all();

        ob_start(); // Start a buffer to catch all the mtraces in the task.
        $this->executeAdhocTasks();
        ob_end_clean();

        $entity->refresh();
        $this->assertStringStartsWith($third_key, $entity->encrypted);

        $this->reset_mock_models();
        $this->reset_mock_key_manager();
    }
}