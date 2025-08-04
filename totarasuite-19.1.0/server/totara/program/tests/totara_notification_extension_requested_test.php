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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Nathaniel Walmsley <nathaniel.walmsley@totara.com>
 * @package totara_program
 */
use core\json_editor\helper\document_helper;
use core\json_editor\node\paragraph;
use totara_core\extended_context;
use totara_notification\entity\notifiable_event_queue;
use totara_notification\entity\notification_queue;
use totara_notification\external_helper;
use totara_notification\recipient\subject;
use totara_notification\task\process_event_queue_task;
use totara_notification\testing\generator as notification_generator;
use totara_program\totara_notification\resolver\extension_requested as extension_request_resolver;
use totara_notification\json_editor\node\placeholder;


defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/totara_notification_base.php');

/**
 * The purpose of this test is primarily to ensure that the extension_requested resolver is able to
 * collect the relevant options from its placeholders
 */
class totara_program_totara_notification_extension_requested_test extends totara_program_totara_notification_base {
    use \totara_notification\testing\notification_log_test_trait;

    public function test_resolver() {
        global $DB;

        $data = $this->setup_programs();
        $requested_extension_date_epoch_time = time();
        $extension_request_row_id = $DB->insert_record(
            'prog_extension',
            array(
                'programid' => $data->program1->id,
                'userid' => $data->user1->id,
                'extensiondate' => $requested_extension_date_epoch_time,
                'extensionreason' => 'blahblahblah',
                'status' => 0,
            )
        );
        // Create a custom notification in event context.
        $event_context = extended_context::make_with_context(
            context_program::instance($data->program1->id)
        );
        $notification_generator = notification_generator::instance();
        $preference = $notification_generator->create_notification_preference(
            extension_request_resolver::class,
            $event_context,
            [
                'schedule_offset' => 0,
                'recipients' => [subject::class],
                'body_format' => FORMAT_JSON_EDITOR,
                'body' => document_helper::json_encode_document(
                    document_helper::create_document_from_content_nodes([
                        paragraph::create_json_node_with_content_nodes([
                            placeholder::create_node_from_key_and_label(
                                'managers:first_name',
                                'The managers to whom this extension request is being sent'
                            ),
                            placeholder::create_node_from_key_and_label(
                                'subject:first_name',
                                'The user requesting an extension'
                            ),
                            placeholder::create_node_from_key_and_label(
                                'extension_request:status',
                                'Status of extension request'
                            ),
                        ]),
                    ])
                ),
                'subject' => 'Test notification subject',
                'subject_format' => FORMAT_PLAIN,
            ]
        );

        // Remove the 'assigned' notifiable event queue record.
        $DB->delete_records(notifiable_event_queue::TABLE);
        self::assertEquals(0, $DB->count_records(notifiable_event_queue::TABLE));
        self::assertEquals(0, $DB->count_records(notification_queue::TABLE));

        $resolver = new extension_request_resolver([
            'program_id' => $data->program1->id,
            'subject_user_id' => $data->user1->id,
            'extension_request_id' => $extension_request_row_id
        ]);
        external_helper::create_notifiable_event_queue($resolver);
        self::assertEquals(1, $DB->count_records(notifiable_event_queue::TABLE));
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

        // Confirm the message contains all the expected substitute strings
        self::assertStringContainsString('Manager1 first name, Manager2 first name', $message->fullmessage); // Managers to whom this request would be sent
        self::assertStringContainsString('User1 first name', $message->fullmessage); // The sender of this request
        self::assertStringContainsString('Request Pending', $message->fullmessage); // Status of request
        self::assertEquals($data->user1->id, $message->userto->id);

        // Check the logs
        $delivery_channels = json_decode($message->totara_notification_delivery_channels);
        self::verify_notification_logs([
            [
                'resolver_class_name' => extension_request_resolver::class,
                'context_id' => $event_context->get_context()->id,
                'logs' => [
                    [
                        'preference_id' => $preference->get_id(),
                        'recipients' => 1,
                        'channels' => count($delivery_channels),
                    ],
                ],
                'event_name' => get_string(
                    'notification_log_extension_request_resolver',
                    'totara_program',
                    [
                        'resolver_title' => extension_request_resolver::get_notification_title(),
                        'user' => 'User1 first name User1 last name',
                        'program' => 'My program1 full name',
                        'courseset' => 'Course set 2',
                        'date' => userdate($data->due_date->getTimestamp()),
                    ]
                )
            ],
        ]);
    }
}
