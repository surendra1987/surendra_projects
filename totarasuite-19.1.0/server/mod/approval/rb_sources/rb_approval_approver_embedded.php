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
 * @author Angela Kuznetsova <angela.kuznetsova@totara.com>
 * @package mod_approval
 */

defined('MOODLE_INTERNAL') || die();

use mod_approval\controllers\workflow\report\approvers_report;
use mod_approval\hook\embedded_report_columns;
use mod_approval\model\workflow\workflow;
use totara_core\advanced_feature;

/**
 * Class rb_approval_approver_embedded
 */
class rb_approval_approver_embedded extends rb_base_embedded {
    /**
     * @var string
     */
    public $defaultsortcolumn;

    /**
     * @var int
     */
    public $defaultsortorder;

    /**
     * rb_approval_approver constructor.
     */
    public function __construct($data) {
        global $CFG;
        require_once $CFG->dirroot . '/mod/approval/rb_sources/rb_source_approval_approver.php';

        $this->source = "approval_approver";
        $this->shortname = 'approval_approver';
        $this->fullname = get_string('approvers_report', 'mod_approval');

        $this->defaultsortcolumn = "workflow_stage";
        $this->defaultsortorder = SORT_ASC;

        $this->columns = $this->get_columns();
        $this->filters = rb_source_approval_approver::get_default_filters();
        $this->contentmode = REPORT_BUILDER_CONTENT_MODE_NONE;

        if (!empty($data['workflow_id'])) {
            $this->url = approvers_report::get_url(['workflow_id' => $data['workflow_id']]);
            $this->embeddedparams['workflow_id'] = $data['workflow_id'];
        } else {
            $this->url = '/mod/approval/workflow/report/approvers_report.php';
        }
        parent::__construct();
    }

    /**
     * Get columns
     *
     * @return array
     */
    private function get_columns(): array {
        $column_hook = new embedded_report_columns('approval_approver', rb_source_approval_approver::get_default_columns());
        $column_hook->execute();

        return $column_hook->get_columns();
    }

    /**
     * @return bool
     */
    public function embedded_global_restrictions_supported(): bool {
        return true;
    }

    /**
     * Check if the current viewer is able to view this report.
     *
     * @param int $userid
     * @param reportbuilder $report
     *
     * @return bool
     */
    public function is_capable(int $userid, reportbuilder $report): bool {
        if (!empty($report->embedobj->embeddedparams['workflow'])) {
            /** @var workflow $workflow */
            $workflow = $report->embedobj->embeddedparams['workflow'];
            $workflow_interactor = $workflow->get_interactor($userid);

            return $workflow_interactor->can_upload_approver_overrides();
        }

        return true;
    }

    /**
     * Hide this source if feature disabled or hidden.
     *
     * @return bool
     */
    public static function is_source_ignored() {
        return advanced_feature::is_disabled('approval_workflows');
    }
}
