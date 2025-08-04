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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author  Matthias Bonk <matthias.bonk@totaralearning.com>
 * @package perform_goal
 */

use core\entity\user as user_entity;
use core\orm\collection;
use core\orm\query\builder;
use core_phpunit\testcase;
use goaltype_basic\model\basic_goal;
use perform_goal\entity\goal as goal_entity;
use perform_goal\entity\goal_activity as goal_activity_entity;
use perform_goal\entity\goal_category as goal_category_entity;
use perform_goal\event\personal_goal_created;
use perform_goal\model\goal as goal_model;
use perform_goal\model\goal_activity;
use perform_goal\model\goal_category;
use perform_goal\model\status\in_progress;
use perform_goal\model\status\not_started;
use perform_goal\model\target_type\date as target_type_date;
use perform_goal\settings_helper;
use perform_goal\testing\generator as goal_generator;
use perform_goal\testing\goal_generator_config;
use totara_comment\comment as totara_comment;
use totara_comment\entity\comment as comment_entity;
use totara_reaction\entity\reaction as reaction_entity;
use totara_reaction\reaction;

/**
 * @group perform_goal
 */
class perform_goal_goal_model_test extends testcase {

    private $goal_category;

    public function setUp(): void {
        $this->goal_category = goal_category::create('basic', 'Basic');
        $this->goal_category->activate();
    }

    protected function tearDown(): void {
        $this->goal_category = null;
        parent::tearDown();
    }

    public function test_create_name_must_not_be_empty(): void {
        self::setAdminUser();

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Goal must have a name.');
        $now = time();
        goal_model::create(context_system::instance(),
            $this->goal_category,
            '',
            $now,
            target_type_date::get_type(),
            $now + (DAYSECS * 30),
            100.0,
            0.0,
            'in_progress'
        );
    }

    public function test_create_name_must_not_be_too_long(): void {
        self::setAdminUser();

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Goal name must not be longer than 1024 characters.');
        $too_long_name = str_repeat('X', 1025);
        $now = time();
        goal_model::create(context_system::instance(),
            $this->goal_category,
            $too_long_name,
            $now,
            target_type_date::get_type(),
            $now + (DAYSECS * 30),
            100.0,
            0.0,
            'in_progress'
        );
    }

    public function test_create_invalid_user(): void {
        self::setAdminUser();

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Invalid user_id: -99');
        $now = time();
        $goal = goal_model::create(context_system::instance(),
            $this->goal_category,
            'Test goal 1',
            $now,
            target_type_date::get_type(),
            $now + (DAYSECS * 30),
            100.0,
            0.0,
            'in_progress',
            null,
            -99
        );
    }

    public function test_create_invalid_owner(): void {
        self::setAdminUser();

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Invalid owner_id: -99');
        $now = time();
        $goal = goal_model::create(context_system::instance(),
            $this->goal_category,
            'Test goal 1',
            $now,
            target_type_date::get_type(),
            $now + (DAYSECS * 30),
            100.0,
            0.0,
            'in_progress',
            -99
        );
    }

    public function test_create_invalid_id_number(): void {
        self::setAdminUser();
        $now = time();
        $params = [
            'context' => context_system::instance(),
            'category' => $this->goal_category,
            'name' => 'Test goal',
            'start_date' => $now,
            'target_type' => target_type_date::get_type(),
            'target_date' => $now + (DAYSECS * 30),
            'target_value' => 100.0,
            'current_value' => 0.0,
            'status' => 'in_progress',
            'id_number' => 'test-id-number'
        ];

        goal_model::create($params['context'],
            $params['category'],
            $params['name'],
            $params['start_date'],
            $params['target_type'],
            $params['target_date'],
            $params['target_value'],
            $params['current_value'],
            $params['status'],
            null,
            null,
            $params['id_number']
        );

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('id_number already exists: test-id-number');
        goal_model::create($params['context'],
            $params['category'],
            $params['name'],
            $params['start_date'],
            $params['target_type'],
            $params['target_date'],
            $params['target_value'],
            $params['current_value'],
            $params['status'],
            null,
            null,
            $params['id_number']
        );
    }

    public function test_create_invalid_status(): void {
        self::setAdminUser();

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Invalid status: bad_status');

        $now = time();
        goal_model::create(context_system::instance(),
            $this->goal_category,
            'Test goal',
            $now,
            target_type_date::get_type(),
            $now + (DAYSECS * 30),
            100.0,
            0.0,
            'bad_status'
        );
    }

    public static function td_invalid_current_value(): array {
        $min = -1.0;
        $max = 999999999999999;
        $inv_current = "Invalid current value: ";

        return [
            'current < 0' => [$inv_current . $min, 100, $min],
            'current > max' => [$inv_current . '1.0E+15', 100, $max]
        ];
    }

    public static function td_invalid_target_value(): array {
        $min = -1.0;
        $max = 999999999999999;
        $inv_target = "Invalid target value: ";

        return [
            'target < 0' => [$inv_target . $min, $min, 0.0],
            'target > max' => [$inv_target . '1.0E+15', $max, 0.0]
        ];
    }

    /**
     * @dataProvider td_invalid_current_value
     * @dataProvider td_invalid_target_value
     */
    public function test_create_invalid_current_or_target_value(
        string $error,
        float $target_value,
        float $current_value
    ): void {
        $owner = self::getDataGenerator()->create_user();
        self::setUser($owner);

        $now = time();

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage($error);
        goal_model::create(
            context_system::instance(),
            $this->goal_category,
            'Test goal 1',
            $now,
            target_type_date::get_type(),
            $now + (DAYSECS * 30),
            $target_value,
            $current_value,
            'in_progress'
        );
    }

    public function test_update_progress_negative_current_value(): void {
        $owner = self::getDataGenerator()->create_user();
        self::setUser($owner);

        $now = time();
        $goal = goal_model::create(context_system::instance(),
            $this->goal_category,
            'Test goal 1',
            $now,
            target_type_date::get_type(),
            $now + (DAYSECS * 30),
            100.0,
            0.0,
            'in_progress'
        );

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Invalid current value: -12.5');
        $args['input'] = [
            'current_value' => -12.5,
            'status' => 'in_progress'
        ];
        $goal->update_progress(
            $args['input']['status'],
            $args['input']['current_value']
        );
    }

    public function test_create_successful_minimum_input(): void {
        $owner = self::getDataGenerator()->create_user();
        self::setUser($owner);

        $sink = $this->redirectEvents();
        $sink->clear();

        $now = time();

        $params = [
            'context' => context_system::instance(),
            'category' => $this->goal_category,
            'name' => 'Test goal',
            'start_date' => $now,
            'target_type' => target_type_date::get_type(),
            'target_date' => $now + (DAYSECS * 30),
            'target_value' => 100.0,
            'current_value' => 0.0,
            'status' => 'not_started'
        ];

        $goal = goal_model::create($params['context'],
            $params['category'],
            $params['name'],
            $params['start_date'],
            $params['target_type'],
            $params['target_date'],
            $params['target_value'],
            $params['current_value'],
            $params['status']
        );

        $this->assertInstanceOf(goal_model::class, $goal);
        $this->assertInstanceOf(basic_goal::class, $goal);

        $this->assertEquals(context_system::instance()->id, $goal->context_id);
        $this->assertEquals($this->goal_category->id, $goal->category_id);
        $this->assertEquals($params['name'], $goal->name);
        $this->assertEquals($params['start_date'], $goal->start_date);
        $this->assertEquals($params['target_date'], $goal->target_date);
        $this->assertEquals($params['target_value'], $goal->target_value);
        $this->assertEquals($params['current_value'], $goal->current_value);

        $this->assertEquals($owner->id, $goal->owner_id);
        $this->assertNull($goal->user_id);
        $this->assertNull($goal->id_number);
        $this->assertNull($goal->description);
        $this->assertNotNull($goal->current_value_updated_at);

        $this->assertInstanceOf(not_started::class, $goal->status);
        $this->assertGreaterThanOrEqual($now, $goal->status_updated_at);
        $this->assertNull($goal->closed_at);
        $this->assertGreaterThanOrEqual($now, $goal->created_at);
        $this->assertGreaterThanOrEqual($now, $goal->updated_at);
        $this->assertNull($goal->user);
        $this->assertEquals(new user_entity($owner->id), $goal->owner);
        $this->assertEquals($this->goal_category, $goal->category);
        $this->assertEquals('Self', $goal->assignment_type);
        $this->assertEquals('basic', $goal->plugin_name);

        $events = $sink->get_events();
        $sink->close();

        // This goal has no user_id => not a personal goal => no events fired.
        $this->assertCount(0, $events);
    }

    public function test_create_successful_maximum_input(): void {
        $logged_in_user = self::getDataGenerator()->create_user();
        $owner = self::getDataGenerator()->create_user();
        $goal_subject_user = self::getDataGenerator()->create_user();
        self::setUser($logged_in_user);

        $user_context = context_user::instance($goal_subject_user->id);

        $sink = $this->redirectEvents();
        $sink->clear();

        $now = time();
        $params = [
            'context' => $user_context,
            'category' => $this->goal_category,
            'name' => 'Test goal 2',
            'start_date' => $now + DAYSECS,
            'target_type' => target_type_date::get_type(),
            'target_date' =>  $now + WEEKSECS,
            'target_value' => 12.25,
            'current_value' => 11.01,
            'status' => 'in_progress',
            'owner_id' => $owner->id,
            'user_id' => $goal_subject_user->id,
            'id_number' => 'goal-id-999',
            'description' => '{"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"test text"}]}]}'
        ];

        $goal = goal_model::create($params['context'],
            $params['category'],
            $params['name'],
            $params['start_date'],
            $params['target_type'],
            $params['target_date'],
            $params['target_value'],
            $params['current_value'],
            $params['status'],
            $params['owner_id'],
            $params['user_id'],
            $params['id_number'],
            $params['description']
        );

        $this->assertInstanceOf(goal_model::class, $goal);
        $this->assertInstanceOf(basic_goal::class, $goal);

        $this->assertEquals($user_context->id, $goal->context_id);
        $this->assertEquals($owner->id, $goal->owner_id);
        $this->assertEquals($goal_subject_user->id, $goal->user_id);
        $this->assertEquals('Test goal 2', $goal->name);
        $this->assertEquals('goal-id-999', $goal->id_number);
        $this->assertEquals('{"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"test text"}]}]}', $goal->description);
        $this->assertEquals($now + DAYSECS, $goal->start_date);
        $this->assertEquals($now + WEEKSECS, $goal->target_date);
        $this->assertEquals(12.25, $goal->target_value);
        $this->assertEquals(11.01, $goal->current_value);
        $this->assertGreaterThanOrEqual($now, $goal->current_value_updated_at);
        $this->assertInstanceOf(in_progress::class, $goal->status);
        $this->assertGreaterThanOrEqual($now, $goal->status_updated_at);
        $this->assertNull($goal->closed_at);
        $this->assertGreaterThanOrEqual($now, $goal->created_at);
        $this->assertGreaterThanOrEqual($now, $goal->updated_at);
        $this->assertEquals(new user_entity($goal_subject_user->id), $goal->user);
        $this->assertEquals(new user_entity($owner->id), $goal->owner);
        $this->assertEquals('Self', $goal->assignment_type);
        $this->assertEquals('basic', $goal->plugin_name);

        $events = $sink->get_events();
        $sink->close();

        // This goal has a user id => personal goal => create event fired
        $this->assertCount(1, $events);
        $event = array_shift($events);
        $this->assertInstanceOf(personal_goal_created::class, $event);
        $this->assertEquals($goal->id, $event->objectid);
        $this->assertEquals($logged_in_user->id, $event->userid);
        $this->assertEquals($goal->context_id, $event->contextid);
        $this->assertEquals($goal->user_id, $event->relateduserid);
    }

    public function test_create_with_basic_system_category() {
        $logged_in_user = self::getDataGenerator()->create_user();
        $owner = self::getDataGenerator()->create_user();
        $goal_subject_user = self::getDataGenerator()->create_user();
        self::setUser($logged_in_user);

        $user_context = context_user::instance($goal_subject_user->id);

        $basic_category_entity = goal_category_entity::repository()
            ->where('id_number', '=', 'system-basic')
            ->one(true);
        $basic_category = goal_category::load_by_entity($basic_category_entity);

        $now = time();
        $goal = goal_model::create($user_context,
            $basic_category,
            'Test goal',
            $now,
            target_type_date::get_type(),
            $now + (DAYSECS * 30),
            100.0,
            0.0,
            'in_progress',
            $owner->id,
            $goal_subject_user->id
        );

        $this->assertInstanceOf(goal_model::class, $goal);
        $this->assertInstanceOf(basic_goal::class, $goal);
        $this->assertEquals($basic_category->id, $goal->category_id);
    }

    public function test_get_empty_activities() {
        $owner = self::getDataGenerator()->create_user();
        self::setUser($owner);

        $now = time();
        $goal1 = goal_model::create(context_system::instance(),
            $this->goal_category,
            'Test goal 1',
            $now,
            target_type_date::get_type(),
            $now + (DAYSECS * 30),
            100.0,
            0.0,
            'in_progress',
        );

        // Make sure it works for empty result.
        $this->assertEquals(new collection([]), $goal1->activities);
    }

    public function test_get_activities() {
        $owner = self::getDataGenerator()->create_user();
        self::setUser($owner);

        $now = time();
        $goal1 = goal_model::create(context_system::instance(),
            $this->goal_category,
            'Test goal 1',
            $now,
            target_type_date::get_type(),
            $now + (DAYSECS * 30),
            100.0,
            0.0,
            'in_progress'
        );
        $goal2 = goal_model::create(context_system::instance(),
            $this->goal_category,
            'Test goal 2',
            $now,
            target_type_date::get_type(),
            $now + (DAYSECS * 30),
            100.0,
            0.0,
            'in_progress'
        );

        $goal1_activity1 = goal_activity::create(
            $goal1->id,
            null,
            'type1',
            'info1'
        );
        $goal1_activity2 = goal_activity::create(
            $goal1->id,
            null,
            'type2',
            'info2'
        );
        $goal2_activity = goal_activity::create(
            $goal2->id,
            null,
            'type3',
            'info3'
        );

        $this->assertEquals(new collection([
            $goal1_activity1->id => $goal1_activity1,
            $goal1_activity2->id => $goal1_activity2
        ]), $goal1->activities);

        $this->assertEquals(new collection([
            $goal2_activity->id => $goal2_activity
        ]), $goal2->activities);
    }

    public function test_refresh(): void {
        $owner = self::getDataGenerator()->create_user();
        self::setUser($owner);

        $now = time();
        $goal = goal_model::create(context_system::instance(),
            $this->goal_category,
            'Test goal 1',
            $now,
            target_type_date::get_type(),
            $now + (DAYSECS * 30),
            100.0,
            0.0,
            'in_progress'
        );

        $goal_activity = goal_activity::create(
            $goal->id,
            null,
            'type1',
            'info1'
        );

        $this->assertNull($goal->user_id);
        $this->assertNull($goal->activities->first()->user_id);

        builder::table(goal_entity::TABLE)->update(['user_id' => $owner->id]);
        builder::table(goal_activity_entity::TABLE)->update(['user_id' => $owner->id]);

        $goal->refresh();
        $this->assertEquals($owner->id, $goal->user_id);
        $this->assertNull($goal->activities->first()->user_id);

        // Refresh with reload=true should also refresh the relations.
        $goal->refresh(true);
        $this->assertEquals($owner->id, $goal->activities->first()->user_id);
    }

    public function test_get_context(): void {
        $tenant_generator = self::getDataGenerator()->get_plugin_generator('totara_tenant');
        $tenant_generator->enable_tenants();
        $tenant1 = $tenant_generator->create_tenant();
        $owner = self::getDataGenerator()->create_user();
        $subject_user_system = self::getDataGenerator()->create_user();
        $subject_user_system_context = context_user::instance($subject_user_system->id);
        $subject_user_tenant = self::getDataGenerator()->create_user(['tenantid' => $tenant1->id]);
        $subject_user_tenant_context = context_user::instance($subject_user_tenant->id);
        self::setUser($owner);

        $now = time();
        $goal_system = goal_model::create($subject_user_system_context,
            $this->goal_category,
            'Test goal 1',
            $now,
            target_type_date::get_type(),
            $now + (DAYSECS * 30),
            100.0,
            0.0,
            'in_progress',
            $owner->id,
            $subject_user_system->id
        );
        $goal_tenant = goal_model::create($subject_user_tenant_context,
            $this->goal_category,
            'Test goal 2',
            $now,
            target_type_date::get_type(),
            $now + (DAYSECS * 30),
            100.0,
            0.0,
            'in_progress',
            $owner->id,
            $subject_user_tenant->id
        );

        // Operate.
        $context_result_system = $goal_system->get_context();
        $context_result_tenant = $goal_tenant->get_context();
        // Assert.
        $this->assertEquals(CONTEXT_USER, $context_result_system->contextlevel);
        $this->assertEquals(CONTEXT_USER, $context_result_tenant->contextlevel); // this might change to CONTEXT_TENANT in future.
    }

    public function test_get_status_choices() {
        $choices = goal_model::get_status_choices();
        $expected = [
            'not_started' => 'Not started',
            'in_progress' => 'In progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled'
        ];
        $this->assertEqualsCanonicalizing($expected, $choices);
    }

    public function test_update_progress() {
        $owner = self::getDataGenerator()->create_user();
        self::setUser($owner);

        $sink = $this->redirectEvents();
        $sink->clear();

        $now = time();
        $goal = goal_model::create(context_system::instance(),
            $this->goal_category,
            'Test goal',
            $now,
            target_type_date::get_type(),
            $now + (DAYSECS * 30),
            100.0,
            0.0,
            'not_started'
        );

        $this->assertInstanceOf(goal_model::class, $goal);
        $this->assertInstanceOf(not_started::class, $goal->status);
        $this->assertEquals(0.0, $goal->current_value);
        $this->assertNotNull($goal->current_value_updated_at);

        $now = time();
        $goal_updated = $goal->update_progress('in_progress', 25.5);

        $events = $sink->get_events();
        $sink->close();

        // This goal has a user id => personal goal => create event fired
        $this->assertCount(1, $events);
        $event = array_shift($events);

        $this->assertInstanceOf(in_progress::class, $goal_updated->status);
        $this->assertEquals(25.5, $goal_updated->current_value);
        $this->assertGreaterThanOrEqual($now, $goal_updated->current_value_updated_at);
        $this->assertGreaterThanOrEqual($now, $goal_updated->status_updated_at);

        $this->assertEquals($goal_updated->id, $event->objectid);
        $this->assertEquals($goal_updated->owner_id, $event->userid);
        $this->assertEquals($goal_updated->context_id, $event->contextid);
        $this->assertEquals($goal_updated->status::get_code(), $event->other['status']);
    }

    public function test_update_progress_same_current_value() {
        $owner = self::getDataGenerator()->create_user();
        self::setUser($owner);

        $now = time();
        $goal = goal_model::create(
            context_system::instance(),
            $this->goal_category,
            'Test goal 1',
            $now,
            target_type_date::get_type(),
            $now + (DAYSECS * 30),
            100.0,
            0.0,
            'not_started'
        );

        // Trick the entity a little...
        $now = time();
        $fake_updated_time = time() - 20;
        $goal_entity = new goal_entity($goal->id);
        $goal_entity->current_value = 42;
        $goal_entity->current_value_updated_at = $fake_updated_time;
        $goal_entity->save();
        $goal->refresh();
        $this->assertEquals($fake_updated_time, $goal->current_value_updated_at);

        // Update with same current_value.
        $goal_updated = $goal->update_progress('in_progress', 42);
        // Assert current value updated at hasn't changed.
        $this->assertEquals($fake_updated_time, $goal->current_value_updated_at);

        // Update with different current_value.
        $goal_updated_again = $goal_updated->update_progress('in_progress', 64);
        $this->assertGreaterThanOrEqual($now, $goal->current_value_updated_at);
    }

    public function test_update_progress_same_status() {
        $owner = self::getDataGenerator()->create_user();
        self::setUser($owner);

        $now = time();
        $goal = goal_model::create(
            context_system::instance(),
            $this->goal_category,
            'Test goal 1',
            $now,
            target_type_date::get_type(),
            $now + (DAYSECS * 30),
            100.0,
            0.0,
            'not_started'
        );

        // Trick the entity a little...
        $now = time();
        $fake_updated_time = time() - 20;
        $goal_entity = new goal_entity($goal->id);
        $goal_entity->status = 'in_progress';
        $goal_entity->status_updated_at = $fake_updated_time;
        $goal_entity->save();
        $goal->refresh();
        $this->assertEquals($fake_updated_time, $goal->status_updated_at);

        // Update with same status.
        $goal_updated = $goal->update_progress('in_progress', 64);
        // Assert status updated at hasn't changed.
        $this->assertEquals($fake_updated_time, $goal->status_updated_at);

        // Update with different status.
        $goal_updated_again = $goal_updated->update_progress('not_started', 64);
        $this->assertGreaterThanOrEqual($now, $goal->status_updated_at);
    }

    public static function statuses_data_provider(): array {
        return [
            ['not_started', false],
            ['in_progress', false],
            ['completed', true],
            ['cancelled', true],
        ];
    }

    /**
     * @dataProvider statuses_data_provider
     */
    public function test_update_progress_sets_closed_at_property_for_closed_statuses(string $status_code, bool $expected_closed) {
        $owner = self::getDataGenerator()->create_user();
        self::setUser($owner);

        $now = time();
        $goal = goal_generator::instance()->create_goal();

        $goal_entity = new goal_entity($goal->id);
        $this->assertEquals(null, $goal_entity->closed_at);

        $goal_updated = $goal->update_progress($status_code, 11.1);

        if ($expected_closed) {
            $this->assertGreaterThanOrEqual($now, $goal_updated->closed_at);
        } else {
            $this->assertEquals(null, $goal_updated->closed_at);
        }
    }

    public function test_update_progress_does_not_set_closed_at_property_again() {
        $owner = self::getDataGenerator()->create_user();
        self::setUser($owner);

        $goal = goal_generator::instance()->create_goal();

        $goal_entity = new goal_entity($goal->id);
        $goal_entity->closed_at = 1;
        $goal_entity->save();
        $goal->refresh();

        $goal_updated = $goal->update_progress('cancelled', 11.1);
        $this->assertEquals(1, $goal_updated->closed_at);
    }

    public function test_delete() {
        $fs = get_file_storage();
        $owner = self::getDataGenerator()->create_user();
        self::setUser($owner);

        $now = time();
        $goal1 = goal_model::create(context_system::instance(),
            $this->goal_category,
            'Test goal 1',
            $now,
            target_type_date::get_type(),
            $now + (DAYSECS * 30),
            100.0,
            0.0,
            'in_progress'
        );

        $goal_activity1 = goal_activity::create(
            $goal1->id,
            null,
            'type1',
            'info1'
        );
        $goal2 = goal_model::create(context_system::instance(),
            $this->goal_category,
            'Test goal 2',
            $now,
            target_type_date::get_type(),
            $now + (DAYSECS * 30),
            100.0,
            0.0,
            'in_progress'
        );
        $goal_activity2 = goal_activity::create(
            $goal2->id,
            null,
            'type2',
            'info2'
        );

        $file1 = [
            'component' => settings_helper::get_component(),
            'filearea' => settings_helper::get_filearea(),
            'filepath' => '/',
            'filename' => 'test1.txt'
        ];
        $owner_file1 = $fs->create_file_from_string(
            array_merge(
                $file1,
                [
                    'contextid' => context_user::instance($owner->id)->id,
                    'itemid' => $goal1->id,
                ]
            ),
            $goal1->description
        );

        // assign to another goal with same user
        $file2 = [
            'component' => settings_helper::get_component(),
            'filearea' => settings_helper::get_filearea(),
            'filepath' => '/',
            'filename' => 'test2.txt'
        ];
        $owner_file2 = $fs->create_file_from_string(
            array_merge(
                $file2,
                [
                    'contextid' => context_user::instance($owner->id)->id,
                    'itemid' => $goal2->id,
                ]
            ),
            $goal2->description
        );
        // Test before delete
        $this->assertEquals(2, (goal_entity::repository())->count());
        $this->assertEquals(2, (goal_activity_entity::repository())->count());
        $this->assertTrue($fs->file_exists_by_hash($owner_file1->get_pathnamehash()));
        $this->assertTrue($fs->file_exists_by_hash($owner_file2->get_pathnamehash()));

        // Delete the goal
        $goal1->delete();

        // Test after deletion
        $this->assertEquals(1, (goal_entity::repository())->count());
        $this->assertEquals(1, (goal_activity_entity::repository())->count());
        $this->assertFalse($fs->file_exists_by_hash($owner_file1->get_pathnamehash()));
        $this->assertTrue($fs->file_exists_by_hash($owner_file2->get_pathnamehash()));
    }

    public function test_delete_with_comments_and_reactions(): void {
        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        $goal_generator = goal_generator::instance();

        $data = goal_generator_config::new(['user_id' => $user->id, 'name' => 'Goal 1']);
        $goal1 = goal_generator::instance()->create_goal($data);

        $data = goal_generator_config::new(['user_id' => $user->id, 'name' => 'Goal 2']);
        $goal2 = goal_generator::instance()->create_goal($data);

        $comment1_goal1 = $goal_generator->create_goal_comment($user->id, $goal1->id);
        $comment2_goal1 = $goal_generator->create_goal_comment($user->id, $goal1->id);

        $comment1_goal2 = $goal_generator->create_goal_comment($user->id, $goal2->id);
        $comment2_goal2 = $goal_generator->create_goal_comment($user->id, $goal2->id);

        $reaction_comment1_goal1 = $this->create_reaction($comment1_goal1);
        $reaction_comment2_goal1 = $this->create_reaction($comment2_goal1);
        $reaction_comment1_goal2 = $this->create_reaction($comment1_goal2);
        $reaction_comment2_goal2 = $this->create_reaction($comment2_goal2);

        $this->assertEquals(4, comment_entity::repository()->count());
        $this->assertEquals(4, reaction_entity::repository()->count());

        // Delete goal 1
        $goal1->delete();

        // Only comments and reactions for goal 2 are left.
        $this->assertEqualsCanonicalizing(
            [$comment1_goal2->get_id(), $comment2_goal2->get_id()],
            comment_entity::repository()->get()->pluck('id')
        );

        $this->assertEqualsCanonicalizing(
            [$reaction_comment1_goal2->get_id(), $reaction_comment2_goal2->get_id()],
            reaction_entity::repository()->get()->pluck('id')
        );
    }

    public function test_create_invalid_target_type(): void {
        self::setAdminUser();
        $bad_target_type = 'I do not not exist';

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Invalid target_type value: ' . $bad_target_type);

        $now = time();
        goal_model::create(context_system::instance(),
            $this->goal_category,
            'test goal',
            $now,
            $bad_target_type,
            $now + (DAYSECS * 30),
            100.0,
            0.0,
            'in_progress'
        );
    }

    public function test_create_invalid_start_date(): void {
        self::setAdminUser();
        $now = time();
        $bad_start_date = $now + DAYSECS;
        $target_date = $now;

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('The goal start date must before the target_date.');

        goal_model::create(context_system::instance(),
            $this->goal_category,
            'Test goal',
            $bad_start_date,
            target_type_date::get_type(),
            $target_date,
            100.0,
            0.0,
            'in_progress'
        );
    }

    public function test_update_with_valid_inputs(): void {
        $subject_user_system = self::getDataGenerator()->create_user();
        $owner = self::getDataGenerator()->create_user();
        $subject_user_system_context = context_user::instance($subject_user_system->id);
        self::setUser($owner);

        $now = time();
        $goal_system = goal_model::create(context_system::instance(),
            $this->goal_category,
            'Test goal 1',
            $now,
            target_type_date::get_type(),
            $now + (DAYSECS * 30),
            100.0,
            0.0,
            'in_progress',
            $owner->id,
            $subject_user_system->id
        );


        $update_data = ['name' => 'name new',
            'id_number' => '1234new',
            'description' => 'descr new',
            'start_date' => time(),
            'target_type' => 'date',
            'target_date' => time() + (DAYSECS * 30),
            'target_value' => 99.0
        ];
        $goal_updated = $goal_system->update($update_data['name'], $update_data['id_number'], $update_data['description'], $update_data['start_date'],
            $update_data['target_type'], $update_data['target_date'], $update_data['target_value']
        );

        $fields_to_check = array_keys($update_data);
        foreach ($fields_to_check as $field) {
            $this->assertEquals($update_data[$field], $goal_updated->{$field});
        }
    }

    /**
     * @dataProvider td_invalid_target_value
     */
    public function test_update_invalid_target_value(string $error, float $value): void {
        $owner = self::getDataGenerator()->create_user();
        self::setUser($owner);

        $now = time();
        $goal = goal_model::create(
            context_system::instance(),
            $this->goal_category,
            'Test goal 1',
            $now,
            target_type_date::get_type(),
            $now + (DAYSECS * 30),
            100,
            10,
            'in_progress'
        );

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage($error);
        $goal->update(null, null, null, null, null, null, $value);
    }

    public function test_get_status_from_code() {
        // Static.
        $this->assertInstanceOf(not_started::class, goal_model::status_from_code('not_started'));

        // As a goal_model property.
        $owner = self::getDataGenerator()->create_user();
        self::setUser($owner);

        $now = time();
        $goal1 = goal_model::create(
            context_system::instance(),
            $this->goal_category,
            'Test goal 1',
            $now,
            target_type_date::get_type(),
            $now + (DAYSECS * 30),
            100.0,
            0.0,
            'not_started'
        );
        $this->assertInstanceOf(not_started::class, $goal1->status);
        $this->assertEquals('not_started', $goal1->status->id);
    }

    public function test_get_goal_tasks_metadata(): void {
        self::setAdminUser();
        $goal_generator = goal_generator::instance();
        $subject_user = self::getDataGenerator()->create_user();

        $goal = $goal_generator->create_goal(
            goal_generator_config::new([
                'user_id' => $subject_user->id,
                'context' => context_user::instance($subject_user->id),
                'name' => 'Test goal 1',
            ])
        );

        $goal_tasks_metadata = $goal->get_goal_tasks_metadata();
        $this->assertInstanceOf($goal_tasks_metadata::class, $goal_tasks_metadata);
        $this->assertSame($goal->id, $goal_tasks_metadata->get_goal_id());

        // Also check that accessing as property works.
        $goal_tasks_metadata = $goal->goal_tasks_metadata;
        $this->assertInstanceOf($goal_tasks_metadata::class, $goal_tasks_metadata);
        $this->assertSame($goal->id, $goal_tasks_metadata->get_goal_id());
    }

    private function create_reaction(totara_comment $comment): reaction {
        $fields = [
            'userid' => $comment->get_userid(),
            'instanceid' => $comment->get_id(),
            'component' => 'totara_comment',
            'area' => 'comment',
            'contextid' => context_user::instance($comment->get_userid())->id,
        ];
        $reaction_entity = new reaction_entity($fields);
        $reaction_entity->save();
        return reaction::from_entity($reaction_entity);
    }
}
