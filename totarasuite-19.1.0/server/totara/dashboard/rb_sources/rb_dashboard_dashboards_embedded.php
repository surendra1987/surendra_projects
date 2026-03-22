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

class rb_dashboard_dashboards_embedded extends rb_base_embedded {

    public function __construct() {
        global $CFG;

        $this->url = '/totara/dashboard/manage.php';
        $this->source = 'dashboard_dashboards';
        // Shortname must be unique, lets try make it really unique.
        $this->shortname = 'dashboard_dashboards';
        $this->fullname = get_string('dashboard', 'totara_dashboard');
        $this->defaultsortcolumn = 'dashboard_sortorder';
        $columns = array(
            array(
                'type' => 'dashboard',
                'value' => 'name'
            ),
            array(
                'type' => 'dashboard',
                'value' => 'allowguest'
            ),
            array(
                'type' => 'dashboard',
                'value' => 'availability'
            ),
            array(
                'type' => 'dashboard',
                'value' => 'sortorder',
                'hidden' => 1
            ),
            array(
                'type' => 'dashboard',
                'value' => 'actions'
            )
        );

        if (!empty($CFG->tenantsenabled)) {
            $columntenant = array(
                array(
                    'type' => 'dashboard',
                    'value' => 'tenantname'
                )
            );
            array_splice($columns, 3, 0, $columntenant);
        }
        $this->columns = $columns;

        $this->filters = array(
            array(
                'type' => 'dashboard',
                'value' => 'name',
            ),
            array(
                'type' => 'dashboard',
                'value' => 'allowguest',
            ),
            array(
                'type' => 'dashboard',
                'value' => 'availability',
            ),
        );

        // No restrictions.
        $this->contentmode = REPORT_BUILDER_CONTENT_MODE_NONE;

        parent::__construct();
    }

    /**
     * Check if the user is capable of accessing this report.
     *
     * @param int $userid id of the user this report is being generated for
     * @param reportbuilder $report the report object.
     * @return boolean true if the user can access this report.
     */
    public function is_capable() {
        $systemcontext = context_system::instance();

        return has_capability('totara/dashboard:manage', $systemcontext);
    }

    /**
     * Define if Global Report restrictions are supported.
     *
     * @return boolean
     */
    public function embedded_global_restrictions_supported() {
        return true;
    }

    /**
     * Can searches be saved?
     *
     * @return bool
     */
    public static function is_search_saving_allowed() : bool {
        return false;
    }
}
