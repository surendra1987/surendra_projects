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
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package totara_customfield
 */

use totara_customfield\webapi\customfield_resolver_helper;

defined('MOODLE_INTERNAL') || die();

class totara_customfield_webapi_resolver_customfield_resolver_helper_test extends \core_phpunit\testcase {
    /**
     * @var \totara_hierarchy\testing\generator
     */
    protected $hierarchy_generator = null;

    /**
     * @return void
     * @throws \totara_customfield\exceptions\customfield_validation_exception
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_save_customfields_for_item_courses(): void {
        // create some custom fields for courses
        $helper = new customfield_resolver_helper('course');

        $cf_generator = $this->getDataGenerator()->get_plugin_generator('totara_customfield');
        /** @var \totara_customfield\testing\generator $result */
        $result = $cf_generator->create_text(
            'course',
            ['text_course_one', 'text_course_two']
        );
        $course = $this->getDataGenerator()->create_course();

        // setup default custom fields for course
        $custom_fields = customfield_get_custom_fields_and_data_for_items('course', [$course->id]);
        $course_customfields = $custom_fields[$course->id];
        $this->assertCount(2, $course_customfields);
        $values = array_column($course_customfields, 'value');
        foreach ($values as $value) {
            $this->assertNull($value);
        }

        $helper->save_customfields_for_item($course, [['shortname' => 'text_course_one', 'data' => 'overridden_data']], [], ['courseid' => $course->id]);

        $custom_fields = customfield_get_custom_fields_and_data_for_items('course', [$course->id]);
        $course_customfields = $custom_fields[$course->id];
        $this->assertCount(2, $course_customfields);

        $text_course_one_field = null;
        $text_course_two_field = null;
        foreach ($course_customfields as $course_customfield) {
            if ($course_customfield['definition']['shortname'] === 'text_course_one') {
                $text_course_one_field = $course_customfield;
                continue;
            }
            $text_course_two_field = $course_customfield;
        }
        $this->assertSame('overridden_data', $text_course_one_field['value']);
        $this->assertSame('', $text_course_one_field['definition']['raw_default_value']);
        $this->assertNull($text_course_two_field['value']);
    }

    public function test_save_customfields_for_item_organisations(): void {
        global $DB;
        $helper = new customfield_resolver_helper('organisation');

        $framework_data = ['fullname' => 'Organisation framework 1', 'idnumber' => 'org_frm_1'];
        $framework = $this->hierarchy_generator->create_org_frame($framework_data);

        $type = $this->hierarchy_generator->create_org_type();

        if (!$typeidnumber = $DB->get_field('org_type', 'idnumber', array('id' => $type))) {
        //    throw an exception here.
        }

        $org_data = ['frameworkid' => $framework->id, 'idnumber' => 'parent_org', 'typeid' => $type];
        $org = $this->hierarchy_generator->create_org($org_data);

        $defaultdata = 'Apple';
        $data = array('hierarchy' => 'organisation', 'typeidnumber' => $typeidnumber, 'value' => $defaultdata);
        $this->hierarchy_generator->create_hierarchy_type_text($data);
        $defaultdata_unchanged = 'Original';
        $data = array('hierarchy' => 'organisation', 'typeidnumber' => $typeidnumber, 'value' => $defaultdata_unchanged);
        $this->hierarchy_generator->create_hierarchy_type_url($data);

        $custom_fields = customfield_get_custom_fields_and_data_for_items('organisation', [$org->id]);
        $org_customfields = $custom_fields[$org->id];
        $this->assertCount(2, $org_customfields);

        // update the custom fields for one of the fields
        $helper->save_customfields_for_item(
            $org,
            [['shortname' => 'text'.$type, 'data' => 'overridden_data']],
            ['typeid' => $org->typeid],
            ['organisationid' => $org->id]
        );

        $custom_fields = customfield_get_custom_fields_and_data_for_items('organisation', [$org->id]);
        $org_customfields = $custom_fields[$org->id];

        $text_org_one_field = null;
        $text_org_two_field = null;
        foreach ($org_customfields as $org_customfield) {
            if ($org_customfield['definition']['shortname'] === 'text'.$type) {
                $text_org_one_field = $org_customfield;
                continue;
            }
            $text_org_two_field = $org_customfield;
        }
        $this->assertSame('overridden_data', $text_org_one_field['value']);
        $this->assertSame('Apple', $text_org_one_field['definition']['raw_default_value']);
        // this should remain unchanged
        $this->assertNull($text_org_two_field['value']);
        $this->assertSame('Original', $text_org_two_field['definition']['raw_default_value']);
    }

    public function test_delete_customfields_for_item_organisations(): void {
        global $DB;
        $helper = new customfield_resolver_helper('organisation');

        $framework_data = ['fullname' => 'Organisation framework 1', 'idnumber' => 'org_frm_1'];
        $framework = $this->hierarchy_generator->create_org_frame($framework_data);

        $type = $this->hierarchy_generator->create_org_type();

        if (!$typeidnumber = $DB->get_field('org_type', 'idnumber', array('id' => $type))) {
            //    throw an exception here.
        }

        $org_data = ['frameworkid' => $framework->id, 'idnumber' => 'parent_org', 'typeid' => $type];
        $org = $this->hierarchy_generator->create_org($org_data);

        $defaultdata = 'Apple';
        $data = array('hierarchy' => 'organisation', 'typeidnumber' => $typeidnumber, 'value' => $defaultdata);
        $this->hierarchy_generator->create_hierarchy_type_text($data);

        // update the custom fields for one of the fields
        $helper->save_customfields_for_item(
            $org,
            [['shortname' => 'text'.$type, 'data' => 'overridden_data']],
            ['typeid' => $org->typeid],
            ['organisationid' => $org->id]
        );

        $custom_fields = customfield_get_custom_fields_and_data_for_items('organisation', [$org->id]);
        $org_customfields = $custom_fields[$org->id];
        $field = reset($org_customfields);

        $this->assertSame('overridden_data', $field['value']);

        // delete the custom field - setting the data to null should allow it to be unsset
        $helper->save_customfields_for_item(
            $org,
            [['shortname' => 'text'.$type, 'data' => null]],
            ['typeid' => $org->typeid],
            ['organisationid' => $org->id]
        );

        $custom_fields = customfield_get_custom_fields_and_data_for_items('organisation', [$org->id]);
        $org_customfields = $custom_fields[$org->id];
        $field = reset($org_customfields);

        $this->assertNull($field['value']);
    }

    public function test_deleting_required_customfields(): void {
        global $DB;
        $helper = new customfield_resolver_helper('organisation');

        $framework_data = ['fullname' => 'Organisation framework 1', 'idnumber' => 'org_frm_1'];
        $framework = $this->hierarchy_generator->create_org_frame($framework_data);

        $type = $this->hierarchy_generator->create_org_type();

        if (!$typeidnumber = $DB->get_field('org_type', 'idnumber', array('id' => $type))) {
            //    throw an exception here.
        }

        $org_data = ['frameworkid' => $framework->id, 'idnumber' => 'parent_org', 'typeid' => $type];
        $org = $this->hierarchy_generator->create_org($org_data);

        $defaultdata = 'Apple';
        $data = ['hierarchy' => 'organisation', 'typeidnumber' => $typeidnumber, 'value' => $defaultdata, 'required' => 1];
        $this->hierarchy_generator->create_hierarchy_type_text($data);

        // update the custom fields for one of the fields
        $helper->save_customfields_for_item(
            $org,
            [['shortname' => 'text'.$type, 'data' => 'overridden_data']],
            ['typeid' => $org->typeid],
            ['organisationid' => $org->id]
        );

        $custom_fields = customfield_get_custom_fields_and_data_for_items('organisation', [$org->id]);
        $org_customfields = $custom_fields[$org->id];
        $field = reset($org_customfields);

        $this->assertSame('overridden_data', $field['value']);

        // delete the custom field - setting the data to null should allow it to be unsset
        $this->expectException(\totara_customfield\exceptions\customfield_validation_exception::class);
        $helper->save_customfields_for_item(
            $org,
            [['shortname' => 'text'.$type, 'data' => null]],
            ['typeid' => $org->typeid],
            ['organisationid' => $org->id]
        );
    }

    protected function setUp(): void {
        parent::setup();
        $this->setAdminUser();

        $generator = $this->getDataGenerator();

        $this->hierarchy_generator = $generator->get_plugin_generator('totara_hierarchy');
    }
}