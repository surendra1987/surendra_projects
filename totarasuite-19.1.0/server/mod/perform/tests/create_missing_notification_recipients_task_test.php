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
 * @author Murali Nair <murali.nair@totaralearning.com>
 * @package mod_perform
 * @category test
 */

use core\collection;
use core\orm\entity\repository;
use core_phpunit\testcase;
use mod_perform\entity\activity\notification;
use mod_perform\entity\activity\notification_recipient;
use mod_perform\models\activity\activity;
use mod_perform\task\create_missing_perform_notification_recipients_task;
use mod_perform\testing\generator;
use totara_core\entity\relationship;

/**
 * @group perform
 */
class mod_perform_create_missing_notification_recipients_task_test extends testcase {
    public function test_execute_no_missing(): void {
        $this->execute_and_assert(
            $this->create_test_data(), collection::new([])
        );
    }

    public function test_execute_all_missing(): void {
        $this->execute_and_assert(
            $this->create_test_data(), relationship::repository()->get()
        );
    }

    public function test_execute_some_missing(): void {
        $this->execute_and_assert(
            $this->create_test_data(),
            relationship::repository()
                ->get()
                ->filter(
                    function (relationship $relationship): bool {
                        return !in_array(
                            $relationship->idnumber,
                            ['manager', 'managers_manager', 'appraiser']
                        );
                    }
                )
        );
    }

    /**
     * Runs the create_missing_perform_notification_recipients_task and checks the results.
     *
     * @param collection|activity $activities generated activities.
     * @param collection|relationship $removed_roles recipient roles that need
     *        to be deleted before the task is run.
     * @throws coding_exception
     */
    private function execute_and_assert(
        collection $activities,
        collection $removed_roles
    ): void {
        $notification_ids = notification::repository()
            ->where_in('activity_id', $activities->pluck('id'))
            ->get()
            ->pluck('id');

        $removed_role_ids = $removed_roles->pluck('id');
        $missing_role_count = count($removed_role_ids);

        if ($missing_role_count > 0) {
            $this->get_recipients_repository($notification_ids, $removed_role_ids)
                ->delete();

            self::assertEquals(
                0,
                $this->get_recipients_repository($notification_ids, $removed_role_ids)
                    ->count(),
                "records for 'missing' recipient roles exist"
            );
        }

        $logs = collection::new([]);
        (new create_missing_perform_notification_recipients_task())
            ->set_logger(
                function (string $message) use ($logs): void {
                    $logs->append($message);
                }
            )
            ->execute();

        $this->assert_logs($logs, $notification_ids, $missing_role_count);

        $expected_role_ids = relationship::repository()->get()->pluck('id');
        self::assertEquals(
            count($notification_ids) * count($expected_role_ids),
            $this->get_recipients_repository($notification_ids, $expected_role_ids)
                ->count(),
            "records recipient roles missing"
        );

        // Check idempotence - repeated execution shouldn't change DB records.
        $logs_before_second_execution = clone $logs;
        $notification_recipient_records_before_second_execution = notification_recipient::repository()
            ->order_by('id')
            ->get()
            ->to_array();

        (new create_missing_perform_notification_recipients_task())
            ->set_logger(
                function (string $message) use ($logs): void {
                    $logs->append($message);
                }
            )
            ->execute();

        $notification_recipient_records_after_second_execution = notification_recipient::repository()
            ->order_by('id')
            ->get()
            ->to_array();

        // We expect two additional log entries.
        $this->assertEquals(
            $logs_before_second_execution
                ->append('Collecting missing notification recipient records')
                ->append('No notification recipient records are missing'),
            $logs
        );

        // We don't expect any change in DB.
        $this->assertEquals(
            $notification_recipient_records_before_second_execution,
            $notification_recipient_records_after_second_execution
        );
    }

    /**
     * Creates test data.
     *
     * @param int $no_of_activities no of activities to create.
     *
     * @return collection|activity test activities.
     */
    private function create_test_data(int $no_of_activities = 1): collection {
        $this->setAdminUser();

        $generator = generator::instance();
        return collection::new(range(0, $no_of_activities - 1))->transform(
            function (int $i) use ($generator): activity {
                return $generator->create_activity_in_container(
                    ['activity_name' => "CreateMissingNotificationsActivity #$i"]
                );
            }
        );
    }

    /**
     * Checks if the generated log messages are correct.
     *
     * @param collection|string $logs generated logs.
     * @param int[] $notification_ids existing activity notification ids.
     * @param int $missing_count no of missing roles.
     */
    private function assert_logs(
        collection $logs, array $notification_ids, int $missing_count
    ): void {
        $expected_logs = collection::new(
            ['Collecting missing notification recipient records']
        );

        if ($missing_count === 0) {
            $expected_logs->append('No notification recipient records are missing');
        } else {
            $expected_logs = array_reduce(
                $notification_ids,
                function (collection $result, int $id) use ($missing_count): collection {
                    return $result->append(
                        "Notification '$id' is missing $missing_count recipient records"
                    );
                },
                $expected_logs
            );

            $total = count($notification_ids) * $missing_count;
            $expected_logs
                ->append("Found $total missing notification recipient records")
                ->append("Creating $total missing notification recipient role records")
                ->append("Created $total missing notification recipient role records");
        }

        self::assertEqualsCanonicalizing(
            $expected_logs->all(), $logs->all(), 'wrong log entries'
        );
    }

    /**
     * Returns notification recipient repository primed for searching for the
     * given notification and roles.
     *
     * @param int[] $notification_ids target notification ids.
     * @param int[] $role_ids target roles.
     *
     * @return repository the repository
     */
    private function get_recipients_repository(
        array $notification_ids,
        array $role_ids
    ): repository {
        return notification_recipient::repository()
            ->where_in('notification_id', $notification_ids)
            ->where_in('core_relationship_id', $role_ids);
    }
}