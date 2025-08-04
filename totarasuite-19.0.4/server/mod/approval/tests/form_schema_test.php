<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2021 onwards Totara Learning Solutions LTD
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
 * @author Chris Snyder <chris.snyder@totaralearning.com>
 * @package mod_approval
 */

use core_phpunit\testcase;
use mod_approval\form_schema\form_schema;
use mod_approval\form_schema\form_schema_field;
use mod_approval\form_schema\form_schema_section;
use mod_approval\model\form\approvalform_base;
use mod_approval\model\form\form_data;
use mod_approval\model\workflow\workflow;
use mod_approval\model\workflow\workflow_stage_formview;
use mod_approval\testing\approval_workflow_test_setup;
use mod_approval\testing\formview_generator_object;

/**
 * @group approval_workflow
 * @coversDefaultClass mod_approval\form_schema\form_schema
 */
class mod_approval_form_schema_test extends testcase {

    use approval_workflow_test_setup;

    private ?form_schema $form_schema;
    private ?form_schema $form_schema_lang_string;
    private ?form_schema $form_schema_description_plain;

    public function setUp(): void {
        parent::setUp();
        global $CFG;

        $fixtures_path = $CFG->dirroot . '/mod/approval/tests/fixtures';

        $this->form_schema = form_schema::from_json(
            file_get_contents($fixtures_path . '/form/test_form.json')
        );
        $this->form_schema_lang_string = form_schema::from_json(
            file_get_contents($fixtures_path . '/form/test_form_lang_string.json')
        );
        $this->form_schema_description_plain = form_schema::from_json(
            file_get_contents($fixtures_path . '/form/test_form_description_plain.json')
        );
    }

    protected function tearDown(): void {
        $this->form_schema = null;
        $this->form_schema_lang_string = null;
        $this->form_schema_description_plain = null;

        parent::tearDown();
    }

    protected static function get_method($name): ReflectionMethod {
        $class = new ReflectionClass(form_schema::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * @covers ::get_version
     */
    public function test_get_version(): void {
        $this->assertEquals('2021030200', $this->form_schema->get_version());
    }

    /**
     * @covers ::get_component
     */
    public function test_get_component(): void {
        $this->assertEquals(null, $this->form_schema->get_component());

        $this->assertEquals('mod_approval', $this->form_schema_lang_string->get_component());
    }

    /**
     * @covers ::get_title
     */
    public function test_get_title(): void {
        $this->assertEquals('Test Form', $this->form_schema->get_title());
        $this->assertEquals('form_title_label', $this->form_schema_lang_string->get_title());
    }

    /**
     * @covers ::set_title
     * @covers ::get_title
     */
    public function test_set_title(): void {
        $set_title = $this->get_method('set_title');

        $original_title = $this->form_schema->get_title();

        $set_title->invoke($this->form_schema,
            'Test Form Lang String'
        );

        $this->assertNotEquals(
            $original_title,
            $this->form_schema->get_title()
        );

        $this->assertEquals(
            'Test Form Lang String',
            $this->form_schema->get_title()
        );
    }

    /**
     * @covers ::get_description
     */
    public function test_get_description(): void {
        $this->assertEquals(null, $this->form_schema->get_description());

        $this->assertEquals('archive_workflow_warning_message', $this->form_schema_lang_string->get_description());
    }

    /**
     * @covers ::set_description
     * @covers ::get_description
     */
    public function test_set_description(): void {
        $set_description = $this->get_method('set_description');

        $original_description = $this->form_schema->get_description();

        $set_description->invoke($this->form_schema,
            'a new description!'
        );

        $this->assertNotEquals(
            $original_description,
            $this->form_schema->get_description()
        );

        $this->assertEquals(
            'a new description!',
            $this->form_schema->get_description()
        );
    }

    /**
     * @covers ::resolve_lang_strings
     * @covers ::resolve_lang_string_description
     */
    public function test_resolve_lang_strings(): void {
        /**
         * Test $this->form_schema
         * This schema does not specify a component or lang strings, so resolve_lang_strings should have no effect
         */
        $original_form_json = $this->form_schema->to_json();
        $this->form_schema->resolve_lang_strings();

        $this->assertEquals(
            $original_form_json,
            $this->form_schema->to_json()
        );

        /**
         * Test $this->form_schema_lang_string
         * This schema specifies a component and lang strings for labels & for the description,
         * so we should see them resolved in the output
         */
        $original_form_json = $this->form_schema_lang_string->to_json();
        $this->form_schema_lang_string->resolve_lang_strings();

        $resolved_form_json = $this->form_schema_lang_string->to_json();

        $this->assertNotEquals(
            $original_form_json,
            $resolved_form_json
        );
        $select_one_field = '{"key":"complete","line":"1","label":"Overall progress","required":false,"type":"select_one","default":null,"attrs":{"choices":[{"key":"completed","label":"Completed"},{"key":"in_progress","label":"In progress"}]}}';
        $this->assertEquals(
            '{"title":"Form title","description":"<p>Are you sure you want to archive the workflow?</p><p><strong>Once archived</strong>: <ul><li>In-flight applications will continue until completed.</li><li>No new applications can be created</li></ul></p>","shortname":"test","revision":"1.0","version":"2021030200","language":"en-US","component":"mod_approval","fields":[{"key":"activity","line":"A","label":"Activity","type":"text"}],"sections":[{"key":"applications","line":"Start of stage","label":"All applications","fields":[{"key":"applicant","line":"1","label":"This is a non-langstring label!","type":"text","required":false},'.$select_one_field.']}]}',
            $resolved_form_json
        );
    }


    /**
     * Test help lang string replacement
     *
     * @return void
     */
    public function test_resolve_help_strings(): void {
        global $CFG;

        $json = json_decode(file_get_contents($CFG->dirroot . '/mod/approval/tests/fixtures/form/test_form_help.json'));
        $json->component = "approvalform_simple";

        $form_schema = form_schema::from_json(json_encode($json));
        $form_schema->resolve_lang_strings();
        // Test plain help text
        $field = $form_schema->get_field('agency_code_plain');
        $this->assertEquals('Discussed with manager?', $field->help);
        // Test html help text
        $field = $form_schema->get_field('agency_code');
        $this->assertEquals('<div class="text_to_html">Simple Request Form</div>', $field->help_html);
    }

    /**
     * @covers ::get_lang_string
     */
    public function test_get_lang_string() {
        $get_lang_string = $this->get_method('get_lang_string');

        $label = $get_lang_string->invoke($this->form_schema,
            'add_action',
            'mod_approval',
            false
        );

        $this->assertEquals(
            get_string('add_action', 'mod_approval'),
            $label
        );

        // Now let's attempt with '$preserve_string_key_reference' functionality
        $label = $get_lang_string->invoke($this->form_schema,
            'add_action',
            'mod_approval',
            true
        );

        $this->assertEquals(
            get_string('add_action', 'mod_approval') . ' [add_action]',
            $label
        );
    }

    /**
     * @covers ::resolve_lang_string_description
     */
    public function test_resolve_lang_string_description(): void {
        $resolve_lang_string_description = $this->get_method('resolve_lang_string_description');

        /**
         * First, test form_schema
         * This does not specify a description, therefore we should get null
         */
        $original_description = $this->form_schema->get_description();

        $resolve_lang_string_description->invoke($this->form_schema,
            'mod_approval',
            false
        );

        $this->assertEquals(
            $original_description,
            $this->form_schema->get_description()
        );

        $this->assertNull($this->form_schema->get_description());

        /**
         * Secondly, test form_lang_string
         * This specifies a description with a lang string which exists, so we expect it to resolve
         */
        $original_description = $this->form_schema_lang_string->get_description();

        $resolve_lang_string_description->invoke($this->form_schema_lang_string,
            'mod_approval',
            false
        );

        $this->assertNotEquals(
            $original_description,
            $this->form_schema_description_plain->get_description()
        );

        $this->assertEquals(
            get_string('archive_workflow_warning_message', 'mod_approval'),
            $this->form_schema_lang_string->get_description()
        );

        /**
         * Thirdly, lets test form_description_plain
         * This specifies a description with plain text (no lang string to resolve to) so we should observe no change
         */
        $original_description = $this->form_schema_description_plain->get_description();

        $resolve_lang_string_description->invoke($this->form_schema_description_plain,
            'mod_approval',
            false
        );

        $this->assertEquals(
            $original_description,
            $this->form_schema_description_plain->get_description()
        );
    }

    /**
     * @covers ::get_fields
     */
    public function test_get_fields(): void {
        $expected_fields = [
            'agency_code',
            'request_status',
            'detailed_description',
            'applicant_name',
            'training_vendor',
        ];
        $actual_fields = $this->form_schema->get_fields();
        $this->assertEquals($expected_fields, array_keys($actual_fields));
        /** @var form_schema_field $agency_code */
        $agency_code = $actual_fields['agency_code'];
        $this->assertInstanceOf(form_schema_field::class, $agency_code);
        $this->assertEquals('A', $agency_code->line);
        $this->assertEquals('Agency code', $agency_code->label);
        $this->assertEquals('text', $agency_code->type);
        $this->assertEquals('top/agency_code', $agency_code->get_index());
        $this->assertNull($agency_code->instruction);
        $this->assertNull($agency_code->default);
        $this->assertFalse($agency_code->required);
        $this->assertFalse($agency_code->disabled);
    }

    /**
     * @covers ::get_fields_of_type
     */
    public function test_get_fields_of_type(): void {
        $text_fields = $this->form_schema->get_fields_of_type('text');
        foreach ($text_fields as $text_field) {
            $this->assertEquals('text', $text_field->type);
        }
    }

    /**
     * @covers ::get_sections
     */
    public function test_get_sections(): void {
        $expected_sections = [
            'A' => ['line' => 'Section A', 'label' => 'Basic Information'],
            'B' => ['line' => 'Section B', 'label' => 'Course Information'],
        ];
        $actual_sections = $this->form_schema->get_sections();
        $this->assertEquals(array_keys($expected_sections), array_keys($actual_sections));
        /** @var form_schema_section $section_a */
        $section_a = $actual_sections['A'];
        $this->assertInstanceOf(form_schema_section::class, $section_a);
        $this->assertEquals('A', $section_a->get_key());
        $this->assertEquals('Section A', $section_a->line);
        $this->assertEquals('Basic Information', $section_a->label);
    }

    /**
     * @covers ::set_section_property
     */
    public function test_set_section_property(): void {
        $set_field_property = $this->get_method('set_section_property');

        $key = 'A';
        $original_section = $this->form_schema->get_sections()[$key];

        $set_field_property->invoke(
            $this->form_schema,
            $key,
            'label',
            'A new value!! :)'
        );
        $modified_section = $this->form_schema->get_sections()[$key];

        $this->assertNotEquals($original_section->label, $modified_section->label);
        $this->assertEquals('A new value!! :)', $modified_section->label);
    }

    /**
     * @covers ::set_section_label
     */
    public function test_set_section_properties(): void {
        $key = 'A';
        $original_section = $this->form_schema->get_sections()[$key];

        $this->form_schema->set_section_label($key, 'This is a brand new label!!!');

        $modified_section = $this->form_schema->get_sections()[$key];

        $this->assertNotEquals($original_section->label, $modified_section->label);
        $this->assertEquals('This is a brand new label!!!', $modified_section->label);

        // Empty labels don't get set
        $this->form_schema->set_section_label($key, '');
        $modified_section = $this->form_schema->get_sections()[$key];
        $this->assertEquals('This is a brand new label!!!', $modified_section->label);
    }

    /**
     * @covers ::get_top_level_fields
     */
    public function test_get_top_level_fields(): void {
        $expected_fields = [
            'agency_code',
            'request_status',
            'detailed_description',
        ];
        $this->assertEquals($expected_fields, array_keys($this->form_schema->get_top_level_fields()));
    }

    /**
     * @covers ::get_section_fields
     */
    public function test_get_section_fields(): void {
        $expected_fields_A = ['applicant_name',];
        $expected_fields_B = ['training_vendor'];
        $this->assertEquals($expected_fields_A, array_keys($this->form_schema->get_section_fields('A')));
        $this->assertEquals($expected_fields_B, array_keys($this->form_schema->get_section_fields('B')));
    }

    /**
     * @covers ::has_field
     */
    public function test_has_field(): void {
        $this->assertTrue($this->form_schema->has_field('applicant_name'));
        $this->assertFalse($this->form_schema->has_field('unknown_key'));
    }

    /**
     * @covers ::get_field
     */
    public function test_get_field(): void {
        // Simple field.
        $expected_field = ['line' => '1', 'label' => 'Applicant\'s Name', 'type' => 'fullname'];
        $field = $this->form_schema->get_field('applicant_name');
        $this->assertInstanceOf(form_schema_field::class, $field);
        $this->assertEquals('0/applicant_name', $field->get_index());
        $this->assertEquals('applicant_name', $field->get_field_key());
        $this->assertEquals('0', $field->get_section_index());
        foreach ($expected_field as $key => $value) {
            $this->assertEquals($value, $field->{$key});
        }

        // Complex field (has choices).
        $expected_field = ["line" => "B", "label" => "Request Status", "type" => "select_one"];
        $field = $this->form_schema->get_field('request_status');
        $this->assertEquals('top/request_status', $field->get_index());
        $this->assertEquals('request_status', $field->get_field_key());
        $this->assertEquals('top', $field->get_section_index());
        foreach ($expected_field as $key => $value) {
            $this->assertEquals($value, $field->{$key});
        }
        $output = $field->to_stdClass();
        $this->assertEquals('request_status', $output->key);
        $this->assertEquals('B', $output->line);
        $this->assertEquals('Request Status', $output->label);
        $this->assertNull($output->instruction);
        $this->assertNull($output->help);
        $this->assertEquals('select_one', $output->type);
        $this->assertObjectNotHasProperty('format', $output->attrs);
        $this->assertFalse($output->required);
        $this->assertFalse($output->disabled);
        $this->assertNull($output->default);
        $this->assertIsArray($output->attrs->choices);
        $this->assertCount(3, $output->attrs->choices);
    }

    /**
     * @covers ::get_field_section
     */
    public function test_get_field_section(): void {
        $expected_section_a = ["line" => "Section A", "label" => "Basic Information"];
        $section = $this->form_schema->get_field_section('applicant_name');
        $this->assertInstanceOf(form_schema_section::class, $section);
        $this->assertEquals('A', $section->get_key());
        foreach ($expected_section_a as $key => $value) {
            $this->assertEquals($value, $section->{$key});
        }
    }

    /**
     * @covers ::to_json
     */
    public function test_to_json(): void {
        $expected =
            '{"title":"Test Form","shortname":"test","revision":"1.0","version":"2021030200","language":"en-US","fields":[{"key":"agency_code","line":"A","label":"Agency code","required":false,"type":"text"},{"key":"request_status","line":"B","label":"Request Status","type":"select_one","required":false,"default":null,"attrs":{"choices":[{"key":null,"label":"Select one"},{"key":"Yes","label":"Yes, of course"},{"key":"No","label":"No thank you"}]}},{"key":"detailed_description","line":"C","label":"A detailed description","required":false,"type":"editor"}],"sections":[{"key":"A","line":"Section A","label":"Basic Information","fields":[{"key":"applicant_name","line":"1","label":"Applicant\'s Name","instruction":"Last, First, Middle Initial","required":false,"type":"fullname","attrs":{"format":"last,first,middle-initial"}}]},{"key":"B","line":"Section B","label":"Course Information","fields":[{"key":"training_vendor","line":"1","label":"Name and Mailing Address of Training Vendor","instruction":"No., Street, City, Sata, ZIP Code","required":false,"type":"address"}]}]}';
        $this->assertEquals($expected, $this->form_schema->to_json());
    }

    /**
     * @covers ::apply_formviews
     */
    public function test_apply_formviews(): void {
        $this->setAdminUser();
        list($workflow_entity, $framework, $assignment) = $this->create_workflow_and_assignment();
        $workflow = workflow::load_by_entity($workflow_entity);

        /** @var \mod_approval\model\workflow\workflow_stage $stage1 */
        $stage1 = $workflow->latest_version->stages->first();
        $new_schema = $this->form_schema->apply_formviews($stage1->formviews);

        // create_workflow_and_assignment creates a default formviews at stage 1
        $this->assertCount(5, $new_schema->get_fields());
        $field = $new_schema->get_field('agency_code');
        $this->assertInstanceOf(form_schema_field::class, $field);
        $this->assertEquals('A', $field->line);
        $this->assertEquals('Agency code', $field->label);
        $this->assertEquals('text', $field->type);
        $this->assertEquals('top/agency_code', $field->get_index());
        $this->assertNull($field->instruction);
        $this->assertNull($field->default);
        $this->assertFalse($field->required);
        $this->assertFalse($field->disabled);

        $schema = json_decode($new_schema->to_json());
        $this->assertCount(2, $schema->sections);
        $this->assertEquals('A', $schema->sections[0]->key);
        $this->assertEquals('B', $schema->sections[1]->key);
    }

    /**
     * @covers ::set_field_property
     */
    public function test_set_field_property(): void {
        $set_field_property = $this->get_method('set_field_property');

        // First, let's test a field within a section
        $key = 'applicant_name';
        $original_field = $this->form_schema->get_field($key);

        $set_field_property->invoke(
            $this->form_schema,
            $key,
            'label',
            'A new value!'
        );
        $modified_field = $this->form_schema->get_field($key);

        $this->assertNotEquals($original_field->label, $modified_field->label);
        $this->assertEquals('A new value!', $modified_field->label);

        // Secondly, let's test a field outside a section (index: top)
        $key = 'request_status';
        $original_field = $this->form_schema->get_field($key);

        $set_field_property->invoke(
            $this->form_schema,
            $key,
            'label',
            'Another new value!'
        );
        $modified_field = $this->form_schema->get_field($key);

        $this->assertNotEquals($original_field->label, $modified_field->label);
        $this->assertEquals('Another new value!', $modified_field->label);
    }

    /**
     *
     * @covers ::set_field_default
     * @covers ::set_field_disabled
     * @covers ::set_field_label
     */
    public function test_set_field_properties(): void {
        $key = 'applicant_name';
        $field = $this->form_schema->get_field($key);
        $this->assertNull($field->default);
        $this->assertFalse($field->disabled);

        $this->form_schema->set_field_default($key, 'Olivia S. Parsons');
        $this->form_schema->set_field_disabled($key, true);
        $this->form_schema->set_field_label($key, 'This is a label');

        $field = $this->form_schema->get_field($key);

        $this->assertEquals('Olivia S. Parsons', $field->default);
        $this->assertTrue($field->disabled);
        $this->assertEquals('This is a label', $field->label);

        // Empty labels don't get set
        $this->form_schema->set_field_label($key, '');
        $field = $this->form_schema->get_field($key);
        $this->assertEquals('This is a label', $field->label);
    }

    /**
     * @covers ::add_field_choices
     */
    public function test_add_field_choices(): void {
        // Add new choices to the existing one
        $key = 'request_status';
        $choices = $this->form_schema->get_field($key)->attrs->choices;
        $this->assertCount(3, $choices);
        $this->form_schema->add_field_choices($key, [
            (object)[
                'key' => 'N/A',
                'label' => 'Not applicable',
            ]
        ]);
        $choices = $this->form_schema->get_field($key)->attrs->choices;
        $this->assertCount(4, $choices);

        // Add an empty choice
        $this->form_schema->add_field_choices($key, [
            (object)[]
        ]);
        $choices = $this->form_schema->get_field($key)->attrs->choices;
        $this->assertCount(4, $choices);

        // Add duplicate choice to a key
        $this->form_schema->add_field_choices($key, [
            (object)[
                'key' => 'Yes',
                'label' => 'Yes, of course',
            ]
        ]);
        $choices = $this->form_schema->get_field($key)->attrs->choices;
        $this->assertCount(4, $choices);

        // Add new choice to the key with no choice
        $key = 'agency_code';
        $choices = $this->form_schema->get_field($key)->attrs;
        $this->assertNull($choices);
        $this->form_schema->add_field_choices($key, [
            (object)[
                'key' => 'first',
                'label' => 'first option',
            ]
        ]);
        $choices = $this->form_schema->get_field($key)->attrs->choices;
        $this->assertCount(1, $choices);
    }
}
