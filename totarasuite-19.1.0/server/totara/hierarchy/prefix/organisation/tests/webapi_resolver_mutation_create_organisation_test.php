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
class hierarchy_organisation_webapi_resolver_mutation_create_organisation_test extends testcase {
    use webapi_phpunit_helper;

    protected string $mutation = 'hierarchy_organisation_create_organisation';
    /**
     * @var \totara_hierarchy\testing\generator
     */
    protected $hierarchy_generator = null;

    public function test_resolve_no_permissions(): void {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $this->expectException(required_capability_exception::class);
        $this->expectExceptionMessage('Sorry, but you do not currently have permissions to do that (Create an organisation)');

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'input' => [
                    'fullname' => 'Xtractor',
                    'idnumber' => 'xtr_org',
                    'framework' => [
                        'idnumber' => 'org_frm'
                    ]
                ]
            ]
        );
    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_resolve_no_framework(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $this->expectException(organisation_exception::class);
        $this->expectExceptionMessage('The organisation framework does not exist or you do not have permissions to view it.');

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'input' => [
                    'fullname' => 'Xtractor',
                    'idnumber' => 'xtr_org',
                    'framework' => [
                        'idnumber' => 'org_frm'
                    ]
                ]
            ]
        );
    }

    /**
     * @return void
     * @throws coding_exception|dml_exception
     */
    public function test_resolve_no_type_found(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $frm = $this->create_org_framework(['fullname' => 'Organisation framework 1', 'idnumber' => 'org_frm_1']);

        $this->expectException(organisation_exception::class);
        $this->expectExceptionMessage('The organisation type does not exist or you do not have permissions to view it.');

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'input' => [
                    'fullname' => 'Xtractor',
                    'idnumber' => 'xtr_org',
                    'framework' => [
                        'idnumber' => $frm->idnumber
                    ],
                    'type' => [
                        'idnumber' => 'org_type'
                    ]
                ]
            ]
        );
    }

    /**
     * @return void
     * @throws coding_exception|dml_exception
     */
    public function test_resolve_no_parent_found(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $frm = $this->create_org_framework(['fullname' => 'Organisation framework 1', 'idnumber' => 'org_frm_1']);

        $this->expectException(organisation_exception::class);
        $this->expectExceptionMessage('The parent organisation does not exist or you do not have permissions to view it.');

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'input' => [
                    'fullname' => 'Xtractor',
                    'idnumber' => 'xtr_org',
                    'framework' => [
                        'idnumber' => $frm->idnumber
                    ],
                    'parent' => [
                        'idnumber' => 'parent_org'
                    ]
                ]
            ]
        );
    }

    /**
     * @return void
     * @throws coding_exception|dml_exception
     */
    public function test_resolve_parent_in_another_org(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $frm1 = $this->create_org_framework(['fullname' => 'Organisation framework 1', 'idnumber' => 'org_frm_1']);
        $frm2 = $this->create_org_framework(['fullname' => 'Organisation framework 2', 'idnumber' => 'org_frm_2']);

        $parent = $this->create_org(['frameworkid' => $frm2->id, 'idnumber' => 'parent_org']);

        $this->expectException(organisation_exception::class);
        $this->expectExceptionMessage('The parent organisation belongs to a different framework.');

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'input' => [
                    'fullname' => 'Xtractor',
                    'idnumber' => 'xtr_org',
                    'framework' => [
                        'idnumber' => $frm1->idnumber
                    ],
                    'parent' => [
                        'idnumber' => $parent->idnumber
                    ]
                ]
            ]
        );
    }

    /**
     * @return void
     * @throws coding_exception|dml_exception
     */
    public function test_resolve_org_already_exists(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $frm1 = $this->create_org_framework(['fullname' => 'Organisation framework 1', 'idnumber' => 'org_frm_1']);

        $org = $this->create_org(['frameworkid' => $frm1->id, 'idnumber' => 'xtr_org']);

        $this->expectException(organisation_exception::class);
        $this->expectExceptionMessage('The idnumber is utilised by an existing organisation.');

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'input' => [
                    'fullname' => 'Xtractor',
                    'idnumber' => $org->idnumber,
                    'framework' => [
                        'idnumber' => $frm1->idnumber
                    ]
                ]
            ]
        );
    }

    /**
     * @return void
     * @throws coding_exception|dml_exception
     */
    public function test_resolve_custom_fields_with_no_type(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $frm1 = $this->create_org_framework(['fullname' => 'Organisation framework 1', 'idnumber' => 'org_frm_1']);

        $this->expectException(organisation_exception::class);
        $this->expectExceptionMessage('Custom fields can not be sent if the organisation type is missing.');

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'input' => [
                    'fullname' => 'Xtractor',
                    'idnumber' => 'xtr_org',
                    'framework' => [
                        'idnumber' => $frm1->idnumber
                    ],
                    'custom_fields' => [
                        ['shortname' => 'cf', "data" => "1638258534"]
                    ]
                ]
            ]
        );
    }

    /**
     * @return void
     * @throws coding_exception|dml_exception
     */
    public function test_resolve_wrong_custom_fields(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $frm1 = $this->create_org_framework(['fullname' => 'Organisation framework 1', 'idnumber' => 'org_frm_1']);
        $type1_id = $this->create_org_type(['fullname' => 'Organisation type 1', 'idnumber' => 'org_type_1']);

        $this->expectException(organisation_exception::class);
        $this->expectExceptionMessage('The following custom fields do not exist in the organisation type: cf');

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'input' => [
                    'fullname' => 'Xtractor',
                    'idnumber' => 'xtr_org',
                    'framework' => [
                        'idnumber' => $frm1->idnumber
                    ],
                    'type' => [
                        'id' => $type1_id
                    ],
                    'custom_fields' => [
                        ['shortname' => 'cf', "data" => "1638258534"]
                    ]
                ]
            ]
        );
    }

    /**
     * @return void
     * @throws multiple_records_found_exception
     * @throws record_not_found_exception
     * @throws coding_exception|dml_exception
     */
    public function test_resolve_create_organisation(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $frm1 = $this->create_org_framework(['fullname' => 'Organisation framework 1', 'idnumber' => 'org_frm_1']);
        $type1_id = $this->create_org_type(['fullname' => 'Organisation type 1', 'idnumber' => 'org_type_1']);
        $parent = $this->create_org(['frameworkid' => $frm1->id, 'idnumber' => 'parent_org']);

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'input' => [
                    'fullname' => 'Xtractor',
                    'idnumber' => 'xtr_org',
                    'framework' => [
                        'idnumber' => $frm1->idnumber
                    ],
                    'parent' => [
                        'idnumber' => $parent->idnumber
                    ],
                    'type' => [
                        'id' => $type1_id
                    ]
                ]
            ]
        );

        $org = builder::table('org')
            ->where('idnumber', 'xtr_org')
            ->one();

        $this->assertSame('Xtractor', $org->fullname);
        $this->assertSame($frm1->id, $org->frameworkid);
        $this->assertSame(strval($type1_id), $org->typeid);
        $this->assertSame($parent->id, $org->parentid);
    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws multiple_records_found_exception
     * @throws record_not_found_exception
     */
    public function test_create_org_with_customfields(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $org_type_idnumber = 'org_frm_1';

        $frm1 = $this->create_org_framework(['fullname' => 'Organisation framework 1', 'idnumber' => 'org_frm_1']);
        $type1_id = $this->create_org_type(['fullname' => 'Organisation type 1', 'idnumber' => $org_type_idnumber]);
        $parent = $this->create_org(['frameworkid' => $frm1->id, 'idnumber' => 'parent_org']);

        // setup some default custom fields
        $defaultdata = 'Apple';
        $data = array('hierarchy' => 'organisation', 'typeidnumber' => $org_type_idnumber, 'value' => $defaultdata);
        $this->hierarchy_generator->create_hierarchy_type_text($data);
        $defaultdata_unchanged = 'Original';
        $data = array('hierarchy' => 'organisation', 'typeidnumber' => $org_type_idnumber, 'value' => $defaultdata_unchanged);
        $this->hierarchy_generator->create_hierarchy_type_url($data);

        $response = $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'input' => [
                    'fullname' => 'Xtractor',
                    'idnumber' => 'xtr_org',
                    'framework' => [
                        'idnumber' => $frm1->idnumber
                    ],
                    'parent' => [
                        'idnumber' => $parent->idnumber
                    ],
                    'type' => [
                        'id' => $type1_id
                    ],
                    'custom_fields' => [
                        ['shortname' => 'text'.$type1_id, "data" => "overridden text"],
                    ],
                ]
            ]
        );

        $org = builder::table('org')
            ->where('idnumber', 'xtr_org')
            ->one();

        $custom_fields = customfield_get_custom_fields_and_data_for_items('organisation', [$org->id]);
        $custom_fields = reset($custom_fields);
        $this->assertCount(2, $custom_fields);
        $tested_customfields = false;
        foreach ($custom_fields as $custom_field) {
            $definition = $custom_field['definition'];
            $raw_value = $custom_field['raw_value'];
            if ($definition['shortname'] === 'text'.$type1_id) {
                $this->assertEquals('overridden text', $raw_value);
                $tested_customfields = true;
            }
        }
        $this->assertTrue($tested_customfields);
    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_create_org_invalid_customfield(): void {
        global $DB;
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $org_type_idnumber = 'org_frm_1';

        $frm1 = $this->create_org_framework(['fullname' => 'Organisation framework 1', 'idnumber' => 'org_frm_1']);
        $type1_id = $this->create_org_type(['fullname' => 'Organisation type 1', 'idnumber' => $org_type_idnumber]);
        $parent = $this->create_org(['frameworkid' => $frm1->id, 'idnumber' => 'parent_org']);

        // setup some default custom fields
        $defaultdata = 'Apple';
        $data = array('hierarchy' => 'organisation', 'typeidnumber' => $org_type_idnumber, 'value' => $defaultdata);
        $this->hierarchy_generator->create_hierarchy_type_text($data);
        $defaultdata_unchanged = 'Original';
        $data = array('hierarchy' => 'organisation', 'typeidnumber' => $org_type_idnumber, 'value' => $defaultdata_unchanged);
        $this->hierarchy_generator->create_hierarchy_type_url($data);

        // the request should fail because the URL supplied for the url type field
        // is incorrect
        $this->expectException(organisation_exception::class);
        $this->expectExceptionMessage('Error saving custom fields: The URL needs to start with http://, https:// or /');

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'input' => [
                    'fullname' => 'Xtractor',
                    'idnumber' => 'xtr_org',
                    'framework' => [
                        'idnumber' => $frm1->idnumber
                    ],
                    'parent' => [
                        'idnumber' => $parent->idnumber
                    ],
                    'type' => [
                        'id' => $type1_id
                    ],
                    'custom_fields' => [
                        ['shortname' => 'url'.$type1_id, "data" => ['url' => "htt://broken.link"]]
                    ],
                ]
            ]
        );
    }

    /**
     * @return stdClass|array
     * @throws coding_exception
     * @throws dml_exception
     */
    private function create_api_user(): stdClass|array {
        $user = $this->getDataGenerator()->create_user();

        // Give the API user the required capabilities through a role.
        $gen = static::getDataGenerator();
        $role_id = $gen->create_role();
        assign_capability('totara/hierarchy:createorganisation', CAP_ALLOW, $role_id, context_system::instance());
        role_assign($role_id, $user->id, context_system::instance());

        return $user;
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
     * @return stdClass
     * @throws coding_exception
     */
    private function create_org_framework($data): stdClass {
        return $this->getDataGenerator()->get_plugin_generator("totara_hierarchy")->create_framework("organisation", $data);
    }

    /**
     * @param $data
     * @return bool|int
     * @throws coding_exception
     */
    private function create_org_type($data): bool|int {
        return $this->getDataGenerator()->get_plugin_generator("totara_hierarchy")->create_org_type($data);
    }

    protected function setUp(): void {
        parent::setup();

        $generator = $this->getDataGenerator();
        $this->hierarchy_generator = $generator->get_plugin_generator('totara_hierarchy');
    }
}
