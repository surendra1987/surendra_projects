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
 * @author Fabian Derschatta <fabian.derschatta@totara.com>
 * @package core_course
 */

use core\json_editor\helper\document_helper;
use core\json_editor\node\paragraph;
use core\orm\query\builder;
use core_course\totara_notification\resolver\course_completed_resolver;
use core_phpunit\testcase;
use core_user\totara_notification\placeholder\user as user_placeholder;
use totara_core\extended_context;
use totara_job\job_assignment;
use totara_notification\entity\notifiable_event_queue;
use totara_notification\entity\notification_preference;
use totara_notification\entity\notification_queue;
use totara_notification\json_editor\node\placeholder;
use totara_notification\recipient\subject;
use totara_notification\testing\generator as notification_generator;

defined('MOODLE_INTERNAL') || die();

/**
 * @group totara_notification
 */
class core_course_deletion_notification_cleanup_test extends testcase {

    private $user = null;
    private $course1 = null;
    private $course2 = null;

    /**
     * @return void
     */
    protected function setUp(): void {
        global $DB;

        parent::setUp();

        user_placeholder::clear_instance_cache();

        // Disable built-in notifications.
        builder::table('notification_preference')->update(['enabled' => 0]);

        $generator = self::getDataGenerator();
        $completiongen = $generator->get_plugin_generator('core_completion');

        // Create a base user.
        $this->user = $generator->create_user(['firstname' => 'User1 first name', 'lastname' => 'User1 last name']);

        // Create 2 managers for the user.
        $manager1 = $generator->create_user(['firstname' => 'Manager1 first name', 'lastname' => 'Manager1 last name']);
        $manager2 = $generator->create_user(['firstname' => 'Manager2 first name', 'lastname' => 'Manager2 last name']);

        // Assign the managers to the user.
        $manager1job = job_assignment::create(['userid' => $manager1->id, 'idnumber' => 'job1']);
        $manager2job = job_assignment::create(['userid' => $manager2->id, 'idnumber' => 'job2']);
        job_assignment::create([
            'userid' => $this->user->id,
            'idnumber' => 'userjob1',
            'managerjaid' => $manager1job->id
        ]);
        job_assignment::create([
            'userid' => $this->user->id,
            'idnumber' => 'userjob2',
            'managerjaid' => $manager2job->id
        ]);

        // Create two courses.
        $this->course1 = $generator->create_course(['fullname' => 'The first course', 'enablecompletion' => '1']);
        $this->course2 = $generator->create_course(['fullname' => 'The second course', 'enablecompletion' => '1']);

        $record = new \stdClass();
        $record->completion = COMPLETION_TRACKING_AUTOMATIC;
        $record->completionview = 1; // These modules must be viewed to be marked as complete.

        $record->course = $this->course1->id;
        $record->name = 'C1choice';
        $generator->create_module('choice', $record);
        $generator->create_module('choice', $record);

        $record->course = $this->course2->id;
        $record->name = 'C2choice';
        $generator->create_module('choice', $record);
        $generator->create_module('choice', $record);

        // Enrol the test user in the courses.
        $generator->enrol_user($this->user->id, $this->course1->id);
        $generator->enrol_user($this->user->id, $this->course2->id);

        $course_context1 = context_course::instance($this->course1->id);
        $course_context2 = context_course::instance($this->course2->id);
        // Create custom notifications in event context.
        $event_context1 = extended_context::make_with_context($course_context1);
        $event_context2 = extended_context::make_with_context($course_context2);

        $notification_generator = notification_generator::instance();
        $notification_generator->create_notification_preference(
            course_completed_resolver::class,
            $event_context1,
            [
                'schedule_offset' => 0,
                'recipient' => subject::class,
                'recipients' => [subject::class],
                'body_format' => FORMAT_JSON_EDITOR,
                'body' => document_helper::json_encode_document(
                    document_helper::create_document_from_content_nodes([
                        paragraph::create_json_node_from_text('Test notification body'),
                        paragraph::create_json_node_with_content_nodes([
                            placeholder::create_node_from_key_and_label('recipient:last_name', 'Recipient last name'),
                            placeholder::create_node_from_key_and_label('course:full_name', 'Course name'),
                        ]),
                    ])
                ),
                'subject' => 'Test notification subject',
                'subject_format' => FORMAT_PLAIN,
            ]
        );

        $notification_generator->create_notification_preference(
            course_completed_resolver::class,
            $event_context2,
            [
                'schedule_offset' => 0,
                'recipient' => subject::class,
                'recipients' => [subject::class],
                'body_format' => FORMAT_JSON_EDITOR,
                'body' => document_helper::json_encode_document(
                    document_helper::create_document_from_content_nodes([
                        paragraph::create_json_node_from_text('Test notification body'),
                        paragraph::create_json_node_with_content_nodes([
                            placeholder::create_node_from_key_and_label('recipient:last_name', 'Recipient last name'),
                            placeholder::create_node_from_key_and_label('course:full_name', 'Course name'),
                        ]),
                    ])
                ),
                'subject' => 'Test notification subject',
                'subject_format' => FORMAT_PLAIN,
            ]
        );
    }

    /**
     * @return void
     */
    protected function tearDown(): void {
        user_placeholder::clear_instance_cache();

        $this->user = null;
        $this->course1 = null;
        $this->course2 = null;

        parent::tearDown();
    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_notification_records_are_cleaned_up_on_course_deletion(): void {
        $course_context1 = context_course::instance($this->course1->id);
        $course_context2 = context_course::instance($this->course2->id);

        self::assertEquals(1, builder::table(notifiable_event_queue::TABLE)->where('context_id', $course_context1->id)->count());
        self::assertEquals(0, builder::table(notification_queue::TABLE)->where('context_id', $course_context1->id)->count());
        self::assertEquals(1, builder::table(notification_preference::TABLE)->where('context_id', $course_context1->id)->count());

        // There should bes still be a record for course 2
        self::assertEquals(1, builder::table(notifiable_event_queue::TABLE)->where('context_id', $course_context2->id)->count());
        self::assertEquals(0, builder::table(notification_queue::TABLE)->where('context_id', $course_context2->id)->count());
        self::assertEquals(1, builder::table(notification_preference::TABLE)->where('context_id', $course_context2->id)->count());

        delete_course($this->course1->id, false);

        // There should be no records anymore for course 1
        self::assertEquals(0, builder::table(notifiable_event_queue::TABLE)->where('context_id', $course_context1->id)->count());
        self::assertEquals(0, builder::table(notification_queue::TABLE)->where('context_id', $course_context1->id)->count());
        self::assertEquals(0, builder::table(notification_preference::TABLE)->where('context_id', $course_context1->id)->count());

        // There should bes still be a record for course 2
        self::assertEquals(1, builder::table(notifiable_event_queue::TABLE)->where('context_id', $course_context2->id)->count());
        self::assertEquals(0, builder::table(notification_queue::TABLE)->where('context_id', $course_context2->id)->count());
        self::assertEquals(1, builder::table(notification_preference::TABLE)->where('context_id', $course_context2->id)->count());
    }
}
