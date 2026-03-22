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
use bi_intellidata\services\dbschema_service;
use core_phpunit\testcase;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * Query dbschema custom test case.
 * @group bi_intellidata
 */
class bi_intellidata_webapi_resolver_query_dbschema_custom_test extends testcase {
    use webapi_phpunit_helper;

    /**
     * @var string
     */
    protected const QUERY = 'bi_intellidata_dbschema_custom';

    /**
     * @return void
     */
    public function test_dbschema_custom(): void {
        self::setAdminUser();
        SettingsHelper::set_setting('enabled', 1);
        $result = $this->resolve_graphql_query(self::QUERY);
        $items = $result['items'];
        $dbschema_service = new dbschema_service();
        $tables = $dbschema_service->get_tables();
        self::assertEquals(count($tables), count($items));

        foreach ($items as $item) {
            // Asserts table name;
            self::assertContains($item['name'], array_values($dbschema_service->get_tableslist()));
            self::assertArrayHasKey('name', $item);

            self::assertArrayHasKey('fields', $item);
            foreach ($item['fields'] as $field) {
                self::assertArrayHasKey('name', $field);
                self::assertArrayHasKey('has_default', $field);
                self::assertArrayHasKey('type', $field);
                // Removed since this list is different between different databases
                //self::assertContains($field['type'], ['varchar', 'text', "bigint", 'int', 'float', 'numeric']);
                self::assertArrayHasKey('max_length', $field);
                self::assertArrayHasKey('primary_key', $field);
                self::assertContains($field['primary_key'], [true, false]);
                self::assertContains($field['not_null'], [true, false]);
                self::assertContains($field['auto_increment'], [true, false]);
                self::assertArrayHasKey('not_null', $field);
                self::assertArrayHasKey('auto_increment', $field);
            }

            self::assertArrayHasKey('plugintype', $item);
            self::assertArrayHasKey('plugin', $item);
            self::assertArrayHasKey('keys', $item);

            foreach ($item['keys'] as $key) {
                self::assertArrayHasKey('name', $key);
                self::assertNotEmpty($key['name'] );
                self::assertArrayHasKey('reftable', $key);
                self::assertIsArray($key['fields']);
                self::assertArrayHasKey('reffields', $key);
                self::assertIsArray($key['reffields']);
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