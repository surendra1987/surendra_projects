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

use core_phpunit\testcase;
use hierarchy_organisation\exception\organisation_exception;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * @group xtractor
 */
class hierarchy_organisation_webapi_resolver_query_organisation_test extends testcase {
    use webapi_phpunit_helper;

    protected string $query = 'hierarchy_organisation_organisation';

    /**
     * @var \totara_hierarchy\testing\generator
     */
    protected $hierarchy_generator = null;

    protected function tearDown(): void {
        $this->hierarchy_generator = null;
        parent::tearDown();
    }

    protected function setUp(): void {
        parent::setup();
        $this->setAdminUser();

        $generator = $this->getDataGenerator();

        // Set \totara_hierarchy\testing\generator.
        $this->hierarchy_generator = $generator->get_plugin_generator('totara_hierarchy');
    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_resolve_no_reference(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $this->expectException(organisation_exception::class);
        $this->expectExceptionMessage('The required variable "reference" is empty.');

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
        assign_capability('totara/hierarchy:vieworganisation', CAP_ALLOW, $role_id, context_system::instance());
        role_assign($role_id, $user->id, context_system::instance());

        return $user;
    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_resolve_no_org(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $result = $this->resolve_graphql_query($this->query, ['reference' => ['idnumber' => 'xtr_org']]);
        $this->assertNull($result['organisation']);
        $this->assertFalse($result['found']);
    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_resolve_organisation(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $frm = $this->create_org_framework([]);
        $org = $this->create_org(['frameworkid' => $frm->id, 'idnumber' => 'xtr_org']);

        $result = $this->resolve_graphql_query($this->query, ['reference' => ['idnumber' => 'xtr_org']]);
        $this->assertEquals($result['organisation']->id, $org->id);
        $this->assertTrue($result['found']);
    }

    public function test_resolve_organisation_with_customfields(): void {
        global $DB;
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $frm = $this->create_org_framework([]);

        $id = $this->hierarchy_generator->create_org_type();
        if (!$typeid = $DB->get_field('org_type', 'idnumber', array('id' => $id))) {
            throw new coding_exception('Unknown hierarchy type id ' . $id . ' in hierarchy definition');
        }
        $org = $this->create_org(['frameworkid' => $frm->id, 'idnumber' => 'xtr_org', 'typeid' => $id]);

        // Create checkbox for organisation type.
        $defaultdata = 1; // Checked.
        $data = array('hierarchy' => 'organisation', 'typeidnumber' => $typeid, 'value' => $defaultdata);
        $this->hierarchy_generator->create_hierarchy_type_checkbox($data);

        // Create text for organisation type.
        $defaultdata = 'Apple';
        $data = array('hierarchy' => 'organisation', 'typeidnumber' => $typeid, 'value' => $defaultdata);
        $this->hierarchy_generator->create_hierarchy_type_text($data);

        // Create menu of choice for organisation type.
        $defaultdata = '2345';
        $data = array('hierarchy' => 'organisation', 'typeidnumber' => $typeid, 'value' => $defaultdata);
        $this->hierarchy_generator->create_hierarchy_type_menu($data);

        // Create text for organisation type.
        $defaultdata = '0'; // No valid value in the default data column needed.
        $data = array('hierarchy' => 'organisation', 'typeidnumber' => $typeid, 'value' => $defaultdata);
        $this->hierarchy_generator->create_hierarchy_type_datetime($data);

        $result = $this->resolve_graphql_query($this->query, ['reference' => ['idnumber' => 'xtr_org']]);
        $this->assertEquals($result['organisation']->id, $org->id);
        $this->assertTrue($result['found']);
        $custom_fields = $this->execution_context->get_variable('custom_fields');

        $org_custom_fields = reset($custom_fields);
        $this->assertNotEmpty($org_custom_fields);
        foreach ($org_custom_fields as $field) {
            $type = $field['definition']["type"];
            $default_value = $field['definition']["raw_default_value"];
            switch ($type) {
                case "checkbox":
                    $this->assertSame("1", $default_value);
                    break;
                case "text":
                    $this->assertSame("Apple", $default_value);
                    break;
                case "menu":
                    $this->assertSame("2345", $default_value);
                    break;
                case "datetime":
                    $this->assertSame("0", $default_value);
                    break;
                default:
                    $this->throwException("Unknown organisation custom field type '$type'");
                    break;
            }
        }
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
     * @param $data
     * @return bool|int
     * @throws coding_exception
     */
    private function create_org_type($data): bool|int {
        return $this->getDataGenerator()->get_plugin_generator("totara_hierarchy")->create_org_type($data);
    }
}
