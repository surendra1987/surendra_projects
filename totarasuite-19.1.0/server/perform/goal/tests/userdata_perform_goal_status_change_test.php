<?php
/**
 * This file is part of Totara Perform
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package perform_goal
 */

use core\orm\collection;
use perform_goal\entity\perform_status_change as perform_status_change_entity;
use perform_goal\model\perform_status_change;
use perform_goal\model\status\status_helper;
use perform_goal\userdata\perform_goal_status_change_other;
use perform_goal\userdata\perform_goal_status_change_self;
use mod_perform\constants;
use totara_userdata\userdata\target_user;

require_once __DIR__ . '/perform_goal_perform_status_change_testcase.php';

/**
 * @group totara_hierarchy
 * @group perform_goal
 */
class perform_goal_userdata_perform_goal_status_change_test extends perform_goal_perform_status_change_testcase {

    public function test_purge_self(): void {
        $data = $this->create_activity_data(constants::RELATIONSHIP_SUBJECT);
        $subject_user = new target_user($data->subject_user);
        $manager_user = new target_user($data->manager_user);

        self::assertEquals(0, perform_status_change_entity::repository()->count());

        self::setUser($data->subject_user);
        perform_status_change::create(
            $data->subject_participant_instance1->id,
            $data->section_element->id,
            'in_progress',
            1.11,
            $data->goal1->id,
        );

        $context = context_system::instance();

        $subject_status_rows = $this->get_perform_status_rows($subject_user->id, $subject_user->id);
        $manager_status_rows = $this->get_perform_status_rows($subject_user->id, $manager_user->id);
        self::assertGreaterThan(0, count($subject_status_rows));
        self::assertCount(0, $manager_status_rows);

        self::assertEquals(
            $subject_status_rows->count(),
            perform_goal_status_change_self::execute_count($subject_user, $context)
        );
        self::assertEquals(
            0,
            perform_goal_status_change_self::execute_count($manager_user, $context)
        );

        // Now purge
        // First purge for manager user - should result in no changes
        perform_goal_status_change_self::execute_purge($manager_user, $context);
        /** @var collection $subject_status_rows2 */
        $subject_status_rows2 = $this->get_perform_status_rows($subject_user->id, $subject_user->id);
        /** @var collection $manager_status_rows2 */
        $manager_status_rows2 = $this->get_perform_status_rows($subject_user->id, $manager_user->id);
        self::assertSameSize($subject_status_rows, $subject_status_rows2);
        self::assertCount(0, $manager_status_rows2);

        // Purge for subject user
        perform_goal_status_change_self::execute_purge($subject_user, $context);
        $subject_status_rows2 = $this->get_perform_status_rows($subject_user->id, $subject_user->id);
        $manager_status_rows2 = $this->get_perform_status_rows($subject_user->id, $manager_user->id);
        self::assertCount(0, $subject_status_rows2);
        self::assertCount(0, $manager_status_rows2);
    }

    public function test_purge_other(): void {
        $data = $this->create_activity_data(constants::RELATIONSHIP_MANAGER);
        $subject_user = new target_user($data->subject_user);
        $manager_user = new target_user($data->manager_user);

        self::assertEquals(0, perform_status_change_entity::repository()->count());

        self::setUser($data->manager_user);
        perform_status_change::create(
            $data->manager_participant_instance1->id,
            $data->section_element->id,
            'in_progress',
            1.11,
            $data->goal1->id,
        );

        $context = context_system::instance();

        $subject_status_rows = $this->get_perform_status_rows($subject_user->id, $subject_user->id);
        $manager_status_rows = $this->get_perform_status_rows($subject_user->id, $manager_user->id);
        self::assertCount(0, $subject_status_rows);
        self::assertNotCount(0, $manager_status_rows);

        self::assertEquals(
            0,
            perform_goal_status_change_other::execute_count($subject_user, $context)
        );
        self::assertEquals(
            count($manager_status_rows),
            perform_goal_status_change_other::execute_count($manager_user, $context)
        );

        // Now purge
        // First purge for subject user - should result in no changes
        perform_goal_status_change_other::execute_purge($subject_user, $context);
        $subject_status_rows2 = $this->get_perform_status_rows($subject_user->id, $subject_user->id);
        $manager_status_rows2 = $this->get_perform_status_rows($subject_user->id, $manager_user->id);
        self::assertCount(0, $subject_status_rows2);
        self::assertEqualsCanonicalizing($manager_status_rows, $manager_status_rows2);

        // Purge for manager user
        // Rows are not deleted, just anonymize
        perform_goal_status_change_other::execute_purge($manager_user, $context);
        $subject_status_rows2 = $this->get_perform_status_rows($subject_user->id, $subject_user->id);
        $manager_status_rows2 = $this->get_perform_status_rows($subject_user->id, $manager_user->id);
        $anon_status_rows = $this->get_perform_status_rows($subject_user->id, null);
        self::assertCount(0, $subject_status_rows2);
        self::assertCount(0, $manager_status_rows2);
        self::assertCount(count($manager_status_rows), $anon_status_rows);

        $expected = $manager_status_rows->map(function ($row) {
            $row->status_changer_user_id = null;
            return $row;
        });
        self::assertEqualsCanonicalizing($expected->to_array(), $anon_status_rows->to_array());
    }

    public function test_export_self(): void {
        $data = $this->create_activity_data(constants::RELATIONSHIP_SUBJECT);
        $subject_user = new target_user($data->subject_user);

        self::assertEquals(0, perform_status_change_entity::repository()->count());

        self::setUser($data->subject_user);
        perform_status_change::create(
            $data->subject_participant_instance1->id,
            $data->section_element->id,
            'in_progress',
            1.11,
            $data->goal1->id,
        );

        $context = context_system::instance();

        $exported = perform_goal_status_change_self::execute_export($subject_user, $context)
            ->data['perform_goal_status_change_self'];

        $this->assert_export_equals($exported, $subject_user->id, $subject_user->id);
    }

    public function test_export_other(): void {
        $data = $this->create_activity_data(constants::RELATIONSHIP_MANAGER);
        $subject_user = new target_user($data->subject_user);
        $manager_user = new target_user($data->manager_user);

        self::assertEquals(0, perform_status_change_entity::repository()->count());

        self::setUser($data->manager_user);
        perform_status_change::create(
            $data->manager_participant_instance1->id,
            $data->section_element->id,
            'in_progress',
            1.11,
            $data->goal1->id,
        );

        $context = context_system::instance();

        // Even though a system context is passed in here, it will not be used as a filter by a perform_goal_status_change
        // export method later.
        $exported = perform_goal_status_change_other::execute_export($manager_user, $context)
            ->data['perform_goal_status_change_other'];

        $this->assert_export_equals($exported, $subject_user->id, $manager_user->id);
    }

    private function assert_export_equals(
        array $exported,
        int $user_id,
        int $status_changer_user_id
    ): void {
        $perform_status_rows = $this->get_perform_status_rows($user_id, $status_changer_user_id);

        self::assertSameSize($perform_status_rows, $exported);
        $perform_status_rows = $perform_status_rows->map(function ($row) {
            $perform_status_model = perform_status_change::load_by_entity($row);
            $goal_name = core_text::entities_to_utf8(format_string($row->goal->name));

            return (object)[
                'id' => (int)$row->id,
                'user_id' => (int)$row->subject_user_id,
                'activity_name' => core_text::entities_to_utf8(format_string($perform_status_model->activity->name)),
                'goal_name' => $goal_name,
                'status' => status_helper::status_from_code($row->status)->label,
                'current_value' => $row->current_value,
                'status_changer_id' => (int)$row->status_changer_user_id,
                'status_changer_relationship' => $perform_status_model->status_changer_role,
                'created_at' => (int)$row->created_at,
            ];
        });

        self::assertEqualsCanonicalizing($perform_status_rows->to_array(), $exported);
    }

    /**
     * @param int $user_id
     * @param int|null $status_changer_user_id
     * @return collection
     */
    private function get_perform_status_rows(int $user_id, ?int $status_changer_user_id): collection {
        return perform_status_change_entity::repository()
            ->where('subject_user_id', $user_id)
            ->where('status_changer_user_id', $status_changer_user_id)
            ->get();
    }
}