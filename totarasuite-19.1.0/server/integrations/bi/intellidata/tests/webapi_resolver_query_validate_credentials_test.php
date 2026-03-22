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


use bi_intellidata\exception\invalid_credentials_exception;
use bi_intellidata\helpers\SettingsHelper;
use bi_intellidata\testing\generator;
use core_phpunit\testcase;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * Query validate credentials test case.
 * @group bi_intellidata
 */
class bi_intellidata_webapi_resolver_query_validate_credentials_test extends testcase {
    use webapi_phpunit_helper;

    /**
     * @var string
     */
    protected const QUERY = 'bi_intellidata_validate_credentials';

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
    public function test_validate_credentials(): void {
        self::setAdminUser();
        $generator = generator::instance();
        [$encryptionkey, $clientidentifier] = $generator->create_credentials();

        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => ['code' => $clientidentifier]
            ]
        );

        self::assertEquals('ok', $result['data']);
    }

    /**
     * @return void
     */
    public function test_validate_invalid_credentials(): void {
        self::setAdminUser();
        $generator = generator::instance();
        $generator->create_credentials();

        self::expectExceptionMessage('Wrong clientid');
        self::expectException(invalid_credentials_exception::class);
        $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => ['code' => 'invalid']
            ]
        );
    }

    /**
     * @return void
     */
    public function test_validate_empty_credentials(): void {
        self::setAdminUser();

        self::expectExceptionMessage('Empty credentials.');
        self::expectException(invalid_credentials_exception::class);
        $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => ['code' => random_string(32)]
            ]
        );
    }

    /**
     * @return void
     */
    public function test_validate_credentials_with_empty_code(): void {
        self::setAdminUser();
        $generator = generator::instance();
        $generator->create_credentials();

        self::expectExceptionMessage('Code can not be decoded.');
        self::expectException(\moodle_exception::class);
        $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => ['code' => '   ']
            ]
        );
    }

}