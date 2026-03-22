<?php
/**
 * This file is part of Totara Learn
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
 */

use core\date_format;
use core\testing\generator;
use core\webapi\formatter\field\date_field_formatter;
use core_phpunit\testcase;
use mod_perform\entity\activity\element;
use mod_perform\entity\activity\element_response_snapshot;
use mod_perform\entity\activity\participant_section;
use mod_perform\models\activity\element as element_model;
use mod_perform\models\response\element_validation_error;
use mod_perform\state\participant_section\closed;
use perform_goal\entity\goal as goal_entity;
use perform_goal\model\status\cancelled;
use perform_goal\model\status\completed;
use perform_goal\model\status\in_progress;
use perform_goal\model\status\not_started;
use perform_goal\testing\generator as goal_generator;
use perform_goal\testing\goal_generator_config;
use performelement_perform_goal_creation\perform_goal_creation;
use performelement_perform_goal_creation\testing\perform_goal_creation_test_trait;
use totara_core\advanced_feature;
use totara_core\dates\date_time_setting;
use totara_core\formatter\date_time_setting_formatter;
use totara_job\job_assignment;

/**
 * @group perform
 * @group perform_element
 */
class performelement_perform_goal_creation_perform_goal_creation_test extends testcase {

    use perform_goal_creation_test_trait;

    protected function setUp(): void {
        parent::setUp();

        perform_goal_creation::reset_permissions_cache();
    }

    protected function tearDown(): void {
        perform_goal_creation::reset_permissions_cache();

        parent::tearDown();
    }

    public function test_element_usage(): void {
        $perform_goal_creation = new perform_goal_creation();
        $element_usage = $perform_goal_creation->get_element_usage();

        self::assertFalse($element_usage->get_can_be_child_element());

        advanced_feature::enable('perform_goals');
        self::assertTrue($element_usage->get_can_be_top_level_element());

        advanced_feature::disable('perform_goals');
        self::assertFalse($element_usage->get_can_be_top_level_element());
    }

    public function test_validate_response_throws_exception_for_bad_response_data(): void {
        $perform_goal_creation = new perform_goal_creation();

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Response data must be a json encoded array.');

        $perform_goal_creation->validate_response('not an array', null);
    }

    public function test_validate_response_throws_exception_for_nonexisting_goals(): void {
        $generator = generator::instance();
        $subject_user = $generator->create_user();
        $acting_user = $generator->create_user();
        self::setUser($acting_user);

        $goal_generator = goal_generator::instance();

        $goal1 = $goal_generator->create_goal(goal_generator_config::new(['user_id' => $subject_user->id]));
        $goal2 = $goal_generator->create_goal(goal_generator_config::new(['user_id' => $subject_user->id]));

        $perform_goal_creation = new perform_goal_creation();

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Not all personal goal ids in the response data exist.');

        $perform_goal_creation->validate_response(json_encode([$goal1->id, $goal2->id, - 111]), null);
    }

    /**
     * This was initially testing soft-deleted goals (when hierarchy goals were still used for this).
     * Perform goals have no soft deletion, but they may in the future, so just left this test.
     *
     * @return void
     * @throws coding_exception
     */
    public function test_validate_response_throws_exception_for_deleted_goals(): void {
        $generator = generator::instance();
        $subject_user = $generator->create_user();
        $acting_user = $generator->create_user();
        self::setUser($acting_user);

        $goal_generator = goal_generator::instance();

        $goal1 = $goal_generator->create_goal(goal_generator_config::new(['user_id' => $subject_user->id]));
        $goal2 = $goal_generator->create_goal(goal_generator_config::new(['user_id' => $subject_user->id]));

        $goal1->delete();

        $perform_goal_creation = new perform_goal_creation();

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Not all personal goal ids in the response data exist.');

        $perform_goal_creation->validate_response(json_encode([$goal1->id, $goal2->id]), null);
    }

    public function test_validate_response_successful(): void {
        $generator = generator::instance();
        $subject_user = $generator->create_user();
        $acting_user = $generator->create_user();
        self::setUser($acting_user);

        $goal_generator = goal_generator::instance();

        $goal1 = $goal_generator->create_goal(goal_generator_config::new(['user_id' => $subject_user->id]));
        $goal2 = $goal_generator->create_goal(goal_generator_config::new(['user_id' => $subject_user->id]));

        $perform_goal_creation = new perform_goal_creation();

        self::assertCount(
            0,
            $perform_goal_creation->validate_response(json_encode([$goal1->id, $goal2->id]), null)
        );
    }

    public function test_validate_response_returns_error_when_required_and_empty(): void {
        $perform_goal_creation = new perform_goal_creation();

        $parent_element = new element([
            'context_id' => context_system::instance()->id,
            'plugin_name' => 'perform_goal_creation',
            'title' => 'Test element',
            'data' => '',
            'is_required' => true,
        ]);
        $parent_element->save();
        $element_model = element_model::load_by_entity($parent_element);

        $errors = $perform_goal_creation->validate_response(json_encode([]), $element_model);
        self::assertCount(1, $errors);
        $error = $errors->first();
        self::assertInstanceOf(element_validation_error::class, $error);
        self::assertEquals('PERSONAL_GOAL_CREATION_REQUIRED', $error->error_code);
        self::assertEquals('Answer is required', $error->error_message);
    }

    /**
     * @return array
     */
    public static function decode_response_returns_null_data_provider(): array {
        return [
            [''],
            ['not_an_array'],
            [json_encode('not_an_array')],
            [json_encode([])],
            [json_encode(null)],
            [json_encode(true)],
        ];
    }

    /**
     * @dataProvider decode_response_returns_null_data_provider
     * @param mixed $value
     * @return void
     */
    public function test_decode_response_returns_null_unless_nonempty_json_encoded_array_is_passed($value): void {
        $perform_goal_creation = new perform_goal_creation();
        self::assertEquals(
            null,
            $perform_goal_creation->decode_response($value, null)
        );
    }

    public function test_decode_response(): void {
        [
            $subject_user,
            $goal1,
            $goal2,
            $expected_goal1_data,
            $expected_goal2_data,
        ] = $this->set_up_data();

        // Subject user will be able to view own goal by default.
        self::setUser($subject_user);
        perform_goal_creation::reset_permissions_cache();

        // Check result for a single goal.
        $perform_goal_creation = new perform_goal_creation();
        self::assertEquals(
            [$expected_goal1_data],
            $perform_goal_creation->decode_response(json_encode([$goal1->id]), null)
        );

        // Check result for two goals.
        self::assertEqualsCanonicalizing(
            [$expected_goal1_data, $expected_goal2_data],
            $perform_goal_creation->decode_response(json_encode([$goal1->id, $goal2->id]), null)
        );
    }

    public function test_decode_response_with_one_deleted_goal(): void {
        [
            $subject_user,
            $goal1,
            $goal2,
            $expected_goal1_data,
            $expected_goal2_data,
        ] = $this->set_up_data();

        $goal1_id = $goal1->id;
        $goal1->delete();

        self::setUser($subject_user);
        perform_goal_creation::reset_permissions_cache();

        // Only goal2 is in the response.
        $perform_goal_creation = new perform_goal_creation();
        self::assertEqualsCanonicalizing(
            [$expected_goal2_data],
            $perform_goal_creation->decode_response(json_encode([$goal1_id, $goal2->id]), null)
        );
    }

    public function test_decode_response_does_not_return_snapshot_data_for_deleted_goal_when_section_is_open(): void {
        [
            $subject_user,
            $goal1,
            $goal2,
            $expected_goal1_data,
            $expected_goal2_data,
            $section_element_response,
        ] = $this->set_up_data(true);

        $goal1_id = $goal1->id;
        $goal1->delete();

        self::setUser($subject_user);
        perform_goal_creation::reset_permissions_cache();

        // Only goal2 is in the response because the section is not closed.
        $perform_goal_creation = new perform_goal_creation();
        $perform_goal_creation->set_section_element_response_id($section_element_response->id);
        self::assertEqualsCanonicalizing(
            [$expected_goal2_data],
            $perform_goal_creation->decode_response(json_encode([$goal1_id, $goal2->id]), null)
        );
    }

    public function test_decode_response_returns_snapshot_data_for_deleted_goal_when_section_is_closed(): void {
        [
            $subject_user,
            $goal1,
            $goal2,
            $expected_goal1_data,
            $expected_goal2_data,
            $section_element_response,
            $expected_goal1_snapshot_data,
        ] = $this->set_up_data(true);

        $goal1_id = $goal1->id;
        $goal1->delete();

        self::setUser($subject_user);
        perform_goal_creation::reset_permissions_cache();

        // Set the section to closed.
        /** @var participant_section $participant_section */
        $participant_section = participant_section::repository()
            ->where('section_id', $section_element_response->section_element->section->id)
            ->where('participant_instance_id', $section_element_response->participant_instance->id)
            ->one(true);
        participant_section::repository()
            ->where('id', $participant_section->id)
            ->update(['availability' => closed::get_code()]);

        // Snapshot data should be returned for goal1.
        $perform_goal_creation = new perform_goal_creation();
        $perform_goal_creation->set_section_element_response_id($section_element_response->id);
        self::assertEqualsCanonicalizing(
            [$expected_goal1_snapshot_data, $expected_goal2_data],
            $perform_goal_creation->decode_response(json_encode([$goal1_id, $goal2->id]), null)
        );
    }

    private function set_up_data(bool $create_section_element_response = false): array {
        $generator = generator::instance();
        $subject_user = $generator->create_user();
        $subject_user_context = context_user::instance($subject_user->id);
        $acting_user = $generator->create_user();
        self::setUser($acting_user);

        $now = time();

        $goal_generator = goal_generator::instance();
        $goal1 = $goal_generator->create_goal(goal_generator_config::new([
            'name' => 'Personal goal 1',
            'id_number' => 'goal_1_id_number',
            'user_id' => $subject_user->id,
            'context' => context_user::instance($subject_user->id),
            'description' => '{"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"test description 1"}]}]}',
            'start_date' => $now + DAYSECS,
            'target_type' => 'date',
            'target_date' => $now + (2 * DAYSECS),
            'target_value' => 111,
            'current_value' => 55,
            'status' => not_started::get_code(),
            'closed_at' => null,
        ]));

        // Adjust a few dates manually.
        goal_entity::repository()
            ->update_record([
                'id' => $goal1->id,
                'current_value_updated_at' => $now + (3 * DAYSECS),
                'created_at' => $now - DAYSECS,
                'updated_at' => $now - HOURSECS,
            ]);

        $goal2 = $goal_generator->create_goal(goal_generator_config::new([
            'name' => 'Personal goal 2',
            'id_number' => 'goal_2_id_number',
            'user_id' => $subject_user->id,
            'context' => context_user::instance($subject_user->id),
            'description' => '{"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"test description 2"}]}]}',
            'start_date' => $now + WEEKSECS,
            'target_type' => 'date',
            'target_date' => $now + (2 * WEEKSECS),
            'target_value' => 222,
            'current_value' => 66,
            'status' => in_progress::get_code(),
            'closed_at' => null,
        ]));

        // Adjust a few dates manually.
        goal_entity::repository()
            ->update_record([
                'id' => $goal2->id,
                'current_value_updated_at' => $now + (3 * WEEKSECS),
                'created_at' => $now - WEEKSECS,
                'updated_at' => $now - 60,
            ]);

        $date_field_formatter = new date_field_formatter(date_format::FORMAT_DATELONG, $subject_user_context);
        $start_date_time_formatter = new date_time_setting_formatter(
            new date_time_setting($now + DAYSECS), context_system::instance()
        );
        $target_date_time_formatter = new date_time_setting_formatter(
            new date_time_setting($now + (2 * DAYSECS)), context_system::instance()
        );
        $owner_data = [
            'id' => $acting_user->id,
            'username' => $acting_user->username,
            'firstname' => $acting_user->firstname,
            'lastname' => $acting_user->lastname,
        ];
        $user_data = [
            'id' => $subject_user->id,
            'username' => $subject_user->username,
            'firstname' => $subject_user->firstname,
            'lastname' => $subject_user->lastname,
        ];
        $available_statuses = [
            [
                'id' => not_started::get_code(),
                'label' => not_started::get_label(),
            ],
            [
                'id' => in_progress::get_code(),
                'label' => in_progress::get_label(),
            ],
            [
                'id' => completed::get_code(),
                'label' => completed::get_label(),
            ],
            [
                'id' => cancelled::get_code(),
                'label' => cancelled::get_label(),
            ],
        ];
        $expected_goal1_data = [
            'goal' => [
                'id' => (int) $goal1->id,
                'context_id' => $subject_user_context->id,
                'owner' => $owner_data,
                'user' => $user_data,
                'name' => 'Personal goal 1',
                'id_number' => 'goal_1_id_number',
                'description' => '<p>test description 1</p>',
                'start_date' => $date_field_formatter->format($now + DAYSECS),
                'target_type' => 'date',
                'target_date' => $date_field_formatter->format($now + (2 * DAYSECS)),
                'target_value' => "111",
                'current_value' => "55",
                'current_value_updated_at' => $date_field_formatter->format($now + (3 * DAYSECS)),
                'status' => [
                    'id' => not_started::get_code(),
                    'label' => not_started::get_label(),
                ],
                'closed_at' => null,
                'created_at' => $date_field_formatter->format($now - DAYSECS),
                'updated_at' => $date_field_formatter->format($now - HOURSECS),
                'plugin_name' => 'basic'
            ],
            'raw' => [
                'available_statuses' => $available_statuses,
                'description' => '{"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"test description 1"}]}]}',
                'start_date' => [
                    'iso' => $start_date_time_formatter->format('iso'),
                ],
                'target_date' => [
                    'iso' => $target_date_time_formatter->format('iso'),
                ],
            ],
            'permissions' => [
                'can_view' => true,
                'can_manage' => true,
                'can_update_status' => true,
            ],
        ];

        // Snapshot data should be the same except for permissions.
        $expected_goal1_snapshot_data = $expected_goal1_data;
        $expected_goal1_snapshot_data['permissions'] = [
            'can_view' => false,
            'can_manage' => false,
            'can_update_status' => false,
        ];

        $expected_goal1_data = json_encode($expected_goal1_data, JSON_THROW_ON_ERROR);
        $expected_goal1_snapshot_data = json_encode($expected_goal1_snapshot_data, JSON_THROW_ON_ERROR);

        $start_date_time_formatter = new date_time_setting_formatter(
            new date_time_setting($now + WEEKSECS), context_system::instance()
        );
        $target_date_time_formatter = new date_time_setting_formatter(
            new date_time_setting($now + (2 * WEEKSECS)), context_system::instance()
        );
        $expected_goal2_data = json_encode([
            'goal' => [
                'id' => (int) $goal2->id,
                'context_id' => $subject_user_context->id,
                'owner' => $owner_data,
                'user' => $user_data,
                'name' => 'Personal goal 2',
                'id_number' => 'goal_2_id_number',
                'description' => '<p>test description 2</p>',
                'start_date' => $date_field_formatter->format($now + WEEKSECS),
                'target_type' => 'date',
                'target_date' => $date_field_formatter->format($now + (2 * WEEKSECS)),
                'target_value' => "222",
                'current_value' => "66",
                'current_value_updated_at' => $date_field_formatter->format($now + (3 * WEEKSECS)),
                'status' => [
                    'id' => in_progress::get_code(),
                    'label' => in_progress::get_label(),
                ],
                'closed_at' => null,
                'created_at' => $date_field_formatter->format($now - WEEKSECS),
                'updated_at' => $date_field_formatter->format($now - 60),
                'plugin_name' => 'basic'
            ],
            'raw' => [
                'available_statuses' => $available_statuses,
                'description' => '{"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"test description 2"}]}]}',
                'start_date' => [
                    'iso' => $start_date_time_formatter->format('iso'),
                ],
                'target_date' => [
                    'iso' => $target_date_time_formatter->format('iso'),
                ],
            ],
            'permissions' => [
                'can_view' => true,
                'can_manage' => true,
                'can_update_status' => true,
            ],
        ], JSON_THROW_ON_ERROR);

        $section_element_response = null;
        if ($create_section_element_response) {
            $section_element_response = $this->create_perform_goal_creation_response($subject_user->id);
            $data = json_encode([$goal1->id, $goal2->id], JSON_THROW_ON_ERROR);
            $section_element_response->set_response_data($data);
            // When save() is called, a hook is executed that will result in creating goal snapshots.
            $section_element_response->save();

            // Make sure the snapshots are there.
            self::assertEqualsCanonicalizing(
                [$goal1->id, $goal2->id],
                element_response_snapshot::repository()
                    ->where('response_id', $section_element_response->id)
                    ->get()
                    ->pluck('item_id')
            );
        }

        return [
            $subject_user,
            $goal1,
            $goal2,
            $expected_goal1_data,
            $expected_goal2_data,
            $section_element_response,
            $expected_goal1_snapshot_data
        ];
    }

    public function test_decode_response_throws_exception_for_multiple_users_goal_ids(): void {
        $generator = generator::instance();
        $user1 = $generator->create_user();
        $user2 = $generator->create_user();
        $goal_generator = goal_generator::instance();
        self::setUser($user1);

        $goal_user1 = $goal_generator->create_goal(goal_generator_config::new([
            'user_id' => $user1->id,
            'name' => 'pers_goal1',
        ]));
        $goal_user2 = $goal_generator->create_goal(goal_generator_config::new([
            'user_id' => $user2->id,
            'name' => 'pers_goal2',
        ]));

        $perform_goal_creation = new perform_goal_creation();

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Cannot have personal goals for different users in the response data.');

        $perform_goal_creation->decode_response(json_encode([$goal_user1->id, $goal_user2->id]), null);
    }

    public function test_get_permissions(): void {
        self::setAdminUser();

        $generator = generator::instance();
        $subject_user = $generator->create_user();

        // Admin has permission.
        $perform_goal_creation = new perform_goal_creation();
        perform_goal_creation::reset_permissions_cache();
        self::assertEquals(
            json_encode(['can_manage' => true]),
            $perform_goal_creation->get_permissions($subject_user->id)
        );

        // Subject user has the permission for own goals by default.
        self::setUser($subject_user);
        perform_goal_creation::reset_permissions_cache();
        self::assertEquals(
            json_encode(['can_manage' => true]),
            $perform_goal_creation->get_permissions($subject_user->id)
        );

        // Any other user does not have the permission by default.
        $other_user = $generator->create_user();
        self::setUser($other_user);
        perform_goal_creation::reset_permissions_cache();
        self::assertEquals(
            json_encode(['can_manage' => false]),
            $perform_goal_creation->get_permissions($subject_user->id)
        );

        // Make that other user the manager, and they should have permissions.
        /** @var job_assignment $manager_ja */
        $manager_ja = job_assignment::create_default($other_user->id);
        job_assignment::create_default($subject_user->id, ['managerjaid' => $manager_ja->id]);
        perform_goal_creation::reset_permissions_cache();
        self::assertEquals(
            json_encode(['can_manage' => true]),
            $perform_goal_creation->get_permissions($subject_user->id)
        );
    }

    public function test_get_permissions_cache(): void {
        global $DB;

        $generator = generator::instance();
        $subject_user = $generator->create_user();
        self::setUser($subject_user);

        $perform_goal_creation = new perform_goal_creation();
        perform_goal_creation::reset_permissions_cache();

        // Without cached results, fetching the permissions will generate some DB queries.
        $query_count_before = $DB->perf_get_reads();
        self::assertEquals(
            json_encode(['can_manage' => true]),
            $perform_goal_creation->get_permissions($subject_user->id)
        );
        $queries_without_cache = $DB->perf_get_reads() - $query_count_before;
        self::assertGreaterThan(0, $queries_without_cache);

        // Now results are cached and repeated call should not create any additional DB queries.
        $query_count_before = $DB->perf_get_reads();
        self::assertEquals(
            json_encode(['can_manage' => true]),
            $perform_goal_creation->get_permissions($subject_user->id)
        );
        $queries_with_cache = $DB->perf_get_reads() - $query_count_before;
        self::assertSame(0, $queries_with_cache);

        // Make sure resetting cache works.
        perform_goal_creation::reset_permissions_cache();
        $query_count_before = $DB->perf_get_reads();
        self::assertEquals(
            json_encode(['can_manage' => true]),
            $perform_goal_creation->get_permissions($subject_user->id)
        );

        $queries_without_cache = $DB->perf_get_reads() - $query_count_before;
        self::assertGreaterThan(0, $queries_without_cache);
    }
}