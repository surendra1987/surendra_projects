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
 * @package hierarchy_position
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

use core\orm\query\builder;
use core\orm\query\exceptions\multiple_records_found_exception;
use core\orm\query\exceptions\record_not_found_exception;
use core_phpunit\testcase;
use hierarchy_position\exception\position_exception;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * @group xtractor
 */
class hierarchy_position_webapi_resolver_mutation_delete_position_test extends testcase {
    use webapi_phpunit_helper;

    protected string $mutation = 'hierarchy_position_delete_position';

    /**
     * @return void
     * @throws coding_exception
     */
    public function test_resolve_no_permissions(): void {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $frm1 = $this->create_pos_framework(['fullname' => 'Position framework 1', 'idnumber' => 'pos_frm_1']);
        $pos = $this->create_pos(['frameworkid' => $frm1->id, 'idnumber' => 'xtr_pos']);

        $this->expectException(required_capability_exception::class);
        $this->expectExceptionMessage('Sorry, but you do not currently have permissions to do that (Delete a position)');

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'target_position' => ['idnumber' => $pos->idnumber]
            ]
        );
    }

    /**
     * @param $data
     * @return stdClass
     * @throws coding_exception
     */
    private function create_pos_framework($data): stdClass {
        return $this->getDataGenerator()->get_plugin_generator("totara_hierarchy")->create_framework("position", $data);
    }

    /**
     * @param $data
     * @return stdClass
     * @throws coding_exception
     */
    private function create_pos($data): stdClass {
        return $this->getDataGenerator()->get_plugin_generator("totara_hierarchy")->create_pos($data);
    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_resolve_target_pos_not_exist(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $this->expectException(position_exception::class);
        $this->expectExceptionMessage('The target position does not exist or you do not have permissions to view it.');

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'target_position' => ['idnumber' => 'xtr_pos']
            ]
        );
    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_resolve_target_position_empty(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $this->expectException(position_exception::class);
        $this->expectExceptionMessage('The required "target_position" parameter is empty.');

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'target_position' => []
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
        assign_capability('totara/hierarchy:deleteposition', CAP_ALLOW, $role_id, context_system::instance());
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
    public function test_resolve_delete_position(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $frm1 = $this->create_pos_framework(['fullname' => 'Position framework 1', 'idnumber' => 'pos_frm_1']);
        $type1_id = $this->create_pos_type(['fullname' => 'Position type 1', 'idnumber' => 'pos_type_1']);
        $parent1 = $this->create_pos(['frameworkid' => $frm1->id, 'idnumber' => 'parent1_pos']);
        $pos = $this->create_pos(['frameworkid' => $frm1->id, 'idnumber' => 'xtr_pos', 'typeid' => $type1_id, 'parentid' => $parent1->id]);

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'target_position' => ['idnumber' => $pos->idnumber]
            ]
        );

        $pos = builder::table('pos')
            ->where('idnumber', $pos->idnumber)
            ->one();

        $this->assertNull($pos);
    }

    /**
     * @param $data
     * @return bool|int
     * @throws coding_exception
     */
    private function create_pos_type($data): bool|int {
        return $this->getDataGenerator()->get_plugin_generator("totara_hierarchy")->create_pos_type($data);
    }
}
