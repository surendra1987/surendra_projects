<?php
/**
 * This file is part of Totara Learn
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Navjeet Singh <navjeet.singh@totara.com>
 * @package totara_hierarchy
 */

use core\pagination\offset_cursor;
use core_phpunit\testcase;
use hierarchy_position\entity\position;

class totara_hierarchy_data_provider_base_test extends testcase {

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

        $this->hierarchy_generator = $generator->get_plugin_generator('totara_hierarchy');
    }

    /**
     * Test data providers
     *
     * @return void
     */
    public function test_all_data_provider(): void {
        $framework_prefix = ['position', 'organisation', 'goal', 'competency'];
        foreach ($framework_prefix as $prefix) {
            $this->assert_hierarchy_provider($prefix);
        }
    }

    /**
     * @param $prefix
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    protected function assert_hierarchy_provider($prefix) {
        $hierarchy = hierarchy::load_hierarchy($prefix);
        $shortprefix = hierarchy::get_short_prefix($prefix);
        $perpage = 20;
        $page = 0;
        $data = $this->create_framework($shortprefix, $prefix);
        $typeid = $data['typeid'];
        $prefix_data = $data[$prefix];
        $framework = $data['framework'];

        // Passing third item which has the typeid
        $field_type_id = $prefix_data[2]->id;

        $this->create_custom_fields($typeid, $field_type_id, $shortprefix, $prefix);

        $filters = [
            'framework_id' => $framework->id,
        ];

        $fields = $hierarchy->get_item_select_fields();
        $group_by = $hierarchy->get_item_group_by_fields();

        $result = $this->fetch_from_data_provider($prefix, $filters, $fields, $group_by, $perpage, $page);

        $this->assertEquals(7, $result['total']);
        $this->assertEquals(3, count($result['items'][2]->type->type_info_field));
        $this->assertSame($typeid, $result['items'][2]->type->id);
        $this->assertSame((int) $field_type_id, $result['items'][2]->id);

        // Trigger search
        $search = 'example';
        $filters['search'] = [
            'prefix' => $prefix,
            'shortprefix' => $shortprefix,
            'search' => $search,
            'custom_fields' => $fields
        ];
        $result = $this->fetch_from_data_provider($prefix, $filters, $fields, $group_by, $perpage, $page);
        $search_type_info_data = [];
        foreach ($result['items'][0]->type_info_data as $info) {
            $search_type_info_data[] = $info->data;
        }

        $this->assertEquals(1, $result['total']);
        $this->assertEquals(3, count($result['items'][0]->type->type_info_field));
        $this->assertContains($search, $search_type_info_data);
    }

    /**
     * @param $prefix
     * @param $filters
     * @param $fields
     * @param $group_by
     * @param $perpage
     * @param $page
     * @throws coding_exception
     */
    protected function fetch_from_data_provider($prefix, $filters, $fields, $group_by, $perpage, $page) {
        $cursor = offset_cursor::create()->set_limit($perpage)->set_page($page + 1);
        return (hierarchy::get_data_provider($prefix))
            ->set_page_size($cursor->get_limit())
            ->set_filters($filters)
            ->set_order('sortthread')
            ->fetch($cursor, ['type.type_info_field', 'type_info_data'], $fields, $group_by);
    }


    /**
     * Create a framework.
     * @return array
     * @throws coding_exception
     */
    protected function create_framework($shortprefix, $prefix): array {
        $frame_function = "create_{$shortprefix}_frame";
        $frame_function_prefix = "create_{$shortprefix}";
        $frame_function_prefix_type = "create_{$shortprefix}_type";
        $gen = $this->hierarchy_generator;

        $framework = $gen->$frame_function([]);

        $framework_items = [];

        // Create a position type.
        $typeid = $gen->$frame_function_prefix_type();

        // Create top level positions.
        for ($x = 1; $x <= 2; ++$x) {
            $framework_items[] = $gen->$frame_function_prefix([
                'frameworkid' => $framework->id,
            ]);
        }

        // Specific name and type.
        $framework_items[] = $gen->$frame_function_prefix([
            'frameworkid' => $framework->id,
            'typeid' => $typeid
        ]);

        // Give a position some children.
        $parent_id = $framework_items[2]->id;
        for ($x = 1; $x <= 4; ++$x) {
            $framework_items[] = $gen->$frame_function_prefix([
                'frameworkid' => $framework->id,
                'parentid' => $parent_id,
                'fullname' => "Child position {$x}"
            ]);
        }

        return [
            'framework' => $framework,
            $prefix => $framework_items,
            'typeid' => $typeid,
        ];
    }

    /**
     * Create three different types of custom field
     * @param $typeid
     * @param $field_type_id
     * @param $shortprefix
     * @param $prefix
     * @return void
     * @throws dml_exception
     */
    protected function create_custom_fields($typeid, $field_type_id, $shortprefix, $prefix) {
        global $DB;
        $gen = $this->hierarchy_generator;
        // Create checkbox type.
        $defaultdata = 1; // Checked.
        $shortname   = 'checkbox'.$typeid;
        $type_idnumber = $DB->get_field($shortprefix.'_type', 'idnumber', array('id' => $typeid));

        $create_type_data_1 = array('hierarchy' => $prefix, 'typeidnumber' => $type_idnumber, 'value' => $defaultdata);
        $gen->create_hierarchy_type_checkbox($create_type_data_1);
        $output = $DB->get_record($shortprefix.'_type_info_field', array('shortname' => $shortname));

        $field_type = $prefix.'id';
        $insert_type_info_data = new stdClass();
        $insert_type_info_data->fieldid = $output->id;
        $insert_type_info_data->$field_type = $field_type_id;
        $insert_type_info_data->data = $defaultdata;
        $DB->insert_record($shortprefix.'_type_info_data', $insert_type_info_data);

        // Create text type.
        $defaultdata = 'Apple';
        $shortname   = 'text'.$typeid;
        $create_type_data_2 = array('hierarchy' => $prefix, 'typeidnumber' => $type_idnumber, 'value' => $defaultdata);
        $gen->create_hierarchy_type_text($create_type_data_2);
        $output = $DB->get_record($shortprefix.'_type_info_field', array('shortname' => $shortname));

        $insert_type_info_data = new stdClass();
        $insert_type_info_data->fieldid = $output->id;
        $insert_type_info_data->$field_type = $field_type_id;
        $insert_type_info_data->data = $defaultdata;
        $DB->insert_record($shortprefix.'_type_info_data', $insert_type_info_data);

        // Create menu of choice.
        $defaultdata = 'example';
        $shortname   = 'menu'.$typeid;
        $create_type_data_3 = array('hierarchy' => $prefix, 'typeidnumber' => $type_idnumber, 'value' => $defaultdata);
        $gen->create_hierarchy_type_menu($create_type_data_3);
        $output = $DB->get_record($shortprefix.'_type_info_field', array('shortname' => $shortname));

        $insert_type_info_data = new stdClass();
        $insert_type_info_data->fieldid = $output->id;
        $insert_type_info_data->$field_type = $field_type_id;
        $insert_type_info_data->data = $defaultdata;
        $DB->insert_record($shortprefix.'_type_info_data', $insert_type_info_data);
    }


    /**
     * Test hierarchy position search for duplicates
     *
     * @return void
     */
    public function test_hierarchy_position_search_for_duplicates(): void {
        global $DB;
        $prefix = 'position';

        $hierarchy = hierarchy::load_hierarchy($prefix);
        $shortprefix = hierarchy::get_short_prefix($prefix);

        $framework = $this->hierarchy_generator->create_pos_frame([]);
        $typeid = $this->hierarchy_generator->create_pos_type();

        $expected_ids = [];
        $expected_data_value = [];

        $position1 =  $this->hierarchy_generator->create_pos([
            'frameworkid' => $framework->id,
            'fullname' => 'example',
            'shortname' => 'example',
            'description' => 'example',
            'typeid' => $typeid
        ]);

        $expected_ids[] = $position1->id;

        $type_idnumber = $DB->get_field('pos_type', 'idnumber', array('id' => $typeid));

        // Add 10 custom text fields and set its value
        for ($x = 1; $x <= 10; ++$x) {
            $defaultdata = 'example'.$x;
            $expected_data_value[] = $defaultdata;
            $shortname   = 'text'.$typeid.$x;
            $create_type_data = array('hierarchy' => $prefix, 'typeidnumber' => $type_idnumber, 'value' => $defaultdata, 'shortname' => $shortname);

            $this->hierarchy_generator->create_hierarchy_type_text($create_type_data);
            $output = $DB->get_record('pos_type_info_field', array('shortname' => $shortname));

            $insert_type_info_data = new stdClass();
            $insert_type_info_data->fieldid = $output->id;
            $insert_type_info_data->positionid = $position1->id;
            $insert_type_info_data->data = $defaultdata;
            $DB->insert_record('pos_type_info_data', $insert_type_info_data);
        }

        $position2 =  $this->hierarchy_generator->create_pos([
            'frameworkid' => $framework->id,
            'fullname' => 'example 1',
            'shortname' => 'example 1',
            'description' => 'example 1',
            'parentid' => $position1->id,
            'typeid' => $typeid
        ]);

        $expected_ids[] = $position2->id;

        $position3 =  $this->hierarchy_generator->create_pos([
            'frameworkid' => $framework->id,
            'fullname' => 'position 1',
            'shortname' => 'position 1',
            'description' => 'position 1',
            'parentid' => $position1->id,
            'typeid' => $typeid
        ]);

        $fields = $hierarchy->get_item_select_fields();
        $group_by = $hierarchy->get_item_group_by_fields();

        $filters = [
            'framework_id' => $framework->id,
            'search' => [
                'prefix' => $prefix,
                'shortprefix' => $shortprefix,
                'search' => 'example',
                'custom_fields' => $fields
            ]
        ];

        $result = $this->fetch_from_data_provider($prefix, $filters, $fields, $group_by,20, 0);

        $search_type_info_data = [];
        foreach ($result['items'] as $item) {
            foreach ($item->type_info_data as $type_data) {
                $search_type_info_data[] = $type_data->data;
            }
        }

        $this->assertEqualsCanonicalizing($expected_ids, array_column($result['items'], 'id'));
        $this->assertEquals($expected_data_value, $search_type_info_data);
    }

    /**
     * Test hierarchy position for visible filter
     *
     * @return void
     */
    public function test_hierarchy_position_visible_filter(): void {
        $prefix = 'position';

        $hierarchy = hierarchy::load_hierarchy($prefix);
        $shortprefix = hierarchy::get_short_prefix($prefix);

        $framework = $this->hierarchy_generator->create_pos_frame([]);
        $typeid = $this->hierarchy_generator->create_pos_type();

        $expected_ids = [];

        //First position record
        $position1 =  $this->hierarchy_generator->create_pos([
            'frameworkid' => $framework->id,
            'fullname' => 'example',
            'shortname' => 'example',
            'description' => 'example',
            'typeid' => $typeid
        ]);

        $expected_ids[] = $position1->id;

        //Second position record and make not visible
        $this->hierarchy_generator->create_pos([
            'frameworkid' => $framework->id,
            'fullname' => 'example 1',
            'shortname' => 'example 1',
            'description' => 'example 1',
            'parentid' => $position1->id,
            'typeid' => $typeid,
            'visible' => 0
        ]);

        //Third position record and make not visible
        $this->hierarchy_generator->create_pos([
            'frameworkid' => $framework->id,
            'fullname' => 'position 1',
            'shortname' => 'position 1',
            'description' => 'position 1',
            'parentid' => $position1->id,
            'typeid' => $typeid,
            'visible' => 0
        ]);

        $fields = $hierarchy->get_item_select_fields();
        $group_by = $hierarchy->get_item_group_by_fields();

        $filters = [
            'framework_id' => $framework->id,
            'search' => [
                'prefix' => $prefix,
                'shortprefix' => $shortprefix,
                'search' => '',
                'custom_fields' => $fields
            ]
        ];

        $result = $this->fetch_from_data_provider($prefix, $filters, $fields, $group_by, 20, 0);
        $this->assertEquals(3, $result['total']);

        // Get only visible items
        $filters['visible'] = 1;

        $result = $this->fetch_from_data_provider($prefix, $filters, $fields, $group_by, 20, 0);
        $this->assertEqualsCanonicalizing($expected_ids, array_column($result['items'], 'id'));
        $this->assertEquals(1, $result['total']);
    }

    /**
     * Test hierarchy position for since_timemodified filter
     *
     * @return void
     */
    public function test_hierarchy_position_since_timemodified(): void {
        $prefix = 'position';

        $framework = $this->hierarchy_generator->create_pos_frame([]);

        $position1 =  $this->hierarchy_generator->create_pos([
            'frameworkid' => $framework->id,
        ]);
        $pos_to_update = position::repository()->find($position1->id);
        $pos_to_update->timemodified = "1111";
        $pos_to_update->save();

        $position2 = $this->hierarchy_generator->create_pos([
            'frameworkid' => $framework->id,
        ]);
        $pos_to_update = position::repository()->find($position2->id);
        $pos_to_update->timemodified = "2222";
        $pos_to_update->save();

        $position3 = $this->hierarchy_generator->create_pos([
            'frameworkid' => $framework->id,
        ]);
        $pos_to_update = position::repository()->find($position3->id);
        $pos_to_update->timemodified = "3333";
        $pos_to_update->save();

        $hierarchy = hierarchy::load_hierarchy($prefix);

        // Check that all records are retrieved when since_timemodified is set lower than all records
        $result = $this->fetch_from_data_provider($prefix,
            [
                'framework_id' => $framework->id,
                'since_timemodified' => '1',
            ],
            $hierarchy->get_item_select_fields(),
            $hierarchy->get_item_group_by_fields(),
            20,
            0
        );
        $this->assertEquals(3, $result['total']);
        $this->assertEqualsCanonicalizing(
            [
                $position1->id,
                $position2->id,
                $position3->id,
            ], array_column($result['items'], 'id')
        );

        // Check that only records which have been modified after timestamp 2000 get retrieved
        $result = $this->fetch_from_data_provider($prefix,
            [
                'framework_id' => $framework->id,
                'since_timemodified' => '2000',
            ],
            $hierarchy->get_item_select_fields(),
            $hierarchy->get_item_group_by_fields(),
            20,
            0
        );

        $this->assertEquals(2, $result['total']);
        $this->assertEqualsCanonicalizing(
            [
                $position2->id,
                $position3->id,
            ], array_column($result['items'], 'id')
        );
    }
}
