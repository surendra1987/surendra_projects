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
 * @author Nathan Lewis <nathan.lewis@totaralms.com>
 * @package totara_reportbuilder
 */

use totara_core\advanced_feature;
use totara_program\program;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/totara/program/lib.php');

class rb_source_program_membership extends rb_base_source {
    use \totara_program\rb\source\program_trait;

    public function __construct($groupid, rb_global_restriction_set $globalrestrictionset = null) {
        if ($groupid instanceof rb_global_restriction_set) {
            throw new coding_exception('Wrong parameter orders detected during report source instantiation.');
        }
        // Remember the active global restriction set.
        $this->globalrestrictionset = $globalrestrictionset;

        // Apply global user restrictions.
        $this->add_global_report_restriction_join('base', 'userid');

        $this->base = $this->define_base();
        $this->joinlist = $this->define_joinlist();
        $this->columnoptions = $this->define_columnoptions();
        $this->filteroptions = $this->define_filteroptions();
        $this->contentoptions = $this->define_contentoptions();
        $this->paramoptions = $this->define_paramoptions();
        $this->defaultcolumns = $this->define_defaultcolumns();
        $this->defaultfilters = $this->define_defaultfilters();
        $this->requiredcolumns = $this->define_requiredcolumns();
        $this->sourcetitle = get_string('sourcetitle', 'rb_source_program_membership');
        $this->sourcesummary = get_string('sourcesummary', 'rb_source_program_membership');
        $this->sourcelabel = get_string('sourcelabel', 'rb_source_program_membership');
        $this->usedcomponents[] = 'totara_program';
        $this->usedcomponents[] = 'totara_cohort';

        $this->cacheable = false;

        parent::__construct();
    }

    private function define_base() {
        global $DB;

        $uniqueid = $DB->sql_concat_join("','", array('userid', 'programid'));

        return "(SELECT " . $uniqueid . " AS id, userid, programid
                   FROM (SELECT pc.userid, pc.programid
                           FROM {prog_completion} pc
                          WHERE pc.coursesetid = 0
                          UNION
                         SELECT pch.userid, pch.programid
                           FROM {prog_completion_history} pch
                          WHERE pch.coursesetid = 0) pcall
                   JOIN {prog} prog ON pcall.programid = prog.id and prog.certifid IS NULL)";
    }

    /**
     * Hide this source if feature disabled or hidden.
     * @return bool
     */
    public static function is_source_ignored() {
        return !advanced_feature::is_enabled('programs');
    }

    /**
     * Global report restrictions are implemented in this source.
     * @return boolean
     */
    public function global_restrictions_supported() {
        return true;
    }

    protected function define_joinlist() {
        $joinlist = array();

        $this->add_core_user_tables($joinlist, 'base', 'userid');
        $this->add_totara_program_tables($joinlist, 'base', 'programid', 'INNER');
        $this->add_totara_program_assignment_tables($joinlist, 'base', 'programid', 'base', 'userid', 'INNER');

        $joinlist[] = new rb_join(
            'prog_completion',
            'LEFT',
            '{prog_completion}',
            "prog_completion.userid = base.userid AND
            prog_completion.programid = base.programid AND
            prog_completion.coursesetid = 0",
            REPORT_BUILDER_RELATION_ONE_TO_ONE
        );

        return $joinlist;
    }

    protected function define_columnoptions() {
        $columnoptions = array();

        $this->add_core_user_columns($columnoptions);
        $this->add_totara_program_columns($columnoptions, 'program');

        $columnoptions[] = new rb_column_option(
            'progmembership',
            'status',
            get_string('status', 'rb_source_program_membership'),
            'prog_completion.status',
            array(
                'joins' => 'prog_completion',
                'displayfunc' => 'program_completion_status',
            )
        );
        $columnoptions[] = new rb_column_option(
            'progmembership',
            'isassigned',
            get_string('isassigned', 'rb_source_program_membership'),
            'CASE WHEN prog_completion.id IS NOT NULL THEN 1 ELSE 0 END',
            array(
                'joins' => 'prog_completion',
                'displayfunc' => 'yes_or_no',
                'dbdatatype' => 'boolean',
            )
        );
        $columnoptions[] = new rb_column_option(
            'progmembership',
            'editcompletion',
            get_string('editcompletion', 'rb_source_program_membership'),
            'base.id',
            array(
                'displayfunc' => 'program_edit_completion',
                'extrafields' => array(
                    'userid' => 'base.userid',
                    'progid' => 'base.programid',
                ),
            )
        );
        $columnoptions[] = new rb_column_option(
            'proguserassignment',
            'exceptionstatus',
            get_string('exceptionstatus','rb_source_program_membership'),
            'prog_user_assignment.exceptionstatus',
            [
                'joins' => 'prog_user_assignment',
                'displayfunc' => 'program_exception_status'
            ]
        );
        $this->add_assignment_columns($columnoptions, 'base.programid');

        return $columnoptions;
    }

    protected function define_filteroptions() {
        $filteroptions = array();

        $this->add_core_user_filters($filteroptions);
        $this->add_totara_program_filters($filteroptions);

        $filteroptions[] = new rb_filter_option(
            'progmembership',
            'status',
            get_string('status', 'rb_source_program_membership'),
            'select',
            array(
                'selectfunc' => 'status',
                'attributes' => rb_filter_option::select_width_limiter(),
            )
        );

        $filteroptions[] = new rb_filter_option(
            'proguserassignment',
            'exceptionstatus',
            get_string('exceptionstatus','rb_source_program_membership'),
            'select',
            [
                'selectchoices' => self::get_exception_status(),
                'attributes' => rb_filter_option::select_width_limiter(),
                'simplemode' => true,
            ]
        );
        $this->add_assignment_filter_options($filteroptions);

        return $filteroptions;
    }

    protected function define_paramoptions() {
        $paramoptions = array(
            new rb_param_option(
                'programid',
                'base.programid',
                'base'
            ),
        );
        return $paramoptions;
    }

    protected function define_defaultcolumns() {
        $defaultcolumns = array(
            array(
                'type' => 'user',
                'value' => 'namelink',
            ),
            array(
                'type' => 'progmembership',
                'value' => 'status',
            ),
        );
        return $defaultcolumns;
    }

    protected function define_defaultfilters() {
        $defaultfilters = array(
            array(
                'type' => 'user',
                'value' => 'fullname',
                'advanced' => 0,
            ),
            array(
                'type' => 'progmembership',
                'value' => 'status',
                'advanced' => 0,
            ),
        );
        return $defaultfilters;
    }

    public function rb_filter_status() {
        $out = array();
        $out[program::STATUS_PROGRAM_INCOMPLETE] = get_string('incomplete', 'totara_program');
        $out[program::STATUS_PROGRAM_COMPLETE] = get_string('complete', 'totara_program');

        return $out;
    }

    /**
     * Returns the mapping of exception status values to display texts.
     *
     * @return array mapping of exception status values to display texts.
     */
    public static function get_exception_status() {
        return [
            program::PROGRAM_EXCEPTION_NONE => get_string('exception_status_none', 'totara_program'),
            program::PROGRAM_EXCEPTION_RAISED => get_string('exception_status_raised', 'totara_program'),
            program::PROGRAM_EXCEPTION_DISMISSED => get_string('exception_status_dismissed', 'totara_program'),
            program::PROGRAM_EXCEPTION_RESOLVED => get_string('exception_status_resolved', 'totara_program')
        ];
    }

    protected function define_source_args() {
        $joins = ['program'];
        $sql = "(base.certifid IS NULL)";

        return [$sql, [], $joins];
    }
}
