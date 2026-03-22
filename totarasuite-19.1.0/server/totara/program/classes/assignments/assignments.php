<?php
/**
 * This file is part of Totara Perform
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Simon player <simon.player@totara.com>
 * @package totara_program
 */

namespace totara_program\assignments;

use stdClass;
use totara_program\assignments\categories\groups;
use totara_program\program;

class assignments {

    const ASSIGNTYPE_ORGANISATION = 1;
    const ASSIGNTYPE_POSITION = 2;
    const ASSIGNTYPE_COHORT = 3;
    const ASSIGNTYPE_INDIVIDUAL = 5;
    const ASSIGNTYPE_MANAGERJA = 6;
    const ASSIGNTYPE_PLAN = 7;
    const ASSIGNTYPE_GROUP = 8;

    const ASSIGNMENT_CATEGORY_CLASSNAMES = array(
        self::ASSIGNTYPE_ORGANISATION => 'organisations',
        self::ASSIGNTYPE_POSITION     => 'positions',
        self::ASSIGNTYPE_COHORT       => 'cohorts',
        self::ASSIGNTYPE_MANAGERJA    => 'managers',
        self::ASSIGNTYPE_INDIVIDUAL   => 'individuals',
        self::ASSIGNTYPE_PLAN         => 'plans',
        self::ASSIGNTYPE_GROUP        => 'groups',
    );

    const COMPLETION_TIME_NOT_SET = -1;
    const COMPLETION_TIME_UNKNOWN = 0;
    const COMPLETION_EVENT_NONE = 0;
    const COMPLETION_EVENT_FIRST_LOGIN = 1;
    const COMPLETION_EVENT_POSITION_ASSIGNED_DATE = 2;
    const COMPLETION_EVENT_PROGRAM_COMPLETION = 3;
    const COMPLETION_EVENT_COURSE_COMPLETION = 4;
    const COMPLETION_EVENT_PROFILE_FIELD_DATE = 5;
    const COMPLETION_EVENT_ENROLLMENT_DATE = 6;
    const COMPLETION_EVENT_POSITION_START_DATE = 7;

    const COMPLETION_EVENTS_CLASSNAMES = array(
        self::COMPLETION_EVENT_FIRST_LOGIN            => '\totara_program\assignments\completion_events\first_login',
        self::COMPLETION_EVENT_POSITION_ASSIGNED_DATE => '\totara_program\assignments\completion_events\position_assigned_date',
        self::COMPLETION_EVENT_POSITION_START_DATE    => '\totara_program\assignments\completion_events\position_start_date',
        self::COMPLETION_EVENT_PROGRAM_COMPLETION     => '\totara_program\assignments\completion_events\program_completion',
        self::COMPLETION_EVENT_COURSE_COMPLETION      => '\totara_program\assignments\completion_events\course_completion',
        self::COMPLETION_EVENT_PROFILE_FIELD_DATE     => '\totara_program\assignments\completion_events\profile_field_date',
        self::COMPLETION_EVENT_ENROLLMENT_DATE        => '\totara_program\assignments\completion_events\enrollment_date',
    );

    /**
     * The assignment records from the database.
     *
     * Prior to Totara 10 there was a protected assignments property.
     * This property was always populated during construction but not always used.
     * Do not change the scope from private, call $this->get_assignments() instead.
     *
     * @internal Never use this directly always use $this->get_assignments()
     * @var stdClass[]
     */
    private $assignmentrecords = null;

    /**
     * @var int
     */
    protected $programid;

    /**
     * Class prog_assignments constructor.
     *
     * @param int $programid
     */
    public function __construct($programid) {
        $this->programid = $programid;
    }

    /**
     * Ensures that assignments are loaded.
     */
    public function ensure_assignments_init() {
        if ($this->assignmentrecords === null) {
            $this->init_assignments();
        }
    }

    /**
     * Resets the assignments property so that it contains only the assignments
     * that are currently stored in the database. This is necessary after
     * assignments are updated
     */
    private function init_assignments() {
        global $DB;
        $this->assignmentrecords = $DB->get_records('prog_assignment', array('programid' => $this->programid));;
    }

    /**
     * Returns the assignments for this program.
     *
     * @return stdClass[]
     */
    public function get_assignments() {
        $this->ensure_assignments_init();
        return $this->assignmentrecords;
    }

    /**
     * Resets the program assignment records to ensure they are accurate.
     */
    public function reset() {
        $this->assignmentrecords = null;
    }

    /**
     * @param int $assignmenttype One of ASSIGNTYPE_*
     * @return \organisations_category|\positions_category|\cohorts_category|\managers_category|\individuals_category|\plans_category
     * @throws \Exception
     */
    public static function factory($assignmenttype) {
        if (!array_key_exists($assignmenttype, self::ASSIGNMENT_CATEGORY_CLASSNAMES)) {
            throw new \coding_exception('Assignment category type not found');
        }

        $classname = '\totara_program\assignments\categories\\' . self::ASSIGNMENT_CATEGORY_CLASSNAMES[$assignmenttype];
        if (class_exists($classname)) {
            return new $classname();
        } else {
            throw new \coding_exception('Assignment category class not found');
        }
    }

    /**
     * Deletes all the assignments and user assignments for this program
     *
     * @return bool true|Exception
     */
    public function delete() {
        global $DB;
        $transaction = $DB->start_delegated_transaction();

        // Delete group-specific data.
        groups::delete_by_program($this->programid);

        // delete all user assignments
        $DB->delete_records('prog_user_assignment', array('programid' => $this->programid));
        // also delete future user assignments
        $DB->delete_records('prog_future_user_assignment', array('programid' => $this->programid));
        // delete all configured assignments
        $DB->delete_records('prog_assignment', array('programid' => $this->programid));
        // delete all exceptions
        $DB->delete_records('prog_exception', array('programid' => $this->programid));

        $transaction->allow_commit();

        return true;
    }

    /**
     * Returns the number of assignments found for the current program
     * who dont have exceptions
     *
     * @return integer The number of user assignments
     */
    public function count_active_user_assignments() {
        global $DB;

        list($exception_sql, $params) = $DB->get_in_or_equal(array(program::PROGRAM_EXCEPTION_NONE, program::PROGRAM_EXCEPTION_RESOLVED));
        $params[] = $this->programid;

        $count = $DB->count_records_sql("SELECT COUNT(DISTINCT userid) FROM {prog_user_assignment} WHERE exceptionstatus {$exception_sql} AND programid = ?", $params);
        return $count;
    }

    /**
     * Returns the total user assignments for the current program
     *
     * @return integer The number of users assigned to the current program
     */
    public function count_total_user_assignments() : int {
        global $DB;

        // also include future assignments in total
        $sql = "SELECT COUNT(DISTINCT userid) FROM (SELECT userid FROM {prog_user_assignment} WHERE programid = ?
            UNION SELECT userid FROM {prog_future_user_assignment} WHERE programid = ?) q";
        $count = $DB->count_records_sql($sql, array($this->programid, $this->programid));

        return $count;
    }

    /**
     * Returns the number of users found for the current program
     * who have exceptions
     *
     * @return integer The number of users
     */
    public function count_user_assignment_exceptions() : int {
        global $DB;

        $sql = "SELECT COUNT(DISTINCT ex.userid)
                FROM {prog_exception} ex
                INNER JOIN {user} us ON us.id = ex.userid
                WHERE ex.programid = ? AND us.deleted = ?";
        return $DB->count_records_sql($sql, array($this->programid, 0));
    }

    /**
     * Returns an HTML string suitable for displaying as the element body
     * for the assignments in the program overview form
     *
     * @param program $program
     * @return string
     */
    public function display_form_element($program = null) : string {
        global $OUTPUT;

        $emptyarray = array(
            'typecount' => 0,
            'users'     => 0
        );

        $assignmentdata = array(
            self::ASSIGNTYPE_ORGANISATION => $emptyarray,
            self::ASSIGNTYPE_POSITION => $emptyarray,
            self::ASSIGNTYPE_COHORT => $emptyarray,
            self::ASSIGNTYPE_MANAGERJA => $emptyarray,
            self::ASSIGNTYPE_INDIVIDUAL => $emptyarray,
            self::ASSIGNTYPE_PLAN => $emptyarray,
            self::ASSIGNTYPE_GROUP => $emptyarray,
        );

        $out = '';

        $assignmentrecords = $this->get_assignments();
        if (count($assignmentrecords)) {

            $usertotal = 0;

            foreach ($assignmentrecords as $assignment) {
                $assignmentob = assignments::factory($assignment->assignmenttype);

                $assignmentdata[$assignment->assignmenttype]['typecount']++;

                $users = $assignmentob->get_affected_users_by_assignment($assignment);
                $usercount = count($users);
                if ($users) {
                    $assignmentdata[$assignment->assignmenttype]['users'] += $usercount;
                }
                $usertotal += $usercount;
            }

            $table = new \html_table();
            $table->head = array(
                get_string('overview', 'totara_program'),
                get_string('numlearners', 'totara_program')
            );
            $table->data = array();

            $categoryrow = 0;
            foreach ($assignmentdata as $categorytype => $data) {
                $categorstr = self::ASSIGNMENT_CATEGORY_CLASSNAMES[$categorytype] . '_category';

                $styleclass = ($categoryrow % 2 == 0) ? 'even' : 'odd';

                $row = array();
                $row[] = $data['typecount'] . ' ' . get_string($categorstr, 'totara_program');
                $row[] = $data['users'];

                $table->data[] = $row;
                $table->rowclass[] = $styleclass;

                $categoryrow++;
            }

            $out .= $OUTPUT->render($table);

        } else {
            $out .= get_string('noprogramassignments', $program && $program->is_certif() ? 'totara_certification' : 'totara_program');
        }

        return $out;
    }

    /**
     * Returns an HTML string suitable for displaying as the label for the
     * assignments in the program overview form
     *
     * @param program $program
     * @return string
     */
    public function display_form_label(program $program = null) : string {
        $out = '';
        $out .= get_string('instructions:assignments1', $program && $program->is_certif() ? 'totara_certification' : 'totara_program');
        return $out;
    }

    /**
     * Returns the script to be run when a specific completion event is chosen
     *
     * @param string $name
     * @return string
     */
    public static function get_completion_events_script($name="eventtype", $programid = null) : string {
        $out = '';

        $out .= "
            function handle_completion_selection() {
                var eventselected = $('#eventtype option:selected').val();
                eventid = eventselected;
        ";

        // Get the script that should be run if we select a specific event
        foreach (self::COMPLETION_EVENTS_CLASSNAMES as $class) {
            $event = new $class($programid);
            $out .= "if (eventid == ". $event->get_id() .") { " . $event->get_script() . " }";
        }

        $out .= "
            };
        ";

        return $out;
    }

    public static function get_confirmation_template() {
        global $OUTPUT;

        $table = new \html_table();
        $table->head = array('', get_string('added', 'totara_program'), get_string('removed', 'totara_program'));
        $table->data = array();
        foreach (self::ASSIGNMENT_CATEGORY_CLASSNAMES as $classname) {
            $classname = '\totara_program\assignments\categories\\' . $classname;

            if (!$classname::show_in_ui()) {
                continue;
            }
            $category = new $classname();
            $spanadded = \html_writer::tag('span', '0', array('class' => 'added_'.$category->id));
            $spanremoved = \html_writer::tag('span', '0', array('class' => 'removed_'.$category->id));
            $table->data[] = array($category->name, $spanadded, $spanremoved);
        }

        $tableHTML = $OUTPUT->render($table);
        // Strip new lines as they screw up the JS
        $order   = array("\r\n", "\n", "\r");
        $table = str_replace($order, '', $tableHTML);

        $data = array();
        $data['html'] = \html_writer::tag('div', get_string('youhavemadefollowingchanges', 'totara_program') . html_writer::empty_tag('br') . html_writer::empty_tag('br') . $tableHTML . html_writer::empty_tag('br') . get_string('tosaveassignments','totara_program'));

        return json_encode($data);
    }
}