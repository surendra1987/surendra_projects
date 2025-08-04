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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package mod_approval
 */

namespace mod_approval\watcher;

use core\orm\query\builder;
use core_enrol\hook\enrol_instance_extra_settings_base;
use core_enrol\hook\enrol_instance_extra_settings_definition;
use core_enrol\hook\enrol_instance_extra_settings_save;
use core_enrol\hook\enrol_instance_extra_settings_validation;
use core_enrol\model\user_enrolment_application;
use core_enrol\testing\enrol_instance_edit_form_test_setup;
use core_plugin_manager;
use mod_approval\form\approvalform_core_enrol_base;
use mod_approval\model\form\approvalform_base;
use mod_approval\model\status;
use mod_approval\model\workflow\stage_type\finished;
use mod_approval\model\workflow\workflow;
use totara_core\advanced_feature;
use totara_tenant\local\util as tenant_util;

/**
 * Approval workflows watches for enrolment settings forms, so that course creators can select enrolment approval workflows
 * to use for course enrolment approval.
 */
class enrol_instance_extra_settings {

    use enrol_instance_edit_form_test_setup;

    private const FORM_KEY = 'workflow_id';

    protected static function get_active_supported_workflows_builder(): builder {
        // Get all approval forms that support core_enrol
        $plugin_manager = core_plugin_manager::instance();
        $plugins = $plugin_manager->get_present_plugins('approvalform');
        $core_enrol_supported = [];
        foreach ($plugins as $plugin_name => $location) {
            $approval_form = approvalform_base::from_plugin_name($plugin_name);
            if ($approval_form->enables_component('core_enrol')) {
                $core_enrol_supported[] = $plugin_name;
            }
        }

        return builder::table('approval_workflow', 'workflow')
            ->join(['approval_workflow_version', 'workflow_version'], 'workflow_version.workflow_id', '=', 'workflow.id')
            ->where('workflow.active', '=', '1')
            ->where('workflow_version.status', '=', status::ACTIVE)
            ->join('approval_form', 'approval_form.id', '=', 'workflow.form_id')
            ->where_in('approval_form.plugin_name', $core_enrol_supported);
    }

    protected static function hook_is_actionable(enrol_instance_extra_settings_base $hook): bool {

        // Check the feature is enabled.
        if (!advanced_feature::is_enabled('approval_workflows')) {
            return false;
        }

        // Check the plugin supports approval workflows.
        $plugin = $hook->get_plugin();
        if (!$plugin->supports_approval_workflow()) {
            return false;
        }

        return true;
    }

    /**
     * If there is a workflow_id in the form passed to the hook, validate it as selectable. If not,
     * add an error.
     *
     * @param enrol_instance_extra_settings_validation $hook
     */
    public static function validation(enrol_instance_extra_settings_validation $hook): void {
        // General test.
        if (!static::hook_is_actionable($hook)) {
            return;
        }

        // Get the data to validate.
        $data = $hook->get_data();
        $workflow_id = $data[self::FORM_KEY] ?? null;

        // If no workflow, no validation needed.
        if (empty($workflow_id)) {
            return;
        }

        // Load the workflow.
        try {
            $workflow = workflow::load_by_id($workflow_id);
        } catch (\Throwable $e) {
            $hook->set_error(self::FORM_KEY, get_string('error:missing_workflow_message', 'mod_approval'));
        }

        // Check that it supports core_enrol.
        if (!$workflow->plugin->enables_component('core_enrol')) {
            $hook->set_error(self::FORM_KEY, get_string('error:workflow_not_core_enrol', 'mod_approval'));
        }

        // Check if workflow is active.
        if (!$workflow->is_any_active()) {
            $hook->set_error(self::FORM_KEY, get_string('error:workflow_not_available_message', 'mod_approval'));
        }

        // Validate multitenancy?
        $hook_context = $hook->get_context();
        $workflow_context = $workflow->get_context();
        if (!tenant_util::context_is_safe_to_associate($workflow_context, $hook_context)) {
            $hook->set_error(self::FORM_KEY, get_string('error:workflow_not_available_message', 'mod_approval'));
        }

        // All checks passed!
    }

    /**
     * This watcher is called before the record is saved - we make sure that
     * any extra fields we added to the enrolment instance are saved too.
     *
     * @param enrol_instance_extra_settings_save $hook
     */
    public static function save(enrol_instance_extra_settings_save $hook): void {
        // General test.
        if (!static::hook_is_actionable($hook)) {
            return;
        }

        $instance = $hook->get_enrolment_instance();

        $workflow_id = $instance->workflow_id ?? null;
        if ($workflow_id == 0) {
            $workflow_id = null;
        }

        // Get the existing enrol instance before updating workflow id.
        $old_enrol = builder::table('enrol')
            ->where('id', '=', $instance->id)
            ->one();

        builder::table('enrol')
            ->where('id', '=', $instance->id)
            ->update([self::FORM_KEY => $workflow_id]);

        // If the existing workflow id is updated, we put the application is archived and delete the record from table 'user_enrolments_application'.
        if (($old_enrol && isset($old_enrol->workflow_id) && $old_enrol->workflow_id > 0) &&
            $old_enrol->workflow_id != $instance->workflow_id
        ) {
            $user_enrolments_applications = builder::table('user_enrolments_application', 'uea')
                ->join(['user_enrolments', 'ue'], 'ue.id', 'uea.user_enrolments_id')
                ->where('ue.enrolid', $old_enrol->id)
                ->get()
                ->map_to(function ($record) {
                    return user_enrolment_application::load_by_id($record->id);
                });

            builder::get_db()->transaction(function () use ($user_enrolments_applications, $instance) {
                foreach ($user_enrolments_applications as $user_enrolments_application) {
                    if (isset($user_enrolments_application->approval_application)) {
                        $application = $user_enrolments_application->approval_application;
                        if ($application->get_overall_progress() === 'DRAFT') {
                            // user_enrolments_application will be deleted when application is deleted.
                            $application->delete();
                            continue;
                        }

                        if ($application->current_state->is_stage_type(finished::get_code()) || $application->completed) {
                            $user_enrolments_application->delete();
                            continue;
                        }

                        $archived_stage = approvalform_core_enrol_base::get_archived_stage($application->workflow_version);
                        $application->set_current_state($archived_stage->state_manager->get_initial_state());
                    }
                    $user_enrolments_application->delete();
                }

                // Current instance set to null or 0, we mark pending user enrolment to active.
                if (!$instance->workflow_id) {
                    $sql = 'UPDATE {user_enrolments} SET status = 0 WHERE enrolid = :enrolid AND status = '. ENROL_USER_PENDING_APPLICATION;
                    builder::get_db()->execute($sql, ['enrolid' => $instance->id]);
                }
            });
        }
    }

    /**
     * Inject the workflow field into the enrolments form.
     *
     * @param enrol_instance_extra_settings_definition $hook
     */
    public static function definition(enrol_instance_extra_settings_definition $hook): void {
        // General test.
        if (!static::hook_is_actionable($hook)) {
            return;
        }

        // Fetch all the active workflows.
        $active_workflows = static::get_active_supported_workflows_builder()->get();

        // Set up an options array.
        $options = [];
        $options[0] = get_string('no');

        // Loop through workflows for last checks and add the options.
        $course_ctx = \context_course::instance($hook->get_enrolment_instance()->courseid);
        foreach ($active_workflows as $workflow_entity) {
            $workflow = workflow::load_by_id($workflow_entity->id);
            if (!tenant_util::context_is_safe_to_associate($workflow->get_context(), $course_ctx)) {
                continue;
            }
            $options[$workflow->id] = format_string($workflow->name);
        }

        $hook->enrolment_form->_form->addElement(
            'select',
            self::FORM_KEY,
            get_string('approve_enrolments', 'approval'),
            $options
        );
        $hook->enrolment_form->_form->addHelpButton(self::FORM_KEY, 'approve_enrolments', 'approval');
    }
}
