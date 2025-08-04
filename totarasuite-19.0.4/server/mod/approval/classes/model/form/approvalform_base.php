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

namespace mod_approval\model\form;

use mod_approval\controllers\assignment\overrides;
use mod_approval\exception\model_exception;
use mod_approval\form_schema\field_type\application_editor;
use mod_approval\form_schema\form_schema;
use mod_approval\form_schema\form_schema_field;
use mod_approval\interactor\application_interactor;
use mod_approval\model\application\application;
use mod_approval\model\status;
use mod_approval\model\workflow\stage_type\finished;
use mod_approval\model\workflow\stage_type\form_submission;
use mod_approval\model\workflow\workflow_stage;
use mod_approval\model\workflow\workflow_version;
use mod_approval\plugininfo\approvalform;
use moodle_url;
use totara_core\path;

/**
 * Base class for approvalform sub-plugins
 *
 * Extend as \approvalform_<plugin_name>\approvalform_<plugin_name>.
 */
class approvalform_base {
    /**
     * @var approvalform
     */
    private $plugininfo;

    /**
     * @var form_schema
     */
    private $form_schema;

    public const ACTION_IMPORT_OVERRIDE = 'import_override';
    public const ACTION_IMPORT_APPROVERS = 'import_approvers';

    /**
     * Private constructor.
     *
     * @param approvalform $plugininfo
     */
    private function __construct(approvalform $plugininfo) {
        $this->plugininfo = $plugininfo;
        $this->form_schema = form_schema::from_json($this->get_form_schema_from_disk());
    }

    /**
     * Get the base import csv url for embeded report
     *
     * @param array $params
     * @param string $action
     * @return \moodle_url
     * @throws \moodle_exception
     */
    public function get_import_csv_url(array $params, string $action): moodle_url {
        return new moodle_url(overrides::get_base_url(), $params);
    }

    /**
     * Factory to instantiate approvalform plugin class.
     *
     * @param string $plugin_name
     * @return static
     */
    public static function from_plugin_name(string $plugin_name): self {
        $plugininfo = approvalform::from_plugin_name($plugin_name);
        if (is_null($plugininfo)) {
            throw new model_exception("'{$plugin_name}' form plugin not found");
        }
        $classname = '\\approvalform_' . $plugin_name . '\\' . $plugin_name;
        if (!class_exists($classname)) {
            throw new \coding_exception("'approvalform_{$plugin_name}' class not implemented", "'{$classname}' does not exist");
        }

        return new $classname($plugininfo);
    }

    /**
     * Getter for plugininfo properties and accessors.
     *
     * @param string $property
     * @return mixed|string|null
     */
    public function __get($property) {
        if (isset($this->plugininfo->$property)) {
            return $this->plugininfo->$property;
        } else if (is_null($property)) {
            return null;
        } else if (method_exists($this, 'get_' . $property)) {
            return $this->{'get_' . $property}();
        }
        throw new \coding_exception('approvalform property does not exist');
    }

    /**
     * Get the raw plugin form schema JSON.
     *
     * @return string
     */
    private function get_form_schema_from_disk(): string {
        // Load JSON schema from disk.
        $schema_path = new path($this->rootdir,'form.json');
        if (!$schema_path->exists() || !$schema_path->is_file() || !$schema_path->is_readable()) {
            throw new \coding_exception('unable to load approvalform JSON schema file');
        }
        return file_get_contents($schema_path->to_native_string());
    }

    /**
     * Get the plugin form_schema as a class
     *
     * @return form_schema
     */
    public function get_form_schema(): form_schema {
        return $this->form_schema;
    }

    /**
     * Get the plugin form_schema as json
     *
     * @return string JSON
     */
    public function get_form_schema_json(): string {
        return $this->form_schema->to_json();
    }

    /**
     * Get the form schema version.
     *
     * @return null|string
     */
    public function get_form_version(): ?string {
        return $this->form_schema->get_version();
    }

    /**
     * Reset form data when cloning.
     *
     * @param application $application
     * @param form_data $form_data
     * @return form_data $form_data
     */
    public function reset_form_data_when_cloning(application $application, form_data $form_data): form_data {
        return $form_data;
    }

    /**
     * Is this plugin enabled?
     *
     * @return bool|null
     */
    public function is_enabled(): ?bool {
        return $this->plugininfo->is_enabled();
    }

    /**
     * Get the status to use for the initial form_version when creating an instance of this form.
     *
     * ACTIVE assumes that the form schema is being loaded from a static file; override this
     * method to use DRAFT if the plugin's form schema is dynamic or not ready to use when the
     * form model is created.
     *
     * @return int
     */
    public function default_version_status(): int {
        return status::ACTIVE;
    }

    /**
     * Allows the approvalform plugin to modify or recreate a form_schema based on an application instance.
     *
     * @param application_interactor $application_interactor
     * @param form_schema $form_schema
     *
     * @return form_schema
     */
    public function adjust_form_schema_for_application(application_interactor $application_interactor, form_schema $form_schema): form_schema {
        $this->add_meta_to_editor_fields($application_interactor, $form_schema);

        return $form_schema;
    }

    /**
     * Add required meta properties to fields with type: editor
     *
     * @param application_interactor $application_interactor
     * @param form_schema $form_schema
     */
    private function add_meta_to_editor_fields(application_interactor $application_interactor, form_schema $form_schema): void {
        $editor_fields = $form_schema->get_fields_of_type(application_editor::FIELD_TYPE);

        if (empty($editor_fields)) {
            return;
        }
        $application_editor = new application_editor($application_interactor);

        /** @var form_schema_field $editor_field */
        foreach ($editor_fields as $editor_field) {
            $form_schema->set_field_meta($editor_field->get_field_key(), $application_editor->get_editor_meta());
        }
    }

    /**
     * Allows the approvalform plugin to observe and perform specific actions based on an application instance.
     *
     * Note that the data may be incoming (from a mutation/post) or outgoing (to a query/form)
     *
     * @param application $application
     * @param form_data $form_data
     * @return void
     */
    public function observe_form_data_for_application(application $application, form_data $form_data): void {
    }

    /**
     * Allows the approvalform plugin to adjust raw form data before it is used for editing.
     *
     * This is called from \mod_approval\model\form\form_data::prepare_fields_for_edit()
     *
     * @param array $raw_form_data
     * @param application_interactor $application_interactor
     * @return array
     */
    public function prepare_raw_form_data_for_edit(array $raw_form_data, application_interactor $application_interactor): array {
        return $raw_form_data;
    }

    /**
     * Allows the approvalform plugin to adjust raw form data before it is viewed.
     *
     * This is called from \mod_approval\model\form\form_data::prepare_fields_for_view()
     *
     * @param array $raw_form_data
     * @param application_interactor $application_interactor
     * @return array
     */
    public function prepare_raw_form_data_for_view(array $raw_form_data, application_interactor $application_interactor): array {
        return $raw_form_data;
    }

    /**
     * Allows an approvalform to declare that it enables/provides approval support for a Totara subsystem or component.
     *
     * @param string $component_name
     * @return bool
     */
    public static function enables_component(string $component_name): bool {
        return false;
    }

    /**
     * Indicates if an approval form plugin provides its own collection of notification triggers, or whether
     * it should inherit the base mod_approval notification triggers.
     *
     * If true then the base mod_approval notification triggers will not be available when configuring
     * notifications within the workflow stages.
     *
     * If false (default) then all base mod_approval notification triggers will be available when configuring
     * workflow stages, and the corresponding notification preferences will be inherited within that workflow.
     *
     * If enabled in a form plugin, the developer must make sure that supports_context in all notification
     * triggers is designed to check that the given workflow context is using the form plugin.
     *
     * @return bool
     */
    public static function replaces_base_notifications(): bool {
        return false;
    }

    /**
     * Whether applications based on this approvalform are allowed to be created via the application dashboard.
     * Override with false in extending form classes to prevent manual creation of applications.
     *
     * @return bool
     */
    public static function supports_application_creation(): bool {
        return true;
    }

    /**
     * Configure the workflow version with default stages and any other configuration.
     *
     * Forms can override this function to create a different set of stages. Note that an approval workflow
     * is always required to have at least one form_submission stage and at least one finished stage.
     *
     * @param workflow_version $workflow_version
     * @return void
     */
    public static function configure_default_workflow(workflow_version $workflow_version): void {
        // Create the start stage.
        workflow_stage::create(
            $workflow_version,
            get_string('default_workflow_start_stage_name', 'mod_approval'),
            form_submission::get_enum()
        );

        // Create the end stage.
        workflow_stage::create(
            $workflow_version,
            get_string('default_workflow_finished_stage_name', 'mod_approval'),
            finished::get_enum()
        );
    }

    /**
     * This function determines whether the new workflow needs an
     * assignment selection based on its type. If not, we can skip the
     * next screen.
     *
     * Forms can override this function to change whether they need an
     * assignment selection.
     *
     * @return bool
     */
    public static function needs_assignment_selection(): bool {
        return true;
    }
}