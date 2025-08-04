<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Wajdi Bshara <wajdi.bshara@aleido.com>
 * @package hierarchy_organisation
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

use core\orm\query\builder;
use core\orm\query\exceptions\multiple_records_found_exception;
use core\orm\query\exceptions\record_not_found_exception;
use core_phpunit\testcase;
use hierarchy_organisation\exception\organisation_exception;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * @group xtractor
 */
class hierarchy_organisation_webapi_resolver_mutation_delete_organisation_test extends testcase {
    use webapi_phpunit_helper;

    protected string $mutation = 'hierarchy_organisation_delete_organisation';

    /**
     * @return void
     * @throws coding_exception
     */
    public function test_resolve_no_permissions(): void {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $frm1 = $this->create_org_framework(['fullname' => 'Organisation framework 1', 'idnumber' => 'org_frm_1']);
        $org = $this->create_org(['frameworkid' => $frm1->id, 'idnumber' => 'xtr_org']);

        $this->expectException(required_capability_exception::class);
        $this->expectExceptionMessage('Sorry, but you do not currently have permissions to do that (Delete an organisation)');

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'target_organisation' => ['idnumber' => $org->idnumber]
            ]
        );
    }

    /**
     * @param $data
     * @return stdClass
     * @throws coding_exception
     */
    private function create_org_framework($data): stdClass {
        return $this->getDataGenerator()->get_plugin_generator("totara_hierarchy")->create_framework("organisation", $data);
    }

    /**
     * @param $data
     * @return stdClass
     * @throws coding_exception
     */
    private function create_org($data): stdClass {
        return $this->getDataGenerator()->get_plugin_generator("totara_hierarchy")->create_org($data);
    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_resolve_target_org_not_exist(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $this->expectException(organisation_exception::class);
        $this->expectExceptionMessage('The target organisation does not exist or you do not have permissions to view it.');

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'target_organisation' => ['idnumber' => 'xtr_org']
            ]
        );
    }

    /**
     * @return stdClass
     * @throws coding_exception
     * @throws dml_exception
     */
    private function create_api_user(): stdClass {
        $user = $this->getDataGenerator()->create_user();

        // Give the API user the required capabilities through a role.
        $gen = $this->getDataGenerator();
        $role_id = $gen->create_role();
        assign_capability('totara/hierarchy:deleteorganisation', CAP_ALLOW, $role_id, context_system::instance());
        role_assign($role_id, $user->id, context_system::instance());

        return $user;
    }

    /**
     * @return void
     * @throws multiple_records_found_exception
     * @throws record_not_found_exception
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_resolve_delete_organisation(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $frm1 = $this->create_org_framework(['fullname' => 'Organisation framework 1', 'idnumber' => 'org_frm_1']);
        $type1_id = $this->create_org_type(['fullname' => 'Organisation type 1', 'idnumber' => 'org_type_1']);
        $parent1 = $this->create_org(['frameworkid' => $frm1->id, 'idnumber' => 'parent1_org']);
        $org = $this->create_org(['frameworkid' => $frm1->id, 'idnumber' => 'xtr_org', 'typeid' => $type1_id, 'parentid' => $parent1->id]);

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'target_organisation' => ['idnumber' => $org->idnumber]
            ]
        );

        $org = builder::table('org')
            ->where('idnumber', $org->idnumber)
            ->one();

        $this->assertNull($org);
    }

    /**
     * @param $data
     * @return bool|int
     * @throws coding_exception
     */
    private function create_org_type($data): bool|int {
        return $this->getDataGenerator()->get_plugin_generator("totara_hierarchy")->create_org_type($data);
    }
}
