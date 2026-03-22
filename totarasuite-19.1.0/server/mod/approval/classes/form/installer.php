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

namespace mod_approval\form;

use core\entity\cohort;
use core\entity\tenant;
use core\entity\user;
use core\json_editor\helper\document_helper;
use core\json_editor\node\mention;
use core\json_editor\node\paragraph;
use core\json_editor\node\text;
use core\session\manager as session_manager;
use core\testing\generator as core_generator;
use mod_approval\entity\workflow\workflow;
use mod_approval\event\stage_started as stage_started_event;
use mod_approval\model\application\action\approve;
use mod_approval\model\application\action\reject;
use mod_approval\model\application\action\submit;
use mod_approval\model\application\activity\stage_started as stage_started_activity;
use mod_approval\model\application\application;
use mod_approval\model\application\application_activity;
use mod_approval\model\application\application_submission;
use mod_approval\model\assignment\assignment_resolver;
use mod_approval\model\form\form_data;
use mod_approval\model\workflow\stage_type\approvals;
use mod_approval\model\workflow\stage_type\finished;
use mod_approval\model\workflow\stage_type\form_submission;
use mod_approval\model\workflow\workflow as workflow_model;
use mod_approval\testing\application_generator_object;
use mod_approval\testing\generator as mod_approval_generator;
use totara_cohort\testing\generator as totara_cohort_generator;
use totara_comment\comment;
use totara_comment\comment_helper;
use totara_comment\exception\comment_exception;
use totara_job\entity\job_assignment;
use totara_tenant\testing\generator as totara_tenant_generator;

abstract class installer {

    protected $usernames = [
        'ablake' => 'Anthony Blake',
        'lcameron' => 'Leonard Cameron',
        'sellison' => 'Sarah Ellison',
    ];

    protected $tenant_mode = false;

    /**
     * @var tenant|null
     */
    protected $tenant = null;

    protected $tenant_usernames = [
        'wchung' => 'Wendy Chung',
        'cfrancisco' => 'Candace Francisco',
        'bchoice' => 'Barry Choice',
    ];

    protected $system_usernames = [];

    /**
     * Get stages for default workflow
     *
     * @return string[]
     */
    public static function get_default_stages(): array {
        return [
            'stage1' => [
                'name' => 'Request',
                'type' => form_submission::get_enum(),
            ],
            'stage2' => [
                'name' => 'Approval',
                'type' => approvals::get_enum(),
            ],
            'stage3' => [
                'name' => 'Followup',
                'type' => form_submission::get_enum(),
            ],
            'stage4' => [
                'name' => 'Verify',
                'type' => approvals::get_enum(),
            ],
            'stage5' => [
                'name' => 'End',
                'type' => finished::get_enum(),
            ]

        ];
    }

    /**
     * Switches between system and tenant mode. Creates a tenant if necessary.
     *
     * @return bool
     */
    public function tenant_mode_switch(): bool {
        if ($this->tenant_mode) {
            $this->tenant_mode = false;
            $this->tenant_usernames = $this->usernames;
            $this->usernames = $this->system_usernames;
        } else {
            $this->tenant_mode = true;
            $this->system_usernames = $this->usernames;
            $this->usernames = $this->tenant_usernames;

            // Is there a tenant?
            $tenant = tenant::repository()->where('name', '=', 'Simple demo tenant')->one();
            if (empty($tenant)) {
                $tenant_obj = totara_tenant_generator::instance()->create_tenant(['name' => 'Simple demo tenant']);
                $tenant = new tenant($tenant_obj->id);
            }
            $this->tenant = $tenant;
        }
        return $this->tenant_mode;
    }

    /**
     * Creates or loads demo cohort.
     *
     * @return cohort
     */
    public function install_demo_cohort(): cohort {
        $name = 'Simple workflow demo';
        $idnumber = 'simpledemo';
        $contextid = \context_system::instance()->id;
        if ($this->tenant_mode) {
            $name = 'Simple tenant demo';
            $idnumber = 'simpletenantdemo';
            $contextid = \context_coursecat::instance($this->tenant->categoryid)->id;
        }
        $cohort = cohort::repository()->where('name', '=', $name)->one();
        if (empty($cohort)) {
            $cohort_obj = $this->cohort_generator()->create_cohort(['name' => $name, 'idnumber' => $idnumber, 'contextid' => $contextid]);
            $cohort = new cohort($cohort_obj->id);
        }
        return $cohort;
    }

    /**
     * @param cohort $cohort
     * @return array
     */
    public function install_demo_assignment(cohort $cohort): array {
        // Create manager
        $data = $this->get_user_data($this->usernames);
        $manager = $this->load_or_create_user($data);
        $data = [
            'userid' => $manager->id,
            'idnumber' => 'manager_' . $cohort->idnumber,
            'fullname' => $cohort->name . ' Manager',
        ];
        $manager_ja = $this->load_or_create_job_assignment($data);

        // Create applicant
        $data = $this->get_user_data($this->usernames);
        $applicant = $this->load_or_create_user($data);
        $data = [
            'userid' => $applicant->id,
            'idnumber' => 'applicant_' . $cohort->idnumber,
            'fullname' => $cohort->name . ' Applicant',
            'managerjaid' => $manager_ja->id,
        ];
        $applicant_ja = $this->load_or_create_job_assignment($data);

        // Assign applicant to cohort
        $this->cohort_generator()->cohort_assign_users($cohort->id, [$applicant->id]);

        return [$applicant, $applicant_ja];
    }

    public function install_demo_applications(
        workflow $workflow,
        user $applicant,
        job_assignment $ja,
        int $draft = 7,
        int $submitted = 4
    ) {
        $workflow_version = $workflow->versions->first();
        $form_version = $workflow_version->form_version;

        $workflow_stage = $workflow_version->stages->first();

        // Assignment resolver should use the new workflow only, in case there is more than one.
        $resolver = new assignment_resolver($applicant, $applicant, null, workflow_model::load_by_entity($workflow));
        $resolver->resolve();
        $possible_assignments = $resolver->get_assignments();

        // Take the first possible assignment for the current workflow.
        $assignment = null;
        foreach ($possible_assignments as $possible_assignment) {
            if ($possible_assignment->course_id == $workflow->course_id) {
                $assignment = $possible_assignment;
                break;
            }
        }
        if (is_null($assignment)) {
            throw new \coding_exception("Unable to resolve assignment for applicant {$applicant->fullname} on workflow {$workflow->id}");
        }

        $application_go = new application_generator_object($workflow_version->id, $form_version->id, $assignment->id);
        $application_go->user_id = $applicant->id;
        $application_go->job_assignment_id = $ja->id;
        /** @var application[] $applications */
        $applications = [];
        for ($i = 0; $i < $draft; $i++) {
            $application = $this->generator()->create_application($application_go);
            $application = application::load_by_id($application->id);
            $applications[] = $application;

            $stage_started = stage_started_event::create_from_application($application);
            $stage_started->trigger();

            application_activity::create(
                $application,
                null,
                stage_started_activity::class
            );
        }
        // Submit applications
        $this->set_user($applicant);
        for ($i = 0; $i < $submitted; $i++) {
            $form_data = form_data::from_json('{"request":"Pizza party"}');
            $submission = application_submission::create_or_update($applications[$i], $applications[$i]->user->id, $form_data);
            $submission->publish(user::logged_in()->id);
            submit::execute($applications[$i], user::logged_in()->id);
        }

        /** @var user $approver */
        $approver = $this->get_first_approver($applications[0]);
        $this->set_user($approver);

        // Two applications are APPROVED
        approve::execute($applications[0], $approver->id);
        approve::execute($applications[1], $approver->id);

        // One application is REJECTED
        reject::execute($applications[2], $approver->id);

        // Create some comments and activities
        $comment = $this->post_comment(
            $applications[0],
            $approver,
            [text::create_json_node_from_text('This is a first comment')]
        );
        $this->post_reply(
            $comment,
            $applicant,
            [text::create_json_node_from_text('This is a first reply')]
        );
        $comment = $this->post_comment(
            $applications[1],
            $applicant,
            [text::create_json_node_from_text('This is a second comment')]
        );
        $this->post_reply(
            $comment,
            $approver,
            [
                paragraph::create_json_node_with_content_nodes([mention::create_raw_node($applicant->id)]),
                paragraph::create_json_node_with_content_nodes([text::create_json_node_from_text('This is a second reply')]),
            ]
        );

        /** @var user $approver */
        $approver = $this->get_first_approver($applications[1]);
        $this->set_user($approver);
        approve::execute($applications[1], $approver->id);
    }

    /**
     * Creates a demo simple workflow with a default assignment.
     *
     * @param cohort $cohort Cohort to use for default assignment
     * @param string $type_name Workflow_type name
     * @return workflow
     */
    abstract public function install_demo_workflow(cohort $cohort, string $type_name = 'Simple'): workflow;

    /**
     * @return totara_cohort_generator
     */
    protected function cohort_generator(): totara_cohort_generator {
        return totara_cohort_generator::instance();
    }

    /**
     * @return totara_tenant_generator
     */
    protected function tenant_generator(): totara_tenant_generator {
        return totara_tenant_generator::instance();
    }

    /**
     * Gets the generator instance
     *
     * @return mod_approval_generator
     */
    protected function generator(): mod_approval_generator {
        return mod_approval_generator::instance();
    }

    /**
     * Gets the next user from an array of 'username' => 'Firstname Lastname' pairs.
     *
     * @param $usernames
     * @return array
     */
    protected function get_user_data(&$usernames): array {
        list($firstname, $lastname) = explode(' ', current($usernames));
        $data = ['username' => key($usernames), 'firstname' => $firstname, 'lastname' => $lastname, 'password' => 'simple'];
        next($usernames);
        return $data;
    }

    /**
     * Loads (by username) or creates a user.
     *
     * @param $data
     * @return user
     * @throws \coding_exception
     */
    protected function load_or_create_user($data): user {
        $core_generator = core_generator::instance();

        // Handle multitenancy.
        $user_repository = user::repository()->where('username', '=', $data['username']);
        if (!$this->tenant_mode) {
            $user_repository->where_null('tenantid');
        }
        else {
            $user_repository->where('tenantid', '=', $this->tenant->id);
            $data['tenantid'] = $this->tenant->id;
        }

        $user = $user_repository->one();
        if (is_null($user)) {
            $rec = $core_generator->create_user($data);
            $user = new user($rec->id);
        }
        return $user;
    }

    /**
     * Loads (by idnumber) or creates a new job assignment.
     *
     * @param $data
     * @return job_assignment
     * @throws \coding_exception
     */
    protected function load_or_create_job_assignment($data): job_assignment {
        $ja = job_assignment::repository()->where('idnumber', '=', $data['idnumber'])->one();
        if (is_null($ja)) {
            $rec = \totara_job\job_assignment::create($data);
            $ja = new job_assignment($rec->id);
        }
        return $ja;
    }

    /**
     * @param application $application
     * @param user $actor
     * @param array $content content of JSON document
     * @return comment
     * @throws \coding_exception
     * @throws comment_exception
     */
    protected function post_comment(application $application, user $actor, array $content): comment {
        $document = [
            'type' => 'doc',
            'content' => $content,
        ];
        return comment_helper::create_comment(
            'mod_approval',
            'comment',
            $application->id,
            document_helper::json_encode_document($document),
            FORMAT_JSON_EDITOR,
            null,
            $actor->id
        );
    }

    /**
     * @param comment $comment
     * @param user $actor
     * @param array $content content of JSON document
     * @return comment
     * @throws \coding_exception
     * @throws comment_exception
     */
    protected function post_reply(comment $comment, user $actor, array $content): comment {
        $document = [
            'type' => 'doc',
            'content' => $content,
        ];
        return comment_helper::create_reply(
            $comment->get_id(),
            document_helper::json_encode_document($document),
            null,
            FORMAT_JSON_EDITOR,
            $actor->id
        );
    }

    /**
     * @param application $application
     * @return user
     */
    private function get_first_approver(application $application): user {
        $approvers = $application->get_approver_users();
        if ($approvers->count() < 1) {
            throw new \coding_exception("Unable to resolve approvers for {$application->user->fullname} on application {$application->id}.");
        }
        return $approvers->first();
    }

    /**
     * @param user $user
     */
    protected function set_user(user $user): void {
        session_manager::set_user($user->to_record());
    }
}