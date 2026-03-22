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

use core\collection;
use core_phpunit\testcase;
use totara_core\extended_context;
use totara_notification\entity\notification_preference;
use totara_notification\testing\generator;
use totara_notification_mock_recipient as mock_recipient;
use totara_notification_mock_scheduled_aware_event_resolver as mock_resolver;

class totara_notification_notification_preference_repository_test extends testcase {

    /**
     * @return void
     */
    public function test_delete_entries_via_context(): void {
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
        $generator->include_mock_recipient();
        $generator->include_mock_scheduled_aware_notifiable_event_resolver();
        $generator->include_mock_built_in_notification();

        $context_system = context_system::instance();

        $generator->create_notification_preference(
            mock_resolver::class,
            extended_context::make_with_context($category_context1),
            [
                'recipient' => mock_recipient::class,
                'recipients' => [mock_recipient::class],
                'schedule_offset' => -1,
            ]
        );

        $generator->create_notification_preference(
            mock_resolver::class,
            extended_context::make_with_context($course_context11),
            [
                'recipient' => mock_recipient::class,
                'recipients' => [mock_recipient::class],
                'schedule_offset' => -1,
            ]
        );

        $generator->create_notification_preference(
            mock_resolver::class,
            extended_context::make_with_context($course_context12),
            [
                'recipient' => mock_recipient::class,
                'recipients' => [mock_recipient::class],
                'schedule_offset' => -1,
            ]
        );

        $generator->create_notification_preference(
            mock_resolver::class,
            extended_context::make_with_context($category_context2),
            [
                'recipient' => mock_recipient::class,
                'recipients' => [mock_recipient::class],
                'schedule_offset' => -1,
            ]
        );

        $generator->create_notification_preference(
            mock_resolver::class,
            extended_context::make_with_context($course_context21),
            [
                'recipient' => mock_recipient::class,
                'recipients' => [mock_recipient::class],
                'schedule_offset' => -1,
            ]
        );

        $generator->create_notification_preference(
            mock_resolver::class,
            extended_context::make_with_context($course_context22),
            [
                'recipient' => mock_recipient::class,
                'recipients' => [mock_recipient::class],
                'schedule_offset' => -1,
            ]
        );

        $repository = notification_preference::repository();

        $queue_records = notification_preference::repository()->get();

        $previous_count = $queue_records->count();

        $context_ids = (new collection($queue_records->pluck('context_id')))
            ->map('intval')
            ->to_array();
        $this->assertTrue(in_array($category_context1->id, $context_ids));
        $this->assertTrue(in_array($course_context11->id, $context_ids));
        $this->assertTrue(in_array($course_context12->id, $context_ids));
        $this->assertTrue(in_array($category_context2->id, $context_ids));
        $this->assertTrue(in_array($course_context21->id, $context_ids));
        $this->assertTrue(in_array($course_context22->id, $context_ids));
        $this->assertTrue(in_array($context_system->id, $context_ids));

        // Let's delete the records of the first category context which should also delete its children course context records
        $repository->delete_custom_by_context(extended_context::make_with_context($category_context1));

        $queue_records = notification_preference::repository()->get();

        $this->assertEquals($previous_count - 3, $queue_records->count());

        $context_ids = $queue_records->pluck('context_id');
        $this->assertFalse(in_array($category_context1->id, $context_ids));
        $this->assertFalse(in_array($course_context11->id, $context_ids));
        $this->assertFalse(in_array($course_context12->id, $context_ids));
        $this->assertTrue(in_array($category_context2->id, $context_ids));
        $this->assertTrue(in_array($course_context21->id, $context_ids));
        $this->assertTrue(in_array($course_context22->id, $context_ids));
        $this->assertTrue(in_array($context_system->id, $context_ids));
    }
}