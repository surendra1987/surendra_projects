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
 * @author Tatsuhiro Kirihara <tatsuhiro.kirihara@totaralearning.com>
 * @package mod_approval
 */

use container_approval\approval as approval_container;
use core\entity\user;
use core\orm\collection;
use mod_approval\form_schema\form_schema_field;
use mod_approval\model\form\form;
use mod_approval\model\form\form_data;
use mod_approval\model\form\form_version;
use mod_approval\model\status;
use mod_approval\model\workflow\stage_type\form_submission;
use mod_approval\model\workflow\workflow;
use mod_approval\entity\application\application_action as application_action_entity;
use mod_approval\entity\application\application_submission as application_submission_entity;
use mod_approval\entity\workflow\workflow_version as workflow_version_entity;
use mod_approval\exception\malicious_form_data_exception;
use mod_approval\model\application\application as application_model;
use mod_approval\model\application\application_action as application_action_model;
use mod_approval\model\application\application_submission as application_submission_model;
use mod_approval\model\assignment\assignment;
use mod_approval\model\assignment\assignment_type;
use mod_approval\model\form\approvalform_base;
use mod_approval\model\workflow\workflow_stage;
use mod_approval\model\workflow\workflow_stage_formview;
use mod_approval\model\workflow\workflow_type;
use mod_approval\model\workflow\workflow_version;
use mod_approval\testing\application_generator_object;

require_once(__DIR__ . '/testcase.php');

/**
 * @group approval_workflow
 * @coversDefaultClass mod_approval\model\form\form_data
 */
class mod_approval_form_data_test extends mod_approval_testcase {
    public function setUp(): void {
        parent::setUp();
    }

    protected function tearDown(): void {
        parent::tearDown();
    }

    /**
     * @covers ::from_json
     */
    public function test_from_json(): void {
        $happy_cases = ['{}', '{"a":"b"}', '{"sugar":"1/2 cup"}', '{"sugar":"1\\/2 cup"}', '{"sugar":31415926535897932384}'];
        $sad_cases = ['', 'null', 'true', '"a"', '["a"]', ' {"a":"b"}', '{a:"b"}'];
        foreach ($happy_cases as $json) {
            form_data::from_json($json);
        }
        foreach ($sad_cases as $json) {
            try {
                form_data::from_json($json);
                $this->fail('malicious_form_data_exception expected');
            } catch (malicious_form_data_exception $ex) {
            }
        }
    }

    /**
     * @covers ::to_json
     */
    public function test_to_json(): void {
        $entity = new application_action_entity(['id' => 42]);
        $entity->form_data = '{}';
        $inst = application_action_model::load_by_entity($entity);
        $this->assertEquals('{}', form_data::from_instance($inst)->to_json());
        $entity->form_data = '{"sugar":"1/2 cup"}';
        $inst = application_action_model::load_by_entity($entity);
        $this->assertEquals('{"sugar":"1/2 cup"}', form_data::from_instance($inst)->to_json());
        $entity->form_data = '{"ツッ":"✌️"}';
        $inst = application_action_model::load_by_entity($entity);
        $this->assertEquals('{"\u30c4\u30c3":"\u270c\ufe0f"}', form_data::from_instance($inst)->to_json());
    }

    /**
     * @covers ::from_instance
     */
    public function test_from_instance(): void {
        // entities are not allowed
        $inst = new application_action_entity(['id' => 42, 'form_data' => '{}']);
        try {
            form_data::from_instance($inst);
            $this->fail('coding_exception expected');
        } catch (coding_exception $ex) {
            $this->assertStringContainsString('Unknown instance supplied', $ex->getMessage());
        }
        $inst = new application_submission_entity(['id' => 42, 'form_data' => '{}']);
        try {
            form_data::from_instance($inst);
            $this->fail('coding_exception expected');
        } catch (coding_exception $ex) {
            $this->assertStringContainsString('Unknown instance supplied', $ex->getMessage());
        }
        // invalid instance
        try {
            $inst = new DateTime();
            form_data::from_instance($inst);
            $this->fail('coding_exception expected');
        } catch (coding_exception $ex) {
            $this->assertStringContainsString('Unknown instance supplied', $ex->getMessage());
        }
        // models are accepted
        $inst = application_action_model::load_by_entity(new application_action_entity(['id' => 42, 'form_data' => '{}']));
        $this->assertInstanceOf(form_data::class, form_data::from_instance($inst));
        $inst = application_submission_model::load_by_entity(new application_submission_entity(['id' => 42, 'form_data' => '{}']));
        $this->assertInstanceOf(form_data::class, form_data::from_instance($inst));
    }

    /**
     * @covers ::has_value
     */
    public function test_has_value(): void {
        $form_data = form_data::from_json(json_encode(['null' => null, 'empty' => '', 'kia' => 'ora', 'size' => 0]));
        $this->assertTrue($form_data->has_value('null'));
        $this->assertTrue($form_data->has_value('empty'));
        $this->assertTrue($form_data->has_value('kia'));
        $this->assertTrue($form_data->has_value('size'));
        $this->assertFalse($form_data->has_value('mimetype'));
    }

    /**
     * @covers ::get_value
     */
    public function test_get_value(): void {
        $form_data = form_data::from_json('{"null":null,"empty":"","kia":"ora"}');
        $this->assertSame(null, $form_data->get_value('null'));
        $this->assertSame('', $form_data->get_value('empty'));
        $this->assertSame('ora', $form_data->get_value('kia'));
        $this->assertSame(null, $form_data->get_value('he_who_must_not_exist'));
    }

    /**
     * @covers ::check_readiness
     */
    public function test_check_readiness(): void {
        $happy_cases = [
            ['agency_code' => '?'],
            ['agency_code' => '?', 'request_status' => 'Yes'],
            // choices are not validated in backend at the moment
            ['agency_code' => '?', 'request_status' => null],
            ['agency_code' => '?', 'request_status' => 'Nah'],
            ['agency_code' => '0'],
        ];
        $sad_cases = [
            [['agency_code' => ''], 'Required field(s) are not set: agency_code'],
            [['agency_code' => null], 'Required field(s) are not set: agency_code'],
            [['agency_code' => false], 'Required field(s) are not set: agency_code'],
        ];
        $this->setAdminUser();

        $application = $this->create_application_for_user('default');
        $stage = $application->current_stage;
        workflow_version_entity::repository()->where('id', $application->workflow_version_id)
            ->update([
                'status' => status::DRAFT
            ]);
        $workflow_version = $application->workflow_version->refresh();
        $this->erase_formviews_from_stages($workflow_version);

        $this->assertNotNull($stage);
        workflow_stage_formview::create($stage, 'agency_code', true, false, 'nah');
        workflow_stage_formview::create($stage, 'request_status', false, false, 'bah');

        foreach ($happy_cases as $data) {
            $data = json_encode($data);
            $form_data = form_data::from_json($data);
            try {
                $form_data->check_readiness($stage);
            } catch (Throwable $ex) {
                $this->fail('no exception expected: ' . $data);
            }
        }
        foreach ($sad_cases as [$data, $expected]) {
            $data = json_encode($data);
            $form_data = form_data::from_json($data);
            try {
                $form_data->check_readiness($stage);
                $this->fail('malicious_form_data_exception expected: ' . $data);
            } catch (malicious_form_data_exception $ex) {
                $this->assertStringContainsString($expected, $ex->getMessage());
            }
        }
    }

    /**
     * @covers ::field_is_required
     */
    public function test_field_is_required() {
        $this->setAdminUser();
        $application = $this->create_application_for_user('conditional_required_fields');
        $stage = $application->current_stage;
        $json_schema = json_decode(file_get_contents(__DIR__ . "/fixtures/schema/conditional_required_fields.json"));
        $schema_fields = [];

        foreach ($json_schema->fields as $field) {
            $schema_fields[$field->key] = $field;
        }

        $reflection = new ReflectionClass(form_data::class);
        $method = $reflection->getMethod('field_is_required');
        $method->setAccessible(true);

        $this->test_field_with_two_rules($schema_fields, $stage, $method);
    }

    /**
     * Test field is required for field with two rules.
     *
     * @param stdClass[] $schema_fields
     * @param workflow_stage $stage
     * @param ReflectionMethod $method
     */
    private function test_field_with_two_rules(array $schema_fields, workflow_stage $stage, ReflectionMethod $method): void {
        $test_cases = [
            [
                'data' => [
                    'condition_accepted' => 'Yes',
                    'daily_cost' => 40,
                ],
                'expected' => true,
            ],
            [
                'data' => [
                    'condition_accepted' => 'No',
                    'daily_cost' => 60,
                ],
                'expected' => true,
            ],
            [
                'data' => [
                    'condition_accepted' => 'No',
                    'daily_cost' => 40,
                ],
                'expected' => false,
            ],
            [
                'data' => [
                    'condition_accepted' => 'Yes',
                    'daily_cost' => 60,
                ],
                'expected' => true,
            ],
        ];
        $schema_field = new form_schema_field('field_with_two_rules', $schema_fields['field_with_two_rules']);
        $form_view = workflow_stage_formview::create($stage, 'field_with_two_rules', false, false, '');

        foreach ($test_cases as $test_case) {
            $data = json_encode($test_case['data']);
            $form_data = form_data::from_json($data);

            $is_required = $method->invokeArgs($form_data, [$form_view, $schema_field]);
            $this->assertEquals($test_case['expected'], $is_required);
        }
    }

    /**
     * @covers ::empty_value
     */
    public function test_empty_value(): void {
        $method = new ReflectionMethod(form_data::class, 'empty_value');
        $method->setAccessible(true);
        $this->assertTrue($method->invoke(null, null));
        $this->assertTrue($method->invoke(null, false));
        $this->assertTrue($method->invoke(null, ''));
        $this->assertTrue($method->invoke(null, []));
        $this->assertTrue($method->invoke(null, (object) []));
        $this->assertFalse($method->invoke(null, true));
        $this->assertFalse($method->invoke(null, 0));
        $this->assertFalse($method->invoke(null, 1));
        $this->assertFalse($method->invoke(null, 0.0));
        $this->assertFalse($method->invoke(null, INF));
        $this->assertFalse($method->invoke(null, 'null'));
        $this->assertFalse($method->invoke(null, 'false'));
        $this->assertFalse($method->invoke(null, 'undefined'));
        $this->assertFalse($method->invoke(null, '0'));
        $this->assertFalse($method->invoke(null, '😶'));
        $this->assertFalse($method->invoke(null, [null]));
        $this->assertFalse($method->invoke(null, [false]));
        $this->assertFalse($method->invoke(null, [0]));
        $this->assertFalse($method->invoke(null, ['']));
        $this->assertFalse($method->invoke(null, (object) [0 => 0]));
        $this->assertFalse($method->invoke(null, (object) ['' => '']));
    }

    /**
     * Create assignments, applications, workflow stages and a workflow container to contain them
     *
     * @param array $configuration
     * @return collection|assignment[]
     */
    private function create_full_assignments(array $configuration = []): collection {
        $defaults = [
            'number_of_assignments' => 1,
            'number_of_applications' => 1,
            'number_of_stages' => 1,
            'assignment_type' => assignment_type\cohort::get_code(),
            'category_id' => approval_container::get_default_category_id(),
            'json_schema' => '',
        ];
        $configuration = array_merge($defaults, $configuration);

        if (!user::logged_in()) {
            throw new coding_exception('user not logged in');
        }
        $type = workflow_type::create('test workflow type');
        $form = form::create('simple', 'test form');
        $workflow = workflow::create(
            $type,
            $form,
            'Test workflow',
            '',
            assignment_type\cohort::get_code(),
            $this->getDataGenerator()->create_cohort()->id,
            "?"
        );

        // Parse json form schema
        $form_schema = $configuration['json_schema'] ?: file_get_contents(__DIR__ . '/fixtures/form/test_form.json');
        // TODO: config->number_of_form_versions ??
        $form_version = form_version::create($form, 'test form version', $form_schema);
        // TODO: config->number_of_workflow_versions ??
        $workflow_version = workflow_version::create($workflow, $form_version);
        // Create stages and add all fields to each stage
        $form_schema_parsed = json_decode($form_schema, true);
        $fields = [];
        foreach ($form_schema_parsed['fields'] as $field) {
            $fields[$field['key']] = $field;
        }
        foreach ($form_schema_parsed['sections'] as $section) {
            foreach ($section['fields'] as $field) {
                $fields[$field['key']] = $field;
            }
        }
        /** @var workflow_stage[] */
        $workflow_stages = [];
        for ($wsi = 1; $wsi <= $configuration['number_of_stages']; $wsi++) {
            $workflow_stage = workflow_stage::create($workflow_version, "Test stage {$wsi}", form_submission::get_enum());
            $wfl = 0;
            foreach ($fields as $field) {
                $required = $wfl == 0; // first field of each stage is required
                $disabled = ($wfl % 2) == 0; // even field is disabled
                $default = $field['default'] ?? null;
                $formview = workflow_stage_formview::create($workflow_stage, $field['key'], $required, $disabled, $default);
                $wfl++;
            }
            $workflow_stages[] = $workflow_stage;
        }
        $assignments = [];
        for ($asi = 1; $asi <= $configuration['number_of_assignments']; $asi++) {
            if ($configuration['assignment_type'] == assignment_type\cohort::get_code()) {
                $identifier = $this->getDataGenerator()->create_cohort()->id;
            } else {
                // TODO: support other types
                $this->fail("Sorry, I'm too lazy to implement other types atm");
            }
            $assignment = assignment::create($workflow->container, $configuration['assignment_type'], $identifier, $asi === 1);
            $assignment->activate();
            $assignments[] = $assignment;
            for ($api = 1; $api <= $configuration['number_of_applications']; $api++) {
                $gen = \mod_approval\testing\generator::instance();
                $application = application_model::load_by_entity(
                    $gen->create_application(
                        new application_generator_object(
                            $workflow_version->id,
                            $form_version->id,
                            $assignment->id,
                            count($workflow_stages) ? $workflow_stages[0]->id : null
                        )
                    )
                );
            }
        }
        return new collection($assignments);
    }

    /**
     * @covers ::get_file_item_id
     */
    public function test_file_item_id(): void {
        $data = form_data::from_json('{}');
        $this->assertEquals(null, $data->get_file_item_id());

        $data = form_data::from_json('{}', 14);
        $this->assertEquals(14, $data->get_file_item_id());
    }

    /**
     * @covers ::prepare_fields_for_edit
     */
    public function test_prepare_fields_for_edit(): void {
        $this->setAdminUser();
        $application = $this->create_application_for_user('editor_schema');
        $user = user::logged_in();
        $file_storage = get_file_storage();

        $stored_file = $file_storage->create_file_from_string([
            'contextid' => $application->context->id,
            'component' => form_data::FILE_COMPONENT,
            'filearea'  => form_data::FILE_AREA,
            'itemid'    => $application->id,
            'filepath'  => '/',
            'filename'  => 'hello_world.txt',
        ], 'hello world');

        $data = form_data::from_json('{}');

        $data->prepare_fields_for_edit($application->get_interactor($user->id));

        $files = $file_storage->get_area_files(
            context_user::instance($user->id)->id,
            'user',
            'draft',
            $data->get_file_item_id(),
        );
        $this->assertContains('hello_world.txt', array_map(fn ($x) => $x->get_filename(), $files));
    }

    /**
     * @covers ::prepare_fields_for_submission
     */
    public function test_prepare_fields_for_save(): void {
        $this->setAdminUser();
        $application = $this->create_application_for_user('editor_schema');
        $user = user::logged_in();
        list($value, $draft_id) = $this->generate_editor_content($application, FORMAT_HTML);
        $data = form_data::from_json(
            json_encode([
                'color' => $value,
            ]),
            $draft_id
        );
        $approval_form = approvalform_base::from_plugin_name($application->form_version->form->plugin_name);
        $data->prepare_fields_for_submission($application->get_interactor($user->id), $approval_form);

        $file_names = $this->get_application_file_names($application);
        $this->assertContains('hello_world.txt', $file_names);
    }

    /**
     * Test files are copied when an application is cloned.
     */
    public function test_copy_files_when_cloning() {
        $this->setAdminUser();
        $application = $this->create_application_for_user('editor_schema');
        $user = user::logged_in();

        list($value, $draft_id) = $this->generate_editor_content($application, FORMAT_HTML);

        $response = json_encode([
            'color' => $value
        ]);
        application_submission_model::create_or_update(
            $application,
            $user->id,
            form_data::from_json($response, $draft_id)
        );

        $cloned_application = $application->clone($user->id);

        // Get files for application.
        $source_file_names = $this->get_application_file_names($application);
        // Check source application files still exist.
        $this->assertNotEmpty($source_file_names);

        $cloned_file_names = $this->get_application_file_names($cloned_application);
        $this->assertNotEmpty($cloned_file_names);
        $this->assertEqualsCanonicalizing($source_file_names, $cloned_file_names);
    }

    /**
     * Get file names in an application's file storage area.
     *
     * @param application_model $application
     * @return array
     */
    private function get_application_file_names(application_model $application): array {
        $file_storage = get_file_storage();
        $files = $file_storage->get_area_files(
            $application->context->id,
            form_data::FILE_COMPONENT,
            form_data::FILE_AREA,
            $application->id
        );
        return array_map(fn ($x) => $x->get_filename(), $files);
    }

    /**
     * Generate a draft id.
     *
     * @param application_model $application
     * @return int
     */
    private function generate_draft_id(application_model $application): int {
        $draft_id = 0;

        file_prepare_draft_area(
            $draft_id,
            $application->context->id,
            form_data::FILE_COMPONENT,
            form_data::FILE_AREA,
            $application->id
        );

        return $draft_id;
    }

    /**
     * Creates a file in draft area.
     *
     * @param int $draft_id
     * @return string
     */
    private function create_file(int $draft_id): string {
        $stored_file = get_file_storage()->create_file_from_string([
            'contextid' => context_user::instance(user::logged_in()->id)->id,
            'component' => 'user',
            'filearea'  => 'draft',
            'itemid'    => $draft_id,
            'filepath'  => '/',
            'filename'  => 'hello_world.txt',
        ], 'hello world');

        return moodle_url::make_draftfile_url(
            $draft_id,
            $stored_file->get_filepath(),
            $stored_file->get_filename()
        )->out(false);
    }

    /**
     * Generate content to be processed.
     *
     * @param string $format
     * @return array
     */
    private function generate_editor_content(application_model $application, string $format, $text = 'My content'): array {
        $draft_id = $this->generate_draft_id($application);
        $file = $this->create_file($draft_id);

        // We could extend testing with other format contents. Out of scope for now.
        $content = "<h3>$text</h3><img src='$file' alt='my alt'/>";

        return [
            json_encode([
                'format' => $format,
                'content' => $content,
            ]),
            $draft_id,
        ];
    }

    /**
     * @covers ::from_array()
     * @covers ::to_array()
     */
    public function test_to_and_from_array() {
        $test = ['foo' => 'bar', 'alice' => 'bob'];

        // Test with no file_item_id
        $form_data = form_data::from_array($test);
        $this->assertEquals('bar', $form_data->get_value('foo'));
        $this->assertEquals('bob', $form_data->get_value('alice'));
        $this->assertNull($form_data->get_file_item_id());

        // Test with a file_item_id
        $form_data = form_data::from_array($test, 42);
        $this->assertEquals('bar', $form_data->get_value('foo'));
        $this->assertEquals('bob', $form_data->get_value('alice'));
        $this->assertEquals(42, $form_data->get_file_item_id());

        // Test to_array()
        $export = $form_data->to_array();
        $this->assertEqualsCanonicalizing($test, $export);
    }
}
