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
 * @package totara_notification
 */

use core_phpunit\testcase;
use totara_core\extended_context;
use totara_notification\entity\notifiable_event_queue;
use totara_notification\entity\notification_preference;
use totara_notification\testing\generator;
use totara_notification_mock_notifiable_event_resolver as mock_resolver;

class totara_notification_upgradelib_orphaned_queue_entries_test extends testcase {
    /**
     * @return void
     */
    protected function setUp(): void {
        global $CFG;
        require_once("{$CFG->dirroot}/totara/notification/db/upgradelib.php");

        $generator = generator::instance();
        $generator->include_mock_notifiable_event_resolver();
        $generator->include_mock_scheduled_aware_notifiable_event_resolver();
    }

    /**
     * @return void
     */
    public function test_orphaned_records_getting_removed(): void {
        $user = $this->getDataGenerator()->create_user();

        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $course3 = $this->getDataGenerator()->create_course();

        $course_context1 = context_course::instance($course1->id);
        $course_context2 = context_course::instance($course2->id);
        $course_context3 = context_course::instance($course3->id);

        /** @var generator $generator */
        $generator = self::getDataGenerator()->get_plugin_generator('totara_notification');

        $generator->create_notification_preference(
            'totara_notification_mock_notifiable_event_resolver',
            extended_context::make_with_context($course_context1),
            ['recipient' => totara_notification_mock_recipient::class, 'enabled' => false,]
        );
        $generator->create_notification_preference(
            'totara_notification_mock_notifiable_event_resolver',
            extended_context::make_with_context($course_context2),
            ['recipient' => totara_notification_mock_recipient::class, 'enabled' => false,]
        );
        $generator->create_notification_preference(
            'core_course\\totara_notification\\resolver\\user_unenrolled_resolver',
            extended_context::make_with_context($course_context2),
            ['recipient' => totara_notification_mock_recipient::class, 'enabled' => false,]
        );
        $generator->create_notification_preference(
            'totara_notification_mock_notifiable_event_resolver',
            extended_context::make_with_context($course_context3),
            ['recipient' => totara_notification_mock_recipient::class, 'enabled' => false,]
        );

        $valid_queue_record = new notifiable_event_queue();
        $valid_queue_record->set_extended_context(extended_context::make_with_context($course_context1));
        $valid_queue_record->set_decoded_event_data([
            'course_id' => $course1->id,
            'user_id' => $user->id,
        ]);
        $valid_queue_record->resolver_class_name = 'totara_notification_mock_notifiable_event_resolver';
        $valid_queue_record->save();

        $orphaned_queue_record = new notifiable_event_queue();
        $orphaned_queue_record->set_extended_context(extended_context::make_with_context($course_context2));
        $orphaned_queue_record->set_decoded_event_data([
            'course_id' => $course2->id,
            'user_id' => $user->id,
        ]);
        $orphaned_queue_record->resolver_class_name = 'core_course\\totara_notification\\resolver\\user_unenrolled_resolver';
        $orphaned_queue_record->save();

        $orphaned_queue_record = new notifiable_event_queue();
        $orphaned_queue_record->set_extended_context(extended_context::make_with_context($course_context2));
        $orphaned_queue_record->set_decoded_event_data([
            'course_id' => $course2->id,
            'user_id' => $user->id,
        ]);
        $orphaned_queue_record->resolver_class_name = 'totara_notification_mock_notifiable_event_resolver';
        $orphaned_queue_record->save();

        $orphaned_queue_record = new notifiable_event_queue();
        $orphaned_queue_record->set_extended_context(extended_context::make_with_context($course_context3));
        $orphaned_queue_record->set_decoded_event_data([
            'course_id' => $course3->id,
            'user_id' => $user->id,
        ]);
        $orphaned_queue_record->resolver_class_name = 'core_course\\totara_notification\\resolver\\user_unenrolled_resolver';
        $orphaned_queue_record->save();

        $resolvers = [
            'core_course\\totara_notification\\resolver\\user_unenrolled_resolver',
        ];

        // Let's do a first run with all records being valid
        totara_notification_remove_orphaned_entries($resolvers);

        $this->assertEquals(4, notifiable_event_queue::repository()->count());

        $count = notification_preference::repository()
            ->where('context_id', $course_context1->id)
            ->where('resolver_class_name', mock_resolver::class)
            ->count();
        $this->assertEquals(1, $count);

        $count = notification_preference::repository()
            ->where('context_id', $course_context1->id)
            ->where('resolver_class_name', 'totara_notification_mock_notifiable_event_resolver')
            ->count();
        $this->assertEquals(1, $count);

        $count = notification_preference::repository()
            ->where('context_id', $course_context2->id)
            ->where('resolver_class_name', 'totara_notification_mock_notifiable_event_resolver')
            ->count();
        $this->assertEquals(1, $count);

        $count = notification_preference::repository()
            ->where('context_id', $course_context2->id)
            ->where('resolver_class_name', 'core_course\\totara_notification\\resolver\\user_unenrolled_resolver')
            ->count();
        $this->assertEquals(1, $count);

        $count = notification_preference::repository()
            ->where('context_id', $course_context3->id)
            ->where('resolver_class_name', 'totara_notification_mock_notifiable_event_resolver')
            ->count();
        $this->assertEquals(1, $count);

        context_helper::delete_instance(CONTEXT_COURSE, $course2->id);

        // On this run the invalid one should be deleted
        totara_notification_remove_orphaned_entries($resolvers);

        // First let's check whether the preference got removed
        $this->assertEquals(
            1,
            notification_preference::repository()
                ->where('context_id', $course_context1->id)
                ->exists()
        );
        $this->assertEquals(
            1,
            notification_preference::repository()
                ->where('context_id', $course_context2->id)
                ->exists()
        );
        $this->assertEquals(
            1,
            notification_preference::repository()
                ->where('context_id', $course_context2->id)
                ->where('resolver_class_name', 'totara_notification_mock_notifiable_event_resolver')
                ->exists()
        );
        $this->assertEquals(
            1,
            notification_preference::repository()
                ->where('context_id', $course_context3->id)
                ->exists()
        );

        // Now let's check whether the correct queue records have been deleted
        $this->assertEquals(3, notifiable_event_queue::repository()->count());

        $this->assertEquals(
            1,
            notification_preference::repository()
                ->where('context_id', $course_context1->id)
                ->exists()
        );
        $this->assertEquals(
            1,
            notification_preference::repository()
                ->where('context_id', $course_context2->id)
                ->where('resolver_class_name', 'totara_notification_mock_notifiable_event_resolver')
                ->exists()
        );
        $this->assertEquals(
            1,
            notification_preference::repository()
                ->where('context_id', $course_context3->id)
                ->exists()
        );

        // Now run it again to check if repeated runs are working as expected
        totara_notification_remove_orphaned_entries($resolvers);

        // First let's check whether the preference got removed
        $this->assertEquals(
            1,
            notification_preference::repository()
                ->where('context_id', $course_context1->id)
                ->exists()
        );
        $this->assertEquals(
            1,
            notification_preference::repository()
                ->where('context_id', $course_context2->id)
                ->exists()
        );
        $this->assertEquals(
            1,
            notification_preference::repository()
                ->where('context_id', $course_context2->id)
                ->where('resolver_class_name', 'totara_notification_mock_notifiable_event_resolver')
                ->exists()
        );
        $this->assertEquals(
            1,
            notification_preference::repository()
                ->where('context_id', $course_context3->id)
                ->exists()
        );

        // Now let's check whether the correct queue records have been deleted
        $this->assertEquals(3, notifiable_event_queue::repository()->count());

        $this->assertEquals(
            1,
            notification_preference::repository()
                ->where('context_id', $course_context1->id)
                ->exists()
        );
        $this->assertEquals(
            1,
            notification_preference::repository()
                ->where('context_id', $course_context2->id)
                ->where('resolver_class_name', 'totara_notification_mock_notifiable_event_resolver')
                ->exists()
        );
        $this->assertEquals(
            1,
            notification_preference::repository()
                ->where('context_id', $course_context3->id)
                ->exists()
        );
    }

    /**
     * @return void
     */
    public function test_orphaned_records_getting_removed_multiple(): void {
        $user = $this->getDataGenerator()->create_user();

        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $course3 = $this->getDataGenerator()->create_course();

        $course_context1 = context_course::instance($course1->id);
        $course_context2 = context_course::instance($course2->id);
        $course_context3 = context_course::instance($course3->id);

        /** @var generator $generator */
        $generator = self::getDataGenerator()->get_plugin_generator('totara_notification');

        $generator->create_notification_preference(
            'totara_notification_mock_notifiable_event_resolver',
            extended_context::make_with_context($course_context1),
            ['recipient' => totara_notification_mock_recipient::class, 'enabled' => false,]
        );
        $generator->create_notification_preference(
            'totara_notification_mock_notifiable_event_resolver',
            extended_context::make_with_context($course_context2),
            ['recipient' => totara_notification_mock_recipient::class, 'enabled' => false,]
        );
        $generator->create_notification_preference(
            'core_course\\totara_notification\\resolver\\user_unenrolled_resolver',
            extended_context::make_with_context($course_context2),
            ['recipient' => totara_notification_mock_recipient::class, 'enabled' => false,]
        );
        $generator->create_notification_preference(
            'totara_notification_mock_notifiable_event_resolver',
            extended_context::make_with_context($course_context3),
            ['recipient' => totara_notification_mock_recipient::class, 'enabled' => false,]
        );

        $valid_queue_record = new notifiable_event_queue();
        $valid_queue_record->set_extended_context(extended_context::make_with_context($course_context1));
        $valid_queue_record->set_decoded_event_data([
            'course_id' => $course1->id,
            'user_id' => $user->id,
        ]);
        $valid_queue_record->resolver_class_name = 'totara_notification_mock_notifiable_event_resolver';
        $valid_queue_record->save();

        $orphaned_queue_record = new notifiable_event_queue();
        $orphaned_queue_record->set_extended_context(extended_context::make_with_context($course_context2));
        $orphaned_queue_record->set_decoded_event_data([
            'course_id' => $course2->id,
            'user_id' => $user->id,
        ]);
        $orphaned_queue_record->resolver_class_name = 'core_course\\totara_notification\\resolver\\user_unenrolled_resolver';
        $orphaned_queue_record->save();

        $orphaned_queue_record = new notifiable_event_queue();
        $orphaned_queue_record->set_extended_context(extended_context::make_with_context($course_context2));
        $orphaned_queue_record->set_decoded_event_data([
            'course_id' => $course2->id,
            'user_id' => $user->id,
        ]);
        $orphaned_queue_record->resolver_class_name = 'totara_notification_mock_notifiable_event_resolver';
        $orphaned_queue_record->save();

        $orphaned_queue_record = new notifiable_event_queue();
        $orphaned_queue_record->set_extended_context(extended_context::make_with_context($course_context3));
        $orphaned_queue_record->set_decoded_event_data([
            'course_id' => $course3->id,
            'user_id' => $user->id,
        ]);
        $orphaned_queue_record->resolver_class_name = 'core_course\\totara_notification\\resolver\\user_unenrolled_resolver';
        $orphaned_queue_record->save();

        $resolvers = [
            'totara_notification_mock_notifiable_event_resolver',
            'core_course\\totara_notification\\resolver\\user_unenrolled_resolver',
        ];

        context_helper::delete_instance(CONTEXT_COURSE, $course2->id);

        // On this run the invalid one should be deleted
        totara_notification_remove_orphaned_entries($resolvers);

        // First let's check whether the preference got removed
        $this->assertEquals(
            1,
            notification_preference::repository()
                ->where('context_id', $course_context1->id)
                ->exists()
        );
        $this->assertEquals(
            0,
            notification_preference::repository()
                ->where('context_id', $course_context2->id)
                ->exists()
        );
        $this->assertEquals(
            1,
            notification_preference::repository()
                ->where('context_id', $course_context3->id)
                ->exists()
        );

        // Now let's check whether the correct queue records have been deleted
        $this->assertEquals(2, notifiable_event_queue::repository()->count());

        $this->assertEquals(
            1,
            notification_preference::repository()
                ->where('context_id', $course_context1->id)
                ->exists()
        );
        $this->assertEquals(
            1,
            notification_preference::repository()
                ->where('context_id', $course_context3->id)
                ->exists()
        );

        // Now run it again to check if repeated runs are working as expected
        totara_notification_remove_orphaned_entries($resolvers);

        // First let's check whether the preference got removed
        $this->assertEquals(
            1,
            notification_preference::repository()
                ->where('context_id', $course_context1->id)
                ->exists()
        );
        $this->assertEquals(
            0,
            notification_preference::repository()
                ->where('context_id', $course_context2->id)
                ->exists()
        );
        $this->assertEquals(
            1,
            notification_preference::repository()
                ->where('context_id', $course_context3->id)
                ->exists()
        );

        // Now let's check whether the correct queue records have been deleted
        $this->assertEquals(2, notifiable_event_queue::repository()->count());

        $this->assertEquals(
            1,
            notification_preference::repository()
                ->where('context_id', $course_context1->id)
                ->exists()
        );
        $this->assertEquals(
            1,
            notification_preference::repository()
                ->where('context_id', $course_context3->id)
                ->exists()
        );
    }

    /**
     * @return void
     */
    public function test_no_resolvers_given(): void {
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('You have to provide specific resolvers to delete records for.');

        totara_notification_remove_orphaned_entries([]);
    }

}