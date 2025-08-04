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
 * @author Nathaniel Walmsley <nathaniel.walmsley@totara.com>
 * @package mod_approval
 */

use approvalform_enrol\installer;
use core\entity\tenant;
use core\orm\query\builder;
use core_enrol\hook\enrol_instance_extra_settings_definition;
use core_enrol\hook\enrol_instance_extra_settings_save;
use core_enrol\hook\enrol_instance_extra_settings_validation;
use core_enrol\testing\enrol_instance_edit_form_test_setup;
use mod_approval\watcher\enrol_instance_extra_settings;
use totara_core\advanced_feature;

require_once(__DIR__ . '/testcase.php');

/**
 * @group approval_workflow
 * @coversDefaultClass \mod_approval\watcher\enrol_instance_extra_settings
 */
class mod_approval_enrol_instance_extra_settings_watcher_test extends mod_approval_testcase {

    use enrol_instance_edit_form_test_setup;

    /**
     * Check all three hooks have checks for the approval_workflow feature being disabled.
     */
    public function test_expected_failure_approval_disabled() {
        advanced_feature::disable('approval_workflows');

        /**
         * Shared variables.
         */
        $watcher = new enrol_instance_extra_settings();
        $workflow = $this->create_workflow_for_user(
            'enrol',
            [installer::class, 'configure_publishable_workflow'],
            'enrol',
            null
        );
        $course = $this->getDataGenerator()->create_course();
        $context = context_course::instance($course->id);
        $type = 'self'; // Choose 'self' as a testable enabled plugin type.
        $plugin = enrol_get_plugin($type);
        $return = new moodle_url('/enrol/instances.php', array('id' => $course->id));
        $instance = (object)$plugin->get_instance_defaults();
        $instance->id          = null;
        $instance->name        = 'Testing instance';
        $instance->enrol       = 'self';
        $instance->courseid    = $course->id;
        $instance->status      = ENROL_INSTANCE_ENABLED;
        $form = new \enrol_instance_edit_form(null, array($instance, $plugin, $context, $type, $return));

        /**
         *  HOOK: Definition tests.
         */
        // This is the URL to which the user would be redirected on success
        $definition_hook = new enrol_instance_extra_settings_definition($form, $instance, $plugin, $context);

        $watcher = new enrol_instance_extra_settings();
        $watcher->definition($definition_hook);

        // The form element should NOT have been added.
        $this->assertFalse(isset($form->_elementIndex['workflow_id']));

        /**
         *  HOOK: Validation tests.
         */
        // Create a validation hook with a bad workflow id.
        $data = $this->get_validation_test_data();
        $data['workflow_id'] = -1;
        $validation_hook = new enrol_instance_extra_settings_validation($data, [], $instance, $plugin, $context);
        $watcher->validation($validation_hook);
        $errors = $validation_hook->get_errors();

        // The workflow validation shouldn't run, thus no workflow id mismatch error.
        $this->assertEmpty($errors);

        /**
         *  HOOK: Save tests.
         */
        $instance->workflow_id = null;
        $instance->id = builder::table('enrol')->insert((array)$instance);
        $instance->workflow_id = $workflow->id;

        // No records matching the workflow id.
        $result = builder::table('enrol')
            ->where('workflow_id', '=', $instance->workflow_id)
            ->get();
        $this->assertCount(0, $result);
        
        $save_hook = new enrol_instance_extra_settings_save(new \stdClass(), $instance);
        $watcher->save($save_hook);

        // No records matching the workflow id.
        $result = builder::table('enrol')
            ->where('workflow_id', '=', $instance->workflow_id)
            ->get();
        $this->assertCount(0, $result);

        // For good measure lets check the instance record.
        $result = builder::table('enrol')
            ->where('id', '=', $instance->id)
            ->get();
        $this->assertEmpty($result->first()->workflow_id);
    }

    /**
     * Check all three hooks have checks for the plugin supporting approval workflows.
     */
    public function test_expected_failure_approval_unsupported() {
        // Just double check this is on for this one, to avoid false positives.
        advanced_feature::enable('approval_workflows');

        /**
         * Shared variables.
         */
        $watcher = new enrol_instance_extra_settings();
        $workflow = $this->create_workflow_for_user(
            'enrol',
            [installer::class, 'configure_publishable_workflow'],
            'enrol',
            null
        );
        $course = $this->getDataGenerator()->create_course();
        $context = context_course::instance($course->id);
        $type = 'manual'; // Choose 'manual' as a testable disabled plugin type.
        $plugin = enrol_get_plugin($type);
        $return = new moodle_url('/enrol/instances.php', array('id' => $course->id));
        $instance = (object)$plugin->get_instance_defaults();
        $instance->id           = null;
        $instance->name         = 'Testing instance';
        $instance->enrol        = 'manual';
        $instance->courseid     = $course->id;
        $instance->status       = ENROL_INSTANCE_ENABLED;
        $instance->notifyall    = 0;
        $instance->expirynotify = 0;
        $form = new \enrol_instance_edit_form(null, array($instance, $plugin, $context, $type, $return));

        /**
         *  HOOK: Definition tests.
         */
        // This is the URL to which the user would be redirected on success
        $definition_hook = new enrol_instance_extra_settings_definition($form, $instance, $plugin, $context);

        $watcher = new enrol_instance_extra_settings();
        $watcher->definition($definition_hook);

        // The form element should NOT have been added.
        $this->assertFalse(isset($form->_elementIndex['workflow_id']));

        /**
         *  HOOK: Validation tests.
         */
        // Create a validation hook with a bad workflow id.
        $data = $this->get_validation_test_data();
        $data['workflow_id'] = -1;
        $validation_hook = new enrol_instance_extra_settings_validation($data, [], $instance, $plugin, $context);
        $watcher->validation($validation_hook);
        $errors = $validation_hook->get_errors();

        // The workflow validation shouldn't run, thus no workflow id mismatch error.
        $this->assertEmpty($errors);

        /**
         *  HOOK: Save tests.
         */
        $instance->workflow_id = null;
        $instance->id = builder::table('enrol')->insert((array)$instance);
        $instance->workflow_id = $workflow->id;

        // No records matching the workflow id.
        $result = builder::table('enrol')
            ->where('workflow_id', '=', $instance->workflow_id)
            ->get();
        $this->assertCount(0, $result);

        $save_hook = new enrol_instance_extra_settings_save(new \stdClass(), $instance);
        $watcher->save($save_hook);

        // No records matching the workflow id.
        $result = builder::table('enrol')
            ->where('workflow_id', '=', $instance->workflow_id)
            ->get();
        $this->assertCount(0, $result);

        // For good measure lets check the instance record.
        $result = builder::table('enrol')
            ->where('id', '=', $instance->id)
            ->get();
        $this->assertEmpty($result->first()->workflow_id);
    }

    /**
     * When an approval workflow exists for the course and multitenancy
     * is not enabled, we expect there to be no errors.
     */
    public function test_validate_with_workflow_no_multitenancy(): void {
        list($instance, $plugin, $context, $type, $return) = $this->enrol_instance_edit_form_params();

        $workflow = $this->create_workflow_for_user(
            'enrol',
            [installer::class, 'configure_publishable_workflow'],
            'enrol',
            null
        );
        $data = $this->get_validation_test_data();
        $data['workflow_id'] = $workflow->id;
        $validation_hook = new enrol_instance_extra_settings_validation($data, [], $instance, $plugin, $context);

        $watcher = new enrol_instance_extra_settings();
        $watcher->validation($validation_hook);
        $errors = $validation_hook->get_errors();
        $this->assertEmpty($errors);
    }

    /**
     * As long as the tenants aren't isolated, an approval could belong
     * to a system-level course or a tenant-level course
     */
    public function test_validate_with_workflow_multitenancy_no_isolation(): void {
        $ten_gen = $this->getDataGenerator()->get_plugin_generator('totara_tenant');
        $ten_gen->enable_tenants();
        set_config('tenantsisolated', 0);

        // The course belongs to the tenant.
        $first_tenancy = new tenant($ten_gen->create_tenant());
        $tenant_course = $this->getDataGenerator()->create_course(['category' => $first_tenancy->categoryid]);

        // The workflow belongs to the system.
        $system_workflow = $this->create_workflow_for_user(
            'enrol',
            [installer::class, 'configure_publishable_workflow'],
            'enrol',
            null
        );

        list($instance, $plugin, $context, $type, $return) = $this->enrol_instance_edit_form_params($tenant_course);
        $data = $this->get_validation_test_data();
        $data['workflow_id'] = $system_workflow->id;
        $validation_hook = new enrol_instance_extra_settings_validation($data, [], $instance, $plugin, $context);

        $watcher = new enrol_instance_extra_settings();
        $watcher->validation($validation_hook);
        $errors = $validation_hook->get_errors();
        $this->assertEmpty($errors);

        // The workflow belongs to the same tenant.
        $tenant1_workflow = $this->create_workflow_for_user(
            'enrol',
            [installer::class, 'configure_publishable_workflow'],
            'enrol',
            $first_tenancy->categoryid
        );

        list($instance, $plugin, $context, $type, $return) = $this->enrol_instance_edit_form_params($tenant_course);
        $data = $this->get_validation_test_data();
        $data['workflow_id'] = $tenant1_workflow->id;
        $validation_hook = new enrol_instance_extra_settings_validation($data, [], $instance, $plugin, $context);

        $watcher = new enrol_instance_extra_settings();
        $watcher->validation($validation_hook);
        $errors = $validation_hook->get_errors();
        $this->assertEmpty($errors);


        // The course or workflow belongs to a different tenant that is not the system
        $second_tenancy = new tenant($ten_gen->create_tenant());
        $tenant2_workflow = $this->create_workflow_for_user(
            'enrol',
            [installer::class, 'configure_publishable_workflow'],
            'enrol',
            $second_tenancy->categoryid
        );
        $data['workflow_id'] = $tenant2_workflow->id;
        // Still using the tenant1 course context here.
        /** @var context_course $context */
        $this->assertEquals($tenant_course->id, $context->instanceid);
        $validation_hook = new enrol_instance_extra_settings_validation($data, [], $instance, $plugin, $context);

        $watcher = new enrol_instance_extra_settings();
        $watcher->validation($validation_hook);
        $errors = $validation_hook->get_errors();
        $bad_category_id_message = get_string('error:workflow_not_available_message', 'approval');
        $this->assertContains($bad_category_id_message, $errors);
    }

    /**
     * When tenant isolation is on, we need to ensure that the course and
     * workflow belong to the same tenant, or both to the system.
     */
    public function test_validate_with_workflow_multitenancy_with_isolation(): void {
        $ten_gen = $this->getDataGenerator()->get_plugin_generator('totara_tenant');
        $ten_gen->enable_tenants();
        set_config('tenantsisolated', 1);

        // The course belongs to the tenant.
        $first_tenancy = new tenant($ten_gen->create_tenant());
        $tenant_course = $this->getDataGenerator()->create_course(['category' => $first_tenancy->categoryid]);

        // The workflow belongs to the system.
        $system_workflow = $this->create_workflow_for_user(
            'enrol',
            [installer::class, 'configure_publishable_workflow'],
            'enrol',
            null
        );

        list($instance, $plugin, $context, $type, $return) = $this->enrol_instance_edit_form_params($tenant_course);
        $data = $this->get_validation_test_data();
        $data['workflow_id'] = $system_workflow->id;
        $validation_hook = new enrol_instance_extra_settings_validation($data, [], $instance, $plugin, $context);

        $watcher = new enrol_instance_extra_settings();
        $watcher->validation($validation_hook);
        $errors = $validation_hook->get_errors();
        $this->assertContains(get_string('error:workflow_not_available_message', 'approval'), $errors);

        // The workflow belongs to the same tenant.
        $tenant1_workflow = $this->create_workflow_for_user(
            'enrol',
            [installer::class, 'configure_publishable_workflow'],
            'enrol',
            $first_tenancy->categoryid
        );

        list($instance, $plugin, $context, $type, $return) = $this->enrol_instance_edit_form_params($tenant_course);
        $data = $this->get_validation_test_data();
        $data['workflow_id'] = $tenant1_workflow->id;
        $validation_hook = new enrol_instance_extra_settings_validation($data, [], $instance, $plugin, $context);

        $watcher = new enrol_instance_extra_settings();
        $watcher->validation($validation_hook);
        $errors = $validation_hook->get_errors();
        $this->assertEmpty($errors);


        // The course or workflow belongs to a different tenant that is not the system
        $second_tenancy = new tenant($ten_gen->create_tenant());
        $tenant2_workflow = $this->create_workflow_for_user(
            'enrol',
            [installer::class, 'configure_publishable_workflow'],
            'enrol',
            $second_tenancy->categoryid
        );

        $data['workflow_id'] = $tenant2_workflow->id;
        // Still using the tenant1 course context here.
        /** @var context_course $context */
        $this->assertEquals($tenant_course->id, $context->instanceid);
        $validation_hook = new enrol_instance_extra_settings_validation($data, [], $instance, $plugin, $context);

        $watcher = new enrol_instance_extra_settings();
        $watcher->validation($validation_hook);
        $errors = $validation_hook->get_errors();
        $this->assertContains(get_string('error:workflow_not_available_message', 'approval'), $errors);
    }

    /**
     * Ensure we can save and retrieve the extra settings from the DB
     */
    public function test_save_enrol_extra_settings(): void {
        $plugin = enrol_get_plugin('self'); // Choose 'self' as an arbitrary plugin type
        $instance = (object)$plugin->get_instance_defaults();
        $instance->courseid = $this->getDataGenerator()->create_course()->id;
        $instance->status   = ENROL_INSTANCE_ENABLED;
        $instance->enrol = 'self';
        $instance->id = builder::table('enrol')->insert((array)$instance);

        $result = builder::table('enrol')
            ->where('id', '=', $instance->id)
            ->get();
        $this->assertCount(1, $result);
        $this->assertNull($result->first()->workflow_id);

        // Now set it up with a workflow_id.
        $workflow = $this->create_workflow_for_user(
            'enrol',
            [installer::class, 'configure_publishable_workflow'],
            'enrol',
            null
        );
        $instance->workflow_id = $workflow->id;

        $save_hook = new enrol_instance_extra_settings_save(new \stdClass(), $instance);
        $watcher = new enrol_instance_extra_settings();
        $watcher->save($save_hook);

        $result = builder::table('enrol')
            ->where('workflow_id', '=', $instance->workflow_id)
            ->get();
        $this->assertCount(1, $result);
        $this->assertEquals($result->first()->workflow_id, $instance->workflow_id);
    }

    /**
     * Ensure that the list of workflows in the form definition is correct.
     */
    public function test_definition_enrol_extra_settings(): void {
        $course = $this->getDataGenerator()->create_course();
        $context = context_course::instance($course->id);

        $type = 'self'; // Choose 'self' as an arbitrary plugin type
        $plugin = enrol_get_plugin($type);
        $instance = (object)$plugin->get_instance_defaults();
        $instance->id       = null;
        $instance->enrol    = 'self';
        $instance->courseid = $course->id;
        $instance->status   = ENROL_INSTANCE_ENABLED;

        $workflow = $this->create_workflow_for_user(
            'enrol',
            [installer::class, 'configure_publishable_workflow'],
            'enrol',
            null
        );
        $instance->workflow_id = $workflow->id;

        // This is the URL to which the user would be redirected on success
        $return = new moodle_url('/enrol/instances.php', array('id' => $course->id));
        $form = new \enrol_instance_edit_form(null, array($instance, $plugin, $context, $type, $return));
        $definition_hook = new enrol_instance_extra_settings_definition($form, $instance, $plugin, $context);
        $watcher = new enrol_instance_extra_settings();
        $watcher->definition($definition_hook);

        $form_options = $form->_form->getElement('workflow_id')->_options;
        $this->assertCount(2, $form_options);

        // None should be the first option.
        $option_none = array_shift($form_options);
        $this->assertEquals("No", $option_none['text']);
        $this->assertEquals("No", $option_none['attr']['title']);
        $this->assertEquals(0, $option_none['attr']['value']);

        $option_workflow = array_shift($form_options);
        $this->assertEquals($workflow->name, $option_workflow['text']);
        $this->assertEquals($workflow->name, $option_workflow['attr']['title']);
        $this->assertEquals($workflow->id, $option_workflow['attr']['value']);

        // Archive the workflow.
        /** @var \mod_approval\model\workflow\workflow_version $workflow_version */
        $workflow_version = $workflow->versions->first();
        $workflow_version->archive();

        // Now there should only be a None option.
        $form = new \enrol_instance_edit_form(null, array($instance, $plugin, $context, $type, $return));
        $definition_hook = new enrol_instance_extra_settings_definition($form, $instance, $plugin, $context);
        $watcher = new enrol_instance_extra_settings();
        $watcher->definition($definition_hook);

        $form_options = $form->_form->getElement('workflow_id')->_options;
        $this->assertCount(1, $form_options);
        $option_none = array_shift($form_options);
        $this->assertEquals("No", $option_none['text']);
        $this->assertEquals("No", $option_none['attr']['title']);
        $this->assertEquals(0, $option_none['attr']['value']);
    }
}
