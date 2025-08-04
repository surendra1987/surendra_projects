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
 * @package core
 */

namespace core\task;

use core\cipher\task\rollover_encryption_manager;
use core\event\cipher_encryption_queued;

/**
 * Adhoc task that for a specific model will look for any out of date encrypted records and update them.
 */
class update_encrypted_models_task extends adhoc_task {
    /**
     * Enqueue a instance of this adhoc task, one per valid encryption model.
     *
     * @return array
     */
    public static function enqueue_all(): array {
        $models = rollover_encryption_manager::instance()->get_encrypted_models();

        $tasks = [];
        foreach ($models as $class) {
            $task = new static();
            $task->set_model_class($class);
            $tasks[] = $task;
            manager::queue_adhoc_task($task, true);
        }

        // Fire the event
        cipher_encryption_queued::create_anonymous()->trigger();

        return $tasks;
    }

    /**
     * @param string $model_class
     * @return void
     */
    public static function enqueue_one(string $model_class): void {
        $manager = rollover_encryption_manager::instance();
        if (!$manager->is_encrypted_model($model_class)) {
            throw new \coding_exception($model_class . ' was not a valid encryption model');
        }

        $task = new static();
        $task->set_model_class($model_class);
        manager::queue_adhoc_task($task, true);

        // Fire the event
        cipher_encryption_queued::create_anonymous()->trigger();
    }

    /**
     * @param string $model_class
     * @return void
     */
    public function set_model_class(string $model_class): void {
        $this->set_custom_data(['model_class' => $model_class]);
    }

    /**
     * @return string|null
     */
    public function get_model_class(): ?string {
        $custom_data = $this->get_custom_data();
        return $custom_data->model_class ?? null;
    }

    /**
     * Find & update records for the specified model.
     *
     * @return void
     */
    public function execute() {
        $model_class = $this->get_model_class();
        if (empty($model_class)) {
            debugging('update_encrypted_models_task queued without a model to update', DEBUG_DEVELOPER);
            return;
        }

        $manager = rollover_encryption_manager::instance();
        $is_valid_model = $manager->is_encrypted_model($model_class);
        if (!$is_valid_model) {
            throw new \coding_exception(sprintf('Model %s did not have configured encryption details.', $model_class));
        }

        $debugging = debugging();
        if ($debugging) {
            mtrace('Updating outdated records for ' . $model_class);
        }

        $outdated = $manager->count_outdated_records($model_class);
        if ($debugging) {
            mtrace(sprintf('Outdated records found: %d', $outdated));
        }
        if ($outdated === 0) {
            return;
        }

        if ($debugging) {
            mtrace('Updating records...');
        }
        $updated = $manager->update_outdated_records($model_class);
        if ($debugging) {
            mtrace(sprintf('Updated %d records', $updated));
            mtrace('Finished updating records for ' . $model_class);
        }
    }
}