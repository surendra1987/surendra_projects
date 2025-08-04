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
use hierarchy_position\webapi\resolver\mutation\update_position;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * @group xtractor
 */
class hierarchy_position_webapi_resolver_mutation_update_position_test extends testcase {
    use webapi_phpunit_helper;

    protected string $mutation = 'hierarchy_position_update_position';
    /**
     * @var \totara_hierarchy\testing\generator
     */
    protected $hierarchy_generator = null;

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
        $this->expectExceptionMessage('Sorry, but you do not currently have permissions to do that (Update a position)');

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'target_position' => ['idnumber' => $pos->idnumber],
                'input' => [
                    'fullname' => 'Developer',
                    'framework' => [
                        'idnumber' => 'pos_frm'
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
                'target_position' => ['idnumber' => 'xtr_pos'],
                'input' => [
                    'fullname' => 'Developer',
                    'framework' => [
                        'idnumber' => 'pos_frm'
                    ]
                ]
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
        assign_capability('totara/hierarchy:updateposition', CAP_ALLOW, $role_id, context_system::instance());
        role_assign($role_id, $user->id, context_system::instance());

        return $user;
    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_resolve_no_framework(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $frm1 = $this->create_pos_framework(['fullname' => 'Position framework 1', 'idnumber' => 'pos_frm_1']);
        $pos = $this->create_pos(['frameworkid' => $frm1->id, 'idnumber' => 'xtr_pos']);

        $this->expectException(position_exception::class);
        $this->expectExceptionMessage('The position framework does not exist or you do not have permissions to view it.');

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'target_position' => ['idnumber' => $pos->idnumber],
                'input' => [
                    'fullname' => 'Developer',
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
    public function test_resolve_no_type_found(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $frm = $this->create_pos_framework(['fullname' => 'Position framework 1', 'idnumber' => 'pos_frm_1']);
        $pos = $this->create_pos(['frameworkid' => $frm->id, 'idnumber' => 'xtr_pos']);

        $this->expectException(position_exception::class);
        $this->expectExceptionMessage('The position type does not exist or you do not have permissions to view it.');

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'target_position' => ['idnumber' => $pos->idnumber],
                'input' => [
                    'fullname' => 'Developer',
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
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_resolve_update_framework(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $framework_1 = $this->create_pos_framework(['fullname' => 'Position framework 1', 'idnumber' => 'pos_frm_1']);
        $framework_2 = $this->create_pos_framework(['fullname' => 'Position framework 2', 'idnumber' => 'pos_frm_2']);
        $pos = $this->create_pos(['frameworkid' => $framework_1->id, 'idnumber' => 'pos1']);

        $position = ($this->resolve_graphql_mutation(
            $this->mutation,
            [
                'target_position' => ['idnumber' => $pos->idnumber],
                'input' => [
                    'fullname' => 'Developer',
                    'framework' => [
                        'idnumber' => $framework_2->idnumber
                    ],
                ]
            ]
        ))['position'];

        $this->assertEquals($framework_2->id, $position->frameworkid);
    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_resolve_change_position_framework_with_children(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $framework_1 = $this->create_pos_framework(['fullname' => 'Position framework 1', 'idnumber' => 'framework_1']);
        $framework_2 = $this->create_pos_framework(['fullname' => 'Position framework 2', 'idnumber' => 'framework_2']);

        $parent_1 = $this->create_pos(
            [
                'frameworkid' => $framework_1->id,
            ]
        );

        $this->create_pos(
            [
                'frameworkid' => $framework_1->id,
                'parentid' => (
                    $this->create_pos(
                        [
                            'frameworkid' => $framework_1->id,
                            'parentid' => $parent_1->id,
                        ]
                    )
                )->id
            ]
        );

        $parent_2 = $this->create_pos(
            [
                'frameworkid' => $framework_1->id,
            ]
        );

        $this->create_pos(
            [
                'frameworkid' => $framework_1->id,
                'parentid' => (
                    $this->create_pos(
                        [
                            'frameworkid' => $framework_1->id,
                            'parentid' => $parent_2->id,
                        ]
                    )
                )->id
            ]
        );

        $this->create_pos(
            [
                'frameworkid' => $framework_1->id,
                'parentid' => $parent_2->id,
            ]
        );

        // Update parent_1 to a new framework and check that the children got moved with it
        $parent_1 = ($this->resolve_graphql_mutation(
            $this->mutation,
            [
                'target_position' => ['idnumber' => $parent_1->idnumber],
                'input' => [
                    'framework' => [
                        'idnumber' => $framework_2->idnumber
                    ],
                ]
            ]
        ))['position'];

        $this->assertEquals($framework_2->id, $parent_1->frameworkid);

        $hierarchy = \hierarchy::load_hierarchy('position');
        $children = $hierarchy->get_item_descendants($parent_1->id, '*');

        $this->assertCount(3, $children);
        foreach($children as $child) {
            $this->assertEquals($parent_1->frameworkid, $child->frameworkid);
        }

        // Update parent_2 to a new framework and check that the children got moved with it
        $parent_2 = ($this->resolve_graphql_mutation(
            $this->mutation,
            [
                'target_position' => ['idnumber' => $parent_2->idnumber],
                'input' => [
                    'framework' => [
                        'idnumber' => $framework_2->idnumber
                    ],
                ]
            ]
        ))['position'];

        $this->assertEquals($framework_2->id, $parent_2->frameworkid);

        $hierarchy = \hierarchy::load_hierarchy('position');
        $children = $hierarchy->get_item_descendants($parent_2->id, '*');

        $this->assertCount(4, $children);
        foreach($children as $child) {
            $this->assertEquals($parent_2->frameworkid, $child->frameworkid);
        }
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
        $pos = $this->create_pos(['frameworkid' => $frm->id, 'idnumber' => 'xtr_pos']);

        $this->expectException(position_exception::class);
        $this->expectExceptionMessage('The parent position does not exist or you do not have permissions to view it.');

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'target_position' => ['idnumber' => $pos->idnumber],
                'input' => [
                    'fullname' => 'Developer',
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
    public function test_resolve_parent_same_as_target(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $frm = $this->create_pos_framework(['fullname' => 'Position framework 1', 'idnumber' => 'pos_frm_1']);
        $pos = $this->create_pos(['frameworkid' => $frm->id, 'idnumber' => 'xtr_pos']);

        $this->expectException(position_exception::class);
        $this->expectExceptionMessage('The parent was resolved to be the same as the target position. The position cannot be a parent of itself.');

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'target_position' => ['idnumber' => $pos->idnumber],
                'input' => [
                    'fullname' => 'Developer',
                    'framework' => [
                        'idnumber' => $frm->idnumber
                    ],
                    'parent' => [
                        'idnumber' => $pos->idnumber
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
    public function test_resolve_unsetting_parent(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $frm1 = $this->create_pos_framework(['fullname' => 'Position framework 1', 'idnumber' => 'pos_frm_1']);
        $parent = $this->create_pos(['frameworkid' => $frm1->id, 'idnumber' => 'parent_pos']);
        $pos = $this->create_pos(['frameworkid' => $frm1->id, 'idnumber' => 'xtr_pos', 'parentid' => $parent->id]);

        $result = $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'target_position' => ['idnumber' => $pos->idnumber],
                'input' => [
                    'fullname' => 'Developer',
                    'framework' => [
                        'idnumber' => $frm1->idnumber
                    ],
                    'parent' => null,
                ]
            ]
        );

        $this->assertEquals(0, $result['position']->parentid);
    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_resolve_parent_in_another_pos_framework(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $frm1 = $this->create_pos_framework(['fullname' => 'Position framework 1', 'idnumber' => 'pos_frm_1']);
        $frm2 = $this->create_pos_framework(['fullname' => 'Position framework 2', 'idnumber' => 'pos_frm_2']);
        $pos = $this->create_pos(['frameworkid' => $frm1->id, 'idnumber' => 'xtr_pos']);
        $parent = $this->create_pos(['frameworkid' => $frm2->id, 'idnumber' => 'parent_pos']);

        $this->expectException(position_exception::class);
        $this->expectExceptionMessage('The parent position belongs to a different framework.');

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'target_position' => ['idnumber' => $pos->idnumber],
                'input' => [
                    'fullname' => 'Developer',
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
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_resolve_parent_same_as_target_position(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $frm1 = $this->create_pos_framework(['fullname' => 'Position framework 1', 'idnumber' => 'pos_frm_1']);
        $pos = $this->create_pos(['frameworkid' => $frm1->id, 'idnumber' => 'xtr_pos']);

        $this->expectException(position_exception::class);
        $this->expectExceptionMessage("The parent was resolved to be the same as the target position. The position cannot be a parent of itself.");

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'target_position' => ['idnumber' => $pos->idnumber],
                'input' => [
                    'fullname' => 'Developer',
                    'framework' => [
                        'idnumber' => $frm1->idnumber
                    ],
                    'parent' => [
                        'idnumber' => $pos->idnumber
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
    public function test_resolve_idnumber_already_exists(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $frm1 = $this->create_pos_framework(['fullname' => 'Position framework 1', 'idnumber' => 'pos_frm_1']);
        $pos1 = $this->create_pos(['frameworkid' => $frm1->id, 'idnumber' => 'xtr_pos_1']);
        $pos2 = $this->create_pos(['frameworkid' => $frm1->id, 'idnumber' => 'xtr_pos_2']);

        $this->expectException(position_exception::class);
        $this->expectExceptionMessage('The idnumber is utilised by an existing position.');

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'target_position' => ['idnumber' => $pos1->idnumber],
                'input' => [
                    'fullname' => 'Developer',
                    'idnumber' => $pos2->idnumber
                ]
            ]
        );
    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_resolve_update_framework_but_not_parent(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $frm1 = $this->create_pos_framework(['fullname' => 'Position framework 1', 'idnumber' => 'pos_frm_1']);
        $frm2 = $this->create_pos_framework(['fullname' => 'Position framework 2', 'idnumber' => 'pos_frm_2']);
        $parent = $this->create_pos(['frameworkid' => $frm1->id, 'idnumber' => 'parent_pos']);
        $pos = $this->create_pos(['frameworkid' => $frm1->id, 'idnumber' => 'xtr_pos', 'parentid' => $parent->id]);

        $this->expectException(position_exception::class);
        $this->expectExceptionMessage('The parent position belongs to a different framework.');

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'target_position' => ['idnumber' => $pos->idnumber],
                'input' => [
                    'fullname' => 'Developer',
                    'framework' => [
                        'idnumber' => $frm2->idnumber
                    ],
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

        $frm1 = $this->create_pos_framework(['fullname' => 'Position framework 1', 'idnumber' => 'pos_frm_1']);
        $pos = $this->create_pos(['frameworkid' => $frm1->id, 'idnumber' => 'xtr_pos']);

        $this->expectException(position_exception::class);
        $this->expectExceptionMessage('Position type could not be determined. A type is necessary to update custom fields.');

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'target_position' => ['idnumber' => $pos->idnumber],
                'input' => [
                    'fullname' => 'Developer',
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
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_resolve_wrong_custom_fields(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $frm1 = $this->create_pos_framework(['fullname' => 'Position framework 1', 'idnumber' => 'pos_frm_1']);
        $type1_id = $this->create_pos_type(['fullname' => 'Position type 1', 'idnumber' => 'pos_type_1']);
        $this->create_pos(['frameworkid' => $frm1->id, 'idnumber' => 'xtr_pos', 'typeid' => $type1_id]);

        $this->expectException(position_exception::class);
        $this->expectExceptionMessage('The following custom fields do not exist on the given position type: cf');

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'target_position' => ['idnumber' => 'xtr_pos'],
                'input' => [
                    'fullname' => 'Developer',
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
    public function test_resolve_update_position(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $frm1 = $this->create_pos_framework(['fullname' => 'Position framework 1', 'idnumber' => 'pos_frm_1']);
        $frm2 = $this->create_pos_framework(['fullname' => 'Position framework 2', 'idnumber' => 'pos_frm_2']);
        $type1_id = $this->create_pos_type(['fullname' => 'Position type 1', 'idnumber' => 'pos_type_1']);
        $type2_id = $this->create_pos_type(['fullname' => 'Position type 2', 'idnumber' => 'pos_type_2']);
        $parent1 = $this->create_pos(['frameworkid' => $frm1->id, 'idnumber' => 'parent1_pos']);
        $parent2 = $this->create_pos(['frameworkid' => $frm2->id, 'idnumber' => 'parent2_pos']);
        $pos = $this->create_pos(['frameworkid' => $frm1->id, 'idnumber' => 'xtr_pos', 'typeid' => $type1_id, 'parentid' => $parent1->id]);

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'target_position' => ['idnumber' => $pos->idnumber],
                'input' => [
                    'fullname' => 'Developer',
                    'framework' => [
                        'idnumber' => $frm2->idnumber
                    ],
                    'parent' => [
                        'idnumber' => $parent2->idnumber
                    ],
                    'type' => [
                        'id' => $type2_id
                    ]
                ]
            ]
        );

        $pos = builder::table('pos')
            ->where('idnumber', $pos->idnumber)
            ->one();

        $this->assertSame('Developer', $pos->fullname);
        $this->assertSame($frm2->id, $pos->frameworkid);
        $this->assertSame(strval($type2_id), $pos->typeid);
        $this->assertSame($parent2->id, $pos->parentid);
    }

    /**
     * @throws coding_exception
     * @throws multiple_records_found_exception
     * @throws record_not_found_exception
     * @throws dml_exception
     */
    public function test_update_position_with_customfields(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $pos_type2_idnumber = 'pos_type_2';

        $frm1 = $this->create_pos_framework(['fullname' => 'Position framework 1', 'idnumber' => 'pos_frm_1']);
        $frm2 = $this->create_pos_framework(['fullname' => 'Position framework 2', 'idnumber' => 'pos_frm_2']);
        $type1_id = $this->create_pos_type(['fullname' => 'Position type 1', 'idnumber' => 'pos_type_1']);
        $type2_id = $this->create_pos_type(['fullname' => 'Position type 2', 'idnumber' => $pos_type2_idnumber]);
        $parent1 = $this->create_pos(['frameworkid' => $frm1->id, 'idnumber' => 'parent1_pos']);
        $parent2 = $this->create_pos(['frameworkid' => $frm2->id, 'idnumber' => 'parent2_pos']);
        $pos = $this->create_pos(['frameworkid' => $frm1->id, 'idnumber' => 'xtr_pos', 'typeid' => $type1_id, 'parentid' => $parent1->id]);

        // setup some default custom fields
        $defaultdata = 'Apple';
        $data = array('hierarchy' => 'position', 'typeidnumber' => $pos_type2_idnumber, 'value' => $defaultdata);
        $this->hierarchy_generator->create_hierarchy_type_text($data);
        $defaultdata_unchanged = 'Original';
        $data = array('hierarchy' => 'position', 'typeidnumber' => $pos_type2_idnumber, 'value' => $defaultdata_unchanged);
        $this->hierarchy_generator->create_hierarchy_type_url($data);

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'target_position' => ['idnumber' => $pos->idnumber],
                'input' => [
                    'fullname' => 'Developer',
                    'framework' => [
                        'idnumber' => $frm2->idnumber
                    ],
                    'parent' => [
                        'idnumber' => $parent2->idnumber
                    ],
                    'type' => [
                        'id' => $type2_id
                    ],
                    'custom_fields' => [
                        ['shortname' => 'text'.$type2_id, "data" => "overridden text"],
                    ],
                ]
            ]
        );

        $pos = builder::table('pos')
            ->where('idnumber', $pos->idnumber)
            ->one();

        $custom_fields = customfield_get_custom_fields_and_data_for_items('position', [$pos->id]);
        $custom_fields = reset($custom_fields);
        $this->assertCount(2, $custom_fields);
        $tested_customfields = false;
        foreach ($custom_fields as $custom_field) {
            $definition = $custom_field['definition'];
            $raw_value = $custom_field['raw_value'];
            if ($definition['shortname'] === 'text'.$type2_id) {
                $this->assertEquals('overridden text', $raw_value);
                $tested_customfields = true;
            }
        }
        $this->assertTrue($tested_customfields);
    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_update_position_invalid_customfield(): void {
        $api_user = $this->create_api_user();
        $this->setUser($api_user);

        $pos_type2_idnumber = 'pos_type_2';

        $frm1 = $this->create_pos_framework(['fullname' => 'Position framework 1', 'idnumber' => 'pos_frm_1']);
        $frm2 = $this->create_pos_framework(['fullname' => 'Position framework 2', 'idnumber' => 'pos_frm_2']);
        $type1_id = $this->create_pos_type(['fullname' => 'Position type 1', 'idnumber' => 'pos_type_1']);
        $type2_id = $this->create_pos_type(['fullname' => 'Position type 2', 'idnumber' => $pos_type2_idnumber]);
        $parent1 = $this->create_pos(['frameworkid' => $frm1->id, 'idnumber' => 'parent1_pos']);
        $parent2 = $this->create_pos(['frameworkid' => $frm2->id, 'idnumber' => 'parent2_pos']);
        $pos = $this->create_pos(['frameworkid' => $frm1->id, 'idnumber' => 'xtr_pos', 'typeid' => $type1_id, 'parentid' => $parent1->id]);

        // setup some default custom fields
        $defaultdata = 'Apple';
        $data = array('hierarchy' => 'position', 'typeidnumber' => $pos_type2_idnumber, 'value' => $defaultdata);
        $this->hierarchy_generator->create_hierarchy_type_text($data);
        $defaultdata_unchanged = 'Original';
        $data = array('hierarchy' => 'position', 'typeidnumber' => $pos_type2_idnumber, 'value' => $defaultdata_unchanged);
        $this->hierarchy_generator->create_hierarchy_type_url($data);

        // the request should fail because the URL supplied for the url type field
        // is incorrect
        $this->expectException(position_exception::class);
        $this->expectExceptionMessage('Error saving custom fields: The URL needs to start with http://, https:// or /');

        $this->resolve_graphql_mutation(
            $this->mutation,
            [
                'target_position' => ['idnumber' => $pos->idnumber],
                'input' => [
                    'fullname' => 'Developer',
                    'framework' => [
                        'idnumber' => $frm2->idnumber
                    ],
                    'parent' => [
                        'idnumber' => $parent2->idnumber
                    ],
                    'type' => [
                        'id' => $type2_id
                    ],
                    'custom_fields' => [
                        ['shortname' => 'url'.$type2_id, "data" => ['url' => 'htt://broken.link']],

                    ],
                ]
            ]
        );
    }

    /**
     * @throws ReflectionException
     * @throws coding_exception
     */
    public function test_get_existing_parent__with_valid_parent() {
        /** @var \totara_hierarchy\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');
        $framework = $generator->create_pos_frame([]);
        $typeid = $generator->create_pos_type([]);
        $parent = $generator->create_pos(['frameworkid' => $framework->id, 'typeid' => $typeid]);
        $position = $generator->create_pos(['frameworkid' => $framework->id, 'typeid' => $typeid, 'parentid' => $parent->id]);

        $get_existing_parent = new ReflectionMethod(
            update_position::class,
            'get_existing_parent'
        );
        $get_existing_parent->setAccessible(true);

        $returned_parent = $get_existing_parent->invokeArgs($get_existing_parent,
            [
                $position
            ]
        );

        $this->assertEqualsCanonicalizing($parent, $returned_parent);
    }

    /**
     * @throws ReflectionException
     */
    public function test_get_existing_parent__with_parent_id_0() {
        $get_existing_parent = new ReflectionMethod(
            update_position::class,
            'get_existing_parent'
        );
        $get_existing_parent->setAccessible(true);

        $this->assertNull($get_existing_parent->invokeArgs($get_existing_parent,
            [
                (object)[
                    'parentid' => "0"
                ]
            ]
        ));
    }

    /**
     * @throws ReflectionException
     */
    public function test_get_existing_parent__with_invalid_parent() {
        $get_existing_parent = new ReflectionMethod(
            update_position::class,
            'get_existing_parent'
        );
        $get_existing_parent->setAccessible(true);

        $this->assertNull($get_existing_parent->invokeArgs(
            $get_existing_parent,
            [
                (object)[
                    'parentid' => 12
                ]
            ]
        ));
    }

    /**
     * @throws ReflectionException|coding_exception
     */
    public function test_get_parent__with_invalid_parent() {
        $generator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');
        $framework = $generator->create_pos_frame([]);
        $typeid = $generator->create_pos_type([]);
        $position = $generator->create_pos(['frameworkid' => $framework->id, 'typeid' => $typeid,]);

        $get_parent = new ReflectionMethod(
            update_position::class,
            'get_parent'
        );
        $get_parent->setAccessible(true);

        $this->expectException(position_exception::class);
        $this->expectExceptionMessage("The parent position does not exist or you do not have permissions to view it.");

        $get_parent->invokeArgs(
            $get_parent,
            [
                $position,
                [
                    'parentid' => 12
                ]
            ]
        );
    }

    /**
     * @throws ReflectionException|coding_exception
     */
    public function test_get_parent__with_empty_parent_ref() {
        $generator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');
        $framework = $generator->create_pos_frame([]);
        $typeid = $generator->create_pos_type([]);
        $position = $generator->create_pos(['frameworkid' => $framework->id, 'typeid' => $typeid,]);

        $get_parent = new ReflectionMethod(
            update_position::class,
            'get_parent'
        );
        $get_parent->setAccessible(true);

        $this->assertNull(
            $get_parent->invokeArgs(
                $get_parent,
                [
                    $position,
                    []
                ]
            )
        );
    }

    /**
     * @throws ReflectionException|coding_exception
     */
    public function test_get_parent__with_null_parent_ref_and_no_existing_parent() {
        $generator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');
        $framework = $generator->create_pos_frame([]);
        $typeid = $generator->create_pos_type([]);
        $position = $generator->create_pos(['frameworkid' => $framework->id, 'typeid' => $typeid,]);

        $get_parent = new ReflectionMethod(
            update_position::class,
            'get_parent'
        );
        $get_parent->setAccessible(true);

        $this->assertNull(
            $get_parent->invokeArgs(
                $get_parent,
                [
                    $position,
                    null
                ]
            )
        );
    }

    /**
     * @throws ReflectionException|coding_exception
     */
    public function test_get_parent__with_null_parent_ref_and_an_existing_parent() {
        $generator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');
        $framework = $generator->create_pos_frame([]);
        $typeid = $generator->create_pos_type([]);

        $parent = $generator->create_pos(['frameworkid' => $framework->id, 'typeid' => $typeid,]);
        $position = $generator->create_pos(['frameworkid' => $framework->id, 'typeid' => $typeid, 'parentid' => $parent->id]);

        $get_parent = new ReflectionMethod(
            update_position::class,
            'get_parent'
        );
        $get_parent->setAccessible(true);

       $resolved_parent = $get_parent->invokeArgs(
           $get_parent,
           [
               $position,
               [
                   'id' => $parent->id
               ]
           ]
        );

        $this->assertIsObject($resolved_parent);
        $this->assertEquals($parent->id, $resolved_parent->id);
    }

    /**
     * @throws ReflectionException|coding_exception
     */
    public function test_get_parent__valid_parent() {
        $generator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');
        $framework = $generator->create_pos_frame([]);
        $typeid = $generator->create_pos_type([]);
        $position = $generator->create_pos(['frameworkid' => $framework->id, 'typeid' => $typeid,]);
        $position_2 = $generator->create_pos(['frameworkid' => $framework->id, 'typeid' => $typeid,]);

        $get_parent = new ReflectionMethod(
            update_position::class,
            'get_parent'
        );
        $get_parent->setAccessible(true);

        $resolved_parent = $get_parent->invokeArgs(
            $get_parent,
            [
                $position,
                [
                    'id' => $position_2->id
                ]
            ]
        );

        $this->assertIsObject($resolved_parent);
        $this->assertEquals($position_2->id, $resolved_parent->id);
    }

    /**
     * @throws ReflectionException
     * @throws coding_exception
     */
    public function test_get_existing_type__with_valid_type() {
        $generator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');
        $framework = $generator->create_pos_frame([]);
        $typeid = $generator->create_pos_type([]);
        $position = $generator->create_pos(['frameworkid' => $framework->id, 'typeid' => $typeid]);

        $get_existing_type = new ReflectionMethod(
            update_position::class,
            'get_existing_type'
        );
        $get_existing_type->setAccessible(true);

        $resolved_type = $get_existing_type->invokeArgs($get_existing_type,
            [
                $position,
            ]
        );

        $this->assertIsObject($resolved_type);
        $this->assertEquals($typeid, $resolved_type->id);
    }

    /**
     * @throws ReflectionException
     * @throws coding_exception
     */
    public function test_get_existing_type__with_no_type() {
        $generator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');
        $framework = $generator->create_pos_frame([]);
        $position = $generator->create_pos(['frameworkid' => $framework->id]);

        $get_existing_type = new ReflectionMethod(
            update_position::class,
            'get_existing_type'
        );
        $get_existing_type->setAccessible(true);

        $this->assertNull($get_existing_type->invokeArgs($get_existing_type,
            [
                $position,
            ]
        ));
    }

    /**
     * @throws ReflectionException
     * @throws coding_exception
     */
    public function test_get_type__with_null_type_ref_and_no_type_set_on_position() {
        $generator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');
        $framework = $generator->create_pos_frame([]);
        $position = $generator->create_pos(['frameworkid' => $framework->id]);

        $get_type = new ReflectionMethod(
            update_position::class,
            'get_type'
        );
        $get_type->setAccessible(true);

        $this->assertNull($get_type->invokeArgs($get_type,
            [
                $position,
                null
            ]
        ));
    }

    /**
     * @throws ReflectionException
     * @throws coding_exception
     */
    public function test_get_type__with_null_type_ref_and_type_set_on_position() {
        $generator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');
        $framework = $generator->create_pos_frame([]);
        $typeid = $generator->create_pos_type();
        $position = $generator->create_pos(['frameworkid' => $framework->id, 'typeid' => $typeid]);

        $get_type = new ReflectionMethod(
            update_position::class,
            'get_type'
        );
        $get_type->setAccessible(true);

        $resolved_type = $get_type->invokeArgs($get_type,
            [
                $position,
                null
            ]
        );

        $this->assertIsObject($resolved_type);
        $this->assertEquals($typeid, $resolved_type->id);
    }

    /**
     * @throws ReflectionException
     */
    public function test_get_type__with_empty_type_ref() {
        $get_type = new ReflectionMethod(
            update_position::class,
            'get_type'
        );
        $get_type->setAccessible(true);

        $this->assertNull($get_type->invokeArgs($get_type,
            [
                (object)[],
                []
            ]
        ));
    }

    /**
     * @throws ReflectionException
     * @throws coding_exception
     */
    public function test_get_type__with_invalid_type_ref() {

        $generator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');
        $framework = $generator->create_pos_frame([]);
        $position = $generator->create_pos(['frameworkid' => $framework->id]);

        $get_type = new ReflectionMethod(
            update_position::class,
            'get_type'
        );
        $get_type->setAccessible(true);

        $this->expectException(position_exception::class);
        $this->expectExceptionMessage("The position type does not exist or you do not have permissions to view it.");

        $get_type->invokeArgs($get_type,
            [
                $position,
                [
                    'id' => 123
                ]
            ]
        );
    }

    /**
     * @throws ReflectionException
     * @throws coding_exception
     */
    public function test_get_type__with_valid_type_ref() {

        $generator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');
        $framework = $generator->create_pos_frame([]);
        $position = $generator->create_pos(['frameworkid' => $framework->id]);
        $typeid = $generator->create_pos_type([]);

        $get_type = new ReflectionMethod(
            update_position::class,
            'get_type'
        );
        $get_type->setAccessible(true);

        $resolved_type = $get_type->invokeArgs($get_type,
            [
                $position,
                [
                    'id' => $typeid
                ]
            ]
        );

        $this->assertIsObject($resolved_type);
        $this->assertEquals($typeid, $resolved_type->id);
    }

    /**
     * @throws ReflectionException
     * @throws coding_exception
     */
    public function test_get_framework__with_valid_framework_ref() {
        $generator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');
        $framework = $generator->create_pos_frame([]);
        $position = $generator->create_pos(['frameworkid' => $framework->id]);
        $framework_2 = $generator->create_pos_frame([]);

        $get_framework = new ReflectionMethod(
            update_position::class,
            'get_framework',
        );
        $get_framework->setAccessible(true);

        $resolved_framework = $get_framework->invokeArgs(
            $get_framework,
            [
                $position,
                [
                    'id' => $framework_2->id
                ],
            ]
        );

        $this->assertIsObject($resolved_framework);
        $this->assertEquals($framework_2->id, $resolved_framework->id);
    }

    /**
     * @throws ReflectionException
     * @throws coding_exception
     */
    public function test_get_framework__with_invalid_framework_ref() {
        $generator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');
        $framework = $generator->create_pos_frame([]);
        $position = $generator->create_pos(['frameworkid' => $framework->id]);

        $get_framework = new ReflectionMethod(
            update_position::class,
            'get_framework',
        );
        $get_framework->setAccessible(true);

        $this->expectException(position_exception::class);
        $this->expectExceptionMessage("The position framework does not exist or you do not have permissions to view it.");

        $get_framework->invokeArgs($get_framework,
            [
                $position,
                [
                    'id' => 13234
                ],
            ]
        );
    }

    /**
     * @throws ReflectionException
     * @throws coding_exception
     */
    public function test_get_framework__with_empty_framework_ref_and_valid_framework() {
        $generator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');
        $framework = $generator->create_pos_frame([]);
        $position = $generator->create_pos(['frameworkid' => $framework->id]);

        $get_framework = new ReflectionMethod(
            update_position::class,
            'get_framework',
        );
        $get_framework->setAccessible(true);

        $resolved_framework = $get_framework->invokeArgs($get_framework,
            [
                $position,
                [],
            ]
        );

        $this->assertIsObject($resolved_framework);
        $this->assertEquals($framework->id, $resolved_framework->id);
    }

    /**
     * @throws ReflectionException
     * @throws coding_exception
     */
    public function test_get_framework__with_null_framework_ref_and_valid_framework() {
        $generator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');
        $framework = $generator->create_pos_frame([]);
        $position = $generator->create_pos(['frameworkid' => $framework->id]);

        $get_framework = new ReflectionMethod(
            update_position::class,
            'get_framework',
        );
        $get_framework->setAccessible(true);

        $resolved_framework = $get_framework->invokeArgs($get_framework,
            [
                $position,
                null,
            ]
        );

        $this->assertIsObject($resolved_framework);
        $this->assertEquals($framework->id, $resolved_framework->id);
    }

    /**
     * @throws coding_exception
     */
    protected function setUp(): void {
        parent::setup();

        $generator = $this->getDataGenerator();
        $this->hierarchy_generator = $generator->get_plugin_generator('totara_hierarchy');
    }
}
