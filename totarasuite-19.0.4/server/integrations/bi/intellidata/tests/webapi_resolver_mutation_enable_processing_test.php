<?php
/**
 * This file is part of Totara Core
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
 * @author Scott Davies <scott.davies@totara.com>
 * @package bi_intellidata
 */


use bi_intellidata\exception\invalid_processing_exception;
use bi_intellidata\helpers\SettingsHelper;
use core_phpunit\testcase;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * Unit tests for the enable_processing resolver.
 * @group bi_intellidata
 */
class bi_intellidata_webapi_resolver_mutation_enable_processing_test extends testcase {
    use webapi_phpunit_helper;

    /**
     * @var string
     */
    protected const MUTATION = 'bi_intellidata_enable_processing';

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
    public function test_mutation_without_required_capability(): void {
        $originalValue = SettingsHelper::get_setting('enabled');
        SettingsHelper::set_setting('enabled', 1);
        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        try {
            $this->resolve_graphql_mutation(self::MUTATION);
            $this->fail('Failed to check required capability.');
        } catch (\required_capability_exception $exception) {
            $this->assertTrue(true);
        } finally {
            SettingsHelper::set_setting('enabled', $originalValue);
        }
    }

    /**
     * @return void
     */
    public function test_enable_processing_with_valid_inputs(): void {
        global $DB;
        // Set up.
        $original_config_migrationcallbackurl = get_config('bi_intellidata', 'migrationcallbackurl');
        $system_context = context_system::instance();
        $user = self::getDataGenerator()->create_user();
        $role = $DB->get_record('role', ['shortname' => 'intelliboardapiuser'], '*', MUST_EXIST);
        role_assign($role->id, $user->id, $system_context);
        self::setUser($user);

        // Operate #1.
        $result = self::resolve_graphql_mutation(self::MUTATION, [
            'input' => [
                'status' => SettingsHelper::STATUS_ENABLE,
                'callbackurl' => 'http://xyz.com?value=1234'
            ]
        ]);

        // Assert #1.
        $new_config_ispluginsetup = get_config('bi_intellidata', 'ispluginsetup');
        $new_config_migrationcallbackurl = get_config('bi_intellidata', 'migrationcallbackurl');
        self::assertNotEmpty($result);
        self::assertArrayHasKey('data', $result);
        // We expect a success response because the inputs were valid.
        self::assertEquals('ok', $result['data']);
        // We expect the DB values in config_plugins to be updated.
        self::assertNotEquals($original_config_migrationcallbackurl, $new_config_migrationcallbackurl);
        self::assertEquals($new_config_ispluginsetup, SettingsHelper::STATUS_ENABLE);
        self::assertEquals($new_config_migrationcallbackurl, 'http://xyz.com?value=1234');

        // Operate #2.
        $result = self::resolve_graphql_mutation(self::MUTATION, [
            'input' => [
                'status' => SettingsHelper::STATUS_DISABLE // omitting the optional 'callbackurl' parameter.
            ]
        ]);

        // Assert #2.
        $new_config_ispluginsetup = get_config('bi_intellidata', 'ispluginsetup');
        self::assertEquals('ok', $result['data']);
        self::assertEquals($new_config_ispluginsetup, SettingsHelper::STATUS_DISABLE); // should have updated.
    }

    /**
     * @return void
     */
    public function test_enable_processing_with_invalid_inputs(): void {
        global $DB;
        // Set up.
        $original_config_migrationcallbackurl = get_config('bi_intellidata', 'ispluginsetup');
        $original_config_ispluginsetup = get_config('bi_intellidata', 'ispluginsetup');
        $system_context = context_system::instance();
        $user = self::getDataGenerator()->create_user();
        $role = $DB->get_record('role', ['shortname' => 'intelliboardapiuser'], '*', MUST_EXIST);
        role_assign($role->id, $user->id, $system_context);
        self::setUser($user);

        // Operate #1.
        try {
            $result = self::resolve_graphql_mutation(self::MUTATION, [
                'input' => [
                    'status' => 99999, // this only allows limited values.
                    'callbackurl' => 'http://xyz.com?value=1234'
                ]
            ]);
        } catch (invalid_processing_exception $exc) {
            self::assertEquals('Invalid plugin status.', $exc->getMessage());
        }

        // Assert #1. Nothing should have updated because invalid input(s).
        $new_config_ispluginsetup = get_config('bi_intellidata', 'ispluginsetup');
        $new_config_migrationcallbackurl = get_config('bi_intellidata', 'migrationcallbackurl');
        self::assertEquals($original_config_migrationcallbackurl, $new_config_migrationcallbackurl);
        self::assertEquals($original_config_ispluginsetup, $new_config_ispluginsetup);

        // Operate #2.
        try {
            $result = self::resolve_graphql_mutation(self::MUTATION, [
                'input' => [
                    'status' => SettingsHelper::STATUS_DISABLE,
                    'callbackurl' => 'http:imabadurl.com?value=1234' // bad URL.
                ]
            ]);
        } catch (invalid_processing_exception $exc) {
            self::assertEquals('Invalid callbackurl.', $exc->getMessage());
        }

        // Assert #2. Nothing should have updated because invalid input(s).
        $new_config_migrationcallbackurl = get_config('bi_intellidata', 'migrationcallbackurl');
        self::assertEquals($original_config_migrationcallbackurl, $new_config_migrationcallbackurl);

        // Operate #3.
        $this->expectExceptionMessage('Invalid plugin status.');
        $result = self::resolve_graphql_mutation(self::MUTATION, [
            'input' => [ // Required 'status' input missing.
                'callbackurl' => 'http://xyz.com?value=1234'
            ]
        ]);
    }
}
