<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2010 onwards Totara Learning Solutions LTD
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
 * @author Russell England <russell.england@catalyst-eu.net>
 * @package totara
 * @subpackage reportbuilder
 */

use totara_program\program;

class rb_program_assignment_duedates_embedded extends rb_base_embedded {

    public $url, $source, $fullname, $filters, $columns;
    public $contentmode, $contentsettings, $embeddedparams;
    public $hidden, $accessmode, $accesssettings, $shortname;
    public $defaultsortcolumn, $defaultsortorder;

    public function __construct($data) {
        global $DB;
        $this->url = '/totara/program/manage.php';
        $this->source = 'program_completion'; // Source report not database table.
        $this->defaultsortcolumn = 'user_fullname';
        $this->shortname = 'program_assignment_duedates';
        $this->fullname = get_string('programassignmentduedates', 'totara_program');
        $this->recordsperpage = 15;
        $this->filters = $this->define_filters();
        $this->columns = array(
            array(
                'type' => 'user',
                'value' => 'fullname',
                'heading' => get_string('userfullname', 'totara_reportbuilder'),
                'rowheader' => true,
            ),
            array(
                'type' => 'progcompletion',
                'value' => 'duedate',
                'heading' => get_string('actualduedate', 'totara_program'),
            ),
            array(
                'type' => 'progcompletion',
                'value' => 'isnotcomplete',
                'heading' => get_string('duedatecanbechanged', 'totara_program'),
            )
        );

        // No restrictions.
        $this->contentmode = REPORT_BUILDER_CONTENT_MODE_NONE;

        $this->embeddedparams = array();
        if (isset($data['programid'])) {
            $this->embeddedparams['programid'] = $data['programid'];
        }
        if (isset($data['assignmentid'])) {
            $this->embeddedparams['assignmentid'] = $data['assignmentid'];
        }

        if (!empty($data['showactioncolumn'])) {
            $usednamefields = totara_get_all_user_name_fields_join('auser', null, true);
            $this->requiredcolumns = array(new rb_column(
                'progcompletion',
                'actions',
                get_string('delete_user_group_header', 'totara_program'),
                'base.userid',
                array(
                    'displayfunc' => 'program_group_user_actions',
                    'noexport' => true,
                    'nosort' => true,
                    'graphable' => false,
                    'extrafields' => array(
                        'fullname' => $DB->sql_concat_join("' '", $usednamefields),
                        'assignment_id' => 'prog_user_assignment.assignmentid',
                        'program_id' => 'base.programid'
                    )
                )
            ));
        }
        parent::__construct();
    }

    /**
     * Hide this report because:
     * - it doesn't make sense outside of program context
     * - it doesn't have a dedicate page where it can be viewed
     *
     * @return bool
     */
    public static function is_report_ignored() {
        return true;
    }

    /**
     * Check if the user is capable of accessing this report.
     * We use $reportfor instead of $USER->id and $report->get_param_value() instead of getting report params
     * some other way so that the embedded report will be compatible with the scheduler (in the future).
     *
     * @param int $reportfor userid of the user that this report is being generated for
     * @param reportbuilder $report the report object - can use get_param_value to get params
     * @return boolean true if the user can access this report
     */
    public function is_capable($reportfor, $report) {
        if (empty($this->embeddedparams['programid'])) {
            $context = context_system::instance();
        } else {
            $program = new program($this->embeddedparams['programid']);
            $context = $program->get_context();
        }
        return has_capability('totara/program:configureassignments', $context, $reportfor);
    }


    /**
     * Define the default filters for this report.
     *
     * @return array
     */
    protected function define_filters() {
        return array(
            array(
                'type' => 'user',
                'value' => 'fullname',
                'advanced' => 0,
                'region' => rb_filter_type::RB_FILTER_REGION_STANDARD,
            ),
        );
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
