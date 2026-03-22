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

use core_phpunit\testcase;
use hierarchy_position\exception\position_exception;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * @group xtractor
 */
class hierarchy_position_webapi_resolver_query_position_test extends testcase {
    use webapi_phpunit_helper;

    protected string $query = 'hierarchy_position_position';

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_resolve_no_reference(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $this->expectException(position_exception::class);
        $this->expectExceptionMessage('The required "reference" parameter is empty.');

        $this->resolve_graphql_query($this->query, ['reference' => '']);
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
        assign_capability('totara/hierarchy:viewposition', CAP_ALLOW, $role_id, context_system::instance());
        role_assign($role_id, $user->id, context_system::instance());

        return $user;
    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_resolve_no_pos(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $result = $this->resolve_graphql_query($this->query, ['reference' => ['idnumber' => 'non_existent_position']]);
        $this->assertFalse($result['found']);
        $this->assertNull($result['position']);
    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_resolve_position(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $frm = $this->create_pos_framework([]);
        $pos = $this->create_pos(['frameworkid' => $frm->id, 'idnumber' => 'xtr_pos']);

        $result = $this->resolve_graphql_query($this->query, ['reference' => ['idnumber' => 'xtr_pos']]);
        $this->assertEquals($result['position']->id, $pos->id);
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
     * @param $data
     * @return bool|int
     * @throws coding_exception
     */
    private function create_pos_type($data): bool|int {
        return $this->getDataGenerator()->get_plugin_generator("totara_hierarchy")->create_pos_type($data);
    }
}
