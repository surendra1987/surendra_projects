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
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package hierarchy_organisation
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

use core_phpunit\testcase;
use hierarchy_organisation\exception\organisation_exception;
use hierarchy_organisation\webapi\resolver\organisation_helper;

class hierarchy_organisation_webapi_resolver_organisation_helper_test extends testcase {

    /**
     * @throws ReflectionException
     * @throws coding_exception
     */
    public function test_load_target_organisation__with_valid_organisation() {
        /** @var \totara_hierarchy\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');
        $framework = $generator->create_org_frame([]);
        $type_id = $generator->create_org_type();
        $organisation = $generator->create_org(['frameworkid' => $framework->id, 'typeid' => $type_id]);

        $load_target_organisation = new ReflectionMethod(
            organisation_helper::class,
            'load_target_organisation'
        );
        $load_target_organisation->setAccessible(true);

        $target_organisation = $load_target_organisation->invokeArgs(
            $load_target_organisation,
            [
                [
                    'idnumber' => $organisation->idnumber,
                ]
            ]
        );

        $this->assertEqualsCanonicalizing($organisation, $target_organisation);
    }

    /**
     * @throws ReflectionException
     */
    public function test_load_target_organisation__with_invalid_organisation() {
        $load_target_organisation = new ReflectionMethod(
            organisation_helper::class,
            'load_target_organisation'
        );
        $load_target_organisation->setAccessible(true);

        $this->expectException(organisation_exception::class);
        $this->expectExceptionMessage("The target organisation does not exist or you do not have permissions to view it.");

        $load_target_organisation->invokeArgs(
            $load_target_organisation,
            [
                [
                    'idnumber' => 'anIdNumberWhichDoesNotExist',
                ]
            ]
        );
    }

    /**
     * @throws ReflectionException
     * @throws coding_exception
     */
    public function test_load_framework__with_valid_framework() {
        /** @var \totara_hierarchy\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');
        $framework = $generator->create_org_frame([]);

        $load_framework = new ReflectionMethod(
            organisation_helper::class,
            'load_framework'
        );
        $load_framework->setAccessible(true);

        $validated_framework = $load_framework->invokeArgs(
            $load_framework,
            [
                [
                    'id' => $framework->id,
                ]
            ]
        );

        $this->assertEqualsCanonicalizing($framework, $validated_framework);
    }

    /**
     * @throws ReflectionException
     */
    public function test_load_framework__with_invalid_framework() {
        $load_framework = new ReflectionMethod(
            organisation_helper::class,
            'load_framework'
        );
        $load_framework->setAccessible(true);

        $this->expectException(organisation_exception::class);
        $this->expectExceptionMessage("The organisation framework does not exist or you do not have permissions to view it.");

        $load_framework->invokeArgs(
            $load_framework,
            [
                [
                    'id' => 23141414,
                ]
            ]
        );
    }

    /**
     * @throws ReflectionException
     * @throws coding_exception
     */
    public function test_load_parent__with_valid_parent() {
        /** @var \totara_hierarchy\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');
        $framework = $generator->create_org_frame([]);
        $type_id = $generator->create_org_type();
        $parent_organisation = $generator->create_org(['frameworkid' => $framework->id, 'typeid' => $type_id]);

        $load_parent = new ReflectionMethod(
            organisation_helper::class,
            'load_parent'
        );
        $load_parent->setAccessible(true);

        $validated_parent = $load_parent->invokeArgs(
            $load_parent,
            [
                [
                    'id' => $parent_organisation->id,
                ],
            ]
        );

        $this->assertEqualsCanonicalizing($parent_organisation, $validated_parent);
    }

    /**
     * @throws ReflectionException
     */
    public function test_validate_parent__with_invalid_parent() {
        $load_parent = new ReflectionMethod(
            organisation_helper::class,
            'load_parent'
        );
        $load_parent->setAccessible(true);

        $this->expectException(organisation_exception::class);
        $this->expectExceptionMessage("The parent organisation does not exist or you do not have permissions to view it.");

        $load_parent->invokeArgs(
            $load_parent,
            [
                [
                    'id' => 23141414,
                ],
            ]
        );
    }

    /**
     * @throws ReflectionException
     */
    public function test_validate_parent__with_wrong_framework() {
        $validate_parent = new ReflectionMethod(
            organisation_helper::class,
            'validate_parent'
        );
        $validate_parent->setAccessible(true);

        $this->expectException(organisation_exception::class);
        $this->expectExceptionMessage("The parent organisation belongs to a different framework.");

        $validate_parent->invokeArgs(
            $validate_parent,
            [
                (object) [
                    'id' => 1,
                    'frameworkid' => 1,
                ],
                null,
                (object) [
                    'id' => 2,
                ],
            ]
        );
    }

    /**
     * @throws ReflectionException
     */
    public function test_validate_parent__with_same_parent_and_organisation() {
        $validate_parent = new ReflectionMethod(
            organisation_helper::class,
            'validate_parent'
        );
        $validate_parent->setAccessible(true);

        $this->expectException(organisation_exception::class);
        $this->expectExceptionMessage("The parent was resolved to be the same as the target organisation. The organisation cannot be a parent of itself.");

        $validate_parent->invokeArgs(
            $validate_parent,
            [
                (object) [
                    'id' => 1,
                    'frameworkid' => 1,
                ],
                (object) [
                    'id' => 1,
                ],
                (object) [
                    'id' => 1,
                ],
            ]
        );
    }

    /**
     * @throws ReflectionException
     */
    public function test_validate_parent__with_null_parent() {
        $validate_parent = new ReflectionMethod(
            organisation_helper::class,
            'validate_parent'
        );
        $validate_parent->setAccessible(true);

        $this->assertNull(
            $validate_parent->invokeArgs(
                $validate_parent,
                [
                    null,
                    (object) [
                        'id' => 1,
                    ],
                    (object) [
                        'id' => 1,
                    ],
                ]
            )
        );
    }

    /**
     * @throws ReflectionException
     */
    public function test_validate_parent__with_empty_child_and_correct_framework() {
        $validate_parent = new ReflectionMethod(
            organisation_helper::class,
            'validate_parent'
        );
        $validate_parent->setAccessible(true);

        $this->assertNull(
            $validate_parent->invokeArgs(
                $validate_parent,
                [
                    (object) [
                        'id' => 1,
                        'frameworkid' => 1,
                    ],
                    null,
                    (object) [
                        'id' => 1,
                    ],
                ]
            )
        );
    }

    /**
     * @throws ReflectionException
     */
    public function test_validate_idnumber_is_unique__with_valid_idnumber() {
        $validate_idnumber_is_unique = new ReflectionMethod(
            organisation_helper::class,
            'validate_idnumber_is_unique'
        );
        $validate_idnumber_is_unique->setAccessible(true);

        $this->assertTrue($validate_idnumber_is_unique->invokeArgs(
            $validate_idnumber_is_unique,
            [
                'unique_idnumber'
            ]
        ));
    }

    /**
     * @throws ReflectionException
     * @throws coding_exception
     */
    public function test_validate_idnumber_is_unique__with_invalid_idnumber() {
        /** @var \totara_hierarchy\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');
        $framework = $generator->create_org_frame([]);
        $type_id = $generator->create_org_type();
        $generator->create_org(
            [
                'frameworkid' => $framework->id,
                'typeid' => $type_id,
                'idnumber' => 'myOrganisationIdNumberWhichIsAlreadyUsed'
            ]
        );

        $validate_idnumber_is_unique = new ReflectionMethod(
            organisation_helper::class,
            'validate_idnumber_is_unique'
        );
        $validate_idnumber_is_unique->setAccessible(true);

        $this->expectException(organisation_exception::class);
        $this->expectExceptionMessage("The idnumber is utilised by an existing organisation");

        $validate_idnumber_is_unique->invokeArgs(
            $validate_idnumber_is_unique,
            [
                'myOrganisationIdNumberWhichIsAlreadyUsed'
            ]
        );
    }

    /**
     * @throws ReflectionException
     */
    public function test_validate_idnumber_is_unique__with_null_idnumber() {
        $validate_idnumber_is_unique = new ReflectionMethod(
            organisation_helper::class,
            'validate_idnumber_is_unique'
        );
        $validate_idnumber_is_unique->setAccessible(true);

        $this->assertTrue($validate_idnumber_is_unique->invokeArgs(
            $validate_idnumber_is_unique,
            [
                null,
            ]
        ));
    }

    /**
     * @throws ReflectionException
     * @throws coding_exception
     */
    public function test_load_type__with_valid_type() {
        /** @var \totara_hierarchy\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');
        $type_id = $generator->create_org_type();

        $load_type = new ReflectionMethod(
            organisation_helper::class,
            'load_type'
        );
        $load_type->setAccessible(true);

        $validated_type = $load_type->invokeArgs(
            $load_type,
            [
                [
                    'id' => $type_id,
                ]
            ]
        );

        $this->assertEquals($type_id, $validated_type->id);
    }

    /**
     * @throws ReflectionException
     */
    public function test_load_type__with_invalid_type() {
        $load_type = new ReflectionMethod(
            organisation_helper::class,
            'load_type'
        );
        $load_type->setAccessible(true);

        $this->expectException(organisation_exception::class);
        $this->expectExceptionMessage("The organisation type does not exist or you do not have permissions to view it.");

        $load_type->invokeArgs(
            $load_type,
            [
                [
                    'id' => 23141414,
                ]
            ]
        );
    }

    /**
     * @throws ReflectionException
     * @throws coding_exception
     */
    public function test_validate_custom_fields__with_valid_custom_field() {
        self::setAdminUser();
        /** @var \totara_hierarchy\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');
        $type_id = $generator->create_org_type(['idnumber' => '123']);

        $generator->create_hierarchy_type_text(
            [
                'hierarchy' => 'organisation',
                'typeidnumber' => '123',
                'value' => 'abc',
                'shortname' => 'mycustom_field',
            ]
        );

        $generator->create_hierarchy_type_text(
            [
                'hierarchy' => 'organisation',
                'typeidnumber' => '123',
                'value' => 'abc',
                'shortname' => 'my_second_custom_field',
            ]
        );

        $validate_custom_fields = new ReflectionMethod(
            organisation_helper::class,
            'validate_custom_fields'
        );
        $validate_custom_fields->setAccessible(true);

        $this->assertTrue($validate_custom_fields->invokeArgs(
            $validate_custom_fields,
            [
                [
                    ['shortname' => 'mycustom_field'],
                    ['shortname' => 'my_second_custom_field'],
                ],
                $type_id,
            ]
        ));
    }

    /**
     * @throws ReflectionException
     * @throws coding_exception
     */
    public function test_validate_custom_fields__with_valid_and_invalid_custom_field() {
        self::setAdminUser();

        /** @var \totara_hierarchy\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');
        $type_id = $generator->create_org_type(['idnumber' => '123']);

        $generator->create_hierarchy_type_text(
            [
                'hierarchy' => 'organisation',
                'typeidnumber' => '123',
                'value' => 'abc',
                'shortname' => 'my_second_custom_field',
            ]
        );

        $validate_custom_fields = new ReflectionMethod(
            organisation_helper::class,
            'validate_custom_fields'
        );
        $validate_custom_fields->setAccessible(true);

        $this->expectException(organisation_exception::class);
        $this->expectExceptionMessage('The following custom fields do not exist in the organisation type: my_non_existent_custom_field');
        $validate_custom_fields->invokeArgs(
            $validate_custom_fields,
            [
                [
                    ['shortname' => "my_non_existent_custom_field"],
                    ['shortname' => "my_second_custom_field"],
                ],
                $type_id,
            ]
        );
    }

    /**
     * @throws ReflectionException
     * @throws coding_exception
     */
    public function test_validate_custom_fields__with_invalid_type() {
        self::setAdminUser();

        /** @var \totara_hierarchy\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');
        $generator->create_org_type(["idnumber" => "123"]);

        $generator->create_hierarchy_type_text(
            [
                'hierarchy' => 'organisation',
                'typeidnumber' => '123',
                'value' => 'abc',
                'shortname' => 'mycustom_field',
            ]
        );

        $validate_custom_fields = new ReflectionMethod(
            organisation_helper::class,
            'validate_custom_fields'
        );
        $validate_custom_fields->setAccessible(true);

        $this->expectException(organisation_exception::class);
        $this->expectExceptionMessage('The following custom fields do not exist in the organisation type: mycustom_field');
        $validate_custom_fields->invokeArgs(
            $validate_custom_fields,
            [
                [
                    ['shortname' => 'mycustom_field',],
                ],
                12315,
            ]
        );
    }

    /**
     * @throws ReflectionException
     * @throws coding_exception
     */
    public function test_validate_custom_fields__with_invalid_custom_field() {
        /** @var \totara_hierarchy\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');
        $type_id = $generator->create_org_type();


        $validate_custom_fields = new ReflectionMethod(
            organisation_helper::class,
            'validate_custom_fields'
        );
        $validate_custom_fields->setAccessible(true);

        $this->expectException(organisation_exception::class);
        $this->expectExceptionMessage('The following custom fields do not exist in the organisation type: mycustom_field');
        $validate_custom_fields->invokeArgs(
            $validate_custom_fields,
            [
                [
                    ['shortname' => "mycustom_field",]
                ],
                $type_id,
            ]
        );
    }

    /**
     * @throws ReflectionException
     */
    public function test_validate_custom_fields__with_invalid_custom_field_and_type() {
        $validate_custom_fields = new ReflectionMethod(
            organisation_helper::class,
            'validate_custom_fields'
        );
        $validate_custom_fields->setAccessible(true);

        $this->expectException(organisation_exception::class);
        $this->expectExceptionMessage('The following custom fields do not exist in the organisation type: mycustom_field');
        $validate_custom_fields->invokeArgs(
            $validate_custom_fields,
            [
                [
                    ['shortname' => "mycustom_field",]
                ],
                123,
            ]
        );
    }

    /**
     * @throws ReflectionException
     */
    public function test_validate_custom_fields__with_empty_customfield_shortname() {
        $validate_custom_fields = new ReflectionMethod(
            organisation_helper::class,
            'validate_custom_fields'
        );
        $validate_custom_fields->setAccessible(true);

        $this->expectException(organisation_exception::class);
        $this->expectExceptionMessage('A custom field entry was passed in without the shortname');
        $validate_custom_fields->invokeArgs(
            $validate_custom_fields,
            [
                [
                    ['shortname' => "",]
                ],
                123,
            ]
        );
    }

    /**
     * @throws ReflectionException|coding_exception
     */
    public function test_prepare_organisation__with_all_values_and_no_existing_organisation() {
        global $USER;

        /** @var \totara_hierarchy\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');
        $framework = $generator->create_org_frame([]);
        $type_id = $generator->create_org_type();
        $parent = $generator->create_org(
            [
                'frameworkid' => $framework->id,
                'typeid' => $type_id,
                'idnumber' => 'myOrganisationIdNumberWhichIsAlreadyUsed'
            ]
        );
        $type = new stdClass();
        $type->id = $type_id;

        $prepare_organisation = new ReflectionMethod(
            organisation_helper::class,
            'prepare_organisation'
        );
        $prepare_organisation->setAccessible(true);

        $expected_result = new StdClass();
        $expected_result->fullname = 'myfullname';
        $expected_result->idnumber = 'myidnumber';
        $expected_result->description = 'mydescription';
        $expected_result->typeid = $type_id;
        $expected_result->parentid = $parent->id;
        $expected_result->visible = 1;
        $expected_result->frameworkid = $framework->id;
        $expected_result->usermodified = $USER->id;

        $actual_result = $prepare_organisation->invokeArgs(
            $prepare_organisation,
            [
                null,
                [
                    'fullname' => 'myfullname',
                    'idnumber' => 'myidnumber',
                    'description' => 'mydescription',
                ],
                $framework,
                $type,
                $parent,
            ]
        );

        // As this uses time() we can't predict the exact value.
        $expected_result->timemodified = $actual_result->timemodified;

        $this->assertEquals(
            $expected_result,
            $actual_result,
        );
    }

    /**
     * @throws ReflectionException|coding_exception
     */
    public function test_prepare_organisation__visible_set_on_existing_org_and_not_in_input() {
        global $USER;

        /** @var \totara_hierarchy\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');
        $framework = $generator->create_org_frame([]);
        $type_id = $generator->create_org_type();
        $parent = $generator->create_org(
            [
                'frameworkid' => $framework->id,
                'typeid' => $type_id,
                'idnumber' => 'myOrganisationIdNumberWhichIsAlreadyUsed',
            ]
        );
        $organisation = $generator->create_org(
            [
                'frameworkid' => $framework->id,
                'typeid' => $type_id,
                'idnumber' => 'myOrganisationIdNumberWhichIsAlreadyUsed',
                'visible' => 0,
            ]
        );
        $type = new stdClass();
        $type->id = $type_id;


        $prepare_organisation = new ReflectionMethod(
            organisation_helper::class,
            'prepare_organisation'
        );
        $prepare_organisation->setAccessible(true);

        $expected_result = new StdClass();
        $expected_result->fullname = 'myfullname';
        $expected_result->idnumber = 'myidnumber';
        $expected_result->description = 'mydescription';
        $expected_result->typeid = $type_id;
        $expected_result->parentid = $parent->id;
        $expected_result->frameworkid = $framework->id;
        $expected_result->visible = 0;
        $expected_result->usermodified = $USER->id;

        $actual_result = $prepare_organisation->invokeArgs(
            $prepare_organisation,
            [
                $organisation,
                [
                    'fullname' => 'myfullname',
                    'idnumber' => 'myidnumber',
                    'description' => 'mydescription',
                ],
                $framework,
                $type,
                $parent,
            ]
        );

        // As this uses time() we can't predict the exact value.
        $expected_result->timemodified = $actual_result->timemodified;

        $this->assertEquals(
            $expected_result,
            $actual_result,
        );
    }

    /**
     * @throws ReflectionException|coding_exception
     */
    public function test_prepare_organisation__visible_set_on_existing_org_and_in_input() {
        global $USER;

        /** @var \totara_hierarchy\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');
        $framework = $generator->create_org_frame([]);
        $type_id = $generator->create_org_type();
        $parent = $generator->create_org(
            [
                'frameworkid' => $framework->id,
                'typeid' => $type_id,
                'idnumber' => 'myOrganisationIdNumberWhichIsAlreadyUsed',
            ]
        );
        $organisation = $generator->create_org(
            [
                'frameworkid' => $framework->id,
                'typeid' => $type_id,
                'idnumber' => 'myOrganisationIdNumberWhichIsAlreadyUsed',
                'visible' => 0,
            ]
        );
        $type = new stdClass();
        $type->id = $type_id;


        $prepare_organisation = new ReflectionMethod(
            organisation_helper::class,
            'prepare_organisation'
        );
        $prepare_organisation->setAccessible(true);

        $expected_result = new StdClass();
        $expected_result->fullname = 'myfullname';
        $expected_result->idnumber = 'myidnumber';
        $expected_result->description = 'mydescription';
        $expected_result->typeid = $type_id;
        $expected_result->parentid = $parent->id;
        $expected_result->visible = 1;
        $expected_result->frameworkid = $framework->id;
        $expected_result->usermodified = $USER->id;

        $actual_result = $prepare_organisation->invokeArgs(
            $prepare_organisation,
            [
                $organisation,
                [
                    'fullname' => 'myfullname',
                    'idnumber' => 'myidnumber',
                    'description' => 'mydescription',
                    'visible' => 1,
                ],
                $framework,
                $type,
                $parent,
            ]
        );

        // As this uses time() we can't predict the exact value.
        $expected_result->timemodified = $actual_result->timemodified;

        $this->assertEquals(
            $expected_result,
            $actual_result,
        );
    }

    /**
     * @throws ReflectionException|coding_exception
     */
    public function test_prepare_organisation__with_no_input() {
        global $USER;

        /** @var \totara_hierarchy\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('totara_hierarchy');
        $framework = $generator->create_org_frame([]);
        $type_id = $generator->create_org_type();
        $parent = $generator->create_org(
            [
                'frameworkid' => $framework->id,
                'typeid' => $type_id,
                'idnumber' => 'myOrganisationIdNumberWhichIsAlreadyUsed'
            ]
        );
        $type = new stdClass();
        $type->id = $type_id;


        $prepare_organisation = new ReflectionMethod(
            organisation_helper::class,
            'prepare_organisation'
        );
        $prepare_organisation->setAccessible(true);

        $expected_result = new StdClass();
        $expected_result->typeid = $type_id;
        $expected_result->parentid = $parent->id;
        $expected_result->visible = 1;
        $expected_result->frameworkid = $framework->id;
        $expected_result->usermodified = $USER->id;

        $actual_result = $prepare_organisation->invokeArgs(
            $prepare_organisation,
            [
                null,
                [],
                $framework,
                $type,
                $parent,
            ]
        );

        // As this uses time() we can't predict the exact value.
        $expected_result->timemodified = $actual_result->timemodified;

        $this->assertEquals(
            $expected_result,
            $actual_result,
        );
    }

    /**
     * @throws ReflectionException
     */
    public function test_prepare_organisation__with_empty_fullname() {
        global $USER;

        $prepare_organisation = new ReflectionMethod(
            organisation_helper::class,
            'prepare_organisation'
        );
        $prepare_organisation->setAccessible(true);

        $expected_result = new StdClass();
        $expected_result->typeid = 0;
        $expected_result->parentid = 0;
        $expected_result->visible = 1;
        $expected_result->frameworkid = 1;
        $expected_result->usermodified = $USER->id;

        $this->expectExceptionMessage("The `fullname` field is required and cannot be null or empty.");
        $this->expectException(organisation_exception::class);

        $prepare_organisation->invokeArgs(
            $prepare_organisation,
            [
                null,
                [
                    'fullname' => '',
                ],
                (object)[
                    'id' => 1,
                ],
                (object)[
                    'id' => 1,
                ],
                null
            ]
        );
    }

    /**
     * @throws ReflectionException
     */
    public function test_prepare_organisation__with_null_fullname() {
        global $USER;

        $prepare_organisation = new ReflectionMethod(
            organisation_helper::class,
            'prepare_organisation'
        );
        $prepare_organisation->setAccessible(true);

        $expected_result = new StdClass();
        $expected_result->typeid = 0;
        $expected_result->parentid = 0;
        $expected_result->visible = 1;
        $expected_result->frameworkid = 1;
        $expected_result->usermodified = $USER->id;

        $this->expectExceptionMessage("The `fullname` field is required and cannot be null or empty.");
        $this->expectException(organisation_exception::class);

        $prepare_organisation->invokeArgs(
            $prepare_organisation,
            [
                null,
                [
                    'fullname' => null,
                ],
                (object)[
                  'id' => 1,
                ],
                (object)[
                    'id' => 1,
                ],
                null
            ]
        );
    }
}
