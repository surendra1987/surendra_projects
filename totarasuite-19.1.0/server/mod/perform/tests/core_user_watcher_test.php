<?php
/*
 * This file is part of Totara Learn
 *
 * Copyright (C) 2022 onwards Totara Learning Solutions LTD
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
 * @author Matthias Bonk <matthias.bonk@totaralearning.com>
 * @package mod_perform
 */

use core\orm\query\builder;
use core_phpunit\testcase;
use core_user\access_controller;
use core_user\profile\display_setting;
use mod_perform\constants;
use mod_perform\entity\activity\participant_instance;
use mod_perform\entity\activity\subject_instance;
use mod_perform\entity\activity\track_user_assignment;
use mod_perform\models\activity\activity;
use mod_perform\models\activity\participant_source;
use mod_perform\models\activity\track;
use mod_perform\state\participant_instance\not_started;
use mod_perform\testing\activity_generator_configuration;
use mod_perform\testing\generator as perform_generator;
use mod_perform\watcher\core_user as mod_perform_core_user_watcher;
use totara_core\hook\manager as hook_manager;
use core_user\hook\allow_view_profile_field;
use totara_core\relationship\relationship;
use totara_job\entity\job_assignment as job_assignment_entity;
use totara_job\job_assignment;

class mod_perform_core_user_watcher_test extends testcase {

    public function test_can_view_profile_field_cache(): void {
        global $DB;

        // Reset the hook watchers so that we can just test this single watcher.
        hook_manager::phpunit_replace_watchers([
            [
                'hookname' => allow_view_profile_field::class,
                'callback' => [mod_perform_core_user_watcher::class, 'allow_view_profile_field']
            ],
        ]);
        access_controller::clear_instance_cache();

        self::setAdminUser();

        // Create an activity, so we can use the access controller in the activity's (course) context.
        // Make two users common participants for one subject instance.
        $generator = perform_generator::instance();
        $subject_user = self::getDataGenerator()->create_user();
        $main_user = self::getDataGenerator()->create_user();
        $other_user = self::getDataGenerator()->create_user();
        $subject_instance = $generator->create_subject_instance([
            'subject_is_participating' => false,
            'subject_user_id' => $subject_user->id,
            'other_participant_id' => $other_user->id,
            'include_questions' => false,
        ]);
        $other_participant_instance = new participant_instance();
        $other_participant_instance->core_relationship_id = 0; // stubbed
        $other_participant_instance->participant_source = participant_source::INTERNAL;
        $other_participant_instance->participant_id = $main_user->id;
        $other_participant_instance->subject_instance_id = $subject_instance->id;
        $other_participant_instance->progress = not_started::get_code();
        $other_participant_instance->save();

        $course = get_course($subject_instance->activity()->course);
        self::setUser($main_user);

        mod_perform_core_user_watcher::clear_resolution_cache();

        // Make sure we have three fields configured as display fields.
        display_setting::save_display_fields(['email', 'username', 'department']);

        $hook = new allow_view_profile_field('email', $other_user->id, $main_user->id, $course);
        $query_count_before = $DB->perf_get_reads();
        mod_perform_core_user_watcher::allow_view_profile_field($hook);
        self::assertTrue($hook->has_permission());
        $queries_without_cache = $DB->perf_get_reads() - $query_count_before;

        // Calling it again for a different relevant field should use the cached result.
        // Just make sure that using the cache will result in fewer queries. Being precise here could make the test brittle.
        $hook = new allow_view_profile_field('username', $other_user->id, $main_user->id, $course);
        $query_count_before = $DB->perf_get_reads();
        mod_perform_core_user_watcher::allow_view_profile_field($hook);
        self::assertTrue($hook->has_permission());
        $queries_with_cache = $DB->perf_get_reads() - $query_count_before;

        self::assertLessThan($queries_without_cache, $queries_with_cache);

        // Check clearing the cache works.
        mod_perform_core_user_watcher::clear_resolution_cache();
        $hook = new allow_view_profile_field('department', $other_user->id, $main_user->id, $course);
        $query_count_before = $DB->perf_get_reads();
        mod_perform_core_user_watcher::allow_view_profile_field($hook);
        self::assertTrue($hook->has_permission());
        $queries_after_clearing_cache = $DB->perf_get_reads() - $query_count_before;

        self::assertGreaterThan($queries_with_cache, $queries_after_clearing_cache);

        mod_perform_core_user_watcher::clear_resolution_cache();
    }

    public function test_can_view_participants_when_subject_is_staff(): void {
        global $DB;

        self::setAdminUser();

        $manager_role = builder::table('role')->where('shortname', 'staffmanager')->one(true);

        // Remove all mod/perform capabilities from the manager role.
        builder::table('role_capabilities')
            ->where('roleid', $manager_role->id)
            ->where_like_starts_with('capability', 'mod/perform')
            ->get()
            ->map(
                fn ($role_capability) => unassign_capability($role_capability->capability, $manager_role->id)
            );
        // Also remove 'manage_staff_participation' from the user role (added on installation by default).
        $user_role = $DB->get_record('role', ['shortname' => 'user']);
        unassign_capability('mod/perform:manage_staff_participation', $user_role->id);

        // Add capabilities for response & participation reporting.
        assign_capability('mod/perform:report_on_subject_responses', CAP_ALLOW, $manager_role->id, context_system::instance());
        assign_capability('mod/perform:view_participation_reporting', CAP_ALLOW, $manager_role->id, context_system::instance());

        // Create an activity with an appraiser.
        // Use the access controller in the activity's (course) context.
        $configuration = activity_generator_configuration::new()
            ->set_number_of_activities(1)
            ->set_number_of_users_per_user_group_type(1)
            ->set_relationships_per_section([constants::RELATIONSHIP_SUBJECT, constants::RELATIONSHIP_APPRAISER])
            ->enable_appraiser_for_each_subject_user();

        $generator = perform_generator::instance();
        /** @var activity $activity */
        $activity = $generator->create_full_activities($configuration)->first();

        // Now that the activity has been generated, reset the hook watchers so that we can just test this single watcher.
        hook_manager::phpunit_replace_watchers([
            [
                'hookname' => allow_view_profile_field::class,
                'callback' => [mod_perform_core_user_watcher::class, 'allow_view_profile_field']
            ],
        ]);
        access_controller::clear_instance_cache();

        // Get the subject user and the appraiser from the generated data. There should be only one subject and one appraiser generated.
        $tracks = $activity->tracks;
        self::assertCount(1, $tracks);
        /** @var track $track */
        $track = $tracks->first();
        /** @var track_user_assignment $track_user_assignment */
        $track_user_assignment = track_user_assignment::repository()->where('track_id', $track->id)->one(true);
        /** @var subject_instance $subject_instance */
        $subject_instance = subject_instance::repository()->where('track_user_assignment_id', $track_user_assignment->id)->one(true);
        /** @var participant_instance $participant_instance */
        $participant_instance = participant_instance::repository()
            ->where('subject_instance_id', $subject_instance->id)
            ->where('core_relationship_id', relationship::load_by_idnumber('appraiser')->id)
            ->one(true);
        $subject_user_id = $subject_instance->subject_user_id;
        $appraiser_id = $participant_instance->participant_id;

        // Give the subject user a manager. We don't want the manager involved in the activity, so we do this separately from the activity generation.
        $manager = self::getDataGenerator()->create_user();
        $manager_ja = job_assignment::create_default($manager->id);
        /** @var job_assignment_entity $subject_ja_entity */
        $subject_ja_entity = job_assignment_entity::repository()->where('userid', $subject_user_id)->one(true);
        self::assertEmpty($subject_ja_entity->managerjaid);
        $subject_ja = job_assignment::from_entity($subject_ja_entity);
        $subject_ja->update(['managerjaid' => $manager_ja->id]);

        self::setUser($manager);

        mod_perform_core_user_watcher::clear_resolution_cache();

        // Make sure we have some fields configured as display fields.
        display_setting::save_display_fields(['email', 'username', 'department']);

        $course = get_course($activity->course);
        $hook = new allow_view_profile_field('email', $appraiser_id, $manager->id, $course);
        mod_perform_core_user_watcher::allow_view_profile_field($hook);

        self::assertTrue($hook->has_permission());

        // Remove the manager and the hook should not give permission anymore.
        mod_perform_core_user_watcher::clear_resolution_cache();
        $subject_ja->update(['managerjaid' => null]);
        $hook = new allow_view_profile_field('email', $appraiser_id, $manager->id, $course);
        mod_perform_core_user_watcher::allow_view_profile_field($hook);
        self::assertFalse($hook->has_permission());

        // Re-add the manager.
        mod_perform_core_user_watcher::clear_resolution_cache();
        $subject_ja->update(['managerjaid' => $manager_ja->id]);
        $hook = new allow_view_profile_field('email', $appraiser_id, $manager->id, $course);
        mod_perform_core_user_watcher::allow_view_profile_field($hook);
        self::assertTrue($hook->has_permission());

        // Now remove the appraiser's participation. The hook should not give permission anymore.
        mod_perform_core_user_watcher::clear_resolution_cache();
        $participant_instance->delete();
        $hook = new allow_view_profile_field('email', $appraiser_id, $manager->id, $course);
        mod_perform_core_user_watcher::allow_view_profile_field($hook);
        self::assertFalse($hook->has_permission());

        mod_perform_core_user_watcher::clear_resolution_cache();
    }
}