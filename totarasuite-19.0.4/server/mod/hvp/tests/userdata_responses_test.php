<?php
/*
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
 * @author Nathan Lewis <nathan.lewis@totaralearning.com>
 * @package mod_hvp
 */

defined('MOODLE_INTERNAL') || die();

use core_phpunit\testcase;
use totara_userdata\userdata\target_user;
use mod_hvp\testing\generator as hvp_generator;
use mod_hvp\userdata\responses;

/**
 * @group totara_userdata
 */
class mod_hvp_userdata_responses_test extends testcase {

    /**
     * Runs the purge, export and count methods when no data exists in the system.
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_user_with_no_responses() {
        $course = $this->getDataGenerator()->create_course();
        hvp_generator::instance()->create_hvp($course->id);

        $user = $this->getDataGenerator()->create_user();

        // Purge doesn't fail.
        responses::execute_purge(
            new target_user($user),
            context_system::instance()
        );

        // Export contains no data.
        $export = responses::execute_export(
            new target_user($user),
            context_system::instance()
        );

        $this->assertEmpty($export->data);
        $this->assertEmpty($export->files);

        // Count is zero.
        $count = responses::execute_count(
            new target_user($user),
            context_system::instance()
        );

        self::assertEquals(0, $count);
    }

    /**
     * @throws dml_exception
     */
    private function create_multi_context_data(): stdClass {
        $hvp_generator = hvp_generator::instance();
        $data = new stdClass();

        $data->user1 = self::getDataGenerator()->create_user();
        $data->user2 = self::getDataGenerator()->create_user();

        $data->course1_id = self::getDataGenerator()->create_course()->id;
        $data->course2_id = self::getDataGenerator()->create_course()->id;
        $data->hvp1_id = $hvp_generator->create_hvp($data->course1_id);
        $data->hvp2_id = $hvp_generator->create_hvp($data->course1_id);
        $data->hvp3_id = $hvp_generator->create_hvp($data->course2_id);

        // First record.
        $data->user1_hvp1_current_user_data1_id = $hvp_generator->create_content_user_data(
            $data->user1->id,
            $data->hvp1_id
        );
        $data->user1_hvp1_event1_id = $hvp_generator->create_event(
            $data->user1->id,
            $data->hvp1_id
        );
        $data->user1_hvp1_xapi_result1_id = $hvp_generator->create_xapi_result(
            $data->user1->id,
            $data->hvp1_id
        );

        // Same user and activity.
        $data->user1_hvp1_current_user_data2_id = $hvp_generator->create_content_user_data(
            $data->user1->id,
            $data->hvp1_id
        );
        $data->user1_hvp1_event2_id = $hvp_generator->create_event(
            $data->user1->id,
            $data->hvp1_id
        );
        $data->user1_hvp1_xapi_result2_id = $hvp_generator->create_xapi_result(
            $data->user1->id,
            $data->hvp1_id
        );

        // Same user and course, different activity.
        $data->user1_hvp2_current_user_data3_id = $hvp_generator->create_content_user_data(
            $data->user1->id,
            $data->hvp2_id
        );
        $data->user1_hvp2_event3_id = $hvp_generator->create_event(
            $data->user1->id,
            $data->hvp2_id
        );
        $data->user1_hvp2_xapi_result3_id = $hvp_generator->create_xapi_result(
            $data->user1->id,
            $data->hvp2_id
        );

        // Same user different course.
        $data->user1_hvp3_current_user_data4_id = $hvp_generator->create_content_user_data(
            $data->user1->id,
            $data->hvp3_id
        );
        $data->user1_hvp3_event4_id = $hvp_generator->create_event(
            $data->user1->id,
            $data->hvp3_id
        );
        $data->user1_hvp3_xapi_result4_id = $hvp_generator->create_xapi_result(
            $data->user1->id,
            $data->hvp3_id
        );

        // Different user same activity.
        $data->user2_hvp1_current_user_data5_id = $hvp_generator->create_content_user_data(
            $data->user2->id,
            $data->hvp1_id
        );
        $data->user2_hvp1_event5_id = $hvp_generator->create_event(
            $data->user2->id,
            $data->hvp1_id
        );
        $data->user2_hvp1_xapi_result5_id = $hvp_generator->create_xapi_result(
            $data->user2->id,
            $data->hvp1_id
        );

        return $data;
    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_count_in_system_context(): void {
        $data = $this->create_multi_context_data();

        $count = responses::execute_count(
            new target_user($data->user1),
            context_system::instance()
        );

        // There are many records, but they belong to 3 activities.
        self::assertEquals(3, $count);
    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_export_in_system_context(): void {
        $data = $this->create_multi_context_data();

        $export = responses::execute_export(
            new target_user($data->user1),
            context_system::instance()
        );

        // Three hvp activities are returned.
        $this->assertEmpty($export->files);
        $this->assertCount(3, $export->data);

        foreach ($export->data as $hvp_id => $hvp) {
            switch ($hvp_id) {
                case $data->hvp1_id:
                    // The first hvp contains two events.
                    $this->assertCount(2, $hvp['content_user_data']);
                    $this->assertCount(2, $hvp['events']);
                    $this->assertCount(2, $hvp['xapi_results']);
                    break;
                case $data->hvp2_id:
                case $data->hvp3_id:
                    // The other two hvps contain one each.
                    $this->assertCount(1, $hvp['content_user_data']);
                    $this->assertCount(1, $hvp['events']);
                    $this->assertCount(1, $hvp['xapi_results']);
                    break;
                default:
                    self::fail('Unexpected hvp found');
            }

            // They all belong to the target user.
            foreach ($hvp['content_user_data'] as $event) {
                self::assertEquals($data->user1->id, $event->user_id);
            }
            foreach ($hvp['events'] as $event) {
                self::assertEquals($data->user1->id, $event->user_id);
            }
            foreach ($hvp['xapi_results'] as $event) {
                self::assertEquals($data->user1->id, $event->user_id);
            }
        }
    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_purge_in_system_context(): void {
        $data = $this->create_multi_context_data();

        // Delete in system context, should delete all records for user1.
        responses::execute_purge(
            new target_user($data->user1),
            context_system::instance()
        );

        // Nothing left for user1.
        $user1_count = responses::execute_count(
            new target_user($data->user1),
            context_system::instance()
        );
        self::assertEquals(0, $user1_count);

        // One left for user2, in course1.
        $user2_export = responses::execute_export(
            new target_user($data->user2),
            context_system::instance()
        );
        $this->assertCount(1, $user2_export->data);
        self::assertcount(1, $user2_export->data[$data->hvp1_id]['content_user_data']);
        self::assertcount(1, $user2_export->data[$data->hvp1_id]['events']);
        self::assertcount(1, $user2_export->data[$data->hvp1_id]['xapi_results']);
        self::assertEquals($data->user2->id, $user2_export->data[$data->hvp1_id]['content_user_data'][$data->user2_hvp1_current_user_data5_id]->user_id);
        self::assertEquals($data->user2->id, $user2_export->data[$data->hvp1_id]['events'][$data->user2_hvp1_event5_id]->user_id);
        self::assertEquals($data->user2->id, $user2_export->data[$data->hvp1_id]['xapi_results'][$data->user2_hvp1_xapi_result5_id]->user_id);
    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_count_in_course_context(): void {
        $data = $this->create_multi_context_data();

        $count = responses::execute_count(
            new target_user($data->user1),
            context_course::instance($data->course1_id)
        );

        // There are many records, but they belong to 2 activities.
        self::assertEquals(2, $count);
    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_export_in_course_context(): void {
        $data = $this->create_multi_context_data();

        $export = responses::execute_export(
            new target_user($data->user1),
            context_course::instance($data->course1_id)
        );

        // Two hvp activities are returned.
        $this->assertEmpty($export->files);
        $this->assertCount(2, $export->data);

        foreach ($export->data as $hvp_id => $hvp) {
            switch ($hvp_id) {
                case $data->hvp1_id:
                    // The first hvp contains two events.
                    $this->assertCount(2, $hvp['content_user_data']);
                    $this->assertCount(2, $hvp['events']);
                    $this->assertCount(2, $hvp['xapi_results']);
                    break;
                case $data->hvp2_id:
                    // The other hvp contains one.
                    $this->assertCount(1, $hvp['content_user_data']);
                    $this->assertCount(1, $hvp['events']);
                    $this->assertCount(1, $hvp['xapi_results']);
                    break;
                case $data->hvp3_id:
                default:
                    self::fail('Unexpected hvp found');
            }

            // They all belong to the target user.
            foreach ($hvp['content_user_data'] as $event) {
                self::assertEquals($data->user1->id, $event->user_id);
            }
            foreach ($hvp['events'] as $event) {
                self::assertEquals($data->user1->id, $event->user_id);
            }
            foreach ($hvp['xapi_results'] as $event) {
                self::assertEquals($data->user1->id, $event->user_id);
            }
        }
    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_purge_in_course_context(): void {
        $data = $this->create_multi_context_data();

        // Delete in course1 context, should delete three records for user1.
        responses::execute_purge(
            new target_user($data->user1),
            context_course::instance($data->course1_id)
        );

        // One left for user1, in course2.
        $user1_export = responses::execute_export(
            new target_user($data->user1),
            context_system::instance()
        );
        self::assertCount(1, $user1_export->data);
        self::assertcount(1, $user1_export->data[$data->hvp3_id]['content_user_data']);
        self::assertcount(1, $user1_export->data[$data->hvp3_id]['events']);
        self::assertcount(1, $user1_export->data[$data->hvp3_id]['xapi_results']);
        self::assertEquals($data->user1->id, $user1_export->data[$data->hvp3_id]['content_user_data'][$data->user1_hvp3_current_user_data4_id]->user_id);
        self::assertEquals($data->user1->id, $user1_export->data[$data->hvp3_id]['events'][$data->user1_hvp3_event4_id]->user_id);
        self::assertEquals($data->user1->id, $user1_export->data[$data->hvp3_id]['xapi_results'][$data->user1_hvp3_xapi_result4_id]->user_id);

        // Only one left for user2, in course1.
        $user2_export = responses::execute_export(
            new target_user($data->user2),
            context_system::instance()
        );
        $this->assertCount(1, $user2_export->data);
        self::assertcount(1, $user2_export->data[$data->hvp1_id]['content_user_data']);
        self::assertcount(1, $user2_export->data[$data->hvp1_id]['events']);
        self::assertcount(1, $user2_export->data[$data->hvp1_id]['xapi_results']);
        self::assertEquals($data->user2->id, $user2_export->data[$data->hvp1_id]['content_user_data'][$data->user2_hvp1_current_user_data5_id]->user_id);
        self::assertEquals($data->user2->id, $user2_export->data[$data->hvp1_id]['events'][$data->user2_hvp1_event5_id]->user_id);
        self::assertEquals($data->user2->id, $user2_export->data[$data->hvp1_id]['xapi_results'][$data->user2_hvp1_xapi_result5_id]->user_id);
    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_with_only_content_user_data(): void {
        $hvp_generator = hvp_generator::instance();

        $user = $this->getDataGenerator()->create_user();

        $course_id = self::getDataGenerator()->create_course()->id;
        $hvp_id = $hvp_generator->create_hvp($course_id);

        $hvp_generator->create_content_user_data(
            $user->id,
            $hvp_id
        );

        // Count still returns 1.
        self::assertEquals(1, responses::execute_count(
            new target_user($user),
            context_system::instance()
        ));

        // Export still returns one hvp.
        self::assertCount(1, responses::execute_export(
            new target_user($user),
            context_system::instance()
        )->data);

        // Purge still deletes the record.
        responses::execute_purge(
            new target_user($user),
            context_system::instance()
        );
        self::assertEquals(0, responses::execute_count(
            new target_user($user),
            context_system::instance()
        ));
    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_with_only_event(): void {
        $hvp_generator = hvp_generator::instance();

        $user = $this->getDataGenerator()->create_user();

        $course_id = self::getDataGenerator()->create_course()->id;
        $hvp_id = $hvp_generator->create_hvp($course_id);

        $hvp_generator->create_event(
            $user->id,
            $hvp_id
        );

        // Count still returns 1.
        self::assertEquals(1, responses::execute_count(
            new target_user($user),
            context_system::instance()
        ));

        // Export still returns one hvp.
        self::assertCount(1, responses::execute_export(
            new target_user($user),
            context_system::instance()
        )->data);

        // Purge still deletes the record.
        responses::execute_purge(
            new target_user($user),
            context_system::instance()
        );
        self::assertEquals(0, responses::execute_count(
            new target_user($user),
            context_system::instance()
        ));
    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_with_only_xapi_result(): void {
        $hvp_generator = hvp_generator::instance();

        $user = $this->getDataGenerator()->create_user();

        $course_id = self::getDataGenerator()->create_course()->id;
        $hvp_id = $hvp_generator->create_hvp($course_id);

        $hvp_generator->create_xapi_result(
            $user->id,
            $hvp_id
        );

        // Count still returns 1.
        self::assertEquals(1, responses::execute_count(
            new target_user($user),
            context_system::instance()
        ));

        // Export still returns one hvp.
        self::assertCount(1, responses::execute_export(
            new target_user($user),
            context_system::instance()
        )->data);

        // Purge still deletes the record.
        responses::execute_purge(
            new target_user($user),
            context_system::instance()
        );
        self::assertEquals(0, responses::execute_count(
            new target_user($user),
            context_system::instance()
        ));
    }
}