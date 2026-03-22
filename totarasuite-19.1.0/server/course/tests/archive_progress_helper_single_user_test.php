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

use core_course\local\archive_progress_helper\output\single_user as single_user_page_output;
use core_course\local\archive_progress_helper\output\validator\single_user as single_user_validator;
use core_phpunit\testcase;
use core_course\local\archive_progress_helper\single_user;
use core_course\local\archive_progress_helper\output\page_output;

/**
 * @covers \core_course\local\archive_progress_helper\single_user
 */
class core_course_archive_progress_helper_single_user_test extends testcase {

    public function test_construction_an_instance() {
        $user_1 = $this->getDataGenerator()->create_user();
        $user_2 = $this->getDataGenerator()->create_user();
        new single_user(new stdClass(), (object)['id' => $user_2->id]);

        // When user->id is same as logged-in user.
        $this->setUser($user_1);

        // then an exception is thrown.
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage("Current user cannot their own state using this");
        new single_user(new stdClass(), (object)['id' => $user_1->id]);
    }

    public function test_get_reason_cannot_archive_completion() {
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $course->enablecompletion = 1;
        $guest = guest_user();

        // When a guest user.
        $instance = new single_user($course, $guest);

        // Then guest user progress is the reason.
        $this->assertEquals('Cannot archive guest user progress.', $instance->get_unable_to_archive_reason());

        // When user does not have the capability to archive target user's progress.
        $this->setUser($user);
        $target_user = $this->getDataGenerator()->create_user();
        $instance = new single_user($course, $target_user);

        // Then missing capability is the reason.
        $capability = 'totara/core:archiveusercourseprogress';
        $this->assertStringContainsString("Missing capability: $capability", $instance->get_unable_to_archive_reason());

        // Assign the capability.
        $roleid = $this->getDataGenerator()->create_role();
        $context = context_course::instance($course->id);
        role_change_permission($roleid, $context, $capability, CAP_ALLOW);
        role_assign($roleid, $user->id, $context);

        // When target user's completion info is not tracked.
        // Then completion not tracked is the reason.
        $this->assertEquals('User does not have a completion tracked role', $instance->get_unable_to_archive_reason());

        // When user has the capability to archive target user's progress and target user's completion info is tracked.
        $this->getDataGenerator()->enrol_user($target_user->id, $course->id, 'student');

        // Then the reason is null.
        $this->assertNull($instance->get_unable_to_archive_reason());
    }

    public function test_get_validator() {
        $course = $this->getDataGenerator()->create_course();
        $target_user = $this->getDataGenerator()->create_user();

        $instance = new single_user($course, $target_user);
        $this->assertInstanceOf(single_user_validator::class, $instance->get_validator());
    }

    public function test_get_page_output() {
        $course = $this->getDataGenerator()->create_course();
        $target_user = $this->getDataGenerator()->create_user();

        $instance = new single_user($course, $target_user);
        $this->assertInstanceOf(single_user_page_output::class, $instance->get_page_output());
    }

    /**
     * Test page $confirming_reset and $linked_programs_and_certifications output where
     * false - user is not enrol and is not complete a course
     * &&
     * !false - no program/certification user assignment
     */
    public function test_get_page_output_with_false_false() {
        $generator = static::getDataGenerator();
        $course = $generator->create_course();
        $target_user = $generator->create_user();

        $instance = new single_user($course, $target_user);
        $rp = new ReflectionProperty(page_output::class, 'confirming_reset');
        $rp->setAccessible(true);
        $confirming_reset = $rp->getValue($instance->get_page_output());
        static::assertFalse($confirming_reset);

        $rp = new ReflectionProperty(single_user_page_output::class, 'linked_programs_and_certifications');
        $rp->setAccessible(true);
        $linked_items = $rp->getValue($instance->get_page_output());
        static::assertCount(2, $linked_items);
        static::assertCount(0, $linked_items['programs']);
        static::assertCount(0, $linked_items['certifications']);
    }

    /**
     * Test page $confirming_reset and $linked_programs_and_certifications output where
     * true - user is enrol and is complete a course
     * &&
     * !false - no program/certification user assignment
     */
    public function test_get_page_output_with_true_false() {
        $generator = static::getDataGenerator();
        $course = $generator->create_course();
        $target_user = $generator->create_user();

        $completion_generator = $generator->get_plugin_generator('core_completion');
        $completion_generator->enable_completion_tracking($course);
        $generator->enrol_user($target_user->id, $course->id);
        $completion_generator->complete_course($course, $target_user, time());

        $instance = new single_user($course, $target_user);
        $rp = new ReflectionProperty(page_output::class, 'confirming_reset');
        $rp->setAccessible(true);
        $confirming_reset = $rp->getValue($instance->get_page_output());
        static::assertTrue($confirming_reset);

        $rp = new ReflectionProperty(single_user_page_output::class, 'linked_programs_and_certifications');
        $rp->setAccessible(true);
        $linked_items = $rp->getValue($instance->get_page_output());
        static::assertCount(2, $linked_items);
        static::assertCount(0, $linked_items['programs']);
        static::assertCount(0, $linked_items['certifications']);
    }

    /**
     * Test page $confirming_reset and $linked_programs_and_certifications output where
     * false - user is not enrol and is not complete a course
     * &&
     * !true - there is program/certification user assignment
     */
    public function test_get_page_output_with_false_true() {
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

        $instance = new single_user($course, $user);
        $rp = new ReflectionProperty(page_output::class, 'confirming_reset');
        $rp->setAccessible(true);
        $confirming_reset = $rp->getValue($instance->get_page_output());
        static::assertFalse($confirming_reset);

        $rp = new ReflectionProperty(single_user_page_output::class, 'linked_programs_and_certifications');
        $rp->setAccessible(true);
        $linked_items = $rp->getValue($instance->get_page_output());
        static::assertCount(2, $linked_items);
        static::assertCount(1, $linked_items['programs']);
        static::assertCount(1, $linked_items['certifications']);

        static::assertEquals($program->fullname, $linked_items['programs'][$program->id]);
        static::assertEquals($certification->fullname, $linked_items['certifications'][$certification->id]);
    }

    /**
     * Test page $confirming_reset and $linked_programs_and_certifications output where
     * true - user is complete a course
     * &&
     * !true - there is program/certification user assignment
     * &&
     * $CFG->allow_course_completion_reset_for_program_courses is false (or no config)
     */
    public function test_get_page_output_with_true_true() {
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

        $instance = new single_user($course, $user);
        $rp = new ReflectionProperty(page_output::class, 'confirming_reset');
        $rp->setAccessible(true);
        $confirming_reset = $rp->getValue($instance->get_page_output());
        static::assertFalse($confirming_reset);

        $rp = new ReflectionProperty(single_user_page_output::class, 'linked_programs_and_certifications');
        $rp->setAccessible(true);
        $linked_items = $rp->getValue($instance->get_page_output());
        static::assertCount(2, $linked_items);
        static::assertCount(1, $linked_items['programs']);
        static::assertCount(1, $linked_items['certifications']);

        static::assertEquals($program->fullname, $linked_items['programs'][$program->id]);
        static::assertEquals($certification->fullname, $linked_items['certifications'][$certification->id]);
    }

    /**
     * Test page $confirming_reset and $linked_programs_and_certifications output where
     * true - user is complete a course
     * &&
     * !true - there is program/certification user assignment
     * &&
     * $CFG->allow_course_completion_reset_for_program_courses is true
     */
    public function test_get_page_output_with_true_ignore_true() {
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

        $instance = new single_user($course, $user);
        $rp = new ReflectionProperty(page_output::class, 'confirming_reset');
        $rp->setAccessible(true);
        $confirming_reset = $rp->getValue($instance->get_page_output());
        // $CFG->allow_course_completion_reset_for_program_courses is true
        static::assertTrue($confirming_reset);

        $rp = new ReflectionProperty(single_user_page_output::class, 'linked_programs_and_certifications');
        $rp->setAccessible(true);
        $linked_items = $rp->getValue($instance->get_page_output());
        static::assertCount(2, $linked_items);
        static::assertCount(1, $linked_items['programs']);
        static::assertCount(1, $linked_items['certifications']);

        static::assertEquals($program->fullname, $linked_items['programs'][$program->id]);
        static::assertEquals($certification->fullname, $linked_items['certifications'][$certification->id]);
    }

    public function test_get_user_linked_program_certification_names(): void {
        $generator = static::getDataGenerator();

        $course = $generator->create_course();
        $user = $generator->create_user();

        // Test where no program/certification, no link to a course and no user assignment.
        $instance = new single_user($course, $user);
        // Then has no linked programs or certifications.
        $rm = new ReflectionMethod($instance, 'get_user_linked_program_certification_names');
        $rm->setAccessible(true);

        $linked_items = $rm->invoke($instance);

        static::assertCount(2, $linked_items);
        static::assertCount(0, $linked_items['programs']);
        static::assertCount(0, $linked_items['certifications']);

        // When the course is linked to programs and certifications.
        /** @var \totara_program\testing\generator $program_generator */
        $program_generator = $generator->get_plugin_generator('totara_program');
        $program = $program_generator->create_program();
        $certification = $program_generator->create_certification();
        $program_generator->legacy_add_courseset_program($program->id, [$course->id]);
        $program_generator->legacy_add_courseset_program($certification->id, [$course->id]);


        // Test there is a program and a certification linked to the course, but no user assignment.
        $instance = new single_user($course, $user);
        $rm = new ReflectionMethod($instance, 'get_user_linked_program_certification_names');
        $rm->setAccessible(true);

        $linked_items = $rm->invoke($instance);

        static::assertCount(2, $linked_items);
        static::assertCount(0, $linked_items['programs']);
        static::assertCount(0, $linked_items['certifications']);

        $program_generator->assign_program($program->id, [$user->id]);
        $program_generator->assign_program($certification->id, [$user->id]);

        $completion_generator = $generator->get_plugin_generator('core_completion');
        $completion_generator->enable_completion_tracking($course);
        $completion_generator->complete_course($course, $user, time());

        // Test there is a program and a certification linked to the course with user assignment.
        $instance = new single_user($course, $user);
        $rm = new ReflectionMethod($instance, 'get_user_linked_program_certification_names');
        $rm->setAccessible(true);

        $linked_items = $rm->invoke($instance);

        static::assertCount(2, $linked_items);
        static::assertCount(1, $linked_items['programs']);
        static::assertCount(1, $linked_items['certifications']);

        static::assertEquals($program->fullname, $linked_items['programs'][$program->id]);
        static::assertEquals($certification->fullname, $linked_items['certifications'][$certification->id]);
    }
}
