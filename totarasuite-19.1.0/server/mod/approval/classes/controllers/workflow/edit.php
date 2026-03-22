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
 * @author Oleg Demeshev <oleg.demeshev@totaralearning.com>
 * @package mod_approval
 */

namespace mod_approval\controllers\workflow;

use context_coursecat;
use core\entity\user;
use core\record\tenant;
use mod_approval\model\assignment\assignment_approver_type;
use mod_approval\model\workflow\stage_type\provider;
use totara_core\extended_context;
use container_approval\approval as approval_container;
use context;
use mod_approval\model\workflow\workflow;
use moodle_exception;
use totara_mvc\tui_view;
use mod_approval\controllers\workflow_controller;
use mod_approval\model\assignment\assignment_type\provider as assignment_type_provider;
use mod_approval\model\assignment\assignment_type\cohort as cohort_type;
use mod_approval\model\assignment\assignment_type\organisation as organisation_type;
use mod_approval\model\assignment\assignment_type\position as position_type;

/**
 * The edit workflow.
 */
class edit extends workflow_controller {

    /**
     * @inheritDoc
     */
    protected $layout = 'legacynolayout';

    /**
     * @return tui_view
     */
    public function action(): tui_view {
        global $CFG;
        $workflow_id = $this->get_required_param('workflow_id', PARAM_INT);
        $this->set_url(self::get_url(['workflow_id' => $workflow_id]));
        $initial_data = $this->load_workflow_query($workflow_id);

        if (empty($initial_data['data'])) {
            if (!empty($CFG->debugdeveloper)) {
                $errors = array_reduce($initial_data['errors'] ?? [], function ($errors, $error) {
                    return $errors . "\n" . $error['message'];
                }, "\n");
                throw new moodle_exception('GraphQL resulted in errors' . $errors);
            }
            throw new moodle_exception('Workflow not found.');
        }

        $workflow = workflow::load_by_id((int)$workflow_id);


        $workflow_version = $workflow->get_latest_version();
        $stages = $workflow_version->get_stages();

        $stages_extended_contexts = [];
        foreach ($stages as $workflow_stage) {
            $extended_context = extended_context::make_with_context(
                $workflow->get_context(),
                'mod_approval',
                'workflow_stage',
                $workflow_stage->id
            );

            $stages_extended_contexts[] = [
                'component'   => $extended_context->get_component(),
                'area'        => $extended_context->get_area(),
                'itemId'      => $extended_context->get_item_id(),
                'contextId'   => $extended_context->get_context_id()
            ];
        }

        // Set tenant_id to URL, if it's on tenant workflow management page.
        $tenant_id = $this->get_optional_param('tenant_id', null, PARAM_INT);
        $user = user::logged_in();

        $category_context = null;
        if ($tenant_id || $user->tenantid) {
            $tenant_id = $tenant_id ?? $user->tenantid;
            // Set back url.
            $back_url = dashboard::get_url(['tenant_id' => $tenant_id])->out(false);
            if ($user->tenantid && $tenant_id == $user->tenantid) {
                $tenant = tenant::fetch($tenant_id, MUST_EXIST);
                $cat_id = approval_container::get_category_id_from_tenant_category($tenant->categoryid);
                $category_context = context_coursecat::instance($cat_id);
            }
        } else {
            $back_url = dashboard::get_url()->out(false);
        }

        $context = $category_context ?? approval_container::get_default_category_context();
        $props = [
            'context-id' => $context->id,
            'stages-extended-contexts' => $stages_extended_contexts,
            'back-url' => $back_url,
            'approver-types' => assignment_approver_type::get_list(),
            'stage-types' => $this->get_stage_types(),
            'query-results' => $initial_data['data'],
            'can-view-orgframeworks' => has_capability('totara/hierarchy:vieworganisationframeworks', $context, $user),
            'can-view-posframeworks' => has_capability('totara/hierarchy:viewpositionframeworks', $context, $user),
            'assignment-types' => $this->get_assignment_types($context, $user),
        ];

        // redirect because tenant user is trying to edit a system workflow
        if (!$workflow->get_context()->tenantid && $user->tenantid) {
            $url = new \moodle_url(dashboard::URL);
            $url->param('tenant_id', $tenant_id);
            redirect($url);
        }

        if ($tenant_id) {
            $props['tenant-id'] = $tenant_id;
        }

        return workflow_controller::create_tui_view('mod_approval/pages/WorkflowEdit', $props)
            ->set_title(get_string('workflow_edit', 'mod_approval'));
    }

    /**
     * Loads the workflow data by resolving a graphQL query.
     *
     * @param int $workflow_id
     * @return array
     */
    private function load_workflow_query(int $workflow_id): array {
        return $this->execute_graphql_operation(
            'mod_approval_load_workflow',
            [
                'input' => [
                    'workflow_id' => $workflow_id,
                ],
            ]
        );
    }

    /**
     * Get formatted list of stage types that can be used as a page property.
     *
     * @return array
     */
    private function get_stage_types(): array {
        $list = [];

        foreach (provider::get_types() as $type) {
            $list[] = [
                'label' => $type::get_label(),
                'enum' => $type::get_enum(),
            ];
        }

        return $list;
    }

    /**
     * @inheritDoc
     */
    protected function setup_context(): context {
        $workflow_id = $this->get_required_param('workflow_id', PARAM_INT);

        return workflow::load_by_id($workflow_id)->get_context();
    }

    /**
     * @inheritDoc
     */
    public static function get_base_url(): string {
        return '/mod/approval/workflow/edit.php';
    }

    /**
     * Formatted list of assigment types
     *
     * @param context $context
     * @param user $user
     * @return array
     */
    protected function get_assignment_types(context $context, user $user): array {
        $assignment_types = [];

        $allowed_assignment_types = [
            cohort_type::get_enum(),
            position_type::get_enum(),
            organisation_type::get_enum()
        ];

        foreach (assignment_type_provider::get_all() as $type) {
            $enum = $type::get_enum();

            if (!in_array($enum, $allowed_assignment_types)) {
                continue;
            }

            if ($this->valid_assignment_type($context, $user, $enum)) {
                $assignment_types[] = [
                    'label' => $type::get_label(),
                    'enum' => $enum,
                ];
            }
        }

        return $assignment_types;
    }

    /**
     * @param context $context
     * @param user $user
     * @param string $enum
     * @return bool
     */
    private function valid_assignment_type(context $context, user $user, string $enum): bool {
        if (
            ($enum == organisation_type::get_enum() && has_capability('totara/hierarchy:vieworganisationframeworks', $context, $user)) ||
            ($enum == position_type::get_enum() && has_capability('totara/hierarchy:viewpositionframeworks', $context, $user)) ||
            !in_array($enum, [organisation_type::get_enum(), position_type::get_enum()])
        ) {
            return true;
        }
        return false;
    }
}
