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
 * @author  Fabian Derschatta <fabian.derschatta@totaralearning.com>
 * @package totara_notification
 */

use core_phpunit\testcase;
use totara_core\extended_context;
use totara_notification\entity\notifiable_event_queue;
use totara_notification\testing\generator;

class totara_notification_notifiable_event_queue_repository_test extends testcase {

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

        /** @var generator $generator */
        $generator = self::getDataGenerator()->get_plugin_generator('totara_notification');
        $generator->include_mock_notifiable_event();
        $generator->include_mock_notifiable_event_resolver();
        $generator->add_mock_built_in_notification_for_component();

        $context_system = context_system::instance();

        $due_queue11 = new notifiable_event_queue();
        $due_queue11->resolver_class_name = totara_notification_mock_notifiable_event_resolver::class;
        $due_queue11->set_decoded_event_data([]);
        $due_queue11->set_extended_context(extended_context::make_with_context($category_context1));
        $due_queue11->save();

        $due_queue12 = new notifiable_event_queue();
        $due_queue12->resolver_class_name = totara_notification_mock_notifiable_event_resolver::class;
        $due_queue12->set_decoded_event_data([]);
        $due_queue12->set_extended_context(extended_context::make_with_context($course_context11));
        $due_queue12->save();

        $due_queue13 = new notifiable_event_queue();
        $due_queue13->resolver_class_name = totara_notification_mock_notifiable_event_resolver::class;
        $due_queue13->set_decoded_event_data([]);
        $due_queue13->set_extended_context(extended_context::make_with_context($course_context12));
        $due_queue13->save();

        $due_queue21 = new notifiable_event_queue();
        $due_queue21->resolver_class_name = totara_notification_mock_notifiable_event_resolver::class;
        $due_queue21->set_decoded_event_data([]);
        $due_queue21->set_extended_context(extended_context::make_with_context($category_context2));
        $due_queue21->save();

        $due_queue22 = new notifiable_event_queue();
        $due_queue22->resolver_class_name = totara_notification_mock_notifiable_event_resolver::class;
        $due_queue22->set_decoded_event_data([]);
        $due_queue22->set_extended_context(extended_context::make_with_context($course_context21));
        $due_queue22->save();

        $due_queue23 = new notifiable_event_queue();
        $due_queue23->resolver_class_name = totara_notification_mock_notifiable_event_resolver::class;
        $due_queue23->set_decoded_event_data([]);
        $due_queue23->set_extended_context(extended_context::make_with_context($course_context22));
        $due_queue23->save();

        $due_queue_system = new notifiable_event_queue();
        $due_queue_system->resolver_class_name = totara_notification_mock_notifiable_event_resolver::class;
        $due_queue_system->set_decoded_event_data([]);
        $due_queue_system->set_extended_context(extended_context::make_with_context($context_system));
        $due_queue_system->save();

        $repository = notifiable_event_queue::repository();

        $queue_records = notifiable_event_queue::repository()->get();
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

        $queue_records = notifiable_event_queue::repository()->get();
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