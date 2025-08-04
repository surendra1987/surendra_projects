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
use bi_intellidata\testing\generator;
use bi_intellidata\persistent\datatypeconfig;

/**
 * Unit tests for the 'bi_intellidata_export_logs' external API query.
 */
class bi_intellidata_webapi_resolver_query_export_logs_test extends testcase {
    use webapi_phpunit_helper;

    /**
     * @var string
     */
    protected const QUERY = 'bi_intellidata_export_logs';

    /** @var \core\testing\generator */
    protected $generator;

    /**
     * @return void
     */
    protected function setUp(): void {
        parent::setUp();
        $this->generator = generator::instance();
        SettingsHelper::set_setting('enabled', '1');
    }

    /**
     * @return void
     */
    protected function tearDown(): void {
        $this->generator = null;
        parent::tearDown();
    }

    /**
     * @return void
     */
    public function test_query_export_logs(): void {
        // Set up.
        self::setAdminUser();
        $num_export_logs = 3;
        $test_export_logs = $this->generator->create_export_logs($num_export_logs);

        // Operate.
        $result = $this->resolve_graphql_query(self::QUERY);

        // Assert.
        $expected_fields = [
            'id',
            'datatype',
            'last_exported_time',
            'last_exported_id',
            'migrated',
            'timestart',
            'recordsmigrated',
            'recordscount',
            'tabletype',
            'timecreated',
            'timemodified',
            'usermodified',
        ];
        self::assertArrayHasKey('items', $result);
        $items = $result['items'];
        self::assertCount($num_export_logs, $items);
        foreach ($items as $item) {
            foreach ($expected_fields as $expected_field) {
                self::assertObjectHasProperty($expected_field, $item);
            }
            self::assertGreaterThan(0, (int)$item->id);
            self::assertEquals(datatypeconfig::STATUS_ENABLED, (string)$item->tabletype);
        }
    }

    /**
     * @return void
     */
    public function test_query_export_logs_without_capability(): void {
        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        self::expectExceptionMessage('Sorry, but you do not currently have permissions to do that (IntelliData view logs)');
        $this->resolve_graphql_query(self::QUERY);
    }

    /**
     * @return void
     */
    public function test_query_export_logs_with_proper_capability(): void {
        $user = self::getDataGenerator()->create_user();
        self::setUser($user);
        $roles = get_archetype_roles('user');
        $role = reset($roles);
        assign_capability('bi/intellidata:viewlogs', CAP_ALLOW, $role->id, context_system::instance());

        $result = $this->resolve_graphql_query(self::QUERY);
        self::assertArrayHasKey('items', $result);
    }

    /**
     * @return void
     */
    public function test_query_can_export_logs_with_role(): void {
        global $DB;
        // Assign the role 'intelliboardapiuser' with the capability 'bi/intellidata:viewlogs' to a test user.
        $role = $DB->get_record('role', ['shortname' => 'intelliboardapiuser'], '*', MUST_EXIST);
        $req_capability = 'bi/intellidata:viewlogs';
        $system_context = context_system::instance();
        $role_capabilities = $DB->get_record('role_capabilities', ['capability' => $req_capability,
            'contextid' => $system_context->id, 'roleid' => $role->id], '*');
        if (!$role_capabilities) {
            assign_capability($req_capability, CAP_ALLOW, $role->id, $system_context);
        }
        $user = self::getDataGenerator()->create_user();
        role_assign($role->id, $user->id, $system_context);
        self::setUser($user);

        // Operate.
        $result = $this->resolve_graphql_query(self::QUERY);

        // Assert
        self::assertArrayHasKey('items', $result);
    }

    /**
     * @return void
     */
    public function test_cannot_query_export_logs_without_role(): void {
        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        self::expectException(required_capability_exception::class);
        $result = $this->resolve_graphql_query(self::QUERY);
    }
}
