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
 * @package mod_perform
 */

use core\orm\query\builder;
use mod_perform\constants;
use mod_perform\entity\activity\participant_instance;
use mod_perform\entity\activity\participant_instance as participant_instance_entity;
use mod_perform\entity\activity\participant_section;
use mod_perform\entity\activity\section as section_entity;
use mod_perform\entity\activity\subject_instance as subject_instance_entity;
use mod_perform\expand_task;
use mod_perform\models\activity\element;
use mod_perform\models\activity\helpers\section_element_manager;
use mod_perform\models\activity\section;
use mod_perform\models\activity\subject_instance;
use mod_perform\models\activity\subject_instance_overview_item;
use mod_perform\models\activity\track;
use mod_perform\models\activity\track_assignment_type;
use mod_perform\models\due_date;
use mod_perform\state\activity\draft;
use mod_perform\state\participant_instance\complete as participant_instance_complete;
use mod_perform\state\participant_instance\in_progress;
use mod_perform\state\participant_section\complete as participant_section_complete;
use mod_perform\state\participant_section\in_progress as participant_section_in_progress;
use mod_perform\state\participant_section\not_started as participant_section_not_started;
use mod_perform\state\subject_instance\in_progress as subject_instance_in_progress;
use mod_perform\task\service\subject_instance_creation;
use mod_perform\user_groups\grouping;
use totara_job\job_assignment;

require_once(__DIR__ . '/subject_instance_testcase.php');

class mod_perform_subject_instance_overview_item_model_test extends mod_perform_subject_instance_testcase {

    public function test_overview_item_bad_participant_id(): void {
        $this->expectException(coding_exception::class);
        $subject_instance_id = self::$about_user_and_participating->id;
        $this->expectExceptionMessage("User with id -1 is not the subject of the subject instance with id {$subject_instance_id}");

        /** @var subject_instance_entity $subject_instance_entity */
        $subject_instance_entity = self::$about_user_and_participating->get_entity_copy();
        $overview_item = new subject_instance_overview_item(- 1, $subject_instance_entity);
    }

    public function test_overview_item(): void {
        $activity = self::$about_user_and_participating->get_activity();

        /** @var subject_instance_entity $subject_instance_entity */
        $subject_instance_entity = self::$about_user_and_participating->get_entity_copy();
        $overview_item = new subject_instance_overview_item(self::$user->id, $subject_instance_entity);

        /** @var participant_instance $participant_instance */
        $participant_instance = participant_instance::repository()
            ->where('subject_instance_id', self::$about_user_and_participating->get_id())
            ->where('participant_id', self::$user->id)
            ->one(true);

        self::assertEquals($activity->name, $overview_item->name);
        self::assertEquals($subject_instance_entity->id, $overview_item->id);
        self::assertEquals(
            'https://www.example.com/moodle/mod/perform/activity/view.php?participant_instance_id=' . $participant_instance->id,
            $overview_item->url
        );
        self::assertEquals(null, $overview_item->completion_date);
        self::assertEquals('test description', $overview_item->description);
        self::assertEquals($participant_instance->created_at, $overview_item->assignment_date);
        self::assertEquals(null, $overview_item->job_assignment);
        self::assertEquals([
            'date' => null,
            'description' => null,
        ], $overview_item->last_update);

        self::assertEquals([
            'date' => null,
            'due_soon' => false,
            'overdue' => false,
        ], $overview_item->due);
    }

    /**
     * Check completion time separately.
     *
     * @return void
     */
    public function test_overview_item_completion_date(): void {
        $completion_time = time() - HOURSECS;

        $subject_instance_entity = self::perform_generator()->create_subject_instance([
            'activity_name' => 'another activity',
            'subject_user_id' => self::$user->id,
            'subject_is_participating' => true,
        ]);
        $subject_instance_entity->completed_at = $completion_time;
        $subject_instance_entity->save();

        $overview_item = new subject_instance_overview_item(self::$user->id, $subject_instance_entity);

        self::assertEquals($completion_time, $overview_item->completion_date);
    }

    /**
     * Check job assignment separately.
     *
     * @return void
     */
    public function test_overview_item_job_assignment(): void {
        /** @var job_assignment $job_assignment */
        $job_assignment = job_assignment::create_default(self::$user->id, [
            'fullname' => 'subject user job assignment'
        ]);

        $subject_instance_entity = self::perform_generator()->create_subject_instance([
            'activity_name' => 'another activity',
            'subject_user_id' => self::$user->id,
            'subject_is_participating' => true,
        ]);
        $subject_instance_entity->job_assignment_id = $job_assignment->id;
        $subject_instance_entity->save();

        $overview_item = new subject_instance_overview_item(self::$user->id, $subject_instance_entity);

        self::assertEquals('subject user job assignment', $overview_item->job_assignment);
    }

    /**
     * Check due date separately.
     *
     * @return void
     */
    public function test_overview_item_due_date(): void {
        $subject_instance_entity = self::perform_generator()->create_subject_instance([
            'activity_name' => 'another activity',
            'subject_user_id' => self::$user->id,
            'subject_is_participating' => true,
        ]);
        $now = time();
        $due_date_not_soon = $now + (10 * DAYSECS);
        $due_date_soon = $now + (5 * DAYSECS);
        $due_date_overdue = $now - (2 * DAYSECS);

        $subject_instance_entity->due_date = $due_date_not_soon;
        $subject_instance_entity->save();
        $overview_item = new subject_instance_overview_item(self::$user->id, $subject_instance_entity);

        self::assertEquals([
            'date' => (new due_date($due_date_not_soon))->get_due_date(),
            'due_soon' => false,
            'overdue' => false,
        ], $overview_item->due);

        $subject_instance_entity->due_date = $due_date_soon;
        $subject_instance_entity->save();
        $overview_item = new subject_instance_overview_item(self::$user->id, $subject_instance_entity);


        self::assertEquals([
            'date' => (new due_date($due_date_soon))->get_due_date(),
            'due_soon' => true,
            'overdue' => false,
        ], $overview_item->due);

        $subject_instance_entity->due_date = $due_date_overdue;
        $subject_instance_entity->save();
        $overview_item = new subject_instance_overview_item(self::$user->id, $subject_instance_entity);


        self::assertEquals([
            'date' => (new due_date($due_date_overdue))->get_due_date(),
            'due_soon' => false, // must be false when overdue
            'overdue' => true,
        ], $overview_item->due);
    }

    /**
     * Check last update separately.
     *
     * @return void
     */
    public function test_overview_item_last_update(): void {
        $now = time();
        $subject_instance_entity = self::perform_generator()->create_subject_instance([
            'activity_name' => 'another activity',
            'subject_user_id' => self::$user->id,
            'subject_is_participating' => true,
        ]);

        $subject_instance_model = subject_instance::load_by_entity($subject_instance_entity);
        $participant_section_model1 = $this->advance_subject_progress($subject_instance_model);

        $subject_instance_entity->refresh();

        $overview_item = new subject_instance_overview_item(self::$user->id, $subject_instance_entity);
        self::assertGreaterThanOrEqual($now, $overview_item->last_update['date']);
        self::assertEquals('Activity was viewed.', $overview_item->last_update['description']);

        // Set participant instance complete.
        /** @var participant_instance_entity $participant_instance */
        $participant_instance = $subject_instance_entity->participant_instances->find(
            fn (participant_instance_entity $participant_instance) =>
                (int)$participant_instance->participant_id === (int)self::$user->id
        );
        $participant_instance->progress = participant_instance_complete::get_code();
        $participant_instance->save();

        $participant_sections = $participant_instance->participant_sections;
        self::assertCount(1, $participant_sections);
        /** @var participant_section $participant_section */
        $participant_section = $participant_sections->first();
        $participant_section->progress = participant_section_complete::get_code();

        $subject_instance_entity->refresh();

        $overview_item = new subject_instance_overview_item(self::$user->id, $subject_instance_entity);
        self::assertGreaterThanOrEqual($now, $overview_item->last_update['date']);
        self::assertEquals('Activity was submitted and is waiting for other participants to complete it.', $overview_item->last_update['description']);
    }

    public function test_overview_item_url(): void {
        $activity = self::$about_user_and_participating->get_activity();

        /** @var subject_instance_entity $subject_instance_entity */
        $subject_instance_entity = self::$about_user_and_participating->get_entity_copy();
        $subject_user_id = (int)self::$about_user_and_participating->subject_user_id;

        self::assertCount(2, $subject_instance_entity->participant_instances);
        /** @var participant_instance_entity $subject_participant_instance */
        $subject_participant_instance = $subject_instance_entity->participant_instances->find(
            fn (participant_instance_entity $participant_instance)
            => (int)$participant_instance->participant_id === $subject_user_id
        );

        /** @var participant_instance_entity $other_user_participant_instance */
        $other_user_participant_instance = $subject_instance_entity->participant_instances->find(
            fn (participant_instance_entity $participant_instance)
                => (int)$participant_instance->participant_id !== $subject_user_id
        );

        self::setUser($subject_user_id);
        $overview_item = new subject_instance_overview_item(self::$user->id, $subject_instance_entity);
        self::assertEquals(
            'https://www.example.com/moodle/mod/perform/activity/view.php?participant_instance_id=' . $subject_participant_instance->id,
            $overview_item->url
        );

        self::setUser($other_user_participant_instance->participant_id);
        $overview_item = new subject_instance_overview_item(self::$user->id, $subject_instance_entity);
        self::assertEquals(
            'https://www.example.com/moodle/mod/perform/activity/view.php?participant_instance_id=' . $other_user_participant_instance->id,
            $overview_item->url
        );

        self::setAdminUser();
        $overview_item = new subject_instance_overview_item(self::$user->id, $subject_instance_entity);
        self::assertNull($overview_item->url);
    }

    public function test_overview_item_last_updated_multi_section(): void {
        // Create a multi-section activity.
        self::setAdminUser();
        $generator = self::getDataGenerator();
        $perform_generator = \mod_perform\testing\generator::instance();

        // Create subject & manager users and add them to an audience.
        $subject_user = $generator->create_user(['firstname' => 'Subject', 'lastname' => 'User']);
        $manager_user = $generator->create_user(['firstname' => 'Manager', 'lastname' => 'User']);
        job_assignment::create([
            'userid' => $subject_user->id,
            'idnumber' => 'subject_job_assignment',
            'managerjaid' => job_assignment::create_default($manager_user->id)->id,
        ]);
        $cohort = $generator->create_cohort();
        cohort_add_member($cohort->id, $subject_user->id);
        cohort_add_member($cohort->id, $manager_user->id);

        // Create an activity with three sections.
        $activity = $perform_generator->create_activity_in_container([
            'create_track' => true,
            'create_section' => false,
            'activity_name' => 'Test activity',
            'activity_status' => draft::get_code()
        ]);
        $section1 = section::create($activity, 'Section 1');
        $section2 = section::create($activity, 'Section 2');
        $section3 = section::create($activity, 'Section 3');
        foreach ([$section1, $section2, $section3] as $section) {
            $perform_generator->create_section_relationship($section, ['relationship' => constants::RELATIONSHIP_SUBJECT]);
            $perform_generator->create_section_relationship($section, ['relationship' => constants::RELATIONSHIP_MANAGER]);

            // Create a question element in each section.
            $element = element::create(
                $activity->get_context(),
                'numeric_rating_scale',
                'Numeric title 1',
                'identifier_numeric',
                '{"defaultValue": "3", "highValue": "5", "lowValue": "1"}',
                false
            );

            /** @var section_entity $section_entity */
            $section_entity = section_entity::repository()->find($section->get_id());
            $section_element_manager = new section_element_manager($section_entity);

            $section_element_manager->add_element_after($element);
        }
        /** @var track $track */
        $track = track::load_by_activity($activity)->first();
        $track->add_assignment(track_assignment_type::ADMIN, grouping::cohort($cohort->id));

        $activity->activate();
        expand_task::create()->expand_all();
        (new subject_instance_creation())->generate_instances();

        // Set subject instance & participant instance to "in progress".
        /** @var subject_instance_entity $subject_instance_entity */
        $subject_instance_entity = subject_instance_entity::repository()
            ->where('subject_user_id', $subject_user->id)
            ->one(true);
        $subject_instance_entity->progress = subject_instance_in_progress::get_code();
        $subject_instance_entity->save();

        /** @var participant_instance_entity $participant_instance */
        $participant_instance = $subject_instance_entity->participant_instances->find(
            fn (participant_instance_entity $participant_instance) =>
                (int)$participant_instance->participant_id === (int)$subject_user->id
        );
        $participant_instance->progress = in_progress::get_code();
        $participant_instance->save();

        // Set two sections to complete.
        /** @var participant_section $participant_section1 */
        $participant_section1 = participant_section::repository()
            ->where('participant_instance_id', $participant_instance->id)
            ->where('section_id', $section1->id)
            ->one(true);
        $now = time();
        builder::table(participant_section::TABLE)
            ->where('id', $participant_section1->id)
            ->update([
                'updated_at' => $now - DAYSECS,
                'progress_updated_at' => $now - DAYSECS,
                'progress' => participant_section_complete::get_code()
            ]);

        /** @var participant_section $participant_section2 */
        $participant_section2 = participant_section::repository()
            ->where('participant_instance_id', $participant_instance->id)
            ->where('section_id', $section2->id)
            ->one(true);
        builder::table(participant_section::TABLE)
            ->where('id', $participant_section2->id)
            ->update([
                'updated_at' => $now - 2 * DAYSECS,
                'progress_updated_at' => $now - 2 * DAYSECS,
                'progress' => participant_section_complete::get_code()
            ]);

        // Set one section to "not started"
        /** @var participant_section $participant_section3 */
        $participant_section3 = participant_section::repository()
            ->where('participant_instance_id', $participant_instance->id)
            ->where('section_id', $section3->id)
            ->one(true);
        $now = time();
        builder::table(participant_section::TABLE)
            ->where('id', $participant_section3->id)
            ->update([
                'updated_at' => null,
                'progress' => participant_section_not_started::get_code()
            ]);

        // Re-create subject instance entity, so the relations are updated (the refresh() method is not sufficient for this).
        /** @var subject_instance_entity $subject_instance_entity */
        $subject_instance_entity = subject_instance_entity::repository()->find($subject_instance_entity->id);

        $overview_item = new subject_instance_overview_item($subject_user->id, $subject_instance_entity);
        // Actual could be 1 second lower as now was taken mid-way through this test case.
        $expected = $now - DAYSECS;
        $result = $overview_item->last_update['date'];
        self::assertLessThanOrEqual(1, abs($expected - $result));
        self::assertEquals('Section 1 was the most recently submitted.', $overview_item->last_update['description']);

        // backdate section 1, so section 2 should be the most recent now.
        builder::table(participant_section::TABLE)
            ->where('id', $participant_section1->id)
            ->update([
                'updated_at' => $now - 3 * DAYSECS,
                'progress_updated_at' => $now - 3 * DAYSECS
            ]);

        /** @var subject_instance_entity $subject_instance_entity */
        $subject_instance_entity = subject_instance_entity::repository()->find($subject_instance_entity->id);

        $overview_item = new subject_instance_overview_item($subject_user->id, $subject_instance_entity);
        // Actual could be 1 second lower as now was taken mid-way through this test case.
        $expected = $now - 2 * DAYSECS;
        $result = $overview_item->last_update['date'];
        self::assertLessThanOrEqual(1, abs($expected - $result));
        self::assertEquals('Section 2 was the most recently submitted.', $overview_item->last_update['description']);

        // Progress the third section.
        builder::table(participant_section::TABLE)
            ->where('id', $participant_section3->id)
            ->update([
                'updated_at' => $now - HOURSECS,
                'progress_updated_at' => $now - HOURSECS,
                'progress' => participant_section_in_progress::get_code()
            ]);

        /** @var subject_instance_entity $subject_instance_entity */
        $subject_instance_entity = subject_instance_entity::repository()->find($subject_instance_entity->id);

        // last update changes to the "in progress" section, but the string remains.
        $overview_item = new subject_instance_overview_item($subject_user->id, $subject_instance_entity);
        // Actual could be 1 second lower as now was taken mid-way through this test case.
        $expected = $now - HOURSECS;
        $result = $overview_item->last_update['date'];
        self::assertLessThanOrEqual(1, abs($expected - $result));
        self::assertEquals('Section 2 was the most recently submitted.', $overview_item->last_update['description']);
    }
}