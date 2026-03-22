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
 * @author Simon Coggins <simon.coggins@totaralms.com>
 * @package totara
 * @subpackage plan
 */

class dp_basic_workflow extends dp_base_workflow {

    public $classname;

    // plan specific settings
    public $cfg_plan_manualcomplete;
    public $cfg_plan_autobyitems;
    public $cfg_plan_autobyplandate;

    // course specific settings
    public $cfg_course_duedatemode;
    public $cfg_course_prioritymode;
    public $cfg_course_priorityscale;

    // program specific settings
    public $cfg_program_duedatemode;
    public $cfg_program_prioritymode;
    public $cfg_program_priorityscale;

    // competency specific settings
    public $cfg_competency_autoassignpos;
    public $cfg_competency_autoassignorg;
    public $cfg_competency_includecompleted;
    public $cfg_competency_autoassigncourses;
    public $cfg_competency_autoadddefaultevidence;
    public $cfg_competency_duedatemode;
    public $cfg_competency_prioritymode;
    public $cfg_competency_priorityscale;

    // objective specific settings
    public $cfg_objective_duedatemode;
    public $cfg_objective_prioritymode;
    public $cfg_objective_priorityscale;
    public $cfg_objective_objectivescale;

    // plan permission settings
    public $perm_plan_view_learner;
    public $perm_plan_view_manager;
    public $perm_plan_create_learner;
    public $perm_plan_create_manager;
    public $perm_plan_update_learner;
    public $perm_plan_update_manager;
    public $perm_plan_delete_learner;
    public $perm_plan_delete_manager;
    public $perm_plan_approve_learner;
    public $perm_plan_approve_manager;
    public $perm_plan_completereactivate_learner;
    public $perm_plan_completereactivate_manager;

    // course permission settings
    public $perm_course_updatecourse_learner;
    public $perm_course_updatecourse_manager;
    public $perm_course_commenton_learner;
    public $perm_course_commenton_manager;
    public $perm_course_setpriority_learner;
    public $perm_course_setpriority_manager;
    public $perm_course_setduedate_learner;
    public $perm_course_setduedate_manager;
    public $perm_course_setcompletionstatus_learner;
    public $perm_course_setcompletionstatus_manager;
    public $perm_course_deletemandatory_learner;
    public $perm_course_deletemandatory_manager;

    // program permission settings
    public $perm_program_updateprogram_learner;
    public $perm_program_updateprogram_manager;
    public $perm_program_setpriority_learner;
    public $perm_program_setpriority_manager;
    public $perm_program_setduedate_learner;
    public $perm_program_setduedate_manager;

    //competency permission settings
    public $perm_competency_updatecompetency_learner;
    public $perm_competency_updatecompetency_manager;
    public $perm_competency_commenton_learner;
    public $perm_competency_commenton_manager;
    public $perm_competency_setpriority_learner;
    public $perm_competency_setpriority_manager;
    public $perm_competency_setduedate_learner;
    public $perm_competency_setduedate_manager;
    public $perm_competency_setproficiency_learner;
    public $perm_competency_setproficiency_manager;
    public $perm_competency_deletemandatory_learner;
    public $perm_competency_deletemandatory_manager;

    //objective permission settings
    public $perm_objective_updateobjective_learner;
    public $perm_objective_updateobjective_manager;
    public $perm_objective_commenton_learner;
    public $perm_objective_commenton_manager;
    public $perm_objective_setpriority_learner;
    public $perm_objective_setpriority_manager;
    public $perm_objective_setduedate_learner;
    public $perm_objective_setduedate_manager;
    public $perm_objective_setproficiency_learner;
    public $perm_objective_setproficiency_manager;

    function __construct() {
        global $CFG;
        require_once($CFG->dirroot.'/totara/plan/objectivescales/lib.php');
        require_once($CFG->dirroot.'/totara/plan/priorityscales/lib.php');
        $defaultpriority = dp_priority_default_scale_id();
        $defaultobjective = dp_objective_default_scale_id();

        $this->classname = 'basic';

        // workflow settings

        // plan specific settings
        $this->cfg_plan_manualcomplete = 1;
        $this->cfg_plan_autobyitems = 0;
        $this->cfg_plan_autobyplandate = 0;

        // course specific settings
        $this->cfg_course_duedatemode = DP_DUEDATES_OPTIONAL;
        $this->cfg_course_prioritymode = DP_PRIORITY_NONE;
        $this->cfg_course_priorityscale = $defaultpriority;

        // program specific settings
        $this->cfg_program_duedatemode = DP_DUEDATES_OPTIONAL;
        $this->cfg_program_prioritymode = DP_PRIORITY_NONE;
        $this->cfg_program_priorityscale = $defaultpriority;

        // competency specific settings
        $this->cfg_competency_autoassignpos = 0;
        $this->cfg_competency_autoassignorg = 0;
        $this->cfg_competency_includecompleted = 1;
        $this->cfg_competency_autoassigncourses = 0;
        $this->cfg_competency_autoadddefaultevidence = 0;
        $this->cfg_competency_duedatemode = DP_DUEDATES_NONE;
        $this->cfg_competency_prioritymode = DP_PRIORITY_OPTIONAL;
        $this->cfg_competency_priorityscale = $defaultpriority;

        // objective specific settings
        $this->cfg_objective_duedatemode = DP_DUEDATES_NONE;
        $this->cfg_objective_prioritymode = DP_PRIORITY_OPTIONAL;
        $this->cfg_objective_priorityscale = $defaultpriority;
        $this->cfg_objective_objectivescale = $defaultobjective;

        // plan permission settings
        $this->perm_plan_view_learner = DP_PERMISSION_ALLOW;
        $this->perm_plan_view_manager = DP_PERMISSION_ALLOW;
        $this->perm_plan_create_learner = DP_PERMISSION_ALLOW;
        $this->perm_plan_create_manager = DP_PERMISSION_ALLOW;
        $this->perm_plan_update_learner = DP_PERMISSION_ALLOW;
        $this->perm_plan_update_manager = DP_PERMISSION_ALLOW;
        $this->perm_plan_delete_learner = DP_PERMISSION_ALLOW;
        $this->perm_plan_delete_manager = DP_PERMISSION_ALLOW;
        $this->perm_plan_approve_learner = DP_PERMISSION_REQUEST;
        $this->perm_plan_approve_manager = DP_PERMISSION_APPROVE;
        $this->perm_plan_completereactivate_learner = DP_PERMISSION_DENY;
        $this->perm_plan_completereactivate_manager = DP_PERMISSION_ALLOW;

        // course permission settings
        $this->perm_course_updatecourse_learner = DP_PERMISSION_REQUEST;
        $this->perm_course_updatecourse_manager = DP_PERMISSION_APPROVE;
        $this->perm_course_commenton_learner = DP_PERMISSION_ALLOW;
        $this->perm_course_commenton_manager = DP_PERMISSION_ALLOW;
        $this->perm_course_setpriority_learner = DP_PERMISSION_DENY;
        $this->perm_course_setpriority_manager = DP_PERMISSION_ALLOW;
        $this->perm_course_setduedate_learner = DP_PERMISSION_DENY;
        $this->perm_course_setduedate_manager = DP_PERMISSION_ALLOW;
        $this->perm_course_setcompletionstatus_learner = DP_PERMISSION_DENY;
        $this->perm_course_setcompletionstatus_manager = DP_PERMISSION_ALLOW;
        $this->perm_course_deletemandatory_learner = DP_PERMISSION_DENY;
        $this->perm_course_deletemandatory_manager = DP_PERMISSION_DENY;

        // program permission settings
        $this->perm_program_updateprogram_learner = DP_PERMISSION_REQUEST;
        $this->perm_program_updateprogram_manager = DP_PERMISSION_APPROVE;
        $this->perm_program_setpriority_learner = DP_PERMISSION_DENY;
        $this->perm_program_setpriority_manager = DP_PERMISSION_ALLOW;
        $this->perm_program_setduedate_learner = DP_PERMISSION_DENY;
        $this->perm_program_setduedate_manager = DP_PERMISSION_ALLOW;

        //competency permission settings
        $this->perm_competency_updatecompetency_learner = DP_PERMISSION_REQUEST;
        $this->perm_competency_updatecompetency_manager = DP_PERMISSION_APPROVE;
        $this->perm_competency_commenton_learner = DP_PERMISSION_ALLOW;
        $this->perm_competency_commenton_manager = DP_PERMISSION_ALLOW;
        $this->perm_competency_setpriority_learner = DP_PERMISSION_DENY;
        $this->perm_competency_setpriority_manager = DP_PERMISSION_ALLOW;
        $this->perm_competency_setduedate_learner = DP_PERMISSION_DENY;
        $this->perm_competency_setduedate_manager = DP_PERMISSION_ALLOW;
        $this->perm_competency_setproficiency_learner = DP_PERMISSION_DENY;
        $this->perm_competency_setproficiency_manager = DP_PERMISSION_ALLOW;
        $this->perm_competency_deletemandatory_learner = DP_PERMISSION_DENY;
        $this->perm_competency_deletemandatory_manager = DP_PERMISSION_DENY;

        //objective permission settings
        $this->perm_objective_updateobjective_learner = DP_PERMISSION_REQUEST;
        $this->perm_objective_updateobjective_manager = DP_PERMISSION_APPROVE;
        $this->perm_objective_commenton_learner = DP_PERMISSION_ALLOW;
        $this->perm_objective_commenton_manager = DP_PERMISSION_ALLOW;
        $this->perm_objective_setpriority_learner = DP_PERMISSION_DENY;
        $this->perm_objective_setpriority_manager = DP_PERMISSION_ALLOW;
        $this->perm_objective_setduedate_learner = DP_PERMISSION_DENY;
        $this->perm_objective_setduedate_manager = DP_PERMISSION_ALLOW;
        $this->perm_objective_setproficiency_learner = DP_PERMISSION_DENY;
        $this->perm_objective_setproficiency_manager = DP_PERMISSION_ALLOW;

        parent::__construct();
    }
}
