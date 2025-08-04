<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2020 onwards Totara Learning Solutions LTD
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
 * @author Marco Song <marco.song@totaralearning.com>
 * @package totara_competency
 */

use core\orm\query\builder;

global $CFG;
require_once $CFG->dirroot . '/totara/competency/tests/scale_query_resolver_testcase.php';

/**
 * @group totara_competency
 */
class totara_competency_webapi_resolver_query_scale_test extends scale_query_resolver_testcase {

    protected const ID_ARG_EXCEPTION_MESSAGE = '/Please provide "id" OR "competency_id" OR "framework_id"/';

    /**
     * @inheritDoc
     */
    protected function get_query_name(): string {
        return 'totara_competency_scale';
    }

    /**
     * @dataProvider query_successful_provider
     * @param closure $get_args
     */
    public function test_query_successful(closure $get_args): void {
        $data = $this->create_data();

        $args = $get_args($data);

        $result = $this->resolve_graphql_query($this->get_query_name(), $args);
        $this->assertEquals($data->scale->id, $result->get_id());
    }

    public static function query_successful_provider(): array {
        return [
            'by scale id' => [
                function (object $data) {
                    return ['id' => $data->scale->id];
                }
            ],
            'by competency id' => [
                function (object $data) {
                    return ['competency_id' => $data->comp1->id];
                }
            ],
            'by framework id' => [
                function (object $data) {
                    return ['framework_id' => $data->fw1->id];
                }
            ],
        ];
    }

    /**
     * Check that a tenant member by default has permission to call this query.
     * (There used to be a bug where it failed with isolation enabled.)
     *
     * @return void
     */
    public function test_as_tenant_member(): void {
        $data = $this->create_data();

        $generator = self::getDataGenerator();

        /** @var \totara_tenant\testing\generator $tenant_generator */
        $tenant_generator = $generator->get_plugin_generator('totara_tenant');
        $tenant_generator->enable_tenants();

        $tenant_1 = $tenant_generator->create_tenant();
        $tenant_1_user = $generator->create_user(['tenantid' => $tenant_1->id]);

        self::setUser($tenant_1_user);

        $args = ['id' => $data->scale->id];
        
        // It should work with and without tenant isolation.
        set_config('tenantsisolated', 0);
        $result = $this->resolve_graphql_query($this->get_query_name(), $args);
        $this->assertEquals($data->scale->id, $result->get_id());

        set_config('tenantsisolated', 1);
        $result = $this->resolve_graphql_query($this->get_query_name(), $args);
        $this->assertEquals($data->scale->id, $result->get_id());
    }

    public function test_capabilities_for_self(): void {
        $data = $this->create_data();

        $user = self::getDataGenerator()->create_user();

        self::setUser($user);

        $args = [
            'id' => $data->scale->id,
        ];

        $user_role = builder::get_db()->get_record('role', ['shortname' => 'user']);
        unassign_capability('totara/hierarchy:viewcompetency', $user_role->id, context_system::instance()->id);

        // User can still view because they have 'view_own_profile' capability by default.
        $result = $this->resolve_graphql_query($this->get_query_name(), $args);
        $this->assertEquals($data->scale->id, $result->get_id());

        // Remove 'view_own_profile' capability and it should fail.
        unassign_capability('totara/competency:view_own_profile', $user_role->id, context_system::instance()->id);
        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessage('User does not have the permission to view the scale');
        $this->resolve_graphql_query($this->get_query_name(), $args);
    }

    public function test_capabilities_for_other_user(): void {
        $data = $this->create_data();

        $acting_user = self::getDataGenerator()->create_user();
        $subject_user = self::getDataGenerator()->create_user();

        self::setUser($acting_user);

        $args = [
            'id' => $data->scale->id,
            'user_id' => $subject_user->id,
        ];

        $user_role = builder::get_db()->get_record('role', ['shortname' => 'user']);
        unassign_capability('totara/hierarchy:viewcompetency', $user_role->id, context_system::instance()->id);

        // 'view_other_profile' capability is sufficient for viewing the scale.
        assign_capability('totara/competency:view_other_profile', CAP_ALLOW, $user_role->id, context_system::instance()->id);

        $result = $this->resolve_graphql_query($this->get_query_name(), $args);
        $this->assertEquals($data->scale->id, $result->get_id());

        // Remove 'view_other_profile' capability and it should fail.
        unassign_capability('totara/competency:view_other_profile', $user_role->id, context_system::instance()->id);
        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessage('User does not have the permission to view the scale');
        $this->resolve_graphql_query($this->get_query_name(), $args);
    }
}