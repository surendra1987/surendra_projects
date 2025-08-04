<?php
/**
 * This file is part of Totara Talent Experience Platform
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * @author Simon Chester <simon.chester@totara.com>
 * @package core_my
 */

namespace core_my\controllers;

use Closure;
use context;
use context_system;
use core\collection;
use totara_core\advanced_feature;
use totara_core\event\myreport_viewed;
use totara_mvc\controller;
use totara_mvc\tui_view;
use totara_mvc\view;

/**
 * Controller for my reports page
 */
class reports extends controller {
    protected $layout = 'standard';
    protected $url = '/my/reports.php';

    /** @inheritDoc */
    protected function setup_context(): context {
        return context_system::instance();
    }

    /** @inheritDoc */
    public function action(): view {
        global $CFG;
        require_once($CFG->dirroot . '/totara/reportbuilder/lib.php');

        $title = get_string('reports', 'totara_core');

        $page = $this->get_page();
        $page->set_pagetype('my-reports');
        $page->set_totara_menu_selected('\totara_core\totara\menu\myreports');
        $page->navbar->add($title);

        $this->setup_editing_button();

        myreport_viewed::create()->trigger();

        $data = get_my_reports_data();

        $props = [
            'reports' => $this->format_array('report', $data['reports']),
            'scheduledReports' => $this->format_array('scheduled_report', get_my_scheduled_reports_list()),
            'tenantOptions' => $this->format_array('tenant', $data['tenants']),
            'error' => !empty($data['errors']) ? get_string('reports_with_errors', 'totara_core') : null,
            'config' => [
                'defaultView' => get_config('totara_reportbuilder', 'defaultreportview'),
                'showDescription' => (bool)get_config('totara_reportbuilder', 'showdescription'),
                'exportToFilesystem' => (bool)get_config('reportbuilder', 'exporttofilesystem'),
            ],
            'capabilities' => [
                'createscheduledreports' => has_capability('totara/reportbuilder:createscheduledreports', \context_system::instance()),
            ]
        ];

        return static::create_tui_view('core_my/pages/Reports', $props)
            ->set_title($title);
    }


    /**
     * Format array by running format_$what for each item.
     *
     * @param string $what
     * @param array $array
     * @return array
     */
    protected function format_array(string $what, array $array): array {
        return collection::new($array)
            ->map(Closure::fromCallable([$this, 'format_' . $what]))
            ->filter(fn ($x) => $x)
            ->all();
    }

    /**
     * Format report object to be passed to frontend.
     *
     * Called dynamically from format_array.
     *
     * @param object $report
     * @return null|array
     */
    protected function format_report(object $report) {
        global $OUTPUT;

        if (!isset($report->url)) {
            debugging(
                'The url property for report ' . $report->fullname . ' is missing, please ask your developers to check your code',
                DEBUG_DEVELOPER
            );
            return null;
        }

        $graph_images = [
            'column' => 'graphicons/report_tile_image_column',
            'line' => 'graphicons/report_tile_image_line',
            'bar' => 'graphicons/report_tile_image_bar',
            'pie' => 'graphicons/report_tile_image_pie',
            'scatter' => 'graphicons/report_tile_image_scatter',
            'area' => 'graphicons/report_tile_image_area',
            'doughnut' => 'graphicons/report_tile_image_donut',
            'progress' => 'graphicons/report_tile_image_progress'
        ];
        $graph_image = advanced_feature::is_enabled('reportgraphs') && isset($graph_images[$report->graph])
            ? $graph_images[$report->graph]
            : 'graphicons/report_tile_image_nograph';

        $report_data = [
            'id' => $report->id,
            'name' => $this->format_string_to_plain($report->fullname),
            'url' => $report->url,
            'description' => $this->format_string_to_plain($report->summary),
            'graph_image_url' => $OUTPUT->image_url($graph_image, 'totara_core')->out(false),
            'tenant_id' => $report->tenantid ? $report->tenantid : null,
        ];

        return $report_data;
    }

    /**
     * Format scheduled report object to be passed to frontend.
     *
     * Called dynamically from format_array.
     *
     * @param object $sched
     * @return null|array
     */
    protected function format_scheduled_report(object $sched) {
        return [
            'id' => $sched->id,
            'name' => $this->format_string_to_plain($sched->fullname),
            'data' => $sched->data,
            'format' => (string)$sched->format,
            'schedule_html' => $sched->schedule,
            'export_to_fs_name' => $sched->exporttofilesystem,
            'tenant_id' => $sched->tenantid ? $sched->tenantid : null,
        ];
    }

    /**
     * Format tenant object to be passed to frontend.
     *
     * Called dynamically from format_array.
     *
     * @param object $tenant
     * @return null|array
     */
    protected function format_tenant(object $tenant) {
        return [
            'id' => $tenant->id,
            'name' => $this->format_string_to_plain($tenant->name),
            'can_create' => $tenant->can_create,
        ];
    }

    /**
     * format_string to plain text. Similar to text_field_formatter.
     *
     * @param string|null $str
     * @return string
     */
    protected function format_string_to_plain(?string $str): string {
        return html_to_text(format_string($str ?? '', true, ['context' => $this->get_context()]), 0, false);
    }

    /**
     * Set up editing mode button.
     */
    protected function setup_editing_button(): void {
        global $OUTPUT;
        $edit = $this->get_optional_param('edit', -1, PARAM_BOOL);

        $page = $this->get_page();
        $user = $this->currently_logged_in_user();

        if (!isset($user->editing)) {
            $user->editing = 0;
        }
        if ($page->user_allowed_editing()) {
            $editbutton = $OUTPUT->edit_button(new \moodle_url($this->url));
            $page->set_button($editbutton);

            if ($edit == 1 && confirm_sesskey()) {
                $user->editing = 1;
                $url = new \moodle_url($this->url, array('notifyeditingon' => 1));
                redirect($url);
            } else if ($edit == 0 && confirm_sesskey()) {
                $user->editing = 0;
                redirect($this->url);
            }
        } else {
            $user->editing = 0;
        }
    }
}
