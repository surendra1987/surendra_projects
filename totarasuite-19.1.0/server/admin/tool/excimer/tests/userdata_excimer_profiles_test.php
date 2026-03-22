<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Maria Torres <maria.torres@totara.com>
 * @package tool_excimer
 */
defined('MOODLE_INTERNAL') || die();

use tool_excimer\profile;
use tool_excimer\flamed3_node;
use totara_userdata\userdata\target_user;

class tool_excimer_userdata_excimer_profiles_test extends \core_phpunit\testcase {

    /**
     * Create profile
     *
     * @param array $data
     * @return int
     * @throws coding_exception
     * @throws dml_exception
     */
    private function create_profile(array $data): int {
        // Set $USER to the user passed in so profile userid column can be filled.
        self::setUser($data['user']);
        $time = time();

        $profile = new profile();
        $profile->add_env($data['request']);
        $profile->set('reason', profile::REASON_FLAMEME);
        $profile->set('parameters', $data['parameters']);
        $profile->set('duration', $data['duration']);
        $profile->set('courseid', $data['courseid']);
        $profile->set('timecreated', $time);
        $profile->set('lockreason', $data['lockreason']);
        $profile->set('usermodified', $data['usermodified']);
        $profile->set('timemodified', $time - 86400);
        $profile->set('created', $time);
        $profile->set('finished', 0);

        return $profile->save_record();
    }

    /**
     * Create Excimer profile data to test.
     *
     * @return stdClass With the data generated to test
     * @throws coding_exception
     * @throws dml_exception
     */
    private function create_excimer_profiles(): stdClass {
        // Create 5 profiles. 3 for user1 and 2 for user2.
        $user1 = $this->getDataGenerator()->create_user(['userid' => 'user1']);
        $user2 = $this->getDataGenerator()->create_user(['userid' => 'user2']);

        $data = new \stdClass();
        $data->user1['user'] = $user1;
        $data->user2['user'] = $user2;

        // Some data to pass to the create_profile function for user1 and user2.
        $profiles_data_user1 = [
            [
                'user' => $user1,
                'request' => 'course/view.php',
                'parameters' => 'id=2',
                'duration' => 57,
                'courseid' => 2,
                'lockreason' => '',
                'usermodified' => $user1->id
            ],
            [
                'user' => $user1,
                'request' => 'cohort/members.php',
                'parameters' => 'id=1',
                'duration' => 0.76,
                'courseid' => 1,
                'lockreason' => 'Locked so it is not deleted',
                'usermodified' => $user1->id
            ],
            [
                'user' => $user1,
                'request' => 'course/view.php',
                'parameters' => 'id=2',
                'duration' => 60,
                'courseid' => 2,
                'lockreason' => '',
                'usermodified' => $user1->id
            ],
        ];

        $profiles_data_user2 = [
            [
                'user' => $user2,
                'request' => 'course/view.php',
                'parameters' => 'id=2',
                'duration' => 57,
                'courseid' => 2,
                'lockreason' => '',
                'usermodified' => $user2->id
            ],
            [
                'user' => $user2,
                'request' => 'cohort/rules.php',
                'parameters' => 'id=3',
                'duration' => 0.89,
                'courseid' => 1,
                'lockreason' => 'Locked so it is not deleted 2',
                'usermodified' => $user2->id
            ],
        ];

        $data->user1['profile_data'] = $profiles_data_user1;
        $data->user2['profile_data'] = $profiles_data_user2;
        foreach ($profiles_data_user1 as $profile) {
            $data->user1['profiles'][] = $this->create_profile($profile);
        }

        foreach ($profiles_data_user2 as $profile) {
            $data->user2['profiles'][] = $this->create_profile($profile);
        }

        return $data;
    }

    /**
     * Test purge for a specific user works and it doesn't delete data for other users
     *
     * @return void
     */
    public function test_purge_excimer_profiles(): void {
        global $DB;

        // Setup data to tests.
        $data = $this->create_excimer_profiles();

        // Get one of the users we created in the setup data for testing.
        $target_user = new target_user($data->user1['user']);

        // Check total records for the user before purging data.
        $this->assertEquals(3, $DB->count_records(PROFILE::TABLE, ['userid' => $data->user1['user']->id]));

        // Purge data and  check the records count again.
        \tool_excimer\userdata\excimer_profiles::execute_purge($target_user, context_system::instance());
        $this->assertEquals(0, $DB->count_records(PROFILE::TABLE, ['userid' => $data->user1['user']->id]));

        // User2 profiles should remain intact.
        $this->assertEquals(2, $DB->count_records(PROFILE::TABLE, ['userid' => $data->user2['user']->id]));
    }

    /**
     * Test the count for userdata works.
     *
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_count_excimer_profiles(): void {
        global $DB;

        $data = $this->create_excimer_profiles();

        // Check count for User1's profiles.
        $target_user = new target_user($data->user1['user']);
        $count = \tool_excimer\userdata\excimer_profiles::execute_count($target_user, context_system::instance());
        $this->assertEquals(3, $DB->count_records(PROFILE::TABLE, ['userid' => $data->user1['user']->id]));
        $this->assertEquals(3, $count);

        // Check 5 records in total 3 for user1 and 2 for user2.
        $this->assertEquals(5, $DB->count_records(PROFILE::TABLE));
    }

    /**
     *  Test export has the corresponding columns and the right values for the user.
     *
     * @return void
     */
    public function test_export_excimer_profiles(): void {
        global $DB;

        $data = $this->create_excimer_profiles();

        $target_user = new target_user($data->user1['user']);
        $export = \tool_excimer\userdata\excimer_profiles::execute_export($target_user, context_system::instance());

        $this->assertNotEmpty($export->data);
        $index = 0;
        foreach ($export->data as $row) {
            // Check keys.
            $this->assertArrayHasKey('userid', $row);
            $this->assertArrayHasKey('useragent', $row);
            $this->assertArrayHasKey('request', $row);
            $this->assertArrayHasKey('pathinfo', $row);
            $this->assertArrayHasKey('parameters', $row);
            $this->assertArrayHasKey('referer', $row);
            $this->assertArrayHasKey('duration', $row);
            $this->assertArrayHasKey('courseid', $row);
            $this->assertArrayHasKey('timecreated', $row);

            // Check Data.
            $profile_data = $data->user1['profile_data'][$index];
            $this->assertEquals($profile_data['user']->id, $row['userid']);
            $this->assertEquals($profile_data['request'], $row['request']);
            $this->assertEquals($profile_data['parameters'], $row['parameters']);
            $this->assertEquals($profile_data['duration'], $row['duration']);
            $this->assertEquals($profile_data['courseid'], $row['courseid']);

            $index = $index + 1;
        }

        // Check the second position of the export data has additional lockrreason column.
        $actual_row_additional_columns = $export->data[1];
        $expected_row_additional_columns = $data->user1['profile_data'][1];
        $this->assertEquals($expected_row_additional_columns['lockreason'], $actual_row_additional_columns['lockreason']);
        $this->assertEquals($expected_row_additional_columns['usermodified'], $actual_row_additional_columns['usermodified']);
    }
}