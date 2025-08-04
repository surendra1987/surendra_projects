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
class bi_intellidata_webapi_resolver_query_data_files_test extends testcase {
    use webapi_phpunit_helper;

    /**
     * @var string
     */
    protected const QUERY = 'bi_intellidata_data_files';

    public function setUp(): void {
        SettingsHelper::set_setting('enabled', 1);
    }

    /**
     * @return void
     */
    public function test_data_files_migrations_with_exception(): void {
        self::setAdminUser();

        try {
            $this->resolve_graphql_query(self::QUERY, [
                'input' => [
                    'timestart' => 11,
                    'timeend' => 10
                ]
            ]);
        } catch (moodle_exception $e) {
            self::assertEquals('Time start must be earlier than time end.', $e->getMessage());
        }

        try {
            $this->resolve_graphql_query(self::QUERY, [
                'input' => [
                    'datatype' => 'sss',
                ]
            ]);
        } catch (moodle_exception $e) {
            self::assertEquals('Invalid datatype.', $e->getMessage());
        }

        try {
            $this->resolve_graphql_query(self::QUERY);
        } catch (moodle_exception $e) {
            self::assertStringContainsString('Migrations not ready:', $e->getMessage());
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
        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        self::expectException(required_capability_exception::class);
        $this->resolve_graphql_query(self::QUERY,
            ['input' => ['datatype' => 'courses']]
        );
    }

}