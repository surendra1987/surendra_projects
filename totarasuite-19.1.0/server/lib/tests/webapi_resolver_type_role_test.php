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
 * @package core
 */


use core\model\role;
use core\orm\query\builder;
use core_phpunit\testcase;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * core_webapi_resolver_type_role_test
 */
class core_webapi_resolver_type_role_test extends testcase {

    use webapi_phpunit_helper;

    /**
     * @return void
     */
    public function test_role_name(): void {
        $record = builder::get_db()->get_record('role', ['shortname' => 'student']);

        $model = role::load_by_id($record->id);
        $result = $this->resolve_graphql_type(
            'core_role',
            'name',
            $model,
        );

        self::assertEmpty($result);
    }

    /**
     * @return void
     */
    public function test_role_shortname(): void {
        $record = builder::get_db()->get_record('role', ['shortname' => 'student']);

        $model = role::load_by_id($record->id);
        $result = $this->resolve_graphql_type(
            'core_role',
            'shortname',
            $model,
        );

        self::assertEquals('student', $result);
    }

    /**
     * @return void
     */
    public function test_role_description(): void {
        $record = builder::get_db()->get_record('role', ['shortname' => 'student']);

        $model = role::load_by_id($record->id);
        $result = $this->resolve_graphql_type(
            'core_role',
            'description',
            $model,
        );

        self::assertEmpty($result);
    }

    /**
     * @return void
     */
    public function test_role_id(): void {
        $record = builder::get_db()->get_record('role', ['shortname' => 'student']);

        $model = role::load_by_id($record->id);
        $result = $this->resolve_graphql_type(
            'core_role',
            'id',
            $model,
        );

        self::assertEquals($record->id, $result);
    }
}