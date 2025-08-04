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

namespace totara_program\user_assignment;

use totara_program\assignments\assignments;
use totara_program\exception\user_assignment as user_assignment_exception;

/**
 * An abstract class that represents the generalised information of an assignment that can belong to a user in a program
 * This class is inherited by the specific types of assignment, as dictated in the assignments enum.
 */
abstract class user_assignment {

    const USER_ASSIGNMENT_CLASSNAMES = array(
        assignments::ASSIGNTYPE_ORGANISATION => 'totara_program\user_assignment\categories\organisations',
        assignments::ASSIGNTYPE_POSITION     => 'totara_program\user_assignment\categories\positions',
        assignments::ASSIGNTYPE_COHORT       => 'totara_program\user_assignment\categories\cohorts',
        assignments::ASSIGNTYPE_MANAGERJA    => 'totara_program\user_assignment\categories\managers',
        assignments::ASSIGNTYPE_INDIVIDUAL   => 'totara_program\user_assignment\categories\individuals',
        assignments::ASSIGNTYPE_PLAN         => 'totara_program\user_assignment\categories\plans',
        assignments::ASSIGNTYPE_GROUP        => 'totara_program\user_assignment\categories\groups'
    );

    /** @var int $id */
    protected $id;
    /** @var int $programid */
    protected $programid;
    /** @var int $userid */
    protected $userid;
    /** @var int $assignmentid */
    protected $assignmentid;
    /** @var int $timeassigned */
    protected $timeassigned;
    /** @var false|mixed|\stdClass $assignment */
    protected $assignment;

    /**
     * @param int $id
     */
    public function __construct(int $id) {
        global $DB;
        // get user assignment db record
        $userassignment = $DB->get_record('prog_user_assignment', array('id' => $id));

        if (!$userassignment) {
            throw new user_assignment_exception('User assignment record not found');
        }

        // set details about this user assignment
        $this->id = $id;
        $this->programid = $userassignment->programid;
        $this->userid = $userassignment->userid;
        $this->assignmentid = $userassignment->assignmentid;
        $this->timeassigned = $userassignment->timeassigned;

        $this->assignment = $DB->get_record('prog_assignment', array('id' => $userassignment->assignmentid));
        if (!$this->assignment) {
            throw new user_assignment_exception(get_string('error:assignmentnotfound', 'totara_program'));
        }
    }

    /**
     * Create a child instance of this class, as dictated by the assignment type enum.
     *
     * @param int $assignmenttype
     * @param int $assignmentid
     * @return user_assignment
     */
    public static function factory(int $assignmenttype, int $assignmentid): user_assignment {
        if (!array_key_exists($assignmenttype, self::USER_ASSIGNMENT_CLASSNAMES)) {
            throw new user_assignment_exception(get_string('error:userassignmenttypenotfound', 'totara_program'));
        }

        if (class_exists(self::USER_ASSIGNMENT_CLASSNAMES[$assignmenttype])) {
            $classname = self::USER_ASSIGNMENT_CLASSNAMES[$assignmenttype];
            return new $classname($assignmentid);
        } else {
            throw new user_assignment_exception(get_string('error:userassignmentclassnotfound', 'totara_program'));
        }
    }

    /**
     * Creates HTML block detailing important information about the user assignment (cohort, individual, etc.)
     */
    abstract public function display_criteria(): string;

    /**
     * Display a date as text
     *
     * @param int $timestamp
     * @return string
     */
    public function display_date_as_text(int $timestamp): string {
        if (isset($timestamp)) {
            return userdate($timestamp, get_string('strftimedate', 'langconfig'), 99, false);
        } else {
            return '';
        }
    }

    /**
     * Convenience function to return a list of assignments for a particular
     * program and user
     *
     * @param int $programid
     * @param int $userid
     * @return array
     */
    public static function get_user_assignments(int $programid, int $userid): array {
        global $DB;
        return $DB->get_records_select('prog_user_assignment', "programid = ? AND userid = ?", array($programid, $userid));
    }

}
