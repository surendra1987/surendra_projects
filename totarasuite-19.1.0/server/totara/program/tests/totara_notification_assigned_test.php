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
 * @author Matthias Bonk <matthias.bonk@totaralearning.com>
 * @package totara_program
 */

use core\json_editor\helper\document_helper;
use core\json_editor\node\paragraph;
use totara_core\extended_context;
use totara_notification\entity\notifiable_event_queue;
use totara_notification\entity\notification_queue;
use totara_notification\task\process_event_queue_task;
use totara_notification\testing\generator as notification_generator;
use totara_notification\recipient\subject;
use totara_notification\json_editor\node\placeholder;
use totara_program\totara_notification\resolver\assigned as assigned_resolver;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/totara_notification_base.php');

/**
 * @group totara_notification
 */
class totara_program_totara_notification_assigned_test extends totara_program_totara_notification_base {
    use \totara_notification\testing\notification_log_test_trait;

    /**
     * @return void
     */
    public function test_resolver(): void {
        global $DB;

        $data = $this->setup_programs();

        // Create a custom notification in event context.
        $event_context = extended_context::make_with_context(
            context_program::instance($data->program1->id)
        );
        $notification_generator = notification_generator::instance();
        $preference = $notification_generator->create_notification_preference(
            assigned_resolver::class,
            $event_context,
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
                            placeholder::create_node_from_key_and_label('program:full_name', 'Program full name'),
                            placeholder::create_node_from_key_and_label('managers:last_name', 'All managers last name'),
                            placeholder::create_node_from_key_and_label(
                                'assignment:due_date_criteria',
                                'Assignment due date criteria'
                            ),
                            placeholder::create_node_from_key_and_label(
                                'assignment:due_date',
                                'Assignment due date'
                            ),
                        ]),
                    ])
                ),
                'subject' => 'Test notification subject',
                'subject_format' => FORMAT_PLAIN,
            ]
        );

        // The event has already been triggered and queued, during setup_programs above.
        self::assertEquals(1, $DB->count_records(notifiable_event_queue::TABLE, ['resolver_class_name' => assigned_resolver::class]));
        self::assertEquals(0, $DB->count_records(notification_queue::TABLE));

        // Redirect messages.
        $sink = self::redirectMessages();

        // Run tasks.
        $task = new process_event_queue_task();
        $task->execute();

        self::assertEquals(0, $DB->count_records(notifiable_event_queue::TABLE));
        self::assertEquals(0, $DB->count_records(notification_queue::TABLE));

        $messages = $sink->get_messages();
        self::assertCount(1, $messages);

        $message = reset($messages);
        self::assertEquals('Test notification subject', $message->subject);
        self::assertStringContainsString('Test notification body', $message->fullmessage);
        self::assertStringContainsString('User1 last name', $message->fullmessage);
        self::assertStringContainsString('My program1 full name', $message->fullmessage);
        self::assertStringContainsString('Manager1 last name, Manager2 last name', $message->fullmessage);
        self::assertStringContainsString('Due date criteria not defined', $message->fullmessage);
        self::assertStringContainsString(userdate($data->due_date->getTimestamp(), '%d/%m/%Y', 99, false), $message->fullmessage);
        self::assertEquals($data->user1->id, $message->userto->id);

        // Check the logs
        $delivery_channels = json_decode($message->totara_notification_delivery_channels);
        self::verify_notification_logs([
            [
                'resolver_class_name' => assigned_resolver::class,
                'context_id' => $event_context->get_context()->id,
                'logs' => [
                    [
                        'preference_id' => $preference->get_id(),
                        'recipients' => 1,
                        'channels' => count($delivery_channels),
                    ],
                ],
                'event_name' => get_string('notification_log_assigned_resolver', 'totara_program', [
                    'resolver_title' => assigned_resolver::get_notification_title(),
                    'user' => 'User1 first name User1 last name',
                    'program' => 'My program1 full name',
                ])
            ],
        ]);
    }

    public function test_get_scheduled_events(): void {
        global $DB;

        $resolver_class_name = assigned_resolver::class;

        $data = $this->setup_programs();

        // Remove the 'assigned' notifiable event queue record.
        $DB->delete_records('notifiable_event_queue');

        // The setup data should have created 1 user assignment.
        $existing = $DB->get_records('prog_user_assignment');
        self::assertCount(1, $existing);

        $existing = array_pop($existing);
        $initial = [
            'program_id' => $existing->programid,
            'user_id' => $existing->userid,
            'first_assigned' => $existing->timeassigned
        ];

        $now = time();
        // No scheduled events because nothing is completed.
        self::assert_scheduled_events(
            $resolver_class_name,
            0,
            $now + 1,
            [
                ['program_id' => $existing->programid, 'user_id' => $existing->userid, 'first_assigned' => $existing->timeassigned],
            ]
        );

        // Right lets insert some data to test.
        $extra1 = self::getDataGenerator()->create_user(['firstname' => 'Extra1 first', 'lastname' => 'Extra1 last']);
        $extra2 = self::getDataGenerator()->create_user(['firstname' => 'Extra2 first', 'lastname' => 'Extra1 last']);

        $assign1 = [
            'programid' => $data->program2->id,
            'userid' => $extra1->id,
            'assignmentid' => $existing->id + 1, // Don't worry about assignment existing, just be unique to avoid fk error.
            'timeassigned' => $now + 1000,
        ];
        $DB->insert_record('prog_user_assignment', $assign1);

        $assign2 = [
            'programid' => $data->program2->id,
            'userid' => $extra1->id,
            'assignmentid' => $existing->id + 2, // Don't worry about assignment existing, just be unique to avoid fk error.
            'timeassigned' => $now + 2000,
        ];
        $DB->insert_record('prog_user_assignment', $assign2);

        // Check that we only get 1 record of the 2 added.
        self::assert_scheduled_events(
            $resolver_class_name,
            $now + 1, // Min time to exclude initial record.
            $now + 5000, // Max time to include both new records.
            [
                ['program_id' => $data->program2->id, 'user_id' => $extra1->id, 'first_assigned' => $now + 1000],
            ]
        );

        // Check we don't get either if the time is after the first_assigned.
        self::assert_scheduled_events(
            $resolver_class_name,
            $now + 1001, // Min time to exclude initial record.
            $now + 5000, // Max time to include both new records.
            []
        );

        // Right and finally check t

        $assign3 = [
            'programid' => $data->program1->id,
            'userid' => $extra2->id,
            'assignmentid' => $existing->id + 3, // Don't worry about assignment existing, just be unique to avoid fk error.
            'timeassigned' => $now + 1100,
        ];
        $DB->insert_record('prog_user_assignment', $assign3);

        $assign4 = [
            'programid' => $data->program1->id,
            'userid' => $extra2->id,
            'assignmentid' => $existing->id + 4, // Don't worry about assignment existing, just be unique to avoid fk error.
            'timeassigned' => $now + 5100,
        ];
        $DB->insert_record('prog_user_assignment', $assign4);

        $assign5 = [
            'programid' => $data->program1->id,
            'userid' => $extra1->id,
            'assignmentid' => $existing->id + 5, // Don't worry about assignment existing, just be unique to avoid fk error.
            'timeassigned' => $now + 2100,
        ];
        $DB->insert_record('prog_user_assignment', $assign5);

        // Check that we only get 1 record of the 2 added.
        self::assert_scheduled_events(
            $resolver_class_name,
            $now + 1, // Min time to exclude initial record.
            $now + 3000, // Max time to include both new records.
            [
                ['program_id' => $data->program1->id, 'user_id' => $extra1->id, 'first_assigned' => $now + 2100],
                ['program_id' => $data->program1->id, 'user_id' => $extra2->id, 'first_assigned' => $now + 1100],
                ['program_id' => $data->program2->id, 'user_id' => $extra1->id, 'first_assigned' => $now + 1000],
            ]
        );

    }
}
