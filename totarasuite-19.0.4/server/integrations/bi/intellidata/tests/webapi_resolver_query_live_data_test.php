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


use core_phpunit\testcase;
use totara_webapi\phpunit\webapi_phpunit_helper;
use bi_intellidata\helpers\SettingsHelper;
use bi_intellidata\exception\invalid_live_data_exception;
use bi_intellidata\services\datatypes_service;

/**
 * Unit tests for the bi_intellidata_live_data query resolver.
 */
class bi_intellidata_webapi_resolver_query_live_data_test extends testcase {
    use webapi_phpunit_helper;

    /**
     * @var string
     */
    protected const QUERY = 'bi_intellidata_live_data';

    /**
     * @return void
     */
    public function setUp(): void {
        parent::setUp();
        SettingsHelper::set_setting('enabled', '1');
    }

    /**
     * @return void
     */
    public function test_live_data_query_with_valid_input(): void {
        global $DB;
        $system_context = context_system::instance();
        $user = self::getDataGenerator()->create_user();
        $role = $DB->get_record('role', ['shortname' => 'intelliboardapiuser'], '*', MUST_EXIST);
        role_assign($role->id, $user->id, $system_context);
        self::setUser($user);

        self::getDataGenerator()->create_course();

        foreach (datatypes_service::LIVE_DATA_DATATYPES as $test_datatype) {
            // Operate.
            $result = $this->resolve_graphql_query(self::QUERY,
                ['input' => ["datatype" => $test_datatype]]
            );

            // Assert.
            self::assertNotEmpty($result);;
            self::assertArrayHasKey('data', $result);
            // The data string will be list of lines like this:
            // {"id":"1","name":"Site Manager","shortname":"manager","recordtimecreated":1677801349,"recordusermodified":"2","crud":"c"}
            // Let's check it has some of the required info.
            $end_position = strpos($result['data'], "}") + 1;
            $record1 = substr($result['data'], 0, $end_position);
            $record = json_decode($record1, true);
            self::assertArrayHasKey('id', $record);
            $name_field_check = ($test_datatype == 'courses' ? 'fullname' : 'name');
            self::assertArrayHasKey($name_field_check, $record);
        }
    }

    /**
     * @return void
     */
    public function test_live_data_query_with_invalid_input(): void {
        global $DB;
        $system_context = context_system::instance();
        $user = self::getDataGenerator()->create_user();
        $role = $DB->get_record('role', ['shortname' => 'intelliboardapiuser'], '*', MUST_EXIST);
        role_assign($role->id, $user->id, $system_context);
        self::setUser($user);

        try {
            // Operate
            $result = $this->resolve_graphql_query(self::QUERY,
                ['input' => ["datatype" => "i'm a datatype value that is not in datatypes_service::LIVE_DATA_DATATYPES"]]
            );
        } catch (invalid_live_data_exception $exc) {
            // Assert.
            self::assertEquals('Invalid datatype specified.', $exc->getMessage());
        }

        try {
            // Operate
            $result = $this->resolve_graphql_query(self::QUERY,
                ['input' => ["i'm_bad_input_parameter" => "xyz"]]
            );
        } catch (invalid_live_data_exception $exc) {
            // Assert.
            self::assertEquals('Datatype parameter missing.', $exc->getMessage());
        }
    }

    /**
     * @return void
     */
    public function test_live_data_query_without_access(): void {
        // No authenticated user making the request.
        $this->expectException('require_login_exception');
        // Operate.
        $result = $this->resolve_graphql_query(self::QUERY,
            ['input' => [ 'datatype' => 'categories']]
        );
    }

    /**
     * @return void
     */
    public function test_live_data_query_without_required_capability(): void {
        // A test user who is authenticated but without the 'intelliboardapiuser' role, i.e. also without the
        // 'bi/intellidata:viewlivedata' capability.
        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        try {
            // Operate.
            $result = $this->resolve_graphql_query(self::QUERY,
                ['input' => ['datatype' => 'courses']]
            );
        } catch (required_capability_exception $exc) {
            self::assertEquals('Sorry, but you do not currently have permissions to do that (IntelliData view live data)', $exc->getMessage());
        }
    }

    /**
     * @return void
     */
    public function test_plugin_disabled_middleware(): void {
        SettingsHelper::set_setting('enabled', '0');
        global $DB;
        $system_context = context_system::instance();
        $user = self::getDataGenerator()->create_user();
        $role = $DB->get_record('role', ['shortname' => 'intelliboardapiuser'], '*', MUST_EXIST);
        role_assign($role->id, $user->id, $system_context);
        self::setUser($user);

        try {
            // Operate.
            $result = $this->resolve_graphql_query(self::QUERY,
                ['input' => ['datatype' => 'courses']]
            );
        } catch (moodle_exception $exc) {
            // Assert.
            self::assertSame('The IntelliData plugin is not enabled.', $exc->getMessage());
        }
    }
}
