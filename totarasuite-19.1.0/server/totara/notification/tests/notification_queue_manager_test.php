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
 * @author  Kian Nguyen <kian.nguyen@totaralearning.com>
 * @package totara_notification
 */

use core_phpunit\testcase;
use totara_core\extended_context;
use totara_notification\entity\notifiable_event_preference as notifiable_event_preference_entity;
use totara_notification\entity\notification_queue;
use totara_notification\manager\notification_queue_manager;
use totara_notification\observer\notifiable_event_observer;
use totara_notification\task\process_event_queue_task;
use totara_notification\testing\generator as notification_generator;
use totara_notification_mock_notifiable_event_resolver as mock_resolver;
use totara_notification_mock_notifiable_event as mock_event;
use totara_notification_mock_built_in_notification as mock_built_in;
use totara_notification_real_mock_lang_string as lang_string;
use totara_tenant\testing\generator as tenant_generator;

class totara_notification_notification_queue_manager_test extends testcase {
    use \core_phpunit\language_pack_faker_trait;
    use \totara_notification\testing\notification_log_test_trait;

    /**
     * @return void
     */
    protected function setUp(): void {
        parent::setUp();
        $notification_generator = notification_generator::instance();
        $notification_generator->include_mock_notifiable_event_resolver();
        $notification_generator->add_mock_built_in_notification_for_component();
    }

    /**
     * @return void
     */
    public function test_dispatch_valid_queues_with_invalid_queues(): void {
        global $DB;

        $generator = self::getDataGenerator();
        $user_one = $generator->create_user();
        $context_user = context_user::instance($user_one->id);

        $notification_generator = notification_generator::instance();
        $notification_generator->add_mock_recipient_ids_to_resolver([$user_one->id]);

        $system_built_in = $notification_generator->add_mock_built_in_notification_for_component();
        $notification_generator->include_mock_recipient();

        // Create a valid queue.
        $valid_queue = new notification_queue();
        $valid_queue->set_decoded_event_data([
            'message' => 'This is message',
            'expected_context_id' => context_system::instance()->id,
        ]);
        $valid_queue->notification_preference_id = $system_built_in->get_id();
        $valid_queue->scheduled_time = 10;
        $valid_queue->set_extended_context(extended_context::make_with_context($context_user));
        $valid_queue->save();


        // Create an invalid queue. To create an invalid record, we need to first
        // create the preference then delete it.
        $custom_preference = $notification_generator->create_notification_preference(
            mock_resolver::class,
            extended_context::make_with_context($context_user),
            ['recipient' => totara_notification_mock_recipient::class]
        );

        $invalid_queue = new notification_queue();
        $invalid_queue->set_decoded_event_data(['message' => 'This is invalid message']);
        $invalid_queue->notification_preference_id = $custom_preference->get_id();
        $invalid_queue->scheduled_time = 10;
        $invalid_queue->set_extended_context(extended_context::make_with_context($context_user));
        $invalid_queue->save();

        $custom_preference->delete();

        $trace = $notification_generator->get_test_progress_trace();
        $manager = new notification_queue_manager($trace);

        // 2 records at this point.
        self::assertEquals(2, $DB->count_records(notification_queue::TABLE));

        $sink = $this->redirectMessages();
        self::assertEquals(0, $sink->count());
        self::assertEmpty($sink->get_messages());

        // Check that the resolver sent call counts are zero before we start.
        mock_resolver::reset_notification_not_sent_call_count();
        mock_resolver::reset_notification_sent_call_count();

        $manager->dispatch_queues(15);

        // Check the queue is empty after sending the notifications. errors will add into the trace
        self::assertEquals(0, $DB->count_records(notification_queue::TABLE));

        // Check that only "sent" was called.
        $this->assertEquals(0, mock_resolver::get_notification_not_sent_call_count());
        $this->assertGreaterThan(0, mock_resolver::get_notification_sent_call_count());

        // There is one message sent out
        $notifications = $sink->get_messages();
        self::assertCount(1, $notifications);

        self::assertNotEmpty($notifications);
        self::assertCount(1, $notifications);

        $first_notification = reset($notifications);
        self::assertIsObject($first_notification);
        self::assertObjectHasProperty('fullmessage', $first_notification);
        self::assertEquals(
            totara_notification_mock_built_in_notification::get_default_body()->out(),
            $first_notification->fullmessage
        );

        self::assertObjectHasProperty('subject', $first_notification);
        self::assertEquals(
            totara_notification_mock_built_in_notification::get_default_subject()->out(),
            $first_notification->subject
        );

        // Check the logs
        $delivery_channels = json_decode($first_notification->totara_notification_delivery_channels);
        self::verify_notification_logs([
            [
                'context_id' => context_system::instance()->id,
                'logs' => [
                    [
                        'preference_id' => $valid_queue->notification_preference_id,
                        'recipients' => 1,
                        'channels' => count($delivery_channels),
                    ],
                ],
            ],
        ]);

        // There should be only one error message for fail record
        $error_messages = $trace->get_messages();
        self::assertNotEmpty($error_messages);
        self::assertCount(1, $error_messages);

        $first_message = reset($error_messages);
        self::assertEquals(
            "The notification preference record with id '{$invalid_queue->notification_preference_id}' does not exist",
            $first_message
        );
    }

    /**
     * @return void
     */
    public function test_dispatch_respects_recipients_language(): void {
        $generator = notification_generator::instance();
        $generator->include_mock_notifiable_event();
        $generator->include_mock_built_in_notification();
        $generator->include_real_mock_lang_string();

        $fake_language = 'xz';
        $this->add_fake_language_pack($fake_language, [
            'totara_notification' => [
                'notification_subject_label' => 'Fake Subject',
                'notification_body_label' => 'Fake Body'
            ],
        ]);

        $generator = self::getDataGenerator();
        $user1 = $generator->create_user();
        $user2 = $generator->create_user(['lang' => $fake_language]);

        $user_one_create_event = new mock_event(
            context_user::instance($user2->id)->id,
            ['recipient_id' => $user2->id]
        );

        $user_two_create_event = new mock_event(
            context_user::instance($user1->id)->id,
            ['recipient_id' => $user1->id]
        );

        notifiable_event_observer::watch_notifiable_event($user_one_create_event);
        notifiable_event_observer::watch_notifiable_event($user_two_create_event);

        mock_resolver::set_recipient_ids_resolver(
            function (array $event_data): array {
                return [$event_data['recipient_id']];
            }
        );

        mock_built_in::set_default_body(new lang_string('notification_body_label', 'totara_notification'));
        mock_built_in::set_default_subject(new lang_string('notification_subject_label', 'totara_notification'));

        $sink = self::redirectMessages();
        $task = new process_event_queue_task();
        $task->execute();

        self::assertEquals(2, $sink->count());
        $actual = array_map(function (stdClass $message) {
            return [
                'userid' => $message->useridto,
                'subject' => $message->subject,
                'body' => trim($message->fullmessage),
            ];
        }, $sink->get_messages());
        self::assertEqualsCanonicalizing([
            [
                'userid' => (int) $user1->id,
                'subject' => 'Subject',
                'body' => 'Body',
            ],
            [
                'userid' => (int)$user2->id,
                'subject' => 'Fake Subject',
                'body' => 'Fake Body',
            ],
        ], $actual);
    }

    /**
     * @return void
     */
    public function test_dispatch_skips_disabled_preferences(): void {
        global $DB;

        $generator = self::getDataGenerator();
        $user_one = $generator->create_user();
        $context_user = context_user::instance($user_one->id);

        $notification_generator = notification_generator::instance();
        $notification_generator->add_mock_recipient_ids_to_resolver([$user_one->id]);

        $system_built_in = $notification_generator->add_mock_built_in_notification_for_component();
        $notification_generator->include_mock_recipient();

        // Create a valid queue.
        $valid_queue = new notification_queue();
        $valid_queue->set_decoded_event_data([
            'message' => 'This is message',
            'expected_context_id' => context_system::instance()->id,
        ]);
        $valid_queue->notification_preference_id = $system_built_in->get_id();
        $valid_queue->scheduled_time = 10;
        $valid_queue->set_extended_context(extended_context::make_with_context($context_user));
        $valid_queue->save();

        // Create an invalid queue. To create an invalid record, we need to first
        // create the preference then delete it.
        $disabled_preference = $notification_generator->create_notification_preference(
            mock_resolver::class,
            extended_context::make_with_context($context_user),
            ['recipient' => totara_notification_mock_recipient::class, 'enabled' => false]
        );

        $invalid_queue = new notification_queue();
        $invalid_queue->set_decoded_event_data(['message' => 'This is invalid message']);
        $invalid_queue->notification_preference_id = $disabled_preference->get_id();
        $invalid_queue->scheduled_time = 10;
        $invalid_queue->set_extended_context(extended_context::make_with_context($context_user));
        $invalid_queue->save();

        $trace = $notification_generator->get_test_progress_trace();
        $manager = new notification_queue_manager($trace);

        // 2 records at this point.
        self::assertEquals(2, $DB->count_records(notification_queue::TABLE));

        $sink = $this->redirectMessages();
        self::assertEquals(0, $sink->count());
        self::assertEmpty($sink->get_messages());

        // Check that the resolver sent call counts are zero before we start.
        mock_resolver::reset_notification_not_sent_call_count();
        mock_resolver::reset_notification_sent_call_count();

        $manager->dispatch_queues(15);

        // Check the queue is empty after sending the notifications. errors will add into the trace
        self::assertEquals(0, $DB->count_records(notification_queue::TABLE));

        // Check that both "sent" and "not sent" functions were called.
        $this->assertGreaterThan(0, mock_resolver::get_notification_not_sent_call_count());
        $this->assertGreaterThan(0, mock_resolver::get_notification_sent_call_count());

        // There is one message sent out
        $notifications = $sink->get_messages();
        self::assertCount(1, $notifications);

        self::assertNotEmpty($notifications);
        self::assertCount(1, $notifications);

        $first_notification = reset($notifications);
        self::assertIsObject($first_notification);
        self::assertObjectHasProperty('fullmessage', $first_notification);
        self::assertEquals(
            totara_notification_mock_built_in_notification::get_default_body()->out(),
            $first_notification->fullmessage
        );

        self::assertObjectHasProperty('subject', $first_notification);
        self::assertEquals(
            totara_notification_mock_built_in_notification::get_default_subject()->out(),
            $first_notification->subject
        );

        // Check the logs
        $delivery_channels = json_decode($first_notification->totara_notification_delivery_channels);
        self::verify_notification_logs([
            [
                'context_id' => context_system::instance()->id,
                'logs' => [
                    [
                        'preference_id' => $valid_queue->notification_preference_id,
                        'recipients' => 1,
                        'channels' => count($delivery_channels),
                    ],
                ],
            ],
        ]);
    }

    /**
     * @return void
     */
    public function test_dispatch_skips_disabled_resolvers(): void {
        global $DB;

        $generator = self::getDataGenerator();
        $user_one = $generator->create_user();
        $context_user = context_user::instance($user_one->id);

        $notification_generator = notification_generator::instance();
        $notification_generator->add_mock_recipient_ids_to_resolver([$user_one->id]);

        $system_built_in = $notification_generator->add_mock_built_in_notification_for_component();
        $notification_generator->include_mock_recipient();

        // Create a valid queue.
        $valid_queue = new notification_queue();
        $valid_queue->set_decoded_event_data([
            'message' => 'This is a message',
            'expected_context_id' => context_system::instance()->id,
        ]);
        $valid_queue->notification_preference_id = $system_built_in->get_id();
        $valid_queue->scheduled_time = 10;
        $valid_queue->set_extended_context(extended_context::make_with_context($context_user));
        $valid_queue->save();

        // Disable the resolver.
        $extended_context = extended_context::make_system();
        $entity = new notifiable_event_preference_entity();
        $entity->context_id = $extended_context->get_context_id();
        $entity->resolver_class_name = mock_resolver::class;
        $entity->component = $extended_context->get_component();
        $entity->area = $extended_context->get_area();
        $entity->item_id = $extended_context->get_item_id();
        $entity->default_delivery_channels = ",email,";
        $entity->enabled = false;
        $entity->save();

        // One record at this point.
        self::assertEquals(1, $DB->count_records(notification_queue::TABLE));

        // Set up notification capture.
        $sink = $this->redirectMessages();
        self::assertEquals(0, $sink->count());
        self::assertEmpty($sink->get_messages());

        // Process the queue.
        $trace = $notification_generator->get_test_progress_trace();
        $manager = new notification_queue_manager($trace);
        $manager->dispatch_queues(15);

        // Check the queue is empty after sending the notifications.
        self::assertEquals(0, $DB->count_records(notification_queue::TABLE));

        // There were no messages sent out.
        $notifications = $sink->get_messages();
        self::assertCount(0, $notifications);

        // Check the logs
        self::verify_notification_logs([]);
    }

    /**
     * Tests that dispatch_from_preference excludes sending when subject or recipient is suspended or deleted
     * or a member of a suspended tenant.
     *
     * @return void
     */
    public function test_dispatch_from_preference_excludes_suspended_users(): void {
        $generator = self::getDataGenerator();

        $tenant_generator = tenant_generator::instance();
        $tenant_generator->enable_tenants();

        $tenant_active = $tenant_generator->create_tenant();
        $tenant_suspended = $tenant_generator->create_tenant(['suspended' => 1]);

        $subject_system_active = $generator->create_user(); // Notification should be sent.
        $subject_system_suspended = $generator->create_user(['suspended' => 1]); // Notification should NOT be sent.
        $subject_system_deleted = $generator->create_user(['deleted' => 1]); // Notification should NOT be sent.
        $subject_tenant_active = $generator->create_user(['tenantid' => $tenant_active->id]); // Notification should be sent.
        $subject_tenant_suspended = $generator->create_user(['tenantid' => $tenant_suspended->id]); // Notification should NOT be sent.
        $recipient_system_active = $generator->create_user(); // Should receive notification.
        $recipient_system_suspended = $generator->create_user(['suspended' => 1]); // Should NOT receive notification.
        $recipient_system_deleted = $generator->create_user(['deleted' => 1]); // Should NOT receive notification.
        $recipient_tenant_active = $generator->create_user(['tenantid' => $tenant_active->id]); // Should receive notification.
        $recipient_tenant_suspended = $generator->create_user(['tenantid' => $tenant_suspended->id]); // Should NOT receive notification.

        $notification_generator = notification_generator::instance();
        $notification_generator->add_mock_recipient_ids_to_resolver([
            $recipient_system_active->id,
            $recipient_system_suspended->id,
            $recipient_system_deleted->id,
            $recipient_tenant_active->id,
            $recipient_tenant_suspended->id,
        ]);

        $notif_pref = $notification_generator->add_mock_built_in_notification_for_component();
        $manager = new notification_queue_manager();

        // Test with active system subject.
        $sink = $this->redirectMessages();
        self::assertCount(0, $sink->get_messages());

        $manager->dispatch_from_preference($notif_pref, [
            'user_id' => $subject_system_active->id,
            'expected_context_id' => context_system::instance()->id,
        ], 0);

        $notifications = $sink->get_messages();
        self::assertCount(2, $notifications);
        $actual_recipients = array_map(function ($notification) {
                return $notification->userto->id;
            },
            $notifications);
        self::assertEqualsCanonicalizing([$recipient_system_active->id, $recipient_tenant_active->id], $actual_recipients);

        // Test with suspended system subject.
        $sink = $this->redirectMessages();
        self::assertCount(0, $sink->get_messages());

        $manager->dispatch_from_preference($notif_pref, [
            'user_id' => $subject_system_suspended->id,
            'expected_context_id' => context_system::instance()->id,
        ], 0);

        self::assertCount(0, $sink->get_messages());

        // Test with deleted system subject.
        $sink = $this->redirectMessages();
        self::assertCount(0, $sink->get_messages());

        $manager->dispatch_from_preference($notif_pref, [
            'user_id' => $subject_system_deleted->id,
            'expected_context_id' => context_system::instance()->id,
        ], 0);

        self::assertCount(0, $sink->get_messages());

        // Test with active tenant.
        $sink = $this->redirectMessages();
        self::assertCount(0, $sink->get_messages());

        $manager->dispatch_from_preference($notif_pref, [
            'user_id' => $subject_tenant_active->id,
            'expected_context_id' => context_system::instance()->id,
        ], 0);

        $notifications = $sink->get_messages();
        self::assertCount(2, $notifications);
        $actual_recipients = array_map(function ($notification) {
            return $notification->userto->id;
        },
            $notifications);
        self::assertEqualsCanonicalizing([$recipient_system_active->id, $recipient_tenant_active->id], $actual_recipients);

        // Test with suspended tenant.
        $sink = $this->redirectMessages();
        self::assertCount(0, $sink->get_messages());

        $manager->dispatch_from_preference($notif_pref, [
            'user_id' => $subject_tenant_suspended->id,
            'expected_context_id' => context_system::instance()->id,
        ], 0);

        self::assertCount(0, $sink->get_messages());
    }

    /**
     * Tests that dispatch_from_preference excludes sending notifications to users who have notifications completely disabled.
     */
    public function test_dispatch_from_preference_excludes_email_stop_users(): void {
        global $DB;

        $generator = self::getDataGenerator();

        $subject_user = $generator->create_user();
        $recipient_system_enabled = $generator->create_user(['emailstop' => 0]); // User should get a notification
        $recipient_system_disabled = $generator->create_user(['emailstop' => 1]); // User should not get a notification

        $notification_generator = notification_generator::instance();
        $notification_generator->add_mock_recipient_ids_to_resolver([
            $recipient_system_enabled->id,
            $recipient_system_disabled->id,
        ]);

        $notif_pref = $notification_generator->add_mock_built_in_notification_for_component();
        $manager = new notification_queue_manager();

        $sink = $this->redirectMessages();
        self::assertCount(0, $sink->get_messages());

        $manager->dispatch_from_preference($notif_pref, [
            'user_id' => $subject_user->id,
            'expected_context_id' => context_system::instance()->id,
        ], 0);

        $notifications = $sink->get_messages();
        self::assertCount(1, $notifications);
        $actual_recipients = array_map(function ($notification) {
            return $notification->userto->id;
        }, $notifications);

        self::assertTrue(in_array($recipient_system_enabled->id, $actual_recipients));
        self::assertFalse(in_array($recipient_system_disabled->id, $actual_recipients));

        // Now enalble them and send again
        $DB->update_record('user', ['id' => $recipient_system_disabled->id, 'emailstop' => 0]);

        $sink = $this->redirectMessages();
        self::assertCount(0, $sink->get_messages());

        $manager->dispatch_from_preference($notif_pref, [
            'user_id' => $subject_user->id,
            'expected_context_id' => context_system::instance()->id,
        ], 0);

        $notifications = $sink->get_messages();
        self::assertCount(2, $notifications);
        $actual_recipients = array_map(function ($notification) {
            return $notification->userto->id;
        }, $notifications);

        self::assertTrue(in_array($recipient_system_enabled->id, $actual_recipients));
        self::assertTrue(in_array($recipient_system_disabled->id, $actual_recipients));
    }
}