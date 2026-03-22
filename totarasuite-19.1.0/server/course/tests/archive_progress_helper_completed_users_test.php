<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2021 onwards Totara Learning Solutions LTD
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
 * @author Sam Hemelryk <sam.hemelryk@totaralearning.com>
 * @package core_course
 */

use core\collection;
use core_course\local\archive_progress_helper\output\completed_users as completed_users_page_output;
use core_course\local\archive_progress_helper\output\validator\completed_users as completed_users_validator;
use core_phpunit\testcase;
use core_course\local\archive_progress_helper\completed_users;
use totara_core\event\course_completion_archived;

/**
 * @covers \core_course\local\archive_progress_helper\completed_users
 */
class core_course_archive_progress_helper_completed_users_test extends testcase {

    public function test_get_reason_cannot_archive_completion() {
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $course->enablecompletion = 1;

        // When user does not have the capability to archive all users progress.
        $instance = new completed_users($course);
        $this->setUser($user);

        // Then missing capability is the reason.
        $capability = 'totara/core:archiveenrolledcourseprogress';
        $this->assertStringContainsString("Missing capability: $capability", $instance->get_unable_to_archive_reason());

        // When user has the capability to archive all users progress.
        $roleid = $this->getDataGenerator()->create_role();
        $context = context_course::instance($course->id);
        role_change_permission($roleid, $context, $capability, CAP_ALLOW);
        role_assign($roleid, $user->id, $context);

        // Then the reason is null.
        $this->assertNull($instance->get_unable_to_archive_reason());
    }

    public function test_archive_and_reset_no_program_certification() {
        $data = $this->setup_course_with_completed_users(20);
        $instance = new completed_users($data['course']);
        $event_sink = $this->redirectEvents();

        // When Admin archives all course completions.
        $this->setAdminUser();
        $instance->archive_and_reset();

        // Then course_completion_archived event is triggered and 20 completions reset.
        $events = $event_sink->get_events();
        $course_completion_archived_events = collection::new($events)->filter(function ($event) {
            return $event instanceof course_completion_archived;
        });
        $this->assertCount(1, $course_completion_archived_events);

        $reflection_method = new ReflectionMethod($instance, 'get_and_reset_user_completions_reverted');
        $reflection_method->setAccessible(true);
        $user_completions_reverted_count = $reflection_method->invoke($instance);
        $this->assertEquals(20, $user_completions_reverted_count);
    }

    /**
     * Sets ups users that have completed a course.
     *
     * @param int $user_count
     * @return array
     */
    private function setup_course_with_completed_users(int $user_count): array {
        $course = $this->getDataGenerator()->create_course();

        $completion_generator = $this->getDataGenerator()->get_plugin_generator('core_completion');
        $completion_generator->enable_completion_tracking($course);

        $users = [];

        for ($i = 0; $i < $user_count; $i++) {
            $user = $this->getDataGenerator()->create_user();
            $this->getDataGenerator()->enrol_user($user->id, $course->id);
            $completion_generator->complete_course($course, $user, time());
            $users[] = $user;
        }

        return [
            'course' => $course,
            'users' => $users,
        ];
    }

    public function test_get_validator() {
        $course = $this->getDataGenerator()->create_course();
        $instance = new completed_users($course);
        $this->assertInstanceOf(completed_users_validator::class, $instance->get_validator());
    }

    public function test_get_page_output() {
        $course = $this->getDataGenerator()->create_course();
        $instance = new completed_users($course);
        $this->assertInstanceOf(completed_users_page_output::class, $instance->get_page_output());
    }

    public function test_archive_and_reset_no_users(): void {
        $generator = static::getDataGenerator();
        $course = $generator->create_course();
        $completion_generator = $generator->get_plugin_generator('core_completion');
        $completion_generator->enable_completion_tracking($course);

        $instance = new completed_users($course);
        $event_sink = $this->redirectEvents();

        // When Admin archives all course completions.
        static::setAdminUser();
        $instance->archive_and_reset();

        // Then course_completion_archived event is triggered and 20 completions reset.
        $events = $event_sink->get_events();
        $course_completion_archived_events = collection::new($events)->filter(function ($event) {
            return $event instanceof course_completion_archived;
        });
        static::assertCount(1, $course_completion_archived_events);

        $reflection_method = new ReflectionMethod($instance, 'get_and_reset_user_completions_reverted');
        $reflection_method->setAccessible(true);
        $user_completions_reverted_count = $reflection_method->invoke($instance);
        static::assertEquals(0, $user_completions_reverted_count);
    }

    /**
     * Where no $CFG->allow_course_completion_reset_for_program_courses or it is false
     *
     * @return void
     */
    public function test_archive_and_reset_with_programs_certifications(): void {
        $data = $this->setup_course_with_completed_users(20);
        $course = $data['course'];
        $users = $data['users'];

        /** @var \totara_program\testing\generator $program_generator */
        $generator = static::getDataGenerator();
        $program_generator = $generator->get_plugin_generator('totara_program');
        $program = $program_generator->create_program();
        $certification = $program_generator->create_certification();
        $program_generator->legacy_add_courseset_program($program->id, [$course->id]);
        $program_generator->legacy_add_courseset_program($certification->id, [$course->id]);
        // Assign 12 random users to the program and certification,
        // these users will not get course completion archive and reset.
        $user_ids = [];
        foreach (array_rand($users, 12) as $i => $key) {
            $user_ids[] = $users[$key]->id;
        }
        $program_generator->assign_program($program->id, $user_ids);
        $program_generator->assign_program($certification->id, $user_ids);

        $instance = new completed_users($course);
        $event_sink = $this->redirectEvents();

        // When Admin archives all course completions.
        static::setAdminUser();
        $instance->archive_and_reset();

        // Then course_completion_archived event is triggered and 20 completions reset.
        $events = $event_sink->get_events();
        $course_completion_archived_events = collection::new($events)->filter(function ($event) {
            return $event instanceof course_completion_archived;
        });
        static::assertCount(1, $course_completion_archived_events);

        $reflection_method = new ReflectionMethod($instance, 'get_and_reset_user_completions_reverted');
        $reflection_method->setAccessible(true);
        $user_completions_reverted_count = $reflection_method->invoke($instance);
        static::assertEquals(8, $user_completions_reverted_count);
    }

    /**
     * Where $CFG->allow_course_completion_reset_for_program_courses is true
     *
     * @return void
     */
    public function test_archive_and_reset_ignore_programs_certifications(): void {
        global $CFG;
        $CFG->allow_course_completion_reset_for_program_courses = true;
        $data = $this->setup_course_with_completed_users(20);
        $course = $data['course'];
        $users = $data['users'];

        /** @var \totara_program\testing\generator $program_generator */
        $generator = static::getDataGenerator();
        $program_generator = $generator->get_plugin_generator('totara_program');
        $program = $program_generator->create_program();
        $certification = $program_generator->create_certification();
        $program_generator->legacy_add_courseset_program($program->id, [$course->id]);
        $program_generator->legacy_add_courseset_program($certification->id, [$course->id]);
        // Assign 12 random users to the program and certification,
        // these users will not get course completion archive and reset.
        $user_ids = [];
        foreach (array_rand($users, 12) as $i => $key) {
            $user_ids[] = $users[$key]->id;
        }
        $program_generator->assign_program($program->id, $user_ids);
        $program_generator->assign_program($certification->id, $user_ids);

        $instance = new completed_users($course);
        $event_sink = $this->redirectEvents();

        // When Admin archives all course completions.
        static::setAdminUser();
        $instance->archive_and_reset();

        // Then course_completion_archived event is triggered and 20 completions reset.
        $events = $event_sink->get_events();
        $course_completion_archived_events = collection::new($events)->filter(function ($event) {
            return $event instanceof course_completion_archived;
        });
        static::assertCount(1, $course_completion_archived_events);

        $reflection_method = new ReflectionMethod($instance, 'get_and_reset_user_completions_reverted');
        $reflection_method->setAccessible(true);
        $user_completions_reverted_count = $reflection_method->invoke($instance);
        static::assertEquals(20, $user_completions_reverted_count);
    }
}
