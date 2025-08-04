<?php
/*
 * This file is part of Totara Learn
 *
 * Copyright (C) 2019 onwards Totara Learning Solutions LTD
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
 * @author Alastair Munro <alastair.munro@totaralearning.com>
 * @package totara_program
 */

namespace totara_program\assignment;

use stdClass;
use totara_certification\entity\certification;
use totara_program\assignments\assignments;
use totara_program\event\bulk_learner_assignments_ended;
use totara_program\event\bulk_learner_assignments_started;
use totara_program\event\program_assigned;
use totara_program\program;
use totara_program\utils;

class base {

    protected $id = 0;
    protected $programid;
    protected $typeid;
    protected $instanceid;

    protected $name = '';
    protected $completionevent = assignments::COMPLETION_EVENT_NONE;
    protected $completioninstance = 0;
    protected $completiontime = assignments::COMPLETION_TIME_NOT_SET;
    protected $completionoffsetamount = null;
    protected $completionoffsetunit = null;
    protected $includechildren = 0;

    protected $program = null;
    protected $category = null;

    protected function __construct(int $id = 0) {
        global $DB;

        // Load from DB
        if ($id !== 0) {
            $record = $DB->get_record('prog_assignment', ['id' => $id], '*', MUST_EXIST);

            // Load into object
            $this->id = $record->id;
            $this->programid = $record->programid;
            $this->includechildren = $record->includechildren;
            $this->completiontime = $record->completiontime;
            $this->completionoffsetamount = $record->completionoffsetamount;
            $this->completionoffsetunit = $record->completionoffsetunit;
            $this->completionevent = $record->completionevent;
            $this->completioninstance = $record->completioninstance;

            $this->typeid = $record->assignmenttype;
            $this->instanceid = $record->assignmenttypeid;
        }
    }

    public function get_record(): stdClass {
        $record = new stdClass();
        $record->id = $this->id;
        $record->programid = $this->programid;
        $record->assignmenttype = $this->typeid;
        $record->assignmenttypeid = $this->instanceid;
        $record->includechildren = (int)$this->includechildren;
        $record->completionevent = $this->completionevent;
        $record->completioninstance = $this->completioninstance;
        $record->completiontime = $this->completiontime;
        $record->completionoffsetamount = $this->completionoffsetamount;
        $record->completionoffsetunit = $this->completionoffsetunit;
        return $record;
    }

    /**
     * Should the assignment type be used via the user interface?
     *
     * @return bool
     */
    public static function show_in_ui() :bool {
        return true;
    }

    /**
     * Can the assignment be updated?
     *
     * @param int $programid
     * @return bool
     */
    public static function can_be_updated(int $programid) : bool {
        $program_context = \context_program::instance($programid);
        return has_capability('totara/program:configureassignments', $program_context);
    }

    /**
     * Load program object for this assignment
     */
    protected function ensure_program_loaded() {
        if ($this->program === null) {
            $this->program = new program($this->programid);
        }
    }

    /**
     * Load category for the assignment
     */
    private function ensure_category_loaded() {
        global $CFG;

        if ($this->category === null) {
            $this->category = assignments::factory($this->typeid);
        }
    }

    /**
     * Create a new assignment given an instance id
     *
     * @param int $programid
     * @param int $type
     * @param int $instanceid
     */
    public static function create_from_instance_id(int $programid, int $typeid, int $instanceid): base {

        $types = helper::get_types();
        $class = $types[$typeid];
        $classpath = '\\totara_program\\assignment\\' . $class;
        $assignment = new $classpath();

        $assignment->instanceid = $instanceid;
        $assignment->typeid = $typeid;
        $assignment->programid = $programid;

        // Set the name of the assignment
        $assignment->name = $assignment->get_name();

        return $assignment;
    }

    /**
     * Create instances of assignment given an assignemnt id
     *
     * @param int $id Id of program assignment
     *
     * @return base $assignment
     */
    public static function create_from_id(int $id): base {
        global $DB;

        // Get record
        $record = $DB->get_record('prog_assignment', ['id' => $id], '*', MUST_EXIST);
        $types = helper::get_types();
        $class = $types[$record->assignmenttype];
        $classpath = '\\totara_program\\assignment\\' . $class;

        // Create instance of correct type for this assignment
        $assignment = new $classpath($id);

        return $assignment;
    }


    /**
     * Create from object (database record). Must contain
     * id and assignmenttype properties
     *
     * @param \stdClass $record
     *
     * @return base Instance of assignment
     */
    public static function create_from_record(\stdClass $record): base {
        $types = helper::get_types();
        $class = $types[$record->assignmenttype];
        $classpath = '\\totara_program\\assignment\\' . $class;

        $assignment = new $classpath($record->id);

        return $assignment;
    }

    /**
     * Get type for this assignment
     */
    public function get_type(): int {
        return $this->typeid;
    }

    public function get_name(): string {
        return $this->name;
    }

    public function get_description(): string {
        return '';
    }

    public function can_self_enrol(): bool {
        return false;
    }

    public function can_self_unenrol(): bool {
        return false;
    }

    public function get_programid(): int {
        return $this->programid;
    }

    public function get_program(): program {
        $this->ensure_program_loaded();
        return $this->program;
    }

    public function get_includechildren(): int {
        return $this->includechildren;
    }

    public function get_id(): int {
        return $this->id;
    }

    public function get_completionevent(): int {
        return $this->completionevent;
    }

    public function get_completionoffsetunit(): int {
        return $this->completionoffsetunit;
    }

    public function get_completionoffsetamount(): int {
        return $this->completionoffsetamount;
    }

    public function get_completioninstance(): int {
        return $this->completioninstance;
    }

    /**
     * Get the instanceid
     *
     * @return int
     */
    public function get_instanceid() : int {
        return $this->instanceid;
    }

    /**
     * Set include children value
     *
     * @param bool
     */
    public function set_includechildren(bool $value) {
        global $DB;

        $this->includechildren = $value;
        $this->save();

        if ($this->includechildren == 1) {
            $this->create_user_assignment_records();
            // Completiontime is not applicable when the relative time is active, so it is null.
            // But the set_duedate function can't handle the null duedate param.
            $this->completiontime ?? $this->completiontime = 0;

            if ($this->completiontime !== assignments::COMPLETION_TIME_NOT_SET || isset($this->completionoffsetamount)) {
                // If a completion time has been set then all new users need to be updated.
                $this->set_duedate($this->completiontime, $this->completionevent, $this->completioninstance, $this->completionoffsetamount, $this->completionoffsetunit);
            }
        } else {
            $this->ensure_program_loaded();

            // Figure out which ones are not part of parent...
            $children = $this->get_children();

            if (!empty($children)) {
                list($insql, $inparams) = $DB->get_in_or_equal($children, SQL_PARAMS_NAMED);
                $params = array_merge(['programid' => $this->programid, 'assignid' => $this->id], $inparams);
                $DB->delete_records_select('prog_user_assignment', "programid = :programid AND assignmentid = :assignid AND userid $insql", $params);

                // Check to see if user is still assigned by another method
                // if they aren't then delete prog_completion (and maybe certif_completion?)
                // for this program/certification
                foreach ($children as $userid) {
                    $isassigned = $this->program->user_is_assigned($userid);
                    if (!$isassigned) {
                        $DB->delete_records('prog_completion', ['programid' => $this->programid, 'userid' => $userid]);

                        if (!empty($this->program->certifid)) {
                            // Delete certification completion records
                            $DB->delete_records('certif_completion', ['certifid' => $this->program->certifid, 'userid' => $userid]);
                        }
                    }
                }
            }
        }
    }

    /**
     * Get the duedate string given the due date
     * Note: This logic is copied from the get_completion function
     * in prog_assignment_category class
     *
     * @return \stdClass
     */
    public function get_duedate(): \stdClass {
        global $CFG;

        $canupdate = helper::can_update($this->programid, $this->get_type());

        $completiondate = new \stdClass();

        if ($canupdate) {
            $completiondate->string = '';
        } else {
            $completiondate->string = get_string('noduedate', 'totara_program');
        }

        $completiondate->changeable = true;

        if (empty($this->completiontime)) {
            $this->completiontime = assignments::COMPLETION_TIME_NOT_SET;
        }

        if (!isset($this->completionevent)) {
            $this->completionevent = assignments::COMPLETION_EVENT_NONE;
        }

        if (!isset($this->completioninstance)) {
            $this->completioninstance = 0;
        }

        if (!isset($this->completionoffsetamount)) {
            $this->completionoffsetamount = null;
        }

        if (!isset($this->completionoffsetunit)) {
            $this->completionoffsetunit = null;
        }

        if ($this->completionevent == assignments::COMPLETION_EVENT_NONE) {
            // Completiontime must be a timestamp.
            if ($this->completiontime != assignments::COMPLETION_TIME_NOT_SET) {
                $completiondate->string = self::get_completion_string();
                $completiondate->changeable = true;
            }
        } else {
            $completiondate->string = self::get_completion_string();
            $completiondate->changeable = true;
        }

        return $completiondate;
    }

    /**
     * Can user update due date
     * true by default
     *
     * Note: This doesn't check users permission, rather determines if
     * the date should be updatable given the program/certification
     * assignment state.
     *
     * @return bool
     */
    public function can_update_date(): bool {
        return true;
    }

    /**
     * Get actual duedate null by default
     */
    public function get_actual_duedate() {
        if (!helper::can_update($this->programid, $this->get_type())) {
            return get_string('noduedate', 'totara_program');
        }
        return null;
    }

    /**
     * Has this assignment a due date set, whether fixed or relative
     * @return bool
     */
    public function has_duedate(): bool {
        // Either there's a fixed date
        // Or there's an event AND (amount+unit OR instance)
        return $this->completiontime > 0 ||
            (!empty($this->completionevent) && (!empty($this->completionoffsetamount) && !empty($this->completionoffsetunit)) || !empty($this->completioninstance));
    }

    /**
     * Get count of user for this assignment
     *
     * @return int
     */
    public function get_user_count(): int {
        return 0;
    }

    /**
     * Gets list of children for an assignment type
     */
    public function get_children(): array {
        return [];
    }


    /**
     * Indicates whether a specific user can self enrol into this assignment, at this time.
     * If the enrolment would create an exception or the program is not available, then self enrolment is not allowed
     *
     * @param $userid
     * @return bool true if self enrolment is allowed.
     */
    public function can_user_self_enrol(int $userid): bool {
        $now = time();
        $isavailable = (
            $this->get_program()->available == program::AVAILABILITY_TO_STUDENTS &&
            (empty($program->availablefrom) || $program->availablefrom < $now) &&
            (empty($program->availableuntil) || $program->availableuntil > $now)
        );
        if ($this->can_self_enrol() && $isavailable) {
            if ($this->has_duedate()) {
                $timedue = $this->get_program()->make_timedue($userid, $this->get_record(), false);
                $now = time();

                $certifpath = get_certification_path_user($this->get_program()->certifid, $userid);
                if ($certifpath == CERTIFPATH_UNSET) {
                    $certifpath = CERTIFPATH_CERT;
                }
                $timetocomplete = $this->get_program()->content->get_total_time_allowance($certifpath);

                // make_timedue returns false if it can't calculate the due date (which is 0)
                // If we don't have enough time to complete the prog, then don't allow self enrolment
                return $timedue > ($now + $timetocomplete);
            }
            return true;
        }
        return false;
    }

    /**
     * Indicates whether a specific user can self unenrol (withdraw) from this assignment, at this time.
     * This assumes the user is enrolled with this assignment
     *
     * @param $userid
     * @return bool true if self unenrolment is allowed.
     */
    public function can_user_self_unenrol(int $userid): bool {
        $now = time();
        $isavailable = (
            $this->get_program()->available == program::AVAILABILITY_TO_STUDENTS &&
            (empty($program->availableuntil) || $program->availableuntil > $now)
        );
        return $this->can_self_unenrol() && $isavailable;
    }

    /**
     * Create or update prog_assignment record
     * If a user doesn't have permission it will not update
     *
     * @return bool
     */
    public function save(): bool {
        global $DB, $CFG;

        $canupdate = helper::can_update($this->programid, $this->typeid);
        if (!$canupdate) {
            return false;
        }

        if ($this->id === 0) {
            // Prevent duplicate assignments being added to DB
            $params = ['programid' => $this->programid, 'assignmenttype' => $this->typeid, 'assignmenttypeid' => $this->instanceid];
            $alreadyexists = $DB->record_exists('prog_assignment', $params);

            if ($alreadyexists) {
                return false;
            }

            // Create new prog_assignment record
            $data = new \stdClass();
            $data->programid = $this->programid;
            $data->assignmenttype = $this->typeid;
            $data->assignmenttypeid = $this->instanceid;
            $data->includechildren = (int)$this->includechildren;
            $data->completionevent = $this->completionevent;
            $data->completioninstance = $this->completioninstance;
            $data->completiontime = $this->completiontime;
            $data->completionoffsetamount = $this->completionoffsetamount;
            $data->completionoffsetunit = $this->completionoffsetunit;

            $assignmentid = $DB->insert_record('prog_assignment', $data);

            // Set the assignment id now we have one
            $this->id = $assignmentid;

            if (empty($CFG->revert_TL_43089_until_t20)) {
                // Trigger an event for the group type that has been added
                $this->trigger_assignment_event();
            }

            // For new assignment we need to create
            // prog_completion and prog_user_assignment records
            $this->create_user_assignment_records();
        } else {
            // Update existing.
            $data = new \stdClass();

            $data->id = $this->id;
            $data->includechildren = (int)$this->includechildren;
            $data->completionevent = $this->completionevent;
            $data->completioninstance = $this->completioninstance;
            $data->completiontime = $this->completiontime;
            $data->completionoffsetamount = $this->completionoffsetamount;
            $data->completionoffsetunit = $this->completionoffsetunit;

            $DB->update_record('prog_assignment', $data);
        }

        return true;
    }

    /**
     * Remove program assignment
     *
     * @return bool
     */
    public function remove(): bool {
        global $DB, $CFG;

        $canupdate = helper::can_update($this->programid, $this->get_type());
        if (!$canupdate) {
            return false;
        }

        $this->ensure_category_loaded();
        $this->ensure_program_loaded();

        // Users with prog_user_assignment records
        $userids = $DB->get_records_menu('prog_user_assignment', ['assignmentid' => $this->id], '', 'id, userid');

        if (count($userids) > program::PROG_UPDATE_ASSIGNMENTS_DEFER_COUNT) {
            $DB->set_field('prog', 'assignmentsdeferred', 1, ['id' => $this->programid]);
            $DB->delete_records('prog_assignment', ['id' => $this->id]);

            return true;
        }

        // Get array of users that are still assigned
        $sql = "SELECT id, userid FROM {prog_user_assignment}
                 WHERE programid = :programid
                 AND assignmentid != :assignmentid";
        $otherassignmentusers = $DB->get_records_sql_menu($sql, ['programid' => $this->programid, 'assignmentid' => $this->get_id()]);

        foreach ($userids as $id => $userid) {
            if (in_array($userid, $otherassignmentusers)) {
                // Remove prog_user_assignment record
                $DB->delete_records('prog_user_assignment', ['programid' => $this->programid, 'userid' => $userid, 'assignmentid' => $this->get_id()]);

                // Remove user from removed from the program
                unset($userids[$id]);
            }
        }

        // Remove all learners from the assignment
        $this->program->unassign_learners($userids);

        if (empty($CFG->revert_TL_43089_until_t20)) {
            // Send an event for unassigning the audience
            $this->trigger_unassigned_event();
        }

        // Delete the assignment itself
        $DB->delete_records('prog_assignment', ['id' => $this->id]);

        return true;
    }

    /**
     * Set the due date for the assignment
     *
     * @param int      $duedate
     * @param int      $completionevent
     * @param int      $completioninstance
     * @param int|null $offsetamount
     * @param int|null $offsetunit
     *
     * @return false|void
     */
    public function set_duedate(int $duedate = 0, int $completionevent = assignments::COMPLETION_EVENT_NONE, int $completioninstance = 0, int $offsetamount = null, int $offsetunit = null) {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/totara/certification/lib.php');

        $canupdate = helper::can_update($this->programid, $this->get_type());
        if (!$canupdate) {
            return false;
        }

        require_once($CFG->dirroot . '/totara/program/lib.php');

        $this->completiontime = $duedate == 0 ? null : $duedate;
        $this->completionoffsetamount = $offsetamount;
        $this->completionoffsetunit = $offsetunit;
        $this->completionevent = $completionevent;
        $this->completioninstance = $completioninstance;

        // Update completion record for all users in this assignment
        // - Get all users
        // - Loop through users
        // - Check for exceptions
        // - Set completion
        $this->ensure_category_loaded();
        $this->ensure_program_loaded();

        $assignment = new \stdClass();
        $assignment->id = $this->id;
        $assignment->programid = $this->programid;
        $assignment->assignmenttype = $this->typeid;
        $assignment->assignmenttypeid = $this->instanceid;
        $assignment->completionevent = $this->completionevent;
        $assignment->completiontime = $this->completiontime;
        $assignment->completionoffsetamount = $this->completionoffsetamount;
        $assignment->completionoffsetunit = $this->completionoffsetunit;
        $assignment->completioninstance = $this->completioninstance;

        $users = $this->category->get_affected_users_by_assignment($assignment);

        if (count($users) > program::PROG_UPDATE_ASSIGNMENTS_DEFER_COUNT) {
            // Save prog_assignment and update deferred flag
            $this->save();
            $DB->set_field('prog', 'assignmentsdeferred', 1, ['id' => $this->program->id]);
            return;
        }

        $this->update_assignments_for_users($users);

        // Update prog_assignment record
        $this->save();
    }

    /**
     * Creates or updates assignment and completion data for the given users.
     *
     * @param array $users of stdClass containing a property "id"
     */
    public function update_assignments_for_users(array $users): void {
        global $DB;

        $assignment = $this->get_record();
        $this->ensure_program_loaded();

        $futureassignments = [];
        $updateassignusersbuffer = [];
        $newassignusersbuffer = [];

        $is_certification = $this->program->is_certif();
        if ($is_certification) {
            $cert = new certification($this->program->certifid);
            $course_set_minimum_time_allowance = $this->program->content->get_total_time_allowance(CERTIFPATH_CERT);
        }
        foreach ($users as $user) {
            $params = ['programid' => $this->programid, 'userid' => $user->id, 'certifid' => $this->program->certifid];
            $sql = "SELECT pc.userid, pc.timedue, pc.status, cc.certifpath, cc.status AS certifstatus
                FROM {prog_completion} pc
                LEFT JOIN {certif_completion} cc ON cc.certifid = :certifid AND pc.userid = cc.userid
                WHERE pc.programid = :programid AND pc.coursesetid = 0 AND pc.userid = :userid";
            $completionrecord = $DB->get_record_sql($sql, $params);
            $userassignment = $DB->get_record('prog_user_assignment', ['assignmentid' => $this->id, 'userid' => $user->id]);

            // Get the new timedue for completion event
            $timedue = $completionrecord ? $completionrecord->timedue : false;
            // Time due should be the actual timestamp if we can calculate it
            // not the interval of the relative event!!
            $timedue = $this->program->make_timedue($user->id, $assignment, $timedue);

            // Auto-adjust timedue when using fixed expiry recertification method.
            if ($is_certification && $cert->recertifydatetype == CERTIFRECERT_FIXED && $timedue != assignments::COMPLETION_TIME_NOT_SET) {
                $certifpath = get_certification_path_user($cert->id, $user->id);
                // It's possible that the certif path hasn't yet been defined, in which case it will end up being the primary path.
                if ($certifpath == CERTIFPATH_UNSET) {
                    $certifpath = CERTIFPATH_CERT;
                }
                // Only calculate while on the primary certification path and the assingment date is static
                // and the timedue matches the static assignment date and timedue hasn't already been recorded.
                if ($certifpath == CERTIFPATH_CERT &&
                    $assignment->completionevent == assignments::COMPLETION_EVENT_NONE &&
                    $assignment->completiontime > 0 &&
                    $timedue == $assignment->completiontime &&
                    (!$completionrecord || $completionrecord->timedue <= 0)
                ) {
                    $now = time();
                    // The current timedue might require adding one or more active periods to avoid a time allowance exception.
                    $active_period_increments = 0;
                    while ($timedue - $now < $course_set_minimum_time_allowance) {
                        $timedue = strtotime("+ ". $cert->activeperiod, $timedue);
                        $active_period_increments++;
                    }
                    if ($active_period_increments > 0) {
                        prog_log_completion(
                            $this->program->id,
                            $user->id,
                            'Due date was pushed forward by ' . $active_period_increments . ' active periods to avoid time allowance exception'
                        );
                    }
                }
            }

            if ($assignment->completionevent == assignments::COMPLETION_EVENT_FIRST_LOGIN && !$timedue) {
                // Add to future assignment list
                $futureassignments[$user->id] = $user->id;
                continue;
            }

            if (!empty($userassignment)) {
                $sendmessage = false;
                // Update user assignment record
                if (empty($completionrecord)) {
                    // This is bad and shouldn't happen
                    continue;
                }

                if (!empty($userassignment->programexceptionid) &&
                    $userassignment->programexceptiontimeraised == $userassignment->timeassigned) {
                    // This exception was raised the first time they were assigned, meaning they haven't received
                    // an assignment message yet.
                    $sendmessage = true;
                }

                // Skip completed programs (includes certifications which are certified and window is not yet open).
                if ($completionrecord->status == program::STATUS_PROGRAM_COMPLETE) {
                    continue;
                }

                // Skip certifications which are on the recert path or are expired.
                if (!empty($this->program->certifid)) {
                    if ($completionrecord->certifpath == CERTIFPATH_RECERT ||
                        $completionrecord->certifstatus == CERTIFSTATUS_EXPIRED) {
                        continue;
                    }
                }

                // Make sure that the exceptionstatus property is present (protection against a previous bug).
                if (!isset($userassignment->exceptionstatus)) {
                    throw new \coding_exception('The property "exceptionstatus" is missing.');
                }

                if ($timedue > $completionrecord->timedue || ($timedue === false && (int)$completionrecord->timedue === -1)) {
                    // The timedue has increased, we'll need to update it and check for exceptions.

                    // If it currently has no timedue then we need to check exceptions.
                    // If there was a previously unresolved or dismissed exception then we need to recheck.
                    if ($completionrecord->timedue <= 0 ||
                        in_array($userassignment->exceptionstatus, array(program::PROGRAM_EXCEPTION_RAISED, program::PROGRAM_EXCEPTION_DISMISSED))) {
                        if ($this->program->update_exceptions($user->id, $assignment, $timedue)) {
                            $userassignment->exceptionstatus = program::PROGRAM_EXCEPTION_RAISED;
                        } else {
                            $userassignment->exceptionstatus = program::PROGRAM_EXCEPTION_NONE;
                        }
                        if ($userassignment->exceptionstatus == program::PROGRAM_EXCEPTION_RAISED) {
                            // Store raised exception status (was reset by update_exceptions).
                            $updateduserassignment = new \stdClass();
                            $updateduserassignment->id = $userassignment->id;
                            $updateduserassignment->exceptionstatus = program::PROGRAM_EXCEPTION_RAISED;
                            $DB->update_record('prog_user_assignment', $updateduserassignment);
                        }
                    }

                    // Update user's due date.
                    $completionrecord->timedue = $timedue; // Updates $allpreviousprogcompletions, for following assignments.
                    $this->program->set_timedue(
                        $user->id,
                        $timedue,
                        'Due date updated for existing program assignment in set_duedate'
                    );

                    if ($userassignment->exceptionstatus == program::PROGRAM_EXCEPTION_NONE && $sendmessage) {
                        // Trigger event for observers to deal with resolved exception from first assignment.
                        // We don't add this to the new assignments buffer because we're not creating a new assignment.
                        $updateassignusersbuffer[$user->id] = 0;
                    }
                } // Else no change or decrease, skipped. If we want to allow decrease then it should be added here.

            } else {
                // If the user is already complete, or has a timedue, skip checking for time allowance exceptions and carry on with assignments.
                if (!empty($completionrecord) && ($completionrecord->status == program::STATUS_PROGRAM_COMPLETE || $completionrecord->timedue > 0)) {
                    $exceptions = $this->program->update_exceptions($user->id, $assignment, assignments::COMPLETION_TIME_NOT_SET);
                } else {
                    $exceptions = $this->program->update_exceptions($user->id, $assignment, $timedue);
                }

                // Fix the timedue before we put it into the database. Empty includes \totara_program\assignments\assignments::COMPLETION_TIME_UNKNOWN, null, 0, ''.
                $timedue = empty($timedue) ? assignments::COMPLETION_TIME_NOT_SET : $timedue;
                $newassignusersbuffer[$user->id] = array('timedue' => $timedue, 'exceptions' => $exceptions);

                if (empty($completionrecord)) {
                    $newassignusersbuffer[$user->id]['needsupdateduedate'] = true;
                    // Maybe not needed as we are just setting duedates
                    $newassignusersbuffer[$user->id]['needscompletionrecord'] = true;
                } else if ($timedue > $completionrecord->timedue) {
                    // Update user's due date.
                    $this->program->set_timedue($user->id, $timedue, 'Due date updated for new program assignment');
                }
            }
        }

        $context = \context_program::instance($this->program->id); // Used for events.

        // Flush future user assignments after program assignment loop finished.
        if (!empty($futureassignments)) {
            $this->program->create_future_assignments_bulk($this->program->id, $futureassignments, $this->id);
            unset($futureassignments);
        }

        // Flush new user assignments after program assignment loop finished.
        if (!empty($newassignusersbuffer) || !empty($updateassignusersbuffer)) {
            $eventdata = array('other' => array('programid' => $this->program->id, 'assignmentid' => $this->id));
            bulk_learner_assignments_started::create_from_data($eventdata)->trigger();

            // We need to do this after every program assignment so that the records will exist and be updated in case
            // the same user is present in a following assignment.
            if (!empty($newassignusersbuffer)) {
                $this->program->assign_learners_bulk($newassignusersbuffer, $assignment);
            }

            // Both new and updated user assignments need to trigger the program_assigned event (note "+" to preserve keys).
            $allassignusers = $newassignusersbuffer + $updateassignusersbuffer;

            // Trigger each individual event.
            // If this is a certification, certification_event_handler creates the certif_completion records.
            foreach ($allassignusers as $userid => $data) {
                $other = [];
                if ($is_certification && !empty($data['timedue']) && !empty($data['needsupdateduedate'])) {
                    $other = [
                        'timedue' => $data['timedue'],
                        'needsupdateduedate' => true,
                    ];
                }
                $event = program_assigned::create(
                    array(
                        'objectid' => $this->program->id,
                        'context' => $context,
                        'userid' => $userid,
                        'other' => $other,
                    )
                );
                $event->trigger();
            }

            bulk_learner_assignments_ended::create()->trigger();

            // Update completion of all just-assigned users. This will mark them complete if they have already completed
            // all program content, or else create the first non-0 course set record (with course set group timedue).
            foreach ($allassignusers as $userid => $data) {
                prog_update_completion($userid, $this->program);
            }

            unset($newassignusersbuffer);
        }
    }

    /**
     * Get date string given the event
     *
     * @return string
     */
    private function get_completion_string(): string {
        if ((int)$this->completionevent !== assignments::COMPLETION_EVENT_NONE) {
            $class = assignments::COMPLETION_EVENTS_CLASSNAMES[$this->completionevent];

            $eventobject = new $class;

            $a = new \stdClass();
            if ($this->completionoffsetamount !== null) {
                $a->num = $this->completionoffsetamount;
                $a->period = get_string(utils::$timeallowancestrings[$this->completionoffsetunit], 'totara_program');
            } else {
                return '';
            }
            $a->event = $eventobject->get_completion_string();
            $a->instance = $eventobject->get_item_name($this->completioninstance);

            if (!empty($a->instance)) {
                $a->instance = "'$a->instance'";
            }

            $date_string = get_string('completewithinevent', 'totara_program', $a);
        } else {
            if ($this->completiontime == assignments::COMPLETION_TIME_NOT_SET) {
                $date_string = '';
            } else {
                $timestamp = $this->completiontime;
                $completiontimestring = userdate($timestamp, get_string('strfdateattime', 'langconfig'));

                $date_string = get_string('completebytime', 'totara_program', $completiontimestring);
            }
        }

        return $date_string;
    }

    /**
     * Create prog_completion records for new assignments
     * no due dates are set when we first create assignment records
     * so the logic is quite simple.
     *
     */
    public function create_user_assignment_records() {
        global $DB;

        // Create a dummy assignment object to use in this function.
        $progassignment = new \stdClass();
        $progassignment->id = $this->id;
        $progassignment->programid = $this->programid;
        $progassignment->assignmenttype = $this->typeid;
        $progassignment->assignmenttypeid = $this->instanceid;
        $progassignment->completionevent = $this->completionevent;
        $progassignment->completiontime = $this->completiontime;
        $progassignment->completionoffsetamount = $this->completionoffsetamount;
        $progassignment->completionoffsetunit = $this->completionoffsetunit;
        $progassignment->includechildren = $this->includechildren;
        $progassignment->timedue = -1; // No due time for new records

        $this->ensure_category_loaded();
        $this->ensure_program_loaded();

        // Get users who are affected by this assignment
        $affectedusers = $this->category->get_affected_users_by_assignment($progassignment);
        if (count($affectedusers) == 0) {
            // Nothing to do
            return;
        } else if (count($affectedusers) > program::PROG_UPDATE_ASSIGNMENTS_DEFER_COUNT) {
            // Set deferred and return
            $DB->set_field('prog', 'assignmentsdeferred', 1, array('id' => $this->program->id));
            return;
        }

        // Get array of userids
        $affecteduserids = array_map(function($o) { return $o->id; }, $affectedusers);

        // Find out if users already have a completion record for this
        // program (via another assignment type)
        $sql = "SELECT userid FROM {prog_completion} WHERE programid = :programid AND coursesetid = 0";
        $params = ['programid' => $this->programid];
        $existingcompletion = $DB->get_records_sql($sql, $params);
        $existinguserids = array_map(function($o) { return $o->userid; }, $existingcompletion);

        // Calculate who needs completion records
        $requiredusers = array_diff($affecteduserids, $existinguserids);

        // Timedue is not set for new records.
        $timedue = -1;

        $users = [];
        $existing_user_assignments = $DB->get_records('prog_user_assignment', ['programid' => $this->programid, 'assignmentid' => $this->id], '', 'userid');

        foreach ($affecteduserids as $userid) {
            if (!empty($existing_user_assignments[$userid])) {
                // Existing user assignments so skip
                continue;
            }

            $exceptions = $this->program->update_exceptions($userid, $progassignment, $timedue);

            if (in_array($userid, $requiredusers)) {
                $users[$userid] = ['timedue' => $timedue, 'exceptions' => $exceptions, 'needscompletionrecord' => true];
            } else {
                $users[$userid] = ['timedue' => $timedue, 'exceptions' => $exceptions];
            }
        }

        unset($existing_user_assignments);

        // Create completion and user_assignment records
        $this->program->assign_learners_bulk($users, $progassignment);

        // Load context for event trigger
        $context = \context_program::instance($this->programid); // Used for events.

        // Must be run after assigning learners
        foreach ($affecteduserids as $userid) {
            // Trigger event to create completion records for certifications
            $event = program_assigned::create(
                array(
                    'objectid' => $this->programid,
                    'context' => $context,
                    'userid' => $userid,
                )
            );
            $event->trigger();
        }
    }

    /**
     * When we assign a group of users to a program, we need to create an
     * event to show that that group has been added. This method should
     * be inherited by this class's children to send the appropriate
     * event.
     *
     * @return void
     */
    protected function trigger_assignment_event(): void {}

    /**
     * When we remove a group of users from a program, we need to create an
     * event to show that that group has been unassigned. This method should
     * be inherited by this class's children to send the appropriate
     * event.
     *
     * @return void
     */
    protected function trigger_unassigned_event(): void {}
}
