<?php
/**
 * This file is part of Totara Perform
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package mod_hvp
 */

defined('MOODLE_INTERNAL') || die();

use core\orm\query\builder;
use core_phpunit\testcase;
use mod_hvp\testing\generator as hvp_generator;

require_once(__DIR__ . '/../autoloader.php');
require_once(__DIR__ . '/../lib.php');

class mod_hvp_lib_test extends testcase {
    public function test_hvp_archive_completion(): void {
        $user1_id = 789789;
        $hvp1_id = hvp_generator::instance()->create_h5p_record(1234567, ['course' => 121212]);
        $hvp2_id = hvp_generator::instance()->create_h5p_record(1234567, ['course' => 121212]);

        // Create target data.
        $delete_event1_id = builder::table('hvp_events')
            ->insert([
                'user_id' => $user1_id,
                'content_id' => $hvp1_id,
                'created_at' => 3333,
            ]);
        $delete_event2_id = builder::table('hvp_events')
            ->insert([
                'user_id' => $user1_id,
                'content_id' => $hvp2_id,
                'created_at' => 3333,
            ]);
        $delete_xapi_result1_id = builder::table('hvp_xapi_results')
            ->insert([
                'user_id' => $user1_id,
                'content_id' => $hvp1_id,
                'description' => 4444,
                'correct_responses_pattern' => 5555,
                'response' => 6666,
                'additionals' => 7777,
            ]);
        $delete_xapi_result2_id = builder::table('hvp_xapi_results')
            ->insert([
                'user_id' => $user1_id,
                'content_id' => $hvp2_id,
                'description' => 4444,
                'correct_responses_pattern' => 5555,
                'response' => 6666,
                'additionals' => 7777,
            ]);
        $delete_content_user_data1_id = builder::table('hvp_content_user_data')
            ->insert([
                'user_id' => $user1_id,
                'hvp_id' => $hvp1_id,
                'sub_content_id' => 0,
                'preloaded' => 1,
                'delete_on_content_change' => 0,
            ]);
        $delete_content_user_data2_id = builder::table('hvp_content_user_data')
            ->insert([
                'user_id' => $user1_id,
                'hvp_id' => $hvp2_id,
                'sub_content_id' => 0,
                'preloaded' => 1,
                'delete_on_content_change' => 0,
            ]);

        // Create control data.
        builder::table('hvp_events')
            ->insert([
                'user_id' => 8888,
                'content_id' => $hvp1_id,
                'created_at' => 3333,
            ]);
        builder::table('hvp_events')
            ->insert([
                'user_id' => $user1_id,
                'content_id' => 9999,
                'created_at' => 3333,
            ]);
        builder::table('hvp_xapi_results')
            ->insert([
                'user_id' => 8888,
                'content_id' => $hvp1_id,
                'description' => 4444,
                'correct_responses_pattern' => 5555,
                'response' => 6666,
                'additionals' => 7777,
            ]);
        builder::table('hvp_xapi_results')
            ->insert([
                'user_id' => $user1_id,
                'content_id' => 9999,
                'description' => 4444,
                'correct_responses_pattern' => 5555,
                'response' => 6666,
                'additionals' => 7777,
            ]);
        builder::table('hvp_content_user_data')
            ->insert([
                'user_id' => 8888,
                'hvp_id' => $hvp1_id,
                'sub_content_id' => 0,
                'preloaded' => 1,
                'delete_on_content_change' => 0,
            ]);
        builder::table('hvp_content_user_data')
            ->insert([
                'user_id' => $user1_id,
                'hvp_id' => 9999,
                'sub_content_id' => 0,
                'preloaded' => 1,
                'delete_on_content_change' => 0,
            ]);

        $expected_events = builder::table('hvp_events')->order_by('id')->get();
        $expected_xapi_results = builder::table('hvp_xapi_results')->order_by('id')->get();
        $expected_content_user_datas = builder::table('hvp_content_user_data')->order_by('id')->get();

        // Run the function.
        hvp_archive_completion($user1_id, 121212, 0);

        // Check the result.
        $result_events = builder::table('hvp_events')->order_by('id')->get();
        unset($expected_events[$delete_event1_id]);
        unset($expected_events[$delete_event2_id]);
        self::assertCount(2, $result_events);
        self::assertEquals($expected_events, $result_events);

        $result_xapi_results = builder::table('hvp_xapi_results')->order_by('id')->get();
        unset($expected_xapi_results[$delete_xapi_result1_id]);
        unset($expected_xapi_results[$delete_xapi_result2_id]);
        self::assertCount(2, $result_xapi_results);
        self::assertEquals($expected_xapi_results, $result_xapi_results);

        $result_content_user_datas = builder::table('hvp_content_user_data')->order_by('id')->get();
        unset($expected_content_user_datas[$delete_content_user_data1_id]);
        unset($expected_content_user_datas[$delete_content_user_data2_id]);
        self::assertCount(2, $result_content_user_datas);
        self::assertEquals($expected_content_user_datas, $result_content_user_datas);
    }
}
