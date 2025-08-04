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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Jaron Steenson <jaron.steenson@totaralearning.com>
 * @package mod_perform
 * @category test
 */

use core\collection;
use core\entity\user;
use core\orm\query\builder;
use core_phpunit\event_sink;
use core_phpunit\testcase;
use mod_perform\constants;
use mod_perform\entity\activity\subject_instance as subject_instance_entity;
use mod_perform\entity\activity\subject_static_instance;
use mod_perform\entity\activity\participant_instance as participant_instance_entity;
use mod_perform\event\activity_subject_instances_closed;
use mod_perform\models\activity\participant_instance;
use mod_perform\models\due_date;
use mod_perform\models\activity\activity;
use mod_perform\models\activity\subject_instance;
use mod_perform\state\subject_instance\active as subject_active;
use mod_perform\state\subject_instance\closed as subject_availability_close;
use mod_perform\state\subject_instance\open as subject_availability_open;
use mod_perform\state\subject_instance\pending as subject_pending;
use mod_perform\state\participant_instance\open as participant_availability_open;
use mod_perform\state\participant_instance\closed as participant_availability_closed;
use mod_perform\testing\generator;
use totara_job\job_assignment;
use totara_core\relationship\relationship;

/**
 * @group perform
 */
class mod_perform_subject_instance_model_test extends testcase {

    /**
     * @param int $extra_instance_count
     * @dataProvider get_instance_count_provider
     */
    public function test_get_instance_count(int $extra_instance_count): void {
        $this->setAdminUser();

        /** @var generator $perform_generator */
        $perform_generator = generator::instance();

        $config = \mod_perform\testing\activity_generator_configuration::new()
            ->set_number_of_activities(1)
            ->set_number_of_tracks_per_activity(1)
            ->set_number_of_users_per_user_group_type(1);

        $perform_generator->create_full_activities($config)->first();

        /** @var subject_instance_entity $subject_instance_entity */
        $subject_instance_entity = subject_instance_entity::repository()->order_by('id')->first();

        $i = 0;
        $now = time();
        while ($extra_instance_count > $i) {
            $extra_subject_instance = new subject_instance_entity();
            $extra_subject_instance->track_user_assignment_id = $subject_instance_entity->track_user_assignment_id;
            $extra_subject_instance->subject_user_id = $subject_instance_entity->subject_user_id;
            $extra_subject_instance->created_at = $now + ($i + 1); // Force a decent gap between created at times.
            $extra_subject_instance->save();

            $i++;
        }

        $last_instance_entity = $extra_subject_instance ?? $subject_instance_entity;

        $first_instance_count = (new subject_instance($subject_instance_entity))->get_instance_count();
        $last_instance_count = (new subject_instance($last_instance_entity))->get_instance_count();

        self::assertEquals(1, $first_instance_count);
        self::assertEquals($extra_instance_count + 1, $last_instance_count);
    }

    public static function get_instance_count_provider(): array {
        return [
            'Single' => [0],
            'Double' => [1],
            'Triple' => [2],
        ];
    }

    public function test_overdue(): void {
        $this->setAdminUser();

        $generator = generator::instance();
        $activity = $generator->create_activity_in_container();

        $user_tz = new DateTimeZone(core_date::get_user_timezone());
        $due_dates = [
            new DateTimeImmutable('now', $user_tz),
            new DateTimeImmutable('2 days', $user_tz),
            new DateTimeImmutable('-3 days', $user_tz),
            null // ie no due date.
        ];

        [$due_today, $due_in_future, $overdue, $no_due_date] = array_map(
            function (?DateTimeImmutable $due_date) use ($activity, $generator): subject_instance {
                $subject_instance = $generator->create_subject_instance([
                    'activity_id' => $activity->id,
                    'subject_is_participating' => true,
                    'include_questions' => false,
                    'subject_user_id' => $this->getDataGenerator()->create_user()->id
                ]);

                if ($due_date) {
                    $subject_instance->due_date = $due_date->getTimestamp();
                    $subject_instance->save();
                }

                return subject_instance::load_by_entity($subject_instance);
            },
            $due_dates
        );

        $testcases = [
            'no due date' => [$no_due_date, false, null, null],
            'due today' => [$due_today, true, false, 0],
            'due in future' => [$due_in_future, true, false, 2],
            'overdue' => [$overdue, true, true, 3]
        ];

        foreach ($testcases as $id => $testcase) {
            [$subject_instance_model, $has_due_date, $is_overdue, $expected_interval] = $testcase;

            $due_date = $subject_instance_model->due_on;
            if (!$has_due_date) {
                $this->assertNull($due_date);
                continue;
            }

            $this->assertEquals($is_overdue, $due_date->is_overdue(), "[$id] wrong overdue value");
            $this->assertEquals(
                [$expected_interval, due_date::INTERVAL_IN_DAYS],
                $due_date->get_interval_to_or_past_due_date(),
                "[$id] wrong interval details"
            );
        }
    }

    public function test_close_open_subject_instances_with_opened_participant_instances(): void {
        [$activity, $subject_instances] = $this->create_test_data();

        [$si_open, $si_close, $pi_open, $pi_close] = $this->availability_statuses();
        $this->assert_availabilities($subject_instances, $si_open, $pi_open);

        $user_id = get_admin()->id;
        $sink = $this->redirectEvents();
        subject_instance::close_subject_instances_in_activity($activity, $user_id);

        $this->assert_availabilities($subject_instances, $si_close, $pi_close);
        $this->assert_activity_subject_instances_closed_event_fired($sink, [$activity], $user_id);
    }

    public function test_close_open_subject_instances_with_closed_participant_instances(): void {
        [$activity, $subject_instances] = $this->create_test_data();

        $subject_instances->map_to(
            function (subject_instance $si): void {
                foreach ($si->participant_instances as $pi) {
                    $pi->manually_close();
                }
            }
        );

        [$si_open, $si_close, , $pi_close] = $this->availability_statuses();
        $this->assert_availabilities($subject_instances, $si_open, $pi_close);

        $user_id = get_admin()->id;
        $sink = $this->redirectEvents();
        subject_instance::close_subject_instances_in_activity($activity, $user_id);

        $this->assert_availabilities($subject_instances, $si_close, $pi_close);
        $this->assert_activity_subject_instances_closed_event_fired($sink, [$activity], $user_id);
    }

    public function test_close_closed_subject_instances_with_closed_participant_instances(): void {
        [$activity, $subject_instances] = $this->create_test_data();

        $subject_instances->map_to(
            function (subject_instance $si): void {
                $si->manually_close();
            }
        );

        [, $si_close, , $pi_close] = $this->availability_statuses();
        $this->assert_availabilities($subject_instances, $si_close, $pi_close);

        $user_id = get_admin()->id;
        $sink = $this->redirectEvents();
        subject_instance::close_subject_instances_in_activity($activity, $user_id);

        $this->assert_availabilities($subject_instances, $si_close, $pi_close);
        $this->assert_activity_subject_instances_closed_event_not_fired($sink);
    }

    public function test_close_pending_subject_instances(): void {
        [$activity, $subject_instances] = $this->create_test_data(3, false);

        $subject_instances->map_to(
            function (subject_instance $si): void {
                $this->assertTrue($si->is_pending());
            }
        );

        $user_id = get_admin()->id;
        $sink = $this->redirectEvents();
        subject_instance::close_subject_instances_in_activity($activity, $user_id);

        [, $si_close, , $pi_close] = $this->availability_statuses();
        $this->assert_availabilities($subject_instances, $si_close, $pi_close);
        $this->assert_activity_subject_instances_closed_event_fired($sink, [$activity], $user_id);
    }

    public function test_close_subject_instance_with_manage_capabilities(): void {
        $this->setAdminUser();

        $core_generator = $this->getDataGenerator();
        $subjects = collection::new(range(1, 5))
            ->map_to(
                function (int $i) use ($core_generator): array {
                    $user_id = $core_generator->create_user()->id;
                    $manager_id = $core_generator->create_user()->id;

                    job_assignment::create([
                        'userid' => $user_id,
                        'idnumber' => "$user_id",
                        'managerjaid' => job_assignment::create_default($manager_id)->id,
                    ]);

                    return [$user_id, $manager_id];
                }
            );

        [
            [$super_manager_subject, $super_manager],
            [$normal_manager_subject, $normal_manager],
            $unused
        ] = $subjects->all();

        $role_id = builder::get_db()->get_record('role', ['shortname' => 'user'])->id;
        assign_capability(
            'mod/perform:manage_all_participation',
            CAP_ALLOW,
            $role_id,
            context_user::instance($super_manager)->id,
            true
        );

        assign_capability(
            'mod/perform:manage_subject_user_participation',
            CAP_ALLOW,
            $role_id,
            context_user::instance($normal_manager)->id,
            true
        );

        $perform_generator = generator::instance();
        $activity = $perform_generator->create_activity_in_container();
        $si_data = [
            'activity_id' => $activity->id,
            'subject_is_participating' => true,
            'third_participant_username' => $core_generator->create_user()->username,
            'include_questions' => false,
            'status' => subject_active::get_code()
        ];

        $subject_instances = $subjects
            ->map_to(
                function (array $tuple) use ($si_data, $perform_generator): subject_instance {
                    [$user_id, $manager_id] = $tuple;

                    $data = array_merge(
                        ['subject_user_id' => $user_id, 'other_participant_id' => $manager_id],
                        $si_data
                    );

                    $entity = $perform_generator->create_subject_instance($data);
                    return subject_instance::load_by_entity($entity);
                }
            );

        // Try with user that who cannot close any subject instances.
        $this->setUser($super_manager_subject);
        $sink = $this->redirectEvents();
        subject_instance::close_subject_instances_in_activity($activity, $super_manager_subject);

        [$si_open, $si_closed, $pi_open, $pi_closed] = $this->availability_statuses();
        $this->assert_activity_subject_instances_closed_event_not_fired($sink);
        $this->assert_availabilities($subject_instances, $si_open, $pi_open);

        // Try with user that who can close some subject instances.
        $this->setUser($normal_manager);
        $sink->clear();
        subject_instance::close_subject_instances_in_activity($activity, $normal_manager);
        $this->assert_activity_subject_instances_closed_event_fired($sink, [$activity], $normal_manager);

        foreach ($this->refresh($subject_instances) as $si) {
            [$si_status, $pi_status] = $si->subject_user_id === $normal_manager_subject
                ? [$si_closed, $pi_closed]
                : [$si_open, $pi_open];
            $this->assert_availability($si, $si_status, $pi_status);
        }

        // Try with user that who can close all subject instances.
        $this->setUser($super_manager);
        $sink->clear();
        subject_instance::close_subject_instances_in_activity($activity, $super_manager);
        $this->assert_activity_subject_instances_closed_event_fired($sink, [$activity], $super_manager);
        $this->assert_availabilities($subject_instances, $si_closed, $si_closed);
    }

    /**
     * Test to delete participant instance, participant sections and section element responses.
     */
    public function test_manually_delete(): void {

        [$activity, $subject_instances] = $this->create_test_data();

        /** @var subject_instance $subject_instance */
        $subject_instance = subject_instance::load_by_entity(
            subject_instance_entity::repository()->get()->first()
        );

        $subject_instance_id = $subject_instance->id;

        $subject_instance->manually_delete();

        foreach ($subject_instances as $si) {
            $subject_instance_entity = subject_instance_entity::repository()->find($si->id);
            $participant_instances = $si->get_participant_instances();
            if ($si->id == $subject_instance_id) { // The one we deleted.
                $this->assertNull($subject_instance_entity);
                $this->assertEquals(0, $participant_instances->count());
            } else {
                $this->assertNotNull($subject_instance_entity->id);
                foreach ($participant_instances as $pi) {
                    $participant_instance_entity = participant_instance_entity::repository()->find($pi->get_id());
                    $this->assertNotNull($participant_instance_entity);
                }
            }
        }
    }

    private function create_test_data(
        int $no_of_subject_instances = 5,
        bool $subject_instance_active = true
    ): array {
        $this->setAdminUser();

        $perform_generator = generator::instance();
        $activity = $perform_generator->create_activity_in_container();

        $core_generator = $this->getDataGenerator();
        $si_data = [
            'activity_id' => $activity->id,
            'other_participant_id' => $core_generator->create_user()->id,
            'subject_is_participating' => true,
            'include_questions' => false,
            'status' => $subject_instance_active ? subject_active::get_code() :subject_pending::get_code()
        ];

        $subject_instances = collection::new(range(1, $no_of_subject_instances))
            ->map_to(
                function (int $i) use ($core_generator): int {
                    return $core_generator->create_user()->id;
                }
            )
            ->map_to(
                function (int $uid) use ($si_data, $perform_generator): subject_instance {
                    $data = array_merge(['subject_user_id' => $uid], $si_data);
                    $entity = $perform_generator->create_subject_instance($data);

                    return subject_instance::load_by_entity($entity);
                }
            );

        return [$activity, $subject_instances];
    }

    private function assert_availabilities(
        collection $subject_instances,
        string $si_status,
        string $pi_status
    ): void {
        foreach ($this->refresh($subject_instances) as $si) {
            $this->assert_availability($si, $si_status, $pi_status);
        }
    }

    private function assert_availability(
        subject_instance $si,
        string $si_status,
        string $pi_status
    ): void {
        $this->assertEquals($si_status, $si->availability_status);

        foreach ($si->participant_instances as $pi) {
            $this->assertEquals($pi_status, $pi->availability_status);
        }
    }

    private function assert_activity_subject_instances_closed_event_fired(
        event_sink $sink,
        array $activities,
        int $user_id
    ): void {
        $expected = collection::new($activities)
            ->map(
                function (activity $activity) use ($user_id): array {
                    return [$activity->id, $activity->get_context_id(), $user_id];
                }
            );

        $actual = collection::new($sink->get_events())
            ->filter(
                function ($event): bool {
                    return $event instanceof activity_subject_instances_closed;
                }
            )
            ->map(
                function (activity_subject_instances_closed $event): array {
                    return [$event->objectid, $event->contextid, $event->userid];
                }
            );

        $this->assertEquals($expected->count(), $actual->count());
        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    private function assert_activity_subject_instances_closed_event_not_fired(
        event_sink $sink
    ): void {
        $events = collection::new($sink->get_events())
            ->filter(
                function ($event): bool {
                    return $event instanceof activity_subject_instances_closed;
                }
            );

        $this->assertEquals(0, $events->count());
    }

    private function refresh(
        collection $subject_instances
    ): collection {
        // Unfortunately there is no refresh() method on an ORM model so need
        // to do it in a roundabout way.
        return $subject_instances
            ->map(
                function (subject_instance $si): subject_instance {
                    return subject_instance::load_by_id($si->id);
                }
            );
    }

    private function availability_statuses(): array {
        return [
            subject_availability_open::get_name(),
            subject_availability_close::get_name(),
            participant_availability_open::get_name(),
            participant_availability_closed::get_name()
        ];
    }

    public function test_manually_close_pending_throws_exception(): void {
        $subject_instance = $this->create_pending_subject_instance();

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Cannot close a pending subject instance');
        $subject_instance->manually_close();
    }

    public function test_manually_close_pending_successful(): void {
        $subject_instance = $this->create_pending_subject_instance();

        $subject_instance->manually_close(true);

        // Reload model.
        $subject_instance = $subject_instance::load_by_id($subject_instance->id);

        self::assertTrue($subject_instance->is_closed());
        self::assertTrue($subject_instance->is_pending());
    }

    /**
     * @return subject_instance
     */
    private function create_pending_subject_instance(): subject_instance {
        self::setAdminUser();
        $user = self::getDataGenerator()->create_user();
        $perform_generator = generator::instance();

        $subject_relationship = relationship::load_by_idnumber(constants::RELATIONSHIP_SUBJECT);
        $peer_relationship = relationship::load_by_idnumber(constants::RELATIONSHIP_PEER);

        $activity = $perform_generator->create_activity_in_container();
        $perform_generator->create_manual_relationships_for_activity($activity, [
            ['selector' => $subject_relationship->id, 'manual' => $peer_relationship->id],
        ]);

        $subject_instance_entity = $perform_generator->create_subject_instance_with_pending_selections(
            $activity, $user, [$peer_relationship]
        );
        $subject_instance = subject_instance::load_by_entity($subject_instance_entity);
        self::assertTrue($subject_instance->is_pending());
        self::assertTrue($subject_instance->is_open());

        return $subject_instance;
    }

    public function test_get_subject_instances_to_close_for_suspended_users(): void {
        /** @var collection $subject_instances */
        [$activity, $subject_instances] = $this->create_test_data();

        // There are no suspended users, so result should be empty.
        $result = subject_instance::get_subject_instances_to_close_for_suspended_users()->to_array();
        self::assertEmpty($result);

        // Set two users to suspended and check that their subject instances are in the result.
        /** @var subject_instance $subject_instance1 */
        $subject_instance1 = $subject_instances->first();
        /** @var subject_instance $subject_instance2 */
        $subject_instance2 = $subject_instances->last();

        user::repository()
            ->where_in('id', [$subject_instance1->subject_user_id, $subject_instance2->subject_user_id])
            ->update(['suspended' => 1]);

        $result = subject_instance::get_subject_instances_to_close_for_suspended_users()->to_array();
        self::assertCount(2, $result);
        self::assertEqualsCanonicalizing(
            [$subject_instance1->id, $subject_instance2->id],
            [$result[0]->id, $result[1]->id]
        );

        // Close one subject instance. It should not be in the result anymore.
        $subject_instance1->manually_close();
        $result = subject_instance::get_subject_instances_to_close_for_suspended_users()->to_array();
        self::assertCount(1, $result);
        self::assertEquals($subject_instance2->id, $result[0]->id);
    }

    public function test_get_subject_static_instances(): void {
        [, $subject_instances] = $this->create_test_data(4);
        [$si1, $si2, $si3, $si4] = $subject_instances->all();

        $mgr1_ja = $this->create_ja();
        $mgr2_ja = $this->create_ja();
        $mgr3_ja = $this->create_ja();

        $expected = [];

        // Subject 1 has only one manager throughout the PA life cycle.
        $si1_ja = $this->assign_managers($si1, $mgr1_ja);
        self::assertEquals(1, subject_static_instance::repository()->count());

        $expected[] = [
            'subject instance 1',
            $si1,
            $si1_ja,
            [$mgr1_ja->id => false]
        ];

        // Subject 2 changes managers once throughout the PA life cycle.
        $si2_ja = $this->assign_managers($si2, $mgr1_ja, $mgr2_ja);
        self::assertEquals(3, subject_static_instance::repository()->count());

        $expected[] = [
            'subject instance 2',
            $si2,
            $si2_ja,
            [$mgr1_ja->id => true, $mgr2_ja->id => false]
        ];

        // Subject 3 changes managers once through the PA life cycle; the change
        // removes the existing manager.
        $si3_ja = $this->assign_managers($si3, $mgr1_ja, null);
        self::assertEquals(5, subject_static_instance::repository()->count());

        $expected[] = [
            'subject instance 3',
            $si3,
            $si3_ja,
            [0 => false, $mgr1_ja->id => true]
        ];

        // Subject 4 changes managers many times through the PA life cycle; some
        // changes revert back to previous managers. In this case, each manager
        // should only appear once in the returned static instances.
        $si4_ja = $this->assign_managers(
            $si4,
            $mgr1_ja,
            $mgr2_ja,
            null,
            $mgr1_ja,
            $mgr2_ja,
            null,
            $mgr1_ja,
            $mgr3_ja
        );
        self::assertEquals(13, subject_static_instance::repository()->count());

        $expected[] = [
            'subject instance 4',
            $si4,
            $si4_ja,
            [
                0 => true,
                $mgr1_ja->id => true,
                $mgr2_ja->id => true,
                $mgr3_ja->id => false
            ]
        ];

        foreach ($expected as $tuple) {
            self::assert_subject_static_instance(...$tuple);
        }
    }

    public function test_get_subject_static_instances_grouping_and_sorting(): void {
        [, $subject_instances] = $this->create_test_data(4);
        [$si1, $si2, , ] = $subject_instances->all();
        $mgr1_ja = $this->create_ja();
        $mgr2_ja = $this->create_ja();
        $mgr3_ja = $this->create_ja();
        $mgr4_ja = $this->create_ja();

        $user1_ja1 = $this->create_ja($si1, $mgr1_ja);
        $user1_ja2 = $this->create_ja($si1, $mgr2_ja);

        $ssi1 = new subject_static_instance();
        $ssi1->subject_instance_id = $si1->id;
        $ssi1->job_assignment_id = $user1_ja1->id;
        $ssi1->manager_job_assignment_id = $mgr1_ja->id;
        $ssi1->created_at = time();
        $ssi1->save();

        $ssi2 = new subject_static_instance();
        $ssi2->subject_instance_id = $si1->id;
        $ssi2->job_assignment_id = $user1_ja2->id;
        $ssi2->manager_job_assignment_id = $mgr2_ja->id;
        $ssi2->created_at = time();
        $ssi2->save();

        // Create one record for a different subject instance just to exclude cross-contamination.
        $ssi_should_be_ignored = new subject_static_instance();
        $ssi_should_be_ignored->subject_instance_id = $si2->id;
        $ssi_should_be_ignored->job_assignment_id = $user1_ja2->id;
        $ssi_should_be_ignored->manager_job_assignment_id = $mgr2_ja->id;
        $ssi_should_be_ignored->created_at = time();
        $ssi_should_be_ignored->save();

        $instances = subject_instance::load_by_id($si1->id)->subject_static_instances;

        // More recent JA is first.
        self::assertSame([$ssi2->id, $ssi1->id], $instances->pluck('id'));

        $ssi3 = new subject_static_instance();
        $ssi3->subject_instance_id = $si1->id;
        $ssi3->job_assignment_id = $user1_ja1->id;
        $ssi3->manager_job_assignment_id = $mgr3_ja->id;
        $ssi3->created_at = time();
        $ssi3->save();

        $ssi4 = new subject_static_instance();
        $ssi4->subject_instance_id = $si1->id;
        $ssi4->job_assignment_id = $user1_ja2->id;
        $ssi4->manager_job_assignment_id = $mgr4_ja->id;
        $ssi4->created_at = time();
        $ssi4->save();

        // Must be grouped by job assignments now with more recent records first.
        $instances = subject_instance::load_by_id($si1->id)->subject_static_instances;
        self::assertSame([$ssi4->id, $ssi2->id, $ssi3->id, $ssi1->id], $instances->pluck('id'));
    }

    /**
     * @return array
     */
    public static function remove_access_provider(): array {
        return [
            [true, true],
            [true, false],
            [false, true],
            [false, false],
        ];
    }

    /**
     * @dataProvider remove_access_provider
     * @param bool $remove_access_for_subject
     * @param bool $remove_access_for_other_participant
     * @return void
     */
    public function test_manually_open_does_not_open_participant_instances_without_access(
        bool $remove_access_for_subject,
        bool $remove_access_for_other_participant,
    ): void {
        static::setAdminUser();

        $subject_user = static::getDataGenerator()->create_user();
        $other_participant_user = static::getDataGenerator()->create_user();
        $perform_generator = generator::instance();
        $activity = $perform_generator->create_activity_in_container();
        $subject_instance_entity = $perform_generator->create_subject_instance([
            'activity_id' => $activity->id,
            'subject_username' => $subject_user->username,
            'other_participant_id' => $other_participant_user->id,
            'subject_is_participating' => true,
        ]);

        $participant_instances = $subject_instance_entity->participant_instances;
        static::assertCount(2, $participant_instances);
        /** @var participant_instance_entity $subject_participant_instance */
        $subject_participant_instance = $participant_instances->filter('participant_id', $subject_user->id)->first();
        /** @var participant_instance_entity $other_participant_instance */
        $other_participant_instance = $participant_instances->filter('participant_id', $other_participant_user->id)->first();

        $subject_instance_model = subject_instance::load_by_entity($subject_instance_entity);
        $subject_instance_model->manually_close();

        // Set access_removed flag for subject.
        $subject_participant_instance_model = participant_instance::load_by_entity($subject_participant_instance);
        $subject_participant_instance_model->set_access_removed($remove_access_for_subject);

        // Set access_removed flag for other participant.
        $other_participant_instance_model = participant_instance::load_by_entity($other_participant_instance);
        $other_participant_instance_model->set_access_removed($remove_access_for_other_participant);

        // Re-open the subject instance.
        $subject_instance_model->manually_open();
        $subject_instance_entity->refresh();
        static::assertSame(subject_availability_open::get_code(), (int)$subject_instance_entity->availability);

        // Participant instances should remain closed if access was removed.
        $expected_availability_other = $remove_access_for_other_participant
            ? participant_availability_closed::get_code()
            : participant_availability_open::get_code();
        $other_participant_instance->refresh();
        static::assertSame($expected_availability_other, (int)$other_participant_instance->availability);

        $expected_availability_subject = $remove_access_for_subject
            ? participant_availability_closed::get_code()
            : participant_availability_open::get_code();
        $subject_participant_instance->refresh();
        static::assertSame($expected_availability_subject, (int)$subject_participant_instance->availability);
    }

    /**
     * Checks if the subject static instances for the given subject instance are
     * correct.
     *
     * @param string $tag identifies the subject instance under test.
     * @param subject_instance $si subject instance under test.
     * @param job_assignment $subject_ja the expected subject job assignment.
     * @param array<int,bool> $mgr_jas mapping of manager ja ids to false for
     *        the manager ja that is the current manager, false for all others.
     */
    private static function assert_subject_static_instance(
        string $tag,
        subject_instance $si,
        job_assignment $subject_ja,
        array $mgr_jas
    ): void {
        $instances = subject_instance::load_by_id($si->id)
            ->subject_static_instances;

        self::assertCount(
            count($mgr_jas), $instances, "[$tag] wrong instance record count"
        );

        foreach ($instances as $instance) {
            self::assertEquals(
                $si->id,
                $instance->subject_instance_id,
                "[$tag] wrong subject instance id"
            );

            self::assertEquals(
                $subject_ja->id,
                $instance->job_assignment_id,
                "[$tag] wrong subject job instance id"
            );

            $instance_mgr_ja_id = (int)$instance->manager_job_assignment_id;
            $is_former = $mgr_jas[$instance_mgr_ja_id] ?? null;
            self::assertNotNull(
                $is_former, "[$tag] unknown manager job assignment id"
            );

            self::assertEquals(
                $is_former,
                $instance->get_former_manager(),
                "[$tag] wrong former flag for manager job assignment id"
            );
        }

    }

    /**
     * Creates a job assignment for the specified subject and then updates that
     * with subsequent manager 'changes'.
     *
     * @param subject_instance $si subject being processed.
     * @param ?job_assignment $mgr_jas subject's managers in 'temporal' order.
     *
     * @return job_assignment the subject's job assignment.
     */
    private function assign_managers(
        subject_instance $si,
        ?job_assignment ...$mgr_jas
    ): job_assignment {
        $initial_mgr_ja = array_shift($mgr_jas);
        $si_ja = $this->create_ja($si, $initial_mgr_ja);

        // Creating a job assignment needs manual creation of a subject static
        // instance record.
        $values = [
            'subject_instance_id' => $si->id,
            'job_assignment_id' => $si_ja->id,
            'manager_job_assignment_id' => $si_ja->managerjaid
        ];

        (new subject_static_instance($values))->save();

        // Whereas every update of an existing job assignment will automatically
        // create a new subject static instance record.
        foreach ($mgr_jas as $ja) {
            $si_ja->update(['managerjaid' => $ja?->id]);
        }

        return $si_ja;
    }

    /**
     * Creates a user's job assignment.
     *
     * @param ?subject_instance|stdClass $user subject instance or user for whom
     *        to create a job assignment. If not specified, creates a test user
     *        and returns that.
     * @param ?job_assignment $mgr_ja manager's job assignment if any.
     *
     * @return job_assignment the created job assignment.
     */
    private function create_ja(
        null|subject_instance|stdClass $user = null,
        ?job_assignment $mgr_ja = null
    ): job_assignment {
        $final_user = match (true) {
            $user instanceof stdClass => $user,

            $user instanceof subject_instance => $user->subject_user
                ->get_user()
                ->to_record(),

            default => $this->getDataGenerator()->create_user()
        };

        $attrs = [
            'userid' => $final_user->id,
            'idnumber' => $final_user->id . "_" . uniqid('', true),
            'managerjaid' => $mgr_ja?->id
        ];

        return job_assignment::create($attrs);
    }
}
