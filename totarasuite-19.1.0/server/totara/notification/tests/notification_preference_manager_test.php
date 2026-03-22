<?php
/**
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author  David Curry <david.curry@totara.com>
 * @package totara_notification
 */

use core\json_editor\helper\document_helper;
use core\json_editor\node\paragraph;
use core_phpunit\testcase;
use mod_facetoface\totara_notification\resolver\booking_confirmed;
use mod_perform\testing\generator as perform_generator;
use totara_core\extended_context;
use totara_notification\manager\notification_preference_manager;
use totara_notification\recipient\subject;
use totara_notification\testing\generator as notification_generator;

class totara_notification_notification_preference_manager_test extends testcase {

    /**
     * Check this fails as expected when trying to clone in the same context.
     *
     * @throws dml_exception
     */
    public function test_clone_same_context_failure() {
        // Get the extended system context.
        $sys_context = extended_context::make_with_context(
            context_system::instance()
        );

        // Mark the expected exception.
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage("Preference already exists in target context");

        // Trigger the clone code.
        $manager = notification_preference_manager::get_instance();
        $manager->clone_context_notifications($sys_context, $sys_context);
    }

    /**
     * Check this fails as expected when trying to clone to other context levels.
     *
     * @throws dml_exception
     */
    public function test_clone_context_level_mismatch_failure() {
        global $DB;

        // Get the extended system context.
        $sys_context = extended_context::make_with_context(
            context_system::instance()
        );

        // And a context on a different context level.
        $cat = $DB->get_record('course_categories', ['name' => 'Miscellaneous']);
        $cat_context = extended_context::make_with_context(
            context_coursecat::instance($cat->id)
        );

        // Mark the expected exception.
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage("Preferences currently only support cloning between contexts on the same level");

        // Trigger the clone code.
        $manager = notification_preference_manager::get_instance();
        $manager->clone_context_notifications($sys_context, $cat_context);
    }

    /**
     * Check this fails as expected when trying to clone to a context w
     * The use case here is cloning module level notifications to a different module. i.e. seminar to perform.
     *
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function test_clone_outside_parent_context_for_preference() {
        global $DB;

        $this->setUser(get_admin());

        // Lets group the generators used up here.
        $generator = self::getDataGenerator();
        $f2f_generator = $generator->get_plugin_generator('mod_facetoface');
        $notification_generator = notification_generator::instance();
        $perform_generator = perform_generator::instance();

        // First lets set up a course with a seminar.
        $course = $generator->create_course(['fullname' => 'The course']);
        $f2f = $f2f_generator->create_instance(['course' => $course->id]);
        $f2f_context = extended_context::make_with_context(
            context_module::instance($f2f->cmid)
        );

        // Now we create a custom notification for that seminar.
        $notification_generator->create_notification_preference(
            booking_confirmed::class,
            $f2f_context,
            [
                'schedule_offset' => 0,
                'recipient' => subject::class,
                'recipients' => [subject::class],
                'body_format' => FORMAT_JSON_EDITOR,
                'body' => document_helper::json_encode_document(
                    document_helper::create_document_from_content_nodes([
                        paragraph::create_json_node_from_text('Seminar booking confirmation notification'),
                    ])
                ),
                'subject' => 'Test notification subject',
                'subject_format' => FORMAT_PLAIN,
                'additional_criteria' => '{"ical":["include_ical_attachment"]}',
            ]
        );

        // Create a perform activity.
        $activity = $perform_generator->create_activity_in_container();
        $perform_context = extended_context::make_with_context(
            $activity->get_context()
        );

        // Mark the expected exception.
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage("Preferences currently only support cloning between contexts within the same parent context");

        // And attempt to clone the notification to the perform context.
        $manager = notification_preference_manager::get_instance();
        $manager->clone_context_notifications($perform_context, $f2f_context);
    }

    /**
     * Check this fails as expected when trying to clone to an unsupported context.
     * The use case here is cloning module level notifications to a different module. i.e. seminar to perform.
     *
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function test_clone_unsupported_context_for_preference() {
        global $DB;

        $this->setUser(get_admin());

        // Lets group the generators used up here.
        $generator = self::getDataGenerator();
        $f2f_generator = $generator->get_plugin_generator('mod_facetoface');
        $notification_generator = notification_generator::instance();
        $perform_generator = perform_generator::instance();

        // First lets set up a course with a seminar.
        $course = $generator->create_course(['fullname' => 'The course']);
        $f2f = $f2f_generator->create_instance(['course' => $course->id]);
        $f2f_context = extended_context::make_with_context(
            context_module::instance($f2f->cmid)
        );

        // Now we create a custom notification for that seminar.
        $custom_preference = $notification_generator->create_notification_preference(
            booking_confirmed::class,
            $f2f_context,
            [
                'schedule_offset' => 0,
                'recipient' => subject::class,
                'recipients' => [subject::class],
                'body_format' => FORMAT_JSON_EDITOR,
                'body' => document_helper::json_encode_document(
                    document_helper::create_document_from_content_nodes([
                        paragraph::create_json_node_from_text('Seminar booking confirmation notification'),
                    ])
                ),
                'subject' => 'Test notification subject',
                'subject_format' => FORMAT_PLAIN,
                'additional_criteria' => '{"ical":["include_ical_attachment"]}',
            ]
        );

        // Create a perform activity.
        $activity = $perform_generator->create_activity_in_container();
        $perform_context = extended_context::make_with_context(
            $activity->get_context()
        );

        // Count the preferences before we attempt duplication.
        $count = $DB->count_records('notification_preference');

        // Attempt to manually trigger cloning.
        $result = $custom_preference->clone_for_context($perform_context);

        // There should be a debug message about it.
        $debugging_messages = $this->getDebuggingMessages();
        $debug_message = reset($debugging_messages);
        $expected = 'Failed to clone preference:' . $custom_preference->get_id() . ' to unsupported context:' . $perform_context->get_context_id();
        $this->assertSame($expected, $debug_message->message);
        $this->resetDebugging();

        // Still nothing should happen.
        self::assertEquals($count, $DB->count_records('notification_preference'));
        self::assertFalse($result);
    }
}
