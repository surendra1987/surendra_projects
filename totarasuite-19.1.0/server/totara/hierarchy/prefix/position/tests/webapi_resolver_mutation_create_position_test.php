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
class hierarchy_position_webapi_resolver_mutation_create_position_test extends testcase {
    use webapi_phpunit_helper;

    protected string $mutation = 'hierarchy_position_create_position';
    /**
     * @var \totara_hierarchy\testing\generator
     */
    protected $hierarchy_generator = null;

    /**
     * @return void
     */
    public function test_resolve_no_permissions(): void {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $this->expectException(required_capability_exception::class);
        $this->expectExceptionMessage('Sorry, but you do not currently have permissions to do that (Create a position)');

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'input' => [
                    'fullname' => 'Developer',
                    'idnumber' => 'xtr_pos',
                    'framework' => [
                        'idnumber' => 'pos_frm'
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

        $this->expectException(position_exception::class);
        $this->expectExceptionMessage('The position framework does not exist or you do not have permissions to view it.');

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'input' => [
                    'fullname' => 'Developer',
                    'idnumber' => 'xtr_pos',
                    'framework' => [
                        'idnumber' => 'pos_frm'
                    ]
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
        assign_capability('totara/hierarchy:createposition', CAP_ALLOW, $role_id, context_system::instance());
        role_assign($role_id, $user->id, context_system::instance());

        return $user;
    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_resolve_no_type_found(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $frm = $this->create_pos_framework(['fullname' => 'Position framework 1', 'idnumber' => 'pos_frm_1']);

        $this->expectException(position_exception::class);
        $this->expectExceptionMessage('The position type does not exist or you do not have permissions to view it.');

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'input' => [
                    'fullname' => 'Developer',
                    'idnumber' => 'xtr_pos',
                    'framework' => [
                        'idnumber' => $frm->idnumber
                    ],
                    'type' => [
                        'idnumber' => 'pos_type'
                    ]
                ]
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
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_resolve_no_parent_found(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $frm = $this->create_pos_framework(['fullname' => 'Position framework 1', 'idnumber' => 'pos_frm_1']);

        $this->expectException(position_exception::class);
        $this->expectExceptionMessage('The parent position does not exist or you do not have permissions to view it.');

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'input' => [
                    'fullname' => 'Developer',
                    'idnumber' => 'xtr_pos',
                    'framework' => [
                        'idnumber' => $frm->idnumber
                    ],
                    'parent' => [
                        'idnumber' => 'parent_pos'
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
    public function test_resolve_parent_in_another_pos(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $frm1 = $this->create_pos_framework(['fullname' => 'Position framework 1', 'idnumber' => 'pos_frm_1']);
        $frm2 = $this->create_pos_framework(['fullname' => 'Position framework 2', 'idnumber' => 'pos_frm_2']);

        $parent = $this->create_pos(['frameworkid' => $frm2->id, 'idnumber' => 'parent_pos']);

        $this->expectException(position_exception::class);
        $this->expectExceptionMessage('The parent position belongs to a different framework.');

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'input' => [
                    'fullname' => 'Developer',
                    'idnumber' => 'xtr_pos',
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
    public function test_resolve_pos_already_exists(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $frm1 = $this->create_pos_framework(['fullname' => 'Position framework 1', 'idnumber' => 'pos_frm_2']);

        $pos = $this->create_pos(['frameworkid' => $frm1->id, 'idnumber' => 'xtr_pos']);

        $this->expectException(position_exception::class);
        $this->expectExceptionMessage('The idnumber is utilised by an existing position.');

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'input' => [
                    'fullname' => 'Developer',
                    'idnumber' => $pos->idnumber,
                    'framework' => [
                        'idnumber' => $frm1->idnumber
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
    public function test_resolve_custom_fields_with_no_type(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $frm1 = $this->create_pos_framework(['fullname' => 'Position framework 1', 'idnumber' => 'pos_frm_2']);

        $this->expectException(position_exception::class);
        $this->expectExceptionMessage('Custom fields can not be sent if the position type is missing.');

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'input' => [
                    'fullname' => "Developer",
                    'idnumber' => "xtr_pos",
                    'framework' => [
                        'idnumber' => $frm1->idnumber
                    ],
                    'custom_fields' => [
                        [
                            "shortname" => "cf",
                            "data" => "1638258534"
                        ]
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
    public function test_resolve_wrong_custom_fields(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $frm1 = $this->create_pos_framework(['fullname' => 'Position framework 1', 'idnumber' => 'pos_frm_3']);
        $type1_id = $this->create_pos_type(['fullname' => 'Position type 1', 'idnumber' => 'pos_type_2']);

        $this->expectException(position_exception::class);
        $this->expectExceptionMessage('The following custom fields do not exist on the given position type: cf');

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'input' => [
                    'fullname' => 'Developer',
                    'idnumber' => 'xtr_pos',
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
     * @param $data
     * @return bool|int
     * @throws coding_exception
     */
    private function create_pos_type($data): bool|int {
        return $this->getDataGenerator()->get_plugin_generator("totara_hierarchy")->create_pos_type($data);
    }

    /**
     * @return void
     * @throws multiple_records_found_exception
     * @throws record_not_found_exception
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_resolve_create_position(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $frm1 = $this->create_pos_framework(['fullname' => 'Position framework 1', 'idnumber' => 'pos_frm_1']);
        $type1_id = $this->create_pos_type(['fullname' => 'Position type 1', 'idnumber' => 'pos_type_1']);
        $parent = $this->create_pos(['frameworkid' => $frm1->id, 'idnumber' => 'parent_pos']);

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'input' => [
                    'fullname' => 'Developer',
                    'idnumber' => 'xtr_pos',
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

        $pos = builder::table('pos')
            ->where('idnumber', 'xtr_pos')
            ->one();

        $this->assertSame('Developer', $pos->fullname);
        $this->assertSame($frm1->id, $pos->frameworkid);
        $this->assertSame(strval($type1_id), $pos->typeid);
        $this->assertSame($parent->id, $pos->parentid);
    }

    public function test_create_position_with_customfields(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $pos_type_idnumber = 'pos_type_1';
        $frm1 = $this->create_pos_framework(['fullname' => 'Position framework 1', 'idnumber' => 'pos_frm_1']);
        $type1_id = $this->create_pos_type(['fullname' => 'Position type 1', 'idnumber' => $pos_type_idnumber]);
        $parent = $this->create_pos(['frameworkid' => $frm1->id, 'idnumber' => 'parent_pos']);

        // setup some default custom fields
        $defaultdata = 'Apple';
        $data = array('hierarchy' => 'position', 'typeidnumber' => $pos_type_idnumber, 'value' => $defaultdata);
        $this->hierarchy_generator->create_hierarchy_type_text($data);
        $defaultdata_unchanged = 'Original';
        $data = array('hierarchy' => 'position', 'typeidnumber' => $pos_type_idnumber, 'value' => $defaultdata_unchanged);
        $this->hierarchy_generator->create_hierarchy_type_url($data);

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'input' => [
                    'fullname' => 'Developer',
                    'idnumber' => 'xtr_pos',
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

        $pos = builder::table('pos')
            ->where('idnumber', 'xtr_pos')
            ->one();

        $custom_fields = customfield_get_custom_fields_and_data_for_items('position', [$pos->id]);
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
     * @throws multiple_records_found_exception
     * @throws record_not_found_exception
     */
    public function test_create_position_invalid_customfield(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $pos_type_idnumber = 'pos_type_1';
        $frm1 = $this->create_pos_framework(['fullname' => 'Position framework 1', 'idnumber' => 'pos_frm_1']);
        $type1_id = $this->create_pos_type(['fullname' => 'Position type 1', 'idnumber' => $pos_type_idnumber]);
        $parent = $this->create_pos(['frameworkid' => $frm1->id, 'idnumber' => 'parent_pos']);

        // setup some default custom fields
        $defaultdata = 'Apple';
        $data = array('hierarchy' => 'position', 'typeidnumber' => $pos_type_idnumber, 'value' => $defaultdata);
        $this->hierarchy_generator->create_hierarchy_type_text($data);
        $defaultdata_unchanged = 'Original';
        $data = array('hierarchy' => 'position', 'typeidnumber' => $pos_type_idnumber, 'value' => $defaultdata_unchanged);
        $this->hierarchy_generator->create_hierarchy_type_url($data);

        // the request should fail because the URL supplied for the url type field
        // is incorrect
        $this->expectException(position_exception::class);
        $this->expectExceptionMessage('Error saving custom fields: The URL needs to start with http://, https:// or /');

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'input' => [
                    'fullname' => 'Developer',
                    'idnumber' => 'xtr_pos',
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
                        ['shortname' => 'url'.$type1_id, "data" => ['url' => 'htt://broken.link']],
                    ],
                ]
            ]
        );
    }

    protected function setUp(): void {
        parent::setup();

        $generator = $this->getDataGenerator();
        $this->hierarchy_generator = $generator->get_plugin_generator('totara_hierarchy');
    }
}
