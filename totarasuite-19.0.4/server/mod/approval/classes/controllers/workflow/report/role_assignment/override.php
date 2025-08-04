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

namespace mod_approval\controllers\workflow\report\role_assignment;

use core\output\notification;
use mod_approval\controllers\workflow\base;
use mod_approval\model\form\approvalform_base;
use mod_approval\model\workflow\workflow;
use mod_approval\views\embedded_report_view;
use mod_approval\views\override_nav_breadcrumbs_buttons;
use totara_mvc\has_report;
use totara_mvc\view;
use moodle_url;

/**
 * Override Role assignment report controller
 */
class override extends base {
    use has_report;

    /**
     * Page url
     *
     * @var string
     */
    public const URL =  '/mod/approval/workflow/report/role_assignment/override.php';

    /**
     * @inheritDoc
     */
    public function action() {
        $workflow_id = $this->get_optional_param('workflow_id', 0, PARAM_INT);
        if (!empty($workflow_id)) {
            $debug = $this->get_optional_param('debug', 0, PARAM_INT);
            $page_url = static::get_url(['workflow_id' => $workflow_id]);
            $this->set_url($page_url);
            $workflow = workflow::load_by_id($workflow_id);
            $params = [
                'workflow' => $workflow,
            ];

            $report = $this->load_embedded_report('approval_override_role_assignment', $params);
            $page_title = get_string('workflow_override_role_assignment_title', 'mod_approval', $workflow->name);

            $back_url = new moodle_url('/mod/approval/workflow/edit.php', ['workflow_id' => $workflow_id, 'sub_section' => 'form']);
            $text = get_string('back_to_workflow', 'mod_approval', $workflow->name);

            /* @var \mod_approval\views\embedded_report_view $report_view */
            $report_view = embedded_report_view::create_from_report($report, $debug)
                ->add_override(new override_nav_breadcrumbs_buttons())
                ->set_title($page_title)
                ->set_back_to($back_url, $text)
                ->set_additional_data([
                    'action' => approvalform_base::ACTION_IMPORT_OVERRIDE,
                ]);

            return $report_view->set_report_heading($report_view->get_embedded_report_heading($report, $workflow));
        } else {
            $this->set_url(static::get_url());
            $link_url = new moodle_url('/mod/approval/workflow/index.php');

            return view::create('mod_approval/no_report', [
                'content' => view::core_renderer()->notification(
                    get_string('approval_override_role_assignment_report_warning_message', 'mod_approval', $link_url->out()),
                    notification::NOTIFY_WARNING
                )
            ])->add_override(new override_nav_breadcrumbs_buttons());
        }
    }
}