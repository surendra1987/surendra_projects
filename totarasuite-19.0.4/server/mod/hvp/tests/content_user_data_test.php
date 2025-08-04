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

use core_phpunit\testcase;
use mod_hvp\content_user_data;
use mod_hvp\testing\generator as hvp_generator;

require_once(__DIR__ . '/../autoloader.php');

class mod_hvp_content_user_data_test extends testcase {

    /**
     * Check that store_user_data inserts the correct data when appropriate.
     * @throws dml_exception
     */
    public function test_save_user_data_calls_insert_record(): void {
        global $DB;

        $user1 = self::getDataGenerator()->create_user();
        self::setUser($user1->id);
        $hvp_generator = hvp_generator::instance();

        // Control record #1 - different user.
        $hvp_generator->create_content_user_data(4444, 1111);

        // Control record #2 - different contentid/hvp_id.
        $hvp_generator->create_content_user_data($user1->id, 5555);

        // Control record #3 - different sub_content_id.
        $hvp_generator->create_content_user_data($user1->id, 1111);

        // Control record #2 - different data_id.
        $hvp_generator->create_content_user_data($user1->id, 1111);

        $control_records = $DB->get_records('hvp_content_user_data');

        // Run the function.
        content_user_data::save_user_data(
            1111,
            2222,
            3333,
            1,
            1,
            'some test data t1'
        );

        // Check the results.
        $result_records = $DB->get_records('hvp_content_user_data', [], 'id DESC');
        self::assertCount(count($control_records) + 1, $result_records);

        // The last record must be the newest, which is the one that the function call created.
        $target_record = reset($result_records);
        $expected_record = (object)[
            'id' => $target_record->id,
            'user_id' => $user1->id,
            'hvp_id' => 1111,
            'sub_content_id' => 2222,
            'data_id' => 3333,
            'data' => 'some test data t1',
            'preloaded' => 1,
            'delete_on_content_change' => 1
        ];
        self::assertEquals($expected_record, $target_record);

        // Check that the control records were not modified.
        unset($result_records[$target_record->id]);
        self::assertEqualsCanonicalizing($control_records, $result_records);
    }

    /**
     * Check that store_user_data updates the correct data when appropriate.
     *
     * @throws dml_exception
     */
    public function test_save_user_data_calls_update_record(): void {
        global $DB;

        $user1 = self::getDataGenerator()->create_user();
        self::setUser($user1->id);
        $hvp_generator = hvp_generator::instance();

        // Target record.
        $target_id = $hvp_generator->create_content_user_data($user1->id, 1111, [
            'sub_content_id' => 2222,
            'data_id' => 3333,
        ]);

        // Control record #1 - different user.
        $hvp_generator->create_content_user_data(4444, 1111);

        // Control record #2 - different contentid/hvp_id.
        $hvp_generator->create_content_user_data($user1->id, 5555);

        // Control record #3 - different sub_content_id.
        $hvp_generator->create_content_user_data($user1->id, 1111);

        // Control record #2 - different data_id.
        $hvp_generator->create_content_user_data($user1->id, 1111);

        $initial_records = $DB->get_records('hvp_content_user_data', [], 'id');

        // Run the function.
        content_user_data::save_user_data(
            1111,
            2222,
            3333,
            0, // Changed.
            0, // Changed.
            'some test data t1 - updated' // Changed.
        );

        // Check the results.
        $result_records = $DB->get_records('hvp_content_user_data', [], 'id');
        self::assertCount(count($initial_records), $result_records);

        // Check that the values in the target record were updated.
        $target_record = $result_records[$target_id];
        $expected_record = (object)[
            'id' => $target_id,
            'user_id' => $user1->id,
            'hvp_id' => 1111,
            'sub_content_id' => 2222,
            'data_id' => 3333,
            'data' => 'some test data t1 - updated',
            'preloaded' => 0,
            'delete_on_content_change' => 0
        ];
        self::assertEquals($expected_record, $target_record);

        // Check that the control records were not modified.
        $initial_records[$target_id]->preloaded = 0;
        $initial_records[$target_id]->delete_on_content_change = 0;
        $initial_records[$target_id]->data = 'some test data t1 - updated';
        self::assertEquals($initial_records, $result_records); // Not canonicalising, because we set order.
    }

    /**
     * Check that delete_user_data deletes the correct data.
     *
     * @throws dml_exception
     */
    public function test_delete_user_data(): void {
        global $DB;

        $user1 = self::getDataGenerator()->create_user();
        self::setUser($user1->id);
        $hvp_generator = hvp_generator::instance();

        // Target record #1.
        $cud1_id = $hvp_generator->create_content_user_data($user1->id, 1111, [
            'sub_content_id' => 2222,
            'data_id' => 3333,
        ]);

        // Target record #2.
        $cud2_id = $hvp_generator->create_content_user_data($user1->id, 1111, [
            'sub_content_id' => 2222,
            'data_id' => 3333,
        ]);

        // Control record #1 - different user.
        $hvp_generator->create_content_user_data(4444, 1111);

        // Control record #2 - different content/hvp_id.
        $hvp_generator->create_content_user_data(4444, 5555);

        // Control record #3 - different sub_content_id.
        $hvp_generator->create_content_user_data(4444, 1111);

        // Prepare expected result - everything except the record to be deleted.
        $expected = $DB->get_records_select(
            'hvp_content_user_data',
            'id != :deleteid1 AND id != :deleteid2',
            ['deleteid1' => $cud1_id, 'deleteid2' => $cud2_id]
        );

        // Run the function.
        content_user_data::delete_user_data(1111, 2222, 3333);

        // Check the results.
        $result = $DB->get_records('hvp_content_user_data');
        self::assertEqualsCanonicalizing($expected, $result);
    }

    /**
     * Check that load_pre_loaded_user_data loads the correct data.
     *
     * @throws dml_exception
     */
    public function test_load_pre_loaded_user_data(): void {
        $user1 = self::getDataGenerator()->create_user();
        self::setUser($user1->id);
        $hvp_generator = hvp_generator::instance();

        // Target record #1.
        $hvp_generator->create_content_user_data($user1->id, 1111, [
            'sub_content_id' => 0,
            'data_id' => 2222,
            'data' => 'some test data t1',
            'preloaded' => 1,
        ]);

        // Target record #2.
        $hvp_generator->create_content_user_data($user1->id, 1111, [
            'sub_content_id' => 0,
            'data_id' => 3333,
            'data' => 'some test data t2',
            'preloaded' => 1,
        ]);

        // Control record #1 - wrong user.
        $hvp_generator->create_content_user_data(4444, 1111);

        // Control record #2 - wrong content/hvp_id.
        $hvp_generator->create_content_user_data($user1->id, 5555);

        // Control record #3 - wrong sub_content_id.
        $hvp_generator->create_content_user_data($user1->id, 1111);

        // Control record #4 - wrong preloaded.
        $hvp_generator->create_content_user_data($user1->id, 1111);

        // Run the function.
        $result = content_user_data::load_pre_loaded_user_data(1111);

        // Check the results.
        $expected = [
            'state' => '{}',
            2222 => 'some test data t1',
            3333 => 'some test data t2',
        ];
        self::assertEqualsCanonicalizing($expected, $result);
    }
}
