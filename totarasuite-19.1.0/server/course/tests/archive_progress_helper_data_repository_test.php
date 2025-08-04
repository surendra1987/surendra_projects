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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Kunle Odusan <kunle.odusan@totaralearning.com>
 */

use core_course\local\archive_progress_helper\data_repository;
use core_phpunit\testcase;

/**
 * @covers \core_course\local\archive_progress_helper\data_repository
 */
class core_course_archive_progress_helper_data_repository_test extends testcase {

    public function test_user_has_progress() {
        $data = $this->setup_course_with_completed_users(1);
        $user = $this->getDataGenerator()->create_user();
        $has_progress = data_repository::user_has_progress($data['course']->id, $user->id);
        $this->assertFalse($has_progress);

        $learner = reset($data['users']);
        $has_progress = data_repository::user_has_progress($data['course']->id, $learner->id);
        $this->assertTrue($has_progress);
    }

    /**
     * @deprecated since Totara 19.1.0
     * @return void
     */
    public function test_get_course_completed_users_count() {
        $this->markTestSkipped('test_get_course_completed_users_count has been deprecated');
        $data = $this->setup_course_with_completed_users(10);
        $result = data_repository::get_course_completed_users_count($data['course']->id);
        $this->assertEquals(10, $result);
    }

    public function test_get_course_completed_users() {
        $data = $this->setup_course_with_completed_users(10);
        $result = data_repository::get_course_completed_users($data['course']->id);

        $user_ids = array_map(function($user) {
            return $user->id;
        }, $data['users']);

        $this->assertEqualsCanonicalizing($user_ids, $result);
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

    public function test_get_linked_programs_and_certifications_names() {
        $generator = $this->getDataGenerator();

        // When a course does not have linked programs and certifications.
        $course = $generator->create_course();

        $result = data_repository::get_linked_programs_and_certifications_names($course->id);

        // Then the result is empty.
        $this->assertEmpty($result);

        // When the course has a program and certification.
        /** @var \totara_program\testing\generator $program_generator */
        $program_generator = $generator->get_plugin_generator('totara_program');
        $program = $program_generator->create_program();
        $certification = $program_generator->create_certification();
        $program_generator->legacy_add_courseset_program($program->id, [$course->id]);
        $program_generator->legacy_add_courseset_program($certification->id, [$course->id]);

        // Then there are two items in the result.
        $result = data_repository::get_linked_programs_and_certifications_names($course->id);
        $this->assertCount(2, $result);
        $certification_count = 0;
        $program_count = 0;
        foreach ($result as $item) {
            if (!empty($item->certifid)) {
                $certification_count++;
            } else {
                $program_count++;
            }
        }

        $this->assertEquals(1, $certification_count);
        $this->assertEquals(1, $program_count);
    }

    public function test_allow_course_completion_reset_for_program_courses_no_config(): void {
        $method = new ReflectionMethod(data_repository::class, 'allow_course_completion_reset_for_program_courses');
        $method->setAccessible(true);
        $allow_course_completion_reset_for_program_courses = $method->invoke(null);

        static::assertFalse($allow_course_completion_reset_for_program_courses);
    }

    public function test_allow_course_completion_reset_for_program_courses_false(): void {
        global $CFG;
        $CFG->allow_course_completion_reset_for_program_courses = false;
        $method = new ReflectionMethod(data_repository::class, 'allow_course_completion_reset_for_program_courses');
        $method->setAccessible(true);
        $allow_course_completion_reset_for_program_courses = $method->invoke(null);

        static::assertFalse($allow_course_completion_reset_for_program_courses);
    }

    public function test_allow_course_completion_reset_for_program_courses_true(): void {
        global $CFG;
        $CFG->allow_course_completion_reset_for_program_courses = true;
        $method = new ReflectionMethod(data_repository::class, 'allow_course_completion_reset_for_program_courses');
        $method->setAccessible(true);
        $allow_course_completion_reset_for_program_courses = $method->invoke(null);

        static::assertTrue($allow_course_completion_reset_for_program_courses);
    }

    public function test_get_user_linked_programs_certifications(): void {
        $generator = static::getDataGenerator();

        // When a course does not have linked programs and certifications.
        $course = $generator->create_course();
        $user = $generator->create_user();

        $result = data_repository::get_user_linked_programs_certifications($course->id, $user->id);

        // Then the result is empty.
        static::assertEmpty($result);

        // When the course has a program and certification.
        /** @var \totara_program\testing\generator $program_generator */
        $program_generator = $generator->get_plugin_generator('totara_program');
        $program = $program_generator->create_program();
        $certification = $program_generator->create_certification();
        $program_generator->legacy_add_courseset_program($program->id, [$course->id]);
        $program_generator->legacy_add_courseset_program($certification->id, [$course->id]);
        $program_generator->assign_program($program->id, [$user->id]);
        $program_generator->assign_program($certification->id, [$user->id]);

        // Then there are two items in the result.
        $result = data_repository::get_user_linked_programs_certifications($course->id, $user->id);
        static::assertCount(2, $result);
        $certification_count = 0;
        $program_count = 0;
        foreach ($result as $item) {
            if (!empty($item->certifid)) {
                $certification_count++;
            } else {
                $program_count++;
            }
        }

        static::assertEquals(1, $certification_count);
        static::assertEquals(1, $program_count);
    }

    public function test_user_has_program_and_certification_assignments(): void {
        $generator = static::getDataGenerator();

        // When a course does not have linked programs and certifications.
        $course = $generator->create_course();
        $user = $generator->create_user();

        $result = data_repository::user_has_program_and_certification_assignments($course->id, $user->id);

        // Then the result is empty.
        static::assertFalse($result);

        // When the course has a program and certification.
        /** @var \totara_program\testing\generator $program_generator */
        $program_generator = $generator->get_plugin_generator('totara_program');
        $program = $program_generator->create_program();
        $certification = $program_generator->create_certification();
        $program_generator->legacy_add_courseset_program($program->id, [$course->id]);
        $program_generator->legacy_add_courseset_program($certification->id, [$course->id]);
        $program_generator->assign_program($program->id, [$user->id]);
        $program_generator->assign_program($certification->id, [$user->id]);

        // Then there are two items in the result.
        $result = data_repository::user_has_program_and_certification_assignments($course->id, $user->id);
        static::assertTrue($result);
    }

    public function test_get_users_for_archive_reset_no_program_user_assignment(): void {
        $generator = static::getDataGenerator();

        // When a course does not have linked programs and certifications.
        $course = $generator->create_course();

        $result = data_repository::get_users_for_archive_reset($course->id);
        // Then the result is empty.
        static::assertEmpty($result);

        $completion_generator = $generator->get_plugin_generator('core_completion');
        $completion_generator->enable_completion_tracking($course);

        for ($i = 0; $i < 2; $i++) {
            $user = $generator->create_user();
            $generator->enrol_user($user->id, $course->id);
            $completion_generator->complete_course($course, $user, time());
        }

        $result = data_repository::get_users_for_archive_reset($course->id);
        static::assertCount(2, $result);

        $confirming_reset = data_repository::course_confirming_reset($course->id);
        static::assertTrue($confirming_reset);
    }

    public function test_get_users_for_archive_reset_with_program_user_assignment(): void {
        global $CFG;
        $generator = static::getDataGenerator();

        // When a course does not have linked programs and certifications.
        $course = $generator->create_course();

        $result = data_repository::get_users_for_archive_reset($course->id);
        // Then the result is empty.
        static::assertEmpty($result);
        $confirming_reset = data_repository::course_confirming_reset($course->id);
        static::assertFalse($confirming_reset);

        $completion_generator = $generator->get_plugin_generator('core_completion');
        $completion_generator->enable_completion_tracking($course);

        /** @var \totara_program\testing\generator $program_generator */
        $program_generator = $generator->get_plugin_generator('totara_program');
        $program = $program_generator->create_program();
        $certification = $program_generator->create_certification();
        $program_generator->legacy_add_courseset_program($program->id, [$course->id]);
        $program_generator->legacy_add_courseset_program($certification->id, [$course->id]);

        $users_for_program = [];
        $users_for_course = [];
        for ($i = 0; $i < 2; $i++) {
            $user = $generator->create_user();
            $users_for_course[] = $user;
            $users_for_program[] = $user->id;
        }

        $program_generator->assign_program($program->id, $users_for_program);
        $program_generator->assign_program($certification->id, $users_for_program);

        $result = data_repository::get_users_for_archive_reset($course->id);
        static::assertCount(0, $result);

        $confirming_reset = data_repository::course_confirming_reset($course->id);
        static::assertFalse($confirming_reset);

        foreach ($users_for_course as $user) {
            $completion_generator->complete_course($course, $user, time());
        }

        $result = data_repository::get_users_for_archive_reset($course->id);
        static::assertCount(0, $result);

        $confirming_reset = data_repository::course_confirming_reset($course->id);
        static::assertFalse($confirming_reset);

        $CFG->allow_course_completion_reset_for_program_courses = true;
        $result = data_repository::get_users_for_archive_reset($course->id);
        static::assertCount(2, $result);

        $confirming_reset = data_repository::course_confirming_reset($course->id);
        static::assertTrue($confirming_reset);
    }

    /**
     * Test page $confirming_reset and $linked_programs_and_certifications output where
     * false - user is not enrol and is not complete a course
     * &&
     * !false - no program/certification user assignment
     */
    public function test_user_confirming_reset_with_false_false() {
        $generator = static::getDataGenerator();
        $course = $generator->create_course();
        $target_user = $generator->create_user();

        $confirming_reset = data_repository::user_confirming_reset($course->id, $target_user->id);
        static::assertFalse($confirming_reset);
    }

    /**
     * Test page $confirming_reset and $linked_programs_and_certifications output where
     * true - user is enrol and is complete a course
     * &&
     * !false - no program/certification user assignment
     */
    public function test_user_confirming_reset_with_true_false() {
        $generator = static::getDataGenerator();
        $course = $generator->create_course();
        $target_user = $generator->create_user();

        $completion_generator = $generator->get_plugin_generator('core_completion');
        $completion_generator->enable_completion_tracking($course);
        $generator->enrol_user($target_user->id, $course->id);
        $completion_generator->complete_course($course, $target_user, time());

        $confirming_reset = data_repository::user_confirming_reset($course->id, $target_user->id);
        static::assertTrue($confirming_reset);
    }

    /**
     * Test page $confirming_reset and $linked_programs_and_certifications output where
     * false - user is not enrol and is not complete a course
     * &&
     * !true - there is program/certification user assignment
     */
    public function test_user_confirming_reset_with_false_true() {
        $generator = static::getDataGenerator();
        $course = $generator->create_course();
        $user = $generator->create_user();

        /** @var \totara_program\testing\generator $program_generator */
        $program_generator = $generator->get_plugin_generator('totara_program');
        $program = $program_generator->create_program();
        $certification = $program_generator->create_certification();
        $program_generator->legacy_add_courseset_program($program->id, [$course->id]);
        $program_generator->legacy_add_courseset_program($certification->id, [$course->id]);
        $program_generator->assign_program($program->id, [$user->id]);
        $program_generator->assign_program($certification->id, [$user->id]);

        $confirming_reset = data_repository::user_confirming_reset($course->id, $user->id);
        static::assertFalse($confirming_reset);
    }

    /**
     * Test page $confirming_reset and $linked_programs_and_certifications output where
     * true - user is complete a course
     * &&
     * !true - there is program/certification user assignment
     * &&
     * $CFG->allow_course_completion_reset_for_program_courses is false (or no config)
     */
    public function test_user_confirming_reset_with_true_true() {
        $generator = static::getDataGenerator();
        $course = $generator->create_course();
        $user = $generator->create_user();

        /** @var \totara_program\testing\generator $program_generator */
        $program_generator = $generator->get_plugin_generator('totara_program');
        $program = $program_generator->create_program();
        $certification = $program_generator->create_certification();
        $program_generator->legacy_add_courseset_program($program->id, [$course->id]);
        $program_generator->legacy_add_courseset_program($certification->id, [$course->id]);
        $program_generator->assign_program($program->id, [$user->id]);
        $program_generator->assign_program($certification->id, [$user->id]);

        $completion_generator = $generator->get_plugin_generator('core_completion');
        $completion_generator->enable_completion_tracking($course);
        $completion_generator->complete_course($course, $user, time());

        $confirming_reset = data_repository::user_confirming_reset($course->id, $user->id);
        static::assertFalse($confirming_reset);
    }

    /**
     * Test page $confirming_reset and $linked_programs_and_certifications output where
     * true - user is complete a course
     * &&
     * !true - there is program/certification user assignment
     * &&
     * $CFG->allow_course_completion_reset_for_program_courses is true
     */
    public function test_user_confirming_reset_with_true_ignore_true() {
        global $CFG;
        $CFG->allow_course_completion_reset_for_program_courses = true;

        $generator = static::getDataGenerator();
        $course = $generator->create_course();
        $user = $generator->create_user();

        /** @var \totara_program\testing\generator $program_generator */
        $program_generator = $generator->get_plugin_generator('totara_program');
        $program = $program_generator->create_program();
        $certification = $program_generator->create_certification();
        $program_generator->legacy_add_courseset_program($program->id, [$course->id]);
        $program_generator->legacy_add_courseset_program($certification->id, [$course->id]);
        $program_generator->assign_program($program->id, [$user->id]);
        $program_generator->assign_program($certification->id, [$user->id]);

        $completion_generator = $generator->get_plugin_generator('core_completion');
        $completion_generator->enable_completion_tracking($course);
        $completion_generator->complete_course($course, $user, time());

        $confirming_reset = data_repository::user_confirming_reset($course->id, $user->id);
        // $CFG->allow_course_completion_reset_for_program_courses is true
        static::assertTrue($confirming_reset);
    }
}