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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package mod_approval
 */

namespace mod_approval\form;

use core\entity\course;
use core\format;
use core\orm\entity\traits\json_trait;
use core\orm\query\builder;
use core\webapi\formatter\field\string_field_formatter;
use core_enrol\model\user_enrolment_application;
use mod_approval\entity\workflow\workflow_stage_interaction;
use mod_approval\exception\model_exception;
use mod_approval\form_schema\form_schema;
use mod_approval\interactor\application_interactor;
use mod_approval\model\application\action\approve;
use mod_approval\model\application\application;
use mod_approval\model\form\approvalform_base;
use mod_approval\model\form\form_data;
use mod_approval\model\workflow\workflow_stage;
use mod_approval\model\workflow\workflow_version;

/**
 * Base class for approvalform plugins which provide course enrolment approval.
 */
class approvalform_core_enrol_base extends approvalform_base {

    use json_trait;

    /**
     * Name of the end stage for approved applications.
     */
    public const APPROVED_END_STAGE = 'Approved';

    /**
     * Name of the end stage for archived (rejected/withdrawn/canceled) applications.
     */
    public const ARCHIVED_END_STAGE = 'Archived';

    /**
     * Allows an approvalform to declare that it enables/provides approval support for a Totara subsystem or component.
     *
     * @param string $component_name
     * @return bool
     */
    public static function enables_component(string $component_name): bool {
        if ($component_name == 'core_enrol') {
            return true;
        }
        return false;
    }

    /**
     * Sets the title of the application to the fullname of the requested course.
     *
     * @param application $application
     * @param form_data $form_data
     * @return void
     */
    public function observe_form_data_for_application(application $application, form_data $form_data): void {
        $user_enrolment_application = user_enrolment_application::find_with_application_id($application->id);

        $title = get_string('form_title', 'approvalform_enrol', $user_enrolment_application->course->fullname);

        // Set application title to course title
        if ($application->title != $title) {
            $application->set_title($title);
        }
    }

    /**
     * Set default form values based on the requested course.
     *
     * @param application_interactor $application_interactor
     * @param form_schema $form_schema
     * @return form_schema
     */
    public function adjust_form_schema_for_application(application_interactor $application_interactor, form_schema $form_schema): form_schema {
        // Do the global adjustments.
        $form_schema = parent::adjust_form_schema_for_application($application_interactor, $form_schema);

        // Load the user_enrolment_application record so we have access to the course.
        $user_enrolment_application = user_enrolment_application::find_with_application_id($application_interactor->get_application()->id);

        if (!$user_enrolment_application) {
            return $form_schema;
        }

        // Fill in course_id field.
        if ($form_schema->has_field('course_id')) {
            $form_schema->set_field_default('course_id', $user_enrolment_application->course->id);
        }

        // Fill in course_link field default.
        if ($form_schema->has_field('course_link') && !empty($user_enrolment_application->course)) {
            $form_schema->set_field_default('course_link', static::encode_course_link_data($user_enrolment_application->course));
        }

        return $form_schema;
    }

    /**
     * Overwrite course-related form values so that they are always fresh for the user.
     *
     * @param array $raw_form_data
     * @param application_interactor $application_interactor
     * @return array
     */
    public function prepare_raw_form_data_for_edit(array $raw_form_data, application_interactor $application_interactor): array {
        $raw_form_data = parent::prepare_raw_form_data_for_edit($raw_form_data, $application_interactor);

        // Load the user_enrolment_application record so we have access to the course.
        $user_enrolment_application = user_enrolment_application::find_with_application_id($application_interactor->get_application()->id);

        if (!$user_enrolment_application) {
            return static::get_raw_form_data_if_course_missing($raw_form_data);
        }

        $raw_form_data['course_id'] = $user_enrolment_application->course->id;
        $raw_form_data['course_link'] = static::encode_course_link_data($user_enrolment_application->course);

        return $raw_form_data;
    }

    /**
     * Overwrite course-related form values so that they are always fresh for the user.
     *
     * @param array $raw_form_data
     * @param application_interactor $application_interactor
     * @return array
     */
    public function prepare_raw_form_data_for_view(array $raw_form_data, application_interactor $application_interactor): array {
        $raw_form_data = parent::prepare_raw_form_data_for_edit($raw_form_data, $application_interactor);

        // Load the user_enrolment_application record so we have access to the course.
        $user_enrolment_application = user_enrolment_application::find_with_application_id($application_interactor->get_application()->id);

        $raw_form_data['course_id'] = $user_enrolment_application->course->id;
        $raw_form_data['course_link'] = static::encode_course_link_data($user_enrolment_application->course);

        return $raw_form_data;
    }

    /**
     * Given a course entity, generates JSON-encoded course_link data.
     *
     * @param course $course
     * @return string
     * @throws \JsonException
     * @throws \moodle_exception
     */
    public static function encode_course_link_data(course $course): string {
        $course_url = new \moodle_url('/course/view.php', ['id' => $course->id]);
        $course_link = new \stdClass();
        $course_link->id = $course->id;

        $string_field_formatter = new string_field_formatter(format::FORMAT_PLAIN, \context_course::instance($course->id));
        $course_link->name = $string_field_formatter->format($course->fullname);
        $course_link->url = $course_url->out();
        return static::json_encode($course_link);
    }

    /**
     * Get the approved stage for this enrolment workflow.
     *
     * All active enrol workflows must have an approved stage. This function should not be used
     * before the enrol workflow has been published (which is when approved stage is validated).
     *
     * @param workflow_version $workflow_version
     * @return workflow_stage
     */
    public static function get_approved_stage(workflow_version $workflow_version): workflow_stage {
        foreach ($workflow_version->get_stages() as $stage) {
            if ($stage->name == static::APPROVED_END_STAGE) {
                return $stage;
            }
        }
        throw new \coding_exception('No approved stage found');
    }

    /**
     * Get the archived stage for this enrolment workflow.
     *
     * All active enrol workflows must have an archived stage. This function should not be used
     * before the enrol workflow has been published (which is when archived stage is validated).
     *
     * @param workflow_version $workflow_version
     * @return workflow_stage
     */
    public static function get_archived_stage(workflow_version $workflow_version): workflow_stage {
        foreach ($workflow_version->get_stages() as $stage) {
            if ($stage->name == static::ARCHIVED_END_STAGE) {
                return $stage;
            }
        }
        throw new \coding_exception('No archived stage found');
    }

    /**
     * @param array $raw_form_data
     * @return array
     */
    protected static function get_raw_form_data_if_course_missing(array $raw_form_data): array {
        $course_link = json_decode($raw_form_data['course_link']);
        $course = course::repository()->where('id', $course_link->id)->get()->first();
        if (!$course) {
            $course_link->course_deleted = true;
            $raw_form_data['course_link'] = json_encode($course_link);
        } else {
            $raw_form_data['course_link'] = static::encode_course_link_data($course);
        }

        return $raw_form_data;
    }

    /**
     * Prevent creation of applications via the application dashboard, enrolment forms should
     * have their applications created via the plugin to ensure the pending enrolment is created
     * and linked to the application.
     *
     * @return bool
     */
    public static function supports_application_creation(): bool {
        return false;
    }

    /**
     * Inspects the structure of a workflow_version to ensure that it is fit for use as an enrolment approval workflow.
     *
     * @param workflow_version $workflow_version
     * @return array of warnings, if any
     */
    final public static function verify_workflow_structure(workflow_version $workflow_version): array {
        // Ensure this is an approvalform_enrol workflow.
        $plugin = approvalform_base::from_plugin_name($workflow_version->workflow->form->plugin_name);
        if (!is_a($plugin, approvalform_core_enrol_base::class)) {
            throw new model_exception('Tried to verify an approvalform_core_enrol workflow, but the approvalform plugin does not extend approvalform_core_enrol_base.');
        }

        // Capture any issues to return.
        $warnings = [];

        // Ensure that there are end stages named Approved and Archived.
        try {
            $approved_stage = static::get_approved_stage($workflow_version);
        } catch (\coding_exception $e) {
            $approved_stage = false;
        }
        try {
            $archived_stage = static::get_archived_stage($workflow_version);
        } catch (\coding_exception $e) {
            $archived_stage = false;
        }

        // Approval stage warnings.
        if (!$approved_stage) {
            $warnings[] = get_string('workflow_verification_warning:no_approved_stage', 'approvalform_enrol');
        } else {
            // Check that it has at least one approval interaction that transitions to it.
            $explicit_link = builder::table(\mod_approval\entity\workflow\workflow_stage_interaction_transition::TABLE, 'transition')
                ->join([workflow_stage_interaction::TABLE, 'interaction'], 'transition.workflow_stage_interaction_id', '=', 'interaction.id')
                ->join([\mod_approval\entity\workflow\workflow_stage::TABLE, 'stage'], 'interaction.workflow_stage_id', '=', 'stage.id')
                ->where('stage.workflow_version_id', '=', $workflow_version->id)
                ->where('interaction.action_code', '=', approve::get_code())
                ->where('transition.transition', '=', $approved_stage->id)
                ->exists();
            if (!$explicit_link) {
                $next_link = $previous_link = false;

                // If there is a stage before the approved end stage, check for a NEXT approve transition on it.
                if ($approved_stage->ordinal_number > 1) {
                    $next_link = builder::table(\mod_approval\entity\workflow\workflow_stage_interaction_transition::TABLE, 'transition')
                        ->join([workflow_stage_interaction::TABLE, 'interaction'], 'transition.workflow_stage_interaction_id', '=', 'interaction.id')
                        ->join([\mod_approval\entity\workflow\workflow_stage::TABLE, 'stage'], 'interaction.workflow_stage_id', '=', 'stage.id')
                        ->where('stage.workflow_version_id', '=', $workflow_version->id)
                        ->where('stage.sortorder', '=', $approved_stage->ordinal_number - 1)
                        ->where('interaction.action_code', '=', approve::get_code())
                        ->where('transition.transition', '=', 'NEXT')
                        ->exists();
                }

                // If there is a stage after the approved end stage, check for a PREVIOUS approve transition on it.
                if ($approved_stage->ordinal_number < $workflow_version->stages->count()) {
                    $previous_link = builder::table(\mod_approval\entity\workflow\workflow_stage_interaction_transition::TABLE, 'transition')
                        ->join([workflow_stage_interaction::TABLE, 'interaction'], 'transition.workflow_stage_interaction_id', '=', 'interaction.id')
                        ->join([\mod_approval\entity\workflow\workflow_stage::TABLE, 'stage'], 'interaction.workflow_stage_id', '=', 'stage.id')
                        ->where('stage.workflow_version_id', '=', $workflow_version->id)
                        ->where('stage.sortorder', '=', $approved_stage->ordinal_number + 1)
                        ->where('interaction.action_code', '=', approve::get_code())
                        ->where('transition.transition', '=', 'PREVIOUS')
                        ->exists();
                }

                if (!($next_link || $previous_link)) {
                    $warnings[] = get_string('workflow_verification_warning:no_approval_transition', 'approvalform_enrol');
                }
            }
        }

        // Archived stage warnings.
        if (!$archived_stage) {
            $warnings[] = get_string('workflow_verification_warning:no_archived_stage', 'approvalform_enrol');
        }

        return $warnings;
    }

    /**
     * Configure an enrolment approval workflow_version so that passes appprovalform_core_enrol_base::verify_workflow_structure()
     */
    public static function configure_default_workflow(workflow_version $workflow_version): void {
        \approvalform_enrol\installer::configure_publishable_workflow($workflow_version);
    }

    /**
     * @inheritDoc
     */
    public static function needs_assignment_selection(): bool {
        return false;
    }
}