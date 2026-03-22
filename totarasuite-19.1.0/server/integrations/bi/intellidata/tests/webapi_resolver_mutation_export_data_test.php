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
 * @author Qingyang Liu <qingyang.liu@totaralearning.com>
 * @package bi_intellidata
 */


use bi_intellidata\helpers\SettingsHelper;
use core_phpunit\testcase;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * Query data files test case.
 * @group bi_intellidata
 */
class bi_intellidata_webapi_resolver_mutation_export_data_test extends testcase {
    use webapi_phpunit_helper;

    /**
     * @var string
     */
    protected const MUTATION = 'bi_intellidata_export_data';

    public function setUp(): void {
        SettingsHelper::set_setting('enabled', 1);
    }

    /**
     * @return void
     */
    public function test_when_plugin_disabled(): void {
        SettingsHelper::set_setting('enabled', 0);
        self::setAdminUser();

        self::expectExceptionMessage('The IntelliData plugin is not enabled.');
        self::expectException(moodle_exception::class);
        $this->resolve_graphql_mutation(self::MUTATION);
    }

    /**
     * @return void
     */
    public function test_request_without_required_capability(): void {
        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        self::expectException(required_capability_exception::class);
        $this->resolve_graphql_mutation(self::MUTATION);
    }

    /**
     * @return void
     */
    public function test_valid_request_check_response(): void {
        global $DB;
        // Set up.
        $system_context = context_system::instance();
        $intelliboard_api_user = self::getDataGenerator()->create_user();
        $role = $DB->get_record('role', ['shortname' => 'intelliboardapiuser'], '*', MUST_EXIST);
        role_assign($role->id, $intelliboard_api_user->id, $system_context);
        self::setUser($intelliboard_api_user);

        $expected_file_instance_keys = ['filename',
            'filearea',
            'itemid',
            'filepath',
            'mimetype',
            'filesize',
            'timemodified',
            'isexternalfile',
            'repositorytype',
            'fileurl'
        ];

        // Operate.
        $result = $this->resolve_graphql_mutation(self::MUTATION);

        // Assert.
        self::assertArrayHasKey('items', $result);
        $first_item = $result['items'][0];
        self::assertObjectHasProperty('datatype', $first_item);
        self::assertObjectHasProperty('files', $first_item);
        self::assertObjectHasProperty('migration_files', $first_item);

        // Let's look at the data for one datatype we know will be in the response.
        foreach ($result['items'] as $item_for_datatype) {
            if ($item_for_datatype->datatype == 'roles') {
                break;
            }
        }

        self::assertNotEmpty($item_for_datatype->files);
        // Check that all expected keys are in the file_instance.
        foreach ($expected_file_instance_keys as $expected_key) {
            $file_instance =  $item_for_datatype->files[0];
            self::assertArrayHasKey($expected_key, $file_instance);
            // Let's check some sample file_instance field/type values.
            self::assertTrue(stripos($file_instance['filename'], '.zip') > -1); // a string.
            self::assertGreaterThan(0, $file_instance['filesize']); // an int.
            self::assertEquals("", $file_instance['repositorytype']); // must be an empty string, even if it's actually null.
        }
    }
}
