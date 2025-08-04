<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Navjeet Singh <navjeet.singh@totara.com>
 * @author Ning Zhou <ning.zhou@totara.com>
 * @package mod_approval
 */

use mod_approval\hook\embedded_report_columns;
use totara_core\advanced_feature;
use mod_approval\model\workflow\workflow;
use mod_approval\controllers\workflow\report\role_assignment\override;

/**
 * Embedded override role assignments report
 */
class rb_approval_override_role_assignment_embedded extends rb_base_embedded {

    /**
     * @inheritDoc
     */
    public function __construct($data) {
        $this->source = 'approval_override_role_assignment';
        $this->shortname = 'approval_override_role_assignment';
        $this->fullname = get_string('embedded_override_role_assignment', 'mod_approval');
        $this->filters = $this->define_filters();
        $this->columns = $this->define_columns();
        $this->contentmode = REPORT_BUILDER_CONTENT_MODE_NONE;

        if (!empty($data['workflow'])) {
            /** @var workflow $workflow*/
            $workflow = $data['workflow'];
            $this->url = override::get_url(['workflow_id' => $workflow->id]);
            $this->embeddedparams['workflow_id'] = $workflow->id;
        } else {
            $this->url = '/mod/approval/workflow/report/role_assignment/override.php';
        }

        parent::__construct();
    }

    /**
     * Define the default filters available
     *
     * @return array
     */
    private function define_filters(): array {
        return [
            [
                'type' => 'role',
                'value' => 'role_name',
            ],
            [
                'type' => 'approval',
                'value' => 'assignment_type',
            ],
            [
                'type' => 'approval',
                'value' => 'name',
            ],
            [
                'type' => 'approval',
                'value' => 'id_number',
            ],
            [
                'type' => 'user',
                'value' => 'fullname',
            ],
        ];
    }

    /**
     * Define the default columns available
     *
     * @return array
     */
    private function define_columns(): array {
        $columns = [
            [
                'type' => 'user',
                'value' => 'fullname',
                'heading' => get_string('user_fullname', 'rb_source_approval_override_role_assignment'),
                'rowheader' => true,
            ],
            [
                'type' => 'role',
                'value' => 'name',
                'heading' => get_string('role_name', 'rb_source_approval_override_role_assignment'),
            ],
            [
                'type' => 'approval',
                'value' => 'name',
                'heading' => get_string('assignment_name', 'rb_source_approval_override_role_assignment'),
            ],
            [
                'type' => 'approval',
                'value' => 'assignment_type',
                'heading' => get_string('assignment_type', 'rb_source_approval_override_role_assignment'),
            ],
            [
                'type' => 'approval',
                'value' => 'id_number',
                'heading' => get_string('assignment_id_number', 'rb_source_approval_override_role_assignment'),
            ],
        ];
        $column_hook = new embedded_report_columns('approval_override_role_assignment', $columns);
        $column_hook->execute();
        return $column_hook->get_columns();
    }

    /**
     * @inheritDoc
     */
    public static function is_cloning_allowed(): bool {
        return false;
    }

    /**
     * Hide this source if feature disabled or hidden.
     * @return bool
     */
    public static function is_source_ignored() {
        return advanced_feature::is_disabled('approval_workflows');
    }

    /**
     * @return bool
     */
    public function embedded_global_restrictions_supported(): bool {
        return true;
    }

    /**
     * Check if the user is capable of accessing this report.
     *
     * @param int $reportfor userid of the user that this report is being generated for
     * @param reportbuilder $report the report object - can use get_param_value to get params
     * @return boolean true if the user can access this report
     */
    public function is_capable($reportfor, $report): bool {
        if (!empty($report->embedobj->embeddedparams['workflow'])) {
            /** @var workflow $workflow*/
            $workflow = $report->embedobj->embeddedparams['workflow'];
            $workflow_interactor = $workflow->get_interactor($reportfor);

            return $workflow_interactor->can_assign_roles();
        }
        return true;
    }
}