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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Riana Rossouw <riana.rossouw@totaralearning.com>
 * @author Oleg Demeshev <oleg.demeshev@totaralearning.com>
 * @package mod_approval
 */

namespace mod_approval\views;

use mod_approval\model\workflow\workflow;
use reportbuilder;
use single_button;
use totara_mvc\report_view;
use totara_reportbuilder_renderer;

/**
 * Class embedded_report_view
 *
 * Use this view when using a template that embeds the totara_mvc/report but needs additional data
 */
class embedded_report_view extends report_view {

    /**
     * @var array
     */
    private $additional_data = [];

    /**
     * @var string
     */
    private $report_heading = null;

    /**
     * Gets the additional template data
     *
     * @return array
     */
    public function get_additional_data(): array {
        return $this->additional_data;
    }

    /**
     * Sets the additional template data
     *
     * @param array $additional_data
     * @return $this
     */
    public function set_additional_data(array $additional_data): self {
        $this->additional_data = $additional_data;
        return $this;
    }

    /**
     * Overrides the report heading
     *
     * @param string $heading
     * @return $this
     */
    public function set_report_heading(string $heading): self {
        $this->report_heading = $heading;
        return $this;
    }

    /**
     * Prepare report output
     *
     * @param reportbuilder $report
     * @return array
     */
    protected function prepare_output($report): array {
        $report_data = parent::prepare_output($report);
        if ($this->report_heading !== null) {
            $report_data['heading'] = $this->report_heading;
        }

        return array_merge($report_data,
            $this->additional_data
        );
    }

    /**
     * Get heading for override role assignment embedded report and approvers embedded report
     *
     * @param reportbuilder $report
     * @param workflow $workflow
     * @return string
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function get_embedded_report_heading(reportbuilder $report, workflow $workflow): string {
        /** @var totara_reportbuilder_renderer $render */
        $render = $this->get_page()->get_renderer('totara_reportbuilder');
        $action = $this->additional_data['action'];

        $url = $workflow->form->plugin->get_import_csv_url(['workflow_id' => $workflow->id], $action);
        $button = new single_button($url, get_string('upload_csv_file_button', 'mod_approval'), 'get');
        return $render->render_from_template(
            'mod_approval/report_heading',
            [
                'reportid' => $report->get_id(),
                'heading' => $this->get_report_title(),
                'import_button' => $render->render($button),
            ]
        );
    }
}
