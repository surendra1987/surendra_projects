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
use totara_notification\entity\notification_queue;
use totara_notification\testing\generator;

class totara_notification_notification_queue_repository_test extends testcase {
    /**
     * @return void
     */
    public function test_get_queues_that_are_not_yet_due(): void {
        global $DB;

        $queue = new notification_queue();
        $queue->notification_preference_id = 4242;
        $queue->set_decoded_event_data([]);
        $queue->set_extended_context(extended_context::make_with_context(context_system::instance()));
        $queue->scheduled_time = 15;
        $queue->save();

        self::assertEquals(1, $DB->count_records(notification_queue::TABLE));

        $repository = notification_queue::repository();
        $collection = $repository->get_due_notification_queues(10);

        $records = $collection->to_array();
        $collection->close();

        self::assertEmpty($records);
        self::assertCount(0, $records);

        self::assertEquals(1, $DB->count_records(notification_queue::TABLE));
    }

    /**
     * @return void
     */
    public function test_get_queues_that_are_yet_due(): void {
        global $DB;

        $queue = new notification_queue();
        $queue->notification_preference_id = 4242;
        $queue->set_decoded_event_data([]);
        $queue->set_extended_context(extended_context::make_with_context(context_system::instance()));
        $queue->scheduled_time = 15;
        $queue->save();

        self::assertEquals(1, $DB->count_records(notification_queue::TABLE));

        $repository = notification_queue::repository();
        $collection = $repository->get_due_notification_queues(4242);

        $records = $collection->to_array();
        $collection->close();

        self::assertNotEmpty($records);
        self::assertCount(1, $records);

        /** @var notification_queue $first_record */
        $first_record = reset($records);

        self::assertInstanceOf(notification_queue::class, $first_record);
        self::assertEquals($queue->id, $first_record->id);

        self::assertEquals(1, $DB->count_records(notification_queue::TABLE));
    }

    /**
     * @return void
     */
    public function test_get_queues_with_mixed_records(): void {
        /** @var generator $generator */
        $generator = self::getDataGenerator()->get_plugin_generator('totara_notification');
        $system_built_in = $generator->add_mock_built_in_notification_for_component();

        $context_system = context_system::instance();

        $due_queue = new notification_queue();
        $due_queue->notification_preference_id = $system_built_in->get_id();
        $due_queue->scheduled_time = 15;

        $due_queue->set_decoded_event_data([]);
        $due_queue->set_extended_context(extended_context::make_with_context($context_system));
        $due_queue->save();

        $non_due_queue = new notification_queue();
        $non_due_queue->notification_preference_id = $system_built_in->get_id();
        $non_due_queue->scheduled_time = 21;

        $non_due_queue->set_decoded_event_data([]);
        $non_due_queue->set_extended_context(extended_context::make_with_context($context_system));
        $non_due_queue->save();

        $repository = notification_queue::repository();

        $collection = $repository->get_due_notification_queues(20);
        $records = $collection->to_array();

        $collection->close();
        self::assertCount(1, $records);

        // There should only be due queue returned from the repository.

        /** @var notification_queue $record */
        $record = reset($records);
        self::assertInstanceOf(notification_queue::class, $record);
        self::assertNotEquals($non_due_queue->id, $record->id);
        self::assertEquals($due_queue->id, $record->id);
    }

    /**
     * @return void
     */
    public function test_dequeue_entries_from_notification_queue(): void {
        $generator = self::getDataGenerator();

        $category1 = $generator->create_category();
        $category2 = $generator->create_category();

        $course11 = $generator->create_course(['category' => $category1->id]);
        $course12 = $generator->create_course(['category' => $category1->id]);

        $course21 = $generator->create_course(['category' => $category2->id]);
        $course22 = $generator->create_course(['category' => $category2->id]);

        $category_context1 = context_coursecat::instance($category1->id);
        $category_context2 = context_coursecat::instance($category2->id);

        $course_context11 = context_course::instance($course11->id);
        $course_context12 = context_course::instance($course12->id);

        $course_context21 = context_course::instance($course21->id);
        $course_context22 = context_course::instance($course22->id);

        $time = time();

        /** @var generator $generator */
        $generator = self::getDataGenerator()->get_plugin_generator('totara_notification');
        $system_built_in = $generator->add_mock_built_in_notification_for_component();

        $context_system = context_system::instance();

        $due_queue11 = new notification_queue();
        $due_queue11->notification_preference_id = $system_built_in->get_id();
        $due_queue11->scheduled_time = $time;
        $due_queue11->set_decoded_event_data([]);
        $due_queue11->set_extended_context(extended_context::make_with_context($category_context1));
        $due_queue11->save();

        $due_queue12 = new notification_queue();
        $due_queue12->notification_preference_id = $system_built_in->get_id();
        $due_queue12->scheduled_time = $time;
        $due_queue12->set_decoded_event_data([]);
        $due_queue12->set_extended_context(extended_context::make_with_context($course_context11));
        $due_queue12->save();

        $due_queue13 = new notification_queue();
        $due_queue13->notification_preference_id = $system_built_in->get_id();
        $due_queue13->scheduled_time = $time;
        $due_queue13->set_decoded_event_data([]);
        $due_queue13->set_extended_context(extended_context::make_with_context($course_context12));
        $due_queue13->save();

        $due_queue21 = new notification_queue();
        $due_queue21->notification_preference_id = $system_built_in->get_id();
        $due_queue21->scheduled_time = $time;
        $due_queue21->set_decoded_event_data([]);
        $due_queue21->set_extended_context(extended_context::make_with_context($category_context2));
        $due_queue21->save();

        $due_queue22 = new notification_queue();
        $due_queue22->notification_preference_id = $system_built_in->get_id();
        $due_queue22->scheduled_time = $time;
        $due_queue22->set_decoded_event_data([]);
        $due_queue22->set_extended_context(extended_context::make_with_context($course_context21));
        $due_queue22->save();

        $due_queue23 = new notification_queue();
        $due_queue23->notification_preference_id = $system_built_in->get_id();
        $due_queue23->scheduled_time = $time;
        $due_queue23->set_decoded_event_data([]);
        $due_queue23->set_extended_context(extended_context::make_with_context($course_context22));
        $due_queue23->save();

        $due_queue_system = new notification_queue();
        $due_queue_system->notification_preference_id = $system_built_in->get_id();
        $due_queue_system->scheduled_time = $time;
        $due_queue_system->set_decoded_event_data([]);
        $due_queue_system->set_extended_context(extended_context::make_with_context($context_system));
        $due_queue_system->save();

        $repository = notification_queue::repository();

        $queue_records = notification_queue::repository()->get();
        self::assertEquals(7, $queue_records->count());

        $context_ids = $queue_records->pluck('context_id');
        $this->assertEqualsCanonicalizing(
            [
                $category_context1->id,
                $course_context11->id,
                $course_context12->id,
                $category_context2->id,
                $course_context21->id,
                $course_context22->id,
                $context_system->id
            ],
            $context_ids
        );

        // Let's delete the records of the first category context which should also delete its children course context records
        $repository->dequeue(extended_context::make_with_context($category_context1));

        $queue_records = notification_queue::repository()->get();
        self::assertEquals(4, $queue_records->count());

        $context_ids = $queue_records->pluck('context_id');
        $this->assertEqualsCanonicalizing(
            [
                $category_context2->id,
                $course_context21->id,
                $course_context22->id,
                $context_system->id
            ],
            $context_ids
        );
    }
}