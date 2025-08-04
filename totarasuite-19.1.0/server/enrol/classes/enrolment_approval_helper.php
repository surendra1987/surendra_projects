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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package core_enrol
 */

namespace core_enrol;

use coding_exception;
use core\entity\user;
use core\model\enrol;
use core\orm\query\builder;
use core_enrol\model\user_enrolment_application;
use html_writer;
use mod_approval\controllers\application\edit;
use mod_approval\controllers\application\view;
use mod_approval\form\approvalform_core_enrol_base;
use mod_approval\interactor\application_interactor;
use mod_approval\model\application\application;
use mod_approval\model\assignment\approver_type\relationship;
use mod_approval\model\workflow\stage_type\finished;
use mod_approval\model\workflow\stage_type\form_submission;
use mod_approval\model\workflow\stage_type\provider;
use mod_approval\model\workflow\workflow;
use totara_core\advanced_feature;
use totara_job\entity\job_assignment;

/**
 * General helper to provide functions for enrol plugin.
 */
class enrolment_approval_helper {
    /**
     * Static use only.
     */
    private function __construct() {
    }

    /**
     * Find out if approval is available for a user on an enrolment instance.
     *
     * @param int $enrol_id
     * @param int $user_id
     * @return bool
     */
    public static function approval_available_for(int $enrol_id, int $user_id): bool {
        global $CFG;

        // Not available if it's turned off.
        if (!advanced_feature::is_enabled('approval_workflows')) {
            return false;
        }

        $enrol = enrol::load_by_id($enrol_id);

        // Not needed by this enrolment instance.
        if (!$enrol->workflow_id) {
            return false;
        }

        // Not available if the workflow is not active, unless there is already an application in progress.
        $workflow = workflow::load_by_id($enrol->workflow_id);
        if (!$workflow->active_version) {
            $user_enrolment_application = user_enrolment_application::find_with_instance_and_user_id($enrol->id, $user_id);
            // Couldn't find enrolment_application record.
            if (!$user_enrolment_application) {
                return false;
            }
            // Couldn't load application in record.
            if (!$user_enrolment_application->approval_application) {
                return false;
            }
            // Application exists and is not completed -- return true.
            if (is_null($user_enrolment_application->approval_application->completed)) {
                return true;
            }
            // Yeah nah, archived and no active application.
            return false;
        }

        // Check user vs available workflow assignments.
        // TODO TL-40667

        return true;
    }

    /**
     * What label should be used for the request approval button?
     *
     * @param application $application
     * @param int|null $user_id
     * @return string
     */
    public static function get_approval_button_name(application $application, int $user_id = null): string {
        if (!$user_id) {
            $user_id = user::logged_in()->id;
        }

        if ($application->get_overall_progress() === 'DRAFT') {
            return get_string('application:complete_request', 'core_enrol');
        }

        if (static::needs_create_new_application($application)) {
            return get_string('application:create_application', 'core_enrol');
        }

        $application_interactor = application_interactor::from_application_id($application->id, $user_id);
        if ($application_interactor->can_edit()) {
            $string_name = 'complete_section';
            if ($application->get_overall_progress() === 'REJECTED') {
                $string_name = 'resubmit_application';
            }
            $stage = $application->get_current_stage();
            return get_string($string_name, 'mod_approval', $stage->name);
        }

        return get_string('application:view_application', 'core_enrol');
    }

    /**
     * Is the user waiting for an application to be approved in order to access the course?
     *
     * This function returns false if there is a pending application but the user already
     * has access to the course.
     *
     * @param int $course_id
     * @param int|null $user_id
     * @return bool
     */
    public static function user_enrolment_pending(int $course_id, int $user_id = null): bool {
        if (!$user_id) {
            $user_id = user::logged_in()->id;
        }

        $user_enrolment_pending_records_exist = builder::table('user_enrolments', 'ue')
            ->join(['enrol', 'e'], 'e.id', 'ue.enrolid')
            ->select_raw('ue.*, e.status AS instance_status')
            ->where('e.courseid', $course_id)
            ->where('userid', $user_id)
            ->where('ue.status', ENROL_USER_PENDING_APPLICATION)
            ->where_not_null('e.workflow_id')
            ->where('e.workflow_id', '>', 0)
            ->exists();

        if (!$user_enrolment_pending_records_exist) {
            // Obviously there are no pending enrolments.
            return false;
        }

        // There are pending enrolments, but the user isn't considered to be pending an enrolment if
        // there is some other enrolment which is not pending (and which is non suspended).
        return builder::table('user_enrolments', 'ue')
            ->join(['enrol', 'e'], 'e.id', 'ue.enrolid')
            ->where('e.courseid', $course_id)
            ->where('ue.userid', $user_id)
            ->where('ue.status', '<>', ENROL_USER_PENDING_APPLICATION)
            ->where('ue.status', '<>', ENROL_USER_SUSPENDED)
            ->where('e.status', ENROL_INSTANCE_ENABLED)
            ->does_not_exist();
    }

    /**
     * Create a workflow application for an enrolment instance.
     *
     * Does NOT create a matching user_enrolment_application record.
     *
     * @param enrol $instance
     * @return application
     */
    public static function create_application(enrol $instance, user $user, job_assignment $job_assignment = null): application {
        if (!static::approval_available_for($instance->id, $user->id)) {
            throw new coding_exception('User cannot create application, approval is not available.');
        }

        if (static::workflow_requires_manager($instance->workflow_id) && empty($job_assignment)) {
            $job_assignments = \totara_job\job_assignment::get_all($user->id, true);
            if (count($job_assignments) != 1) {
                throw new coding_exception('Job assignment must be specified');
            }
            $job_assignment = new job_assignment(reset($job_assignments)->id);
        }

        // Load workflow.
        $workflow = workflow::load_by_id($instance->workflow_id);

        // TODO TL-40664: Resolve correct assignment.
        $assignment = $workflow->default_assignment;

        // Create and return the application.
        return application::create(
            $workflow->active_version,
            $assignment,
            $user->id,
            $user->id,
            $job_assignment,
        );
    }

    /**
     * Detects whether the workflow application is missing, or if it is unusable because it is at an end stage and not approved.
     *
     * @param null|application $application
     * @return bool
     */
    public static function needs_create_new_application(?application $application): bool {
        if (is_null($application)) {
            return true;
        }
        if (!is_null($application->completed) && $application->current_stage->name != approvalform_core_enrol_base::APPROVED_END_STAGE) {
            return true;
        }
        return false;
    }

    /**
     * Does a workflow have any manager approvals?
     *
     * @param int $workflow_id
     * @return bool
     */
    public static function workflow_requires_manager(int $workflow_id): bool {
        // TODO TL-40664: Add assignment ID param so we can restrict the check to a particular assigment override.
        return builder::table('approval_workflow', 'workflow')
            ->select_raw('approver.id')
            ->join(['course', 'c'], 'c.id', 'workflow.course_id')
            ->join(['approval', 'a'], 'a.course', 'c.id')
            ->join(['approval_approver', 'approver'], 'approver.approval_id', 'a.id')
            ->where('workflow.id', $workflow_id)
            ->where('approver.type', relationship::get_code())
            ->where('approver.active', '=', 1)
            ->exists();
    }

    /**
     * Adds a job assignment selector to a form if a job assignment is necessary to create a new application.
     *
     * @param int $workflow_id
     * @param int $user_id
     * @param $mform
     * @return bool  A boolean used further down in the form declaration to hide the submit button if there are no available job assignments.
     */
    public static function plugin_form_add_job_selector(int $workflow_id, int $user_id, &$mform): bool {
        $hide_button = false;
        if (static::workflow_requires_manager($workflow_id)) {
            $job_assignments = \totara_job\job_assignment::get_all($user_id, true);

            if (empty($job_assignments)) {
                $hide_button = true;
                $mform->addElement('html', html_writer::tag('p', get_string('requestapprovalrequiresjob', 'enrol_self')));
            } elseif (count($job_assignments) > 1) {
                $mform->addElement('html', html_writer::tag('p', get_string('requestapprovalrequirement', 'enrol_self')));

                $options = [];
                foreach ($job_assignments as $job_assignment) {
                    $options[$job_assignment->id] = $job_assignment->fullname;
                }

                $mform->addElement('select', 'job_assignment', get_string('select_job_assignment', 'core_enrol'), $options);
                $mform->addHelpButton('job_assignment', 'select_job_assignment', 'core_enrol');
            }
            // Else there is just one job assignment then show nothing.
        }

        return $hide_button;
    }

    /**
     * After a non-interactive enrolment that requires approval, attempt to find or create an associated application and
     * return the URL in a suitable result format.
     *
     * @param int $enrol_id
     * @param int $user_id
     * @return string
     */
    public static function get_application_url_after_non_interactive_enrolment(int $enrol_id, int $user_id): string {
        $user_enrolment_application = user_enrolment_application::find_with_instance_and_user_id($enrol_id, $user_id);
        if (!$user_enrolment_application) {
            // There is no user_enrolment_application record, it should have been created by enrol::enrol_user().
            return '';
        }

        // Application might not exist.
        $application = $user_enrolment_application->approval_application;

        // If current application does not exist, or is unusable, create new application.
        if (static::needs_create_new_application($application)) {
            try {
                $application = $user_enrolment_application->replace_application_with_new();
            } catch (coding_exception $e) {
                // Application needed, but not able to create.
                debugging('Unable to create a usable workflow application for user_enrolment_application #' . $user_enrolment_application->id);
                return '';
            }
        }

        return static::get_application_url($application);
    }

    /**
     * Given an enrolment application, get the appropriate URL for learner to access it.
     *
     * @param application $application
     * @return string
     */
    public static function get_application_url(application $application): string {
        $course = user_enrolment_application::find_with_application_id($application->id)->get_course();
        $return_address = '/enrol/index.php?id=' . $course->id;
        $return_address_label = get_string('back_to_course', 'core_enrol');

        if ($application->current_stage->type == provider::get_by_code(form_submission::get_code())) {
            return edit::get_url_for($application->id, $return_address, $return_address_label);
        } else {
            return view::get_url_for($application->id, $return_address, $return_address_label);
        }
    }

    /**
     *  Add request approval button and unenrol button for enrol plugin.
     *
     * @param $mform
     * @param string $submit_label
     */
    public static function add_action_buttons(&$mform, string $submit_label): void {
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', $submit_label);
        $buttonarray[] = $mform->createElement('submit', 'unenrol_user', get_string('unenrol', 'enrol'));
        $mform->addGroup($buttonarray, 'buttonar', '', [''], false);
        $mform->closeHeaderBefore('buttonar');
    }

    /**
     * Deletes drafts or archives in-progress applications associated with pending user enrolments
     * for an enrolment instance.
     *
     *
     * @param int $enrol_id
     * @return void
     */
    public static function delete_or_archive_applications(int $enrol_id): void {
        foreach (enrol::load_by_id($enrol_id)->user_enrolments as $user_enrolment) {
            if ($user_enrolment->status == ENROL_USER_PENDING_APPLICATION) {
                $user_enrolment_application = user_enrolment_application::find_with_user_enrolment_id($user_enrolment->id);
                if (isset($user_enrolment_application->approval_application)) {
                    $application = $user_enrolment_application->approval_application;
                    if ($application->current_state->is_draft()) {
                        $application->delete();
                        continue;
                    }

                    if ($application->completed || $application->current_state->is_stage_type(finished::get_code())) {
                        continue;
                    }

                    $archived_stage = approvalform_core_enrol_base::get_archived_stage($application->workflow_version);
                    $application->set_current_state($archived_stage->state_manager->get_initial_state());
                }
            }
        }
    }
}