<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Murali Nair <murali.nair@totara.com>
 * @package mod_perform
 * @category test
 */

use core\collection;
use core_phpunit\testcase;
use mod_perform\entity\activity\activity;
use mod_perform\entity\activity\subject_instance;
use mod_perform\entity\activity\subject_static_instance;
use mod_perform\state\activity\active;
use mod_perform\state\subject_instance\active as si_active;
use mod_perform\state\subject_instance\closed as si_close;
use mod_perform\state\subject_instance\complete as si_complete;
use mod_perform\state\subject_instance\in_progress as si_in_progress;
use mod_perform\state\subject_instance\open as si_open;
use mod_perform\task\service\subject_static_instance_creation;
use mod_perform\testing\generator;
use totara_hierarchy\testing\generator as hierarchy_generator;
use totara_job\job_assignment;

class mod_perform_subject_static_instance_hierarchy_test extends testcase {
    public function test_non_pa_subject(): void {
        $test_data = self::create_test_data();

        // 2 PAs have 4 subjects each but only 3 subjects have job assignments.
        // Currently no subject static instance record are created for subjects
        // with no job assignment.
        $original_static_rec_count = 3 * 2;
        self::assertEquals(
            $original_static_rec_count,
            subject_static_instance::repository()->count(),
            'wrong number of subject static instances'
        );

        // This creates a user that is not involved in any PA.
        [, $non_subject_ja] = $this->create_ja(mgr_ja: $test_data->manager1_ja);

        self::assertEquals(
            $original_static_rec_count,
            subject_static_instance::repository()->count(),
            'wrong number of subject static instances'
        );

        // Not only should the number of subject_static_instance records be the
        // same, there should not be any record pointing to the non subject user
        // ja.
        self::assertFalse(
            subject_static_instance::repository()
                ->where('job_assignment_id', $non_subject_ja->id)
                ->exists(),
            'non subject ja has static record'
        );

        // Changing the manager for the non subject user should also not affect
        // the subject_static_instance records.
        $non_subject_ja->update(['managerjaid' => $test_data->manager2_ja->id]);

        self::assertEquals(
            $original_static_rec_count,
            subject_static_instance::repository()->count(),
            'wrong number of subject static instances'
        );

        self::assertFalse(
            subject_static_instance::repository()
                ->where('job_assignment_id', $non_subject_ja->id)
                ->exists(),
            'non subject ja has static record'
        );
    }

    public function test_subject_job_assignment_updated_new_manager(): void {
        $test_data = self::create_test_data();

        // 2 PAs have 4 subjects each but only 3 subjects have job assignments.
        // Currently no subject static instance record are created for subjects
        // with no job assignment.
        $original_static_rec_count = 3 * 2;
        self::assertEquals(
            $original_static_rec_count,
            subject_static_instance::repository()->count(),
            'wrong number of subject static instances'
        );

        // Round #1
        // Update a subject job assignment with a new manager; should result in
        // a new subject static instance record being created for the open PA
        // only.
        $subject1_ja = $test_data->subject_with_mgr1_ja;
        $subject1_ja->update(['managerjaid' => $test_data->manager2_ja->id]);

        self::assertEquals(
            $original_static_rec_count + 1,
            subject_static_instance::repository()->count(),
            'wrong number of subject static instances'
        );

        // There should now be 3 subject static instance records for the subject
        // - 2 for the open PA and 1 for the closed one.
        $activity_ids_for_subject1 = subject_static_instance::repository()
            ->where('job_assignment_id', $subject1_ja->id)
            ->get()
            ->map(
                fn (subject_static_instance $it): activity =>
                    $it->subject_instance->activity()
            )
            ->pluck('id');

        self::assertEquals(
            3, count($activity_ids_for_subject1), 'wrong number of activities'
        );

        $open_pa_id = $test_data->ongoing_activity->id;
        $closed_pa_id = $test_data->finished_activity->id;
        self::assertEqualsCanonicalizing(
            [$open_pa_id, $open_pa_id, $closed_pa_id],
            $activity_ids_for_subject1,
            'wrong activity ids'
        );

        // Round #2
        // Update the subject job assignment with the original manager; should
        // also result in a new subject static instance record being created for
        // the open PA only.
        $subject1_ja->update(['managerjaid' => $test_data->manager1_ja->id]);

        self::assertEquals(
            $original_static_rec_count + 2,
            subject_static_instance::repository()->count(),
            'wrong number of subject static instances'
        );

        // There should now be 4 subject static instance records for the subject
        // - 3 for the open PA and 1 for the closed one.
        $activity_ids_for_subject1 = subject_static_instance::repository()
            ->where('job_assignment_id', $subject1_ja->id)
            ->get()
            ->map(
                fn (subject_static_instance $it): activity =>
                    $it->subject_instance->activity()
            )
            ->pluck('id');

        self::assertEquals(
            4, count($activity_ids_for_subject1), 'wrong number of activities'
        );

        self::assertEqualsCanonicalizing(
            [$open_pa_id, $open_pa_id, $open_pa_id, $closed_pa_id],
            $activity_ids_for_subject1,
            'wrong activity ids'
        );

        // Round #3
        // Update the subject job assignment with no manager; should also result
        // in a new subject static instance record being created for the open PA
        // only.
        $subject1_ja->update(['managerjaid' => null]);

        self::assertEquals(
            $original_static_rec_count + 3,
            subject_static_instance::repository()->count(),
            'wrong number of subject static instances'
        );

        // There should now be 5 subject static instance records for the subject
        // - 4 for the open PA and 1 for the closed one.
        $activity_ids_for_subject1 = subject_static_instance::repository()
            ->where('job_assignment_id', $subject1_ja->id)
            ->get()
            ->map(
                fn (subject_static_instance $it): activity =>
                    $it->subject_instance->activity()
            )
            ->pluck('id');

        self::assertEquals(
            5, count($activity_ids_for_subject1), 'wrong number of activities'
        );

        self::assertEqualsCanonicalizing(
            [$open_pa_id, $open_pa_id, $open_pa_id, $open_pa_id, $closed_pa_id],
            $activity_ids_for_subject1,
            'wrong activity ids'
        );
    }

    public function test_subject_job_assignment_updated_position(): void {
        $test_data = self::create_test_data();

        // 2 PAs have 4 subjects each but only 3 subjects have job assignments.
        // Currently no subject static instance record are created for subjects
        // with no job assignment.
        $original_static_rec_count = 3 * 2;
        self::assertEquals(
            $original_static_rec_count,
            subject_static_instance::repository()->count(),
            'wrong number of subject static instances'
        );

        // Update a subject job assignment with a new position only; should not
        // have any new subject static instance record being created.
        $pos_generator = hierarchy_generator::instance();
        $position = $pos_generator->create_pos(
            ['frameworkid' => $pos_generator->create_pos_frame([])->id]
        );

        $subject1_ja = $test_data->subject_with_mgr1_ja;
        $subject1_ja->update(['positionid' => $position->id]);

        self::assertEquals(
            $original_static_rec_count,
            subject_static_instance::repository()->count(),
            'wrong number of subject static instances'
        );

        // There only still be 2 subject static instance records for the subject
        // - 1 for the open PA and 1 for the closed one.
        $activity_ids_for_subject1 = subject_static_instance::repository()
            ->where('job_assignment_id', $subject1_ja->id)
            ->get()
            ->map(
                fn (subject_static_instance $it): activity =>
                    $it->subject_instance->activity()
            )
            ->pluck('id');

        self::assertEquals(
            2, count($activity_ids_for_subject1), 'wrong number of activities'
        );

        $open_pa_id = $test_data->ongoing_activity->id;
        $closed_pa_id = $test_data->finished_activity->id;
        self::assertEqualsCanonicalizing(
            [$open_pa_id, $closed_pa_id],
            $activity_ids_for_subject1,
            'wrong activity ids'
        );
    }

    /**
     * Creates an activity for the given subjects.
     *
     * @param collection<stdClass[]> $subjects tuples of [subject, their manager
     *        (null if absent)] in the activity.
     * @param bool $subject_instance_is_open creates open subject instances for
     *        the activity.
     *
     * @return stdClass test data with these attributes:
     *         - activity activity
     *         - collection<subject_instance> si
     */
    private function create_activity(
        collection $subjects,
        bool $subject_instance_is_open
    ): stdClass {
        $generator = generator::instance();
        $activity = $generator->create_activity_in_container();

        $subject_instance_attrs = [
            'activity_id' => $activity->id,
            'activity_status' => active::get_code(),
            'subject_is_participating' => true,
            'include_questions' => false,
            'relationships_can_view' => 'subject, manager',
            'relationships_can_answer' => 'subject, manager',
            'status' => si_active::get_code(),
            'subject_instance_availability' => $subject_instance_is_open
                ? si_open::get_code()
                : si_close::get_code(),
            'progress' => $subject_instance_is_open
                ? si_in_progress::get_code()
                : si_complete::get_code()
        ];

        $subject_instances = $subjects
            ->map(
                function (array $it) use ($subject_instance_attrs): array {
                    [$subject, $mgr] = $it;

                    return array_merge(
                        [
                            'subject_user_id' => $subject->id,
                            'other_participant_id' => $mgr?->id
                        ],
                        $subject_instance_attrs
                    );
                }
            )
            ->map(
                fn (array $those): subject_instance => $generator
                    ->create_subject_instance($those)
                    ->save()
            );

        // This generates the subject_static_instance records.
        (new subject_static_instance_creation())
            ->generate_instances($subject_instances);

        return (object) ['activity' => $activity, 'si' => $subject_instances];
    }

    /**
     * Creates a user's job assignment.
     *
     * @param ?stdClass $user user for whom to create a job assignment. If not
     *        specified, creates a test user and returns that.
     * @param ?job_assignment $mgr_ja manager's job assignment if any.
     *
     * @return mixed[] [stdclass user, job_assignment job assignment] tuple.
     */
    private function create_ja(
        ?stdClass $user = null,
        ?job_assignment $mgr_ja = null
    ): array {
        $final_user = $user ?: $this->getDataGenerator()->create_user();

        $attrs = [
            'userid' => $final_user->id,
            'idnumber' => $final_user->id,
            'managerjaid' => $mgr_ja?->id
        ];

        return [$final_user, job_assignment::create($attrs)];
    }

    /**
     * Creates test data.
     *
     * @return stdClass test data with these attributes:
     *         - activity ongoing_activity
     *         - collection<subject_instance> ongoing_si
     *         - activity finished_activity
     *         - collection<subject_instance> finished_si
     *         - array<stdClass> subjects
     *         - stdClass subject_with_mgr1
     *         - job_assignment subject_with_mgr1_ja
     *         - stdClass subject_with_mgr2
     *         - job_assignment subject_with_mgr2_ja
     *         - stdClass subject_no_mgr
     *         - job_assignment subject_no_mgr_ja
     *         - stdClass subject_no_ja
     *         - stdClass manager1
     *         - job_assignment manager1_ja
     *         - stdClass manager2
     *         - job_assignment manager2_ja
     */
    private function create_test_data(): stdClass {
        $this->setAdminUser();

        [$mgr1, $mgr1_ja] = $this->create_ja();
        [$mgr2, $mgr2_ja] = $this->create_ja();

        [$subject_with_mgr1, $subject_with_mgr1_ja] = $this->create_ja(
            mgr_ja: $mgr1_ja
        );

        [$subject_with_mgr2, $subject_with_mgr2_ja] = $this->create_ja(
            mgr_ja: $mgr2_ja
        );

        [$subject_no_mgr, $subject_no_mgr_ja] = $this->create_ja();

        $subject_no_ja = $this->getDataGenerator()->create_user();

        $subjects = new collection(
            [
                [$subject_with_mgr1, $mgr1],
                [$subject_with_mgr2, $mgr2],
                [$subject_no_mgr, null],
                [$subject_no_ja, null]
            ]
        );

        $ongoing_activity = $this->create_activity($subjects, true);
        $finished_activity = $this->create_activity($subjects, false);

        return (object) [
            'ongoing_activity' => $ongoing_activity->activity,
            'ongoing_si' => $ongoing_activity->si,
            'finished_activity' => $finished_activity->activity,
            'finished_si' => $finished_activity->si,
            'subject_with_mgr1' => $subject_with_mgr1,
            'subject_with_mgr1_ja' => $subject_with_mgr1_ja,
            'subject_with_mgr2' => $subject_with_mgr2,
            'subject_with_mgr2_ja' => $subject_with_mgr2_ja,
            'subject_no_mgr' => $subject_no_mgr,
            'subject_no_mgr_ja' => $subject_no_mgr_ja,
            'subject_no_ja' => $subject_no_ja,
            'manager1' => $mgr1,
            'manager1_ja' => $mgr1_ja,
            'manager2' => $mgr2,
            'manager2_ja' => $mgr2_ja
        ];
    }
}
