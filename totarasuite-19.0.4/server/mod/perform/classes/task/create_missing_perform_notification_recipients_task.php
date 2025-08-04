<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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
 * @author Murali Nair <murali.nair@totara.com>
 * @package mod_perform
 */

namespace mod_perform\task;

use core\collection;
use core\orm\query\builder;
use core\task\adhoc_task;
use mod_perform\entity\activity\notification;
use mod_perform\entity\activity\notification_recipient;
use totara_core\entity\relationship;

/**
 * Adhoc task to create missing perform notification recipients.
 */
class create_missing_perform_notification_recipients_task extends adhoc_task {
    use perform_task_helper_trait;

    /**
     * @inheritDoc
     */
    public function execute() {
        $missing = $this->find_missing_records();

        if ($missing->count() > 0) {
            builder::get_db()->transaction(
                function () use ($missing): void {
                    self::create_missing_records($missing);
                }
            );
        }
    }

    /**
     * Formulates missing notification recipient records.
     *
     * @return collection|notification_recipient the missing record values.
     */
    private function find_missing_records(): collection {
        $this->log('Collecting missing notification recipient records');

        $missing = collection::new([]);
        $all_role_ids = relationship::repository()->get()->pluck('id');

        foreach (notification::repository()->get() as $notification) {
            $missing_role_ids = array_diff(
                $all_role_ids,
                $notification->recipients->pluck('core_relationship_id')
            );

            if ($missing_role_ids) {
                $notification_id = $notification->id;
                $this->log(
                    "Notification '%d' is missing %d recipient records",
                    $notification_id,
                    count($missing_role_ids)
                );

                foreach ($missing_role_ids as $role_id) {
                    $notification_recipient = new notification_recipient();

                    $notification_recipient->active = false;
                    $notification_recipient->notification_id = $notification_id;
                    $notification_recipient->core_relationship_id = $role_id;

                    $missing->append($notification_recipient);
                }
            }
        }

        $count = $missing->count();
        $msg = $count > 0
            ? "Found $count missing notification recipient records"
            : 'No notification recipient records are missing';
        $this->log($msg);

        return $missing;
    }

    /**
     * Indicates which notification recipient records are missing.
     *
     * @return collection|notification_recipient the missing records.
     */
    private function create_missing_records(collection $records): void {
        $count = $records->count();
        $this->log("Creating $count missing notification recipient role records");

        $records->map(
            function (notification_recipient $entity): notification_recipient {
                return $entity->save();
            }
        );

        $this->log("Created $count missing notification recipient role records");
    }
}
