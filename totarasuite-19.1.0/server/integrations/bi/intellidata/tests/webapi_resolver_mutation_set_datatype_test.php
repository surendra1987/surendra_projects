<?php
/**
 * This file is part of Totara TXP
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
 * @author Michael Ivanov <michael.ivanov@totara.com>
 * @package bi_intellidata
 */


use bi_intellidata\exception\invalid_set_datatype_exception;
use bi_intellidata\helpers\SettingsHelper;
use core_phpunit\testcase;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * Unit tests for the set_datatype resolver.
 * @group bi_intellidata
 */
class bi_intellidata_webapi_resolver_mutation_set_datatype_test extends testcase {
    use webapi_phpunit_helper;

    /**
     * @var string
     */
    protected const MUTATION = 'bi_intellidata_set_datatype';

    /**
     * @return void
     */
    protected function setUp(): void {
        parent::setUp();
        SettingsHelper::set_setting('enabled', '1');
    }

    /**
     * @return void
     */
    public function test_set_datatype_with_valid_inputs(): void {
        global $DB;
        $system_context = context_system::instance();
        $user = self::getDataGenerator()->create_user();
        $role = $DB->get_record('role', ['shortname' => 'intelliboardapiuser'], '*', MUST_EXIST);
        role_assign($role->id, $user->id, $system_context);
        self::setUser($user);

        $result = self::resolve_graphql_mutation(self::MUTATION, [
            'input' => [
                'datatypes' => [
                    'test'
                ],
                'exportfiles' => SettingsHelper::STATUS_DISABLE
            ]
        ]);

        self::assertNotEmpty($result);
        self::assertArrayHasKey('data', $result);
        self::assertEquals('Datatype(s) successfully added / updated.', $result['data']);

        $result = self::resolve_graphql_mutation(self::MUTATION, [
            'input' => [
                'datatypes' => [
                    'test'
                ],
                'exportfiles' => SettingsHelper::STATUS_ENABLE,
                'callbackurl' => 'http://abc.com'
            ]
        ]);

        self::assertNotEmpty($result);
        self::assertArrayHasKey('data', $result);
        self::assertEquals('Datatype(s) successfully added / updated.', $result['data']);
    }

    /**
     * @return void
     */
    public function test_set_datatype_with_invalid_inputs(): void {
        global $DB;
        // Set up.
        $system_context = context_system::instance();
        $user = self::getDataGenerator()->create_user();
        $role = $DB->get_record('role', ['shortname' => 'intelliboardapiuser'], '*', MUST_EXIST);
        role_assign($role->id, $user->id, $system_context);
        self::setUser($user);

        try {
            self::resolve_graphql_mutation(self::MUTATION, [
                'input' => [
                    'exportfiles' => 99999, // this only allows limited values.
                    'callbackurl' => 'http://xyz.com?value=1234'
                ]
            ]);
        } catch (invalid_set_datatype_exception $e) {
            self::assertEquals('Invalid exportfiles parameter.', $e->getMessage());
        }

        try {
            self::resolve_graphql_mutation(self::MUTATION, [
                'input' => [
                    'exportfiles' => SettingsHelper::STATUS_ENABLE,
                    'callbackurl' => 'http:imabadurl.com?value=1234' // bad URL.
                ]
            ]);
        } catch (invalid_set_datatype_exception $e) {
            self::assertEquals('Invalid callbackurl parameter.', $e->getMessage());
        }

        try {
            self::resolve_graphql_mutation(self::MUTATION, [
                'input' => [
                    'exportfiles' => SettingsHelper::STATUS_DISABLE,
                    'callbackurl' => 'http://xyz.com?value=1234'
                ]
            ]);
        } catch (invalid_set_datatype_exception $e) {
            self::assertEquals('Callback URL only works with exportfiles enabled.', $e->getMessage());
        }
    }

    /**
     * @return void
     */
    public function test_set_datatype_with_restricted_table(): void {
        global $DB;
        // Set up.
        $system_context = context_system::instance();
        $user = self::getDataGenerator()->create_user();
        $role = $DB->get_record('role', ['shortname' => 'intelliboardapiuser'], '*', MUST_EXIST);
        role_assign($role->id, $user->id, $system_context);
        self::setUser($user);

        try {
            self::resolve_graphql_mutation(self::MUTATION, [
                'input' => [
                    'datatypes' => [
                        'my_config'
                    ]
                ]
            ]);
        } catch (invalid_set_datatype_exception $e) {
            self::assertEquals('The table my_config is restricted.', $e->getMessage());
        }
    }

    /**
     * @return void
     */
    public function test_set_datatype_with_invalid_datatype(): void {
        global $DB;
        // Set up.
        $system_context = context_system::instance();
        $user = self::getDataGenerator()->create_user();
        $role = $DB->get_record('role', ['shortname' => 'intelliboardapiuser'], '*', MUST_EXIST);
        role_assign($role->id, $user->id, $system_context);
        self::setUser($user);

        $test_datatypes = [
            [''],
            ['test_datatype1', ''],
            ['', 'test_datatype2']
        ];

        foreach ($test_datatypes as $test_datatype_list) {
            try {
                // Operate.
                self::resolve_graphql_mutation(self::MUTATION, [
                    'input' => ['datatypes' => $test_datatype_list]
                ]);
            } catch (invalid_set_datatype_exception $e) {
                // Assert.
                self::assertEquals('A datatype must not be empty.', $e->getMessage());
            }
        }
    }

    /**
     * @return void
     */
    public function test_datatype_that_already_exists_is_not_inserted_again(): void {
        global $DB;
        // Set up.
        $system_context = context_system::instance();
        $user = self::getDataGenerator()->create_user();
        $role = $DB->get_record('role', ['shortname' => 'intelliboardapiuser'], '*', MUST_EXIST);
        role_assign($role->id, $user->id, $system_context);
        self::setUser($user);
        $test_datatype_name = 'test_doubley_doobley';
        // Insert #1.
        self::resolve_graphql_mutation(self::MUTATION, [
            'input' => ['datatypes' => [$test_datatype_name],
            ]
        ]);

        // Check.
        $records = $DB->get_records('bi_intellidata_export_log', ['datatype' => $test_datatype_name]);
        self::assertCount(1, $records);

        // Operate. Try #2 - a duplicate, i.e. datatype_that_already_exists,
        self::resolve_graphql_mutation(self::MUTATION, [
            'input' => ['datatypes' => [$test_datatype_name],
            ]
        ]);

        // Assert.
        $records = $DB->get_records('bi_intellidata_export_log', ['datatype' => $test_datatype_name]);
        self::assertCount(1, $records);
    }
}
