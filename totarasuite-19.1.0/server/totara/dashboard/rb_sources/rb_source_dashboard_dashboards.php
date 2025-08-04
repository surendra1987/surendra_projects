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

defined('MOODLE_INTERNAL') || die();

class rb_source_dashboard_dashboards extends rb_base_source {
    public $base;
    public $joinlist;
    public $columnoptions;
    public $filteroptions;
 
    private $tenantsenabled;

    public function __construct() {
        global $CFG;
        $this->tenantsenabled = $CFG->tenantsenabled;

        $this->base = '{totara_dashboard}';
        $this->joinlist = $this->define_joinlist();
        $this->usedcomponents[] = 'totara_dashboard';
        $this->columnoptions = $this->define_columnoptions();
        $this->filteroptions = $this->define_filteroptions();
        $this->defaultcolumns = $this->define_defaultcolumns();
        $this->defaultfilters = $this->define_defaultfilters();
        $this->sourcetitle = get_string('sourcetitle', 'rb_source_dashboard_dashboards');
        $this->sourcesummary = get_string('sourcesummary', 'rb_source_dashboard_dashboards');
        $this->sourcelabel = get_string('sourcelabel', 'rb_source_dashboard_dashboards');
 
        parent::__construct();
    }

    protected function define_columnoptions(): array {
        $columns = array(
            new rb_column_option(
                'dashboard',
                'name',
                get_string('name', 'totara_dashboard'),
                'base.name',
                array(
                    'style' => ['vertical-align' => 'middle'],
                    'displayfunc' => 'dashboard_name_link',
                    'nosort' => true,
                    'extrafields' => array(
                        'id' => 'base.id'
                    )
                )
            ),
            new rb_column_option(
                'dashboard',
                'allowguest',
                get_string('guestaccess', 'totara_dashboard'),
                'base.allowguest',
                array(
                    'style' => ['vertical-align' => 'middle'],
                    'displayfunc' => 'yes_or_no',
                    'nosort' => true,
                )
            ),
            new rb_column_option(
                'dashboard',
                'availability',
                get_string('availability', 'totara_dashboard'),
                'base.published',
                array(
                    'style' => ['vertical-align' => 'middle'],
                    'displayfunc' => 'dashboard_availability',
                    'joins' => 'cohort',
                    'nosort' => true,
                    'extrafields' => array(
                        'cnt' => 'cohort.cohortcount'
                    )
                )
            ),
            new rb_column_option(
                'dashboard',
                'sortorder',
                get_string('sortorder', 'totara_dashboard'),
                'base.sortorder',
                array(
                    'style' => ['vertical-align' => 'middle'],
                    'displayfunc' => 'format_text',
                    'nosort' => true,
                )
            ),
            new rb_column_option(
                'dashboard',
                'actions',
                get_string('actions'),
                'base.id',
                array(
                    'style' => ['width' => '10em', 'padding-block' => '5px', 'vertical-align' => 'middle'], // Preserves the table layout after adding the Tui action buttons
                    'displayfunc' => 'dashboard_actions',
                    'capability' => 'totara/dashboard:manage',
                    'noexport' => true,
                    'nosort' => true,
                    'graphable' => false,
                    'extrafields' => array(
                        'id' => 'base.id',
                        'sortorder' => 'base.sortorder',
                        'minsort' => '(SELECT MIN(sortorder) FROM {totara_dashboard})',
                        'maxsort' => '(SELECT MAX(sortorder) FROM {totara_dashboard})',
                    )
                )
            )
        );

        if (!empty($this->tenantsenabled)) {
            $columns[] = new rb_column_option(
                'dashboard',
                'tenantname',
                get_string('tenant', 'totara_tenant'),
                'tenant.name',
                array(
                    'style' => ['vertical-align' => 'middle'],
                    'joins' => 'tenant',
                    'nosort' => true,
                )
            );
        }

        return $columns;
    }

    protected function define_filteroptions(): array {
        global $CFG;
        require_once($CFG->dirroot . '/totara/dashboard/lib.php');

        $filters = array(
            new rb_filter_option(
                'dashboard',
                'name',
                get_string('name', 'totara_dashboard'),
                'text'
            ),
            new rb_filter_option(
                'dashboard',
                'allowguest',
                get_string('guestaccess', 'totara_dashboard'),
                'select',
                [
                    'selectchoices' => [0 => get_string('no'), 1 => get_string('yes')],
                    'simplemode' => true
                ]
            ),
            new rb_filter_option(
                'dashboard',
                'availability',
                get_string('availability', 'totara_dashboard'),
                'select',
                [
                    'selectchoices' => [
                        totara_dashboard::NONE => get_string('availablenoneshort', 'totara_dashboard'),
                        totara_dashboard::AUDIENCE => get_string('availableaudiencecnt', 'totara_dashboard', ''),
                        totara_dashboard::ALL => get_string('availableallshort', 'totara_dashboard')
                    ],
                    'simplemode' => true
                ]
            ),
        );

        if (!empty($this->tenantsenabled)) {
            $filters[] = new rb_filter_option(
                'dashboard',
                'tenantname',
                get_string('tenant', 'totara_tenant'),
                'text',
            );
        }
        
        return $filters;
    }

    protected function define_defaultcolumns() {
        return array(
            array('type' => 'dashboard', 'value' => 'name'),
            array('type' => 'dashboard', 'value' => 'allowguest'),
            array('type' => 'dashboard', 'value' => 'availability'),
            array('type' => 'dashboard', 'value' => 'sortorder', 'hidden' => 1),
            array('type' => 'dashboard', 'value' => 'actions')
        );
    }
    
    protected function define_defaultfilters() {
        return array(
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
    }

    protected function define_joinlist() {
        $joinlist = array();

        $joinlist[] = new rb_join(
            'tenant',
            'LEFT',
            '{tenant}',
            'tenant.id = base.tenantid',
            REPORT_BUILDER_RELATION_MANY_TO_ONE
        );

        $joinlist[] = new rb_join(
            'cohort',
            'LEFT',
            '(SELECT COUNT(c.id) AS cohortcount, c.dashboardid FROM {totara_dashboard_cohort} c GROUP BY c.dashboardid)',
            'cohort.dashboardid = base.id',
            REPORT_BUILDER_RELATION_MANY_TO_ONE
        );

        return $joinlist;
    }

    
    /**
     * Global restrictions
     *
     * @return bool
     */
    public function global_restrictions_supported() {
        return true;
    }
}