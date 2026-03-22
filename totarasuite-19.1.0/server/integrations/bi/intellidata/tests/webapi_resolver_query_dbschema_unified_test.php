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
use bi_intellidata\services\datatypes_service;
use core_phpunit\testcase;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * Query dbschema unified test case.
 * @group bi_intellidata
 */
class bi_intellidata_webapi_resolver_query_dbschema_unified_test extends testcase {
    use webapi_phpunit_helper;

    /**
     * @var string
     */
    protected const QUERY = 'bi_intellidata_dbschema_unified';

    /**
     * @return void
     */
    public function test_dbschema_unified(): void {
        global $DB;
        $system_context = context_system::instance();
        $user = self::getDataGenerator()->create_user();
        $role = $DB->get_record('role', ['shortname' => 'intelliboardapiuser'], '*', MUST_EXIST);
        role_assign($role->id, $user->id, $system_context);
        self::setUser($user);
        SettingsHelper::set_setting('enabled', 1);
        $result = $this->resolve_graphql_query(self::QUERY);
        $items = $result['items'];
        $data_types = datatypes_service::get_required_datatypes();
        foreach ($items as $item) {
            self::assertArrayHasKey('name', $item);
            self::assertTrue(in_array($item['name'], array_keys($data_types)));
            self::assertArrayHasKey('fields', $item);

            foreach ($item['fields'] as $field) {
                self::assertContains(strtoupper($field['type']), ['INT', 'TEXT', 'RAW', 'PARAM_RAW_TRIMMED', 'RAW_TRIMMED', 'BOOL', 'ALPHANUMEXT', 'FLOAT']);
                self::assertNotEmpty($field['field_name']);
                self::assertNotEmpty($field['description']);
                self::assertContains($field['null'], [true, false]);
                self::assertArrayHasKey('field_name', $field);
                self::assertArrayHasKey('description', $field);
                self::assertArrayHasKey('null', $field);
                self::assertArrayHasKey('type', $field);
            }
        }
    }


    /**
     * @return void
     */
    public function test_when_plugin_disabled(): void {
        SettingsHelper::set_setting('enabled', 0);
        self::setAdminUser();

        self::expectExceptionMessage('The IntelliData plugin is not enabled.');
        self::expectException(moodle_exception::class);
        $this->resolve_graphql_query(self::QUERY);
    }

    /**
     * @return void
     */
    public function test_query_without_required_capability(): void {
        $originalValue = SettingsHelper::get_setting('enabled');
        SettingsHelper::set_setting('enabled', 1);
        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        try {
            $this->resolve_graphql_query(self::QUERY);
            $this->fail('Failed to check required capability.');
        } catch (\required_capability_exception $exception) {
            $this->assertTrue(true);
        } finally {
            SettingsHelper::set_setting('enabled', $originalValue);
        }
    }

}