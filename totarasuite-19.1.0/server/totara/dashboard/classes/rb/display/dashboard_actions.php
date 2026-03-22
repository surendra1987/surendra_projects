<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Yi-Chin Chyi <yi-chin.chyi@totara.com>
 * @package totara_dashboard
 */

namespace totara_dashboard\rb\display;

use totara_reportbuilder\rb\display\base;
use totara_tui\output\component;
use moodle_url;

/**
 * Class describing column display formatting.
 *
 * Display dashboard actions
 *
 * @author Yi-Chin Chyi <yi-chin.chyi@totara.com>
 * @package totara_dashboard
 */
class dashboard_actions extends base {

    public static function display($dashboardid, $format, \stdClass $row, \rb_column $column, \reportbuilder $report) {
        if ($format !== 'html') {
            // Only applicable to the HTML format.
            return '';
        }

        $manageurl = '/totara/dashboard/manage.php';
        $cloneurl = new \moodle_url($manageurl, array('action' => 'clone', 'id' => $dashboardid));
        $deleteurl = new \moodle_url($manageurl, array('action' => 'delete', 'id' => $dashboardid));
        $editurl = new \moodle_url('/totara/dashboard/edit.php', array('id' => $dashboardid));
        $movedownurl = new \moodle_url($manageurl, array('action' => 'down', 'id' => $dashboardid, 'sesskey' => sesskey()));
        $moveupurl = new \moodle_url($manageurl, array('action' => 'up', 'id' => $dashboardid, 'sesskey' => sesskey()));
        $urltop = new \moodle_url($manageurl, array('action' => 'top', 'id' => $dashboardid, 'sesskey' => sesskey()));

        $props = [
            'cloneUrl' => $cloneurl->out(false),
            'deleteUrl' => $deleteurl->out(false),
            'editUrl' => $editurl->out(false)
        ];

        $dashboard = static::get_extrafields_row($row, $column);

        if ($dashboard->sortorder !== $dashboard->maxsort) {
            $props['moveDownUrl'] = $movedownurl->out(false);
        }

        if ($dashboard->sortorder !== $dashboard->minsort) {
            $props['moveTopUrl'] = $urltop->out(false);
            $props['moveUpUrl'] = $moveupurl->out(false);
        }

        $tui = new component(
            'totara_dashboard/components/report/manage_dashboards/Actions',
            $props
        );

        return $tui->out_html();
    }

    public static function is_graphable(\rb_column $column, \rb_column_option $option, \reportbuilder $report) {
        return false;
    }
}
