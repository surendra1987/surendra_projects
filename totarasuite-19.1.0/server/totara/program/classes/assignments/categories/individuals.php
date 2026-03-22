<?php
/**
 * This file is part of Totara Learn
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
 * @author Simon Player <simon.player@totara.com>
 * @package totara_program
 */

namespace totara_program\assignments\categories;

use totara_program\assignments\assignments;
use totara_program\assignments\category;
use totara_program\program;

/**
 * Individuals category of program assignment.
 */
class individuals extends category {

    function __construct() {
        $this->id = assignments::ASSIGNTYPE_INDIVIDUAL;
        $this->name = get_string('individuals', 'totara_program');
        $this->buttonname = get_string('addindividualstoprogram', 'totara_program');
    }

    /**
     * @inheritdoc
     */
    public function build_table($programidorinstance): void {
        global $DB, $OUTPUT;

        if (is_numeric($programidorinstance)) {
            $program = new program($programidorinstance);
        } else if (get_class($programidorinstance) === 'program') {
            $program = $programidorinstance;
        } else {
            throw new \coding_exception('programidorinstance must be a program id (integer) or instance of program class');
        }

        $this->headers = array(
            get_string('individualname', 'totara_program'),
            get_string('userid', 'totara_program'),
            get_string('assignmentduedate', 'totara_program') .
            $OUTPUT->help_icon('assignmentduedate', 'totara_program', null),
            get_string('actualduedate', 'totara_program') .
            $OUTPUT->help_icon('individualactualduedate', 'totara_program', null),
        );

        // Go to the database and gets the assignments.

        $usernamefields = get_all_user_name_fields(true, 'individual');
        $items = $DB->get_records_sql(
            "SELECT individual.id, " . $usernamefields . ", prog.id AS progid, prog.certifid, pc.timedue, pc.status AS progstatus,
                    cc.certifpath, cc.renewalstatus,
                    prog_assignment.completiontime, prog_assignment.completionevent, prog_assignment.completioninstance,
                    prog_assignment.completionoffsetamount, prog_assignment.completionoffsetunit
               FROM {prog_assignment} prog_assignment
               JOIN {user} individual
                 ON individual.id = prog_assignment.assignmenttypeid
               JOIN {prog} prog
                 ON prog.id = prog_assignment.programid
          LEFT JOIN {prog_completion} pc
                 ON pc.programid = prog_assignment.programid AND pc.userid = individual.id AND pc.coursesetid = 0
          LEFT JOIN {certif_completion} cc
                 ON cc.certifid = prog.certifid AND cc.userid = pc.userid
              WHERE prog_assignment.programid = ?
                AND prog_assignment.assignmenttype = ?", array($program->id, $this->id));

        // Convert these into html.
        if (!empty($items)) {
            foreach ($items as $item) {
                $item->fullname = fullname($item);
                $this->data[] = $this->build_row($item, !$program->has_expired());
            }
        }
    }

    /**
     * Gets a user by id
     *
     * @param int $itemid
     * @return false|\stdClass
     */
    public function get_item($itemid) {
        global $DB;

        $usernamefields = get_all_user_name_fields(true);
        $item = $DB->get_record_select('user',"id = ?", array($itemid), 'id, ' . $usernamefields);
        $item->fullname = fullname($item);

        return $item;
    }

    /**
     * @inheritdoc
     */
    public function build_row($item, bool $canupdate = true): array {
        if (is_int($item)) {
            $item = $this->get_item($item);
        }

        $row = array();
        $row[] = $this->build_first_table_cell($item->fullname, $this->id, $item->id, $canupdate);
        $row[] = $item->id;
        if (isset($item->progid)) {
            $isprog = empty($item->certifid);
            if ($isprog && $item->progstatus == program::STATUS_PROGRAM_COMPLETE) {
                // Program which is complete.
                $row[] = get_string('timeduefixedprog', 'totara_program');
                if (empty($item->timedue) || $item->timedue == assignments::COMPLETION_TIME_NOT_SET) {
                    $row[] = get_string('noduedate', 'totara_program');
                } else {
                    $row[] = trim(userdate($item->timedue, get_string('strfdateattime', 'langconfig'), 99, false));
                }
            } else if (!$isprog && ($item->certifpath == CERTIFPATH_RECERT || $item->renewalstatus == CERTIFRENEWALSTATUS_EXPIRED)) {
                // Certification which is complete.
                $row[] = get_string('timeduefixedcert', 'totara_program');
                if (empty($item->timedue) || $item->timedue == assignments::COMPLETION_TIME_NOT_SET) {
                    $row[] = get_string('noduedate', 'totara_program');
                } else {
                    $row[] = trim(userdate($item->timedue, get_string('strfdateattime', 'langconfig'), 99, false));
                }
            } else if (empty($item->timedue) || $item->timedue == assignments::COMPLETION_TIME_NOT_SET) {
                // No date set.
                $row[] = $this->get_completion($item, $item->progid, $canupdate);
                if ($item->completionevent == assignments::COMPLETION_EVENT_NONE) {
                    $row[] = get_string('noduedate', 'totara_program');
                } else {
                    $row[] = get_string('notyetknown', 'totara_program');
                }
            } else {
                // Date set.
                $row[] = $this->get_completion($item, $item->progid, $canupdate);
                $row[] = trim(userdate($item->timedue,
                    get_string('strfdateattime', 'langconfig'), 99, false));
            }
        } else {
            // New individual assignment.
            $item->completiontime = assignments::COMPLETION_TIME_NOT_SET;
            $item->completionevent = assignments::COMPLETION_EVENT_NONE;
            $row[] = $this->get_completion($item, null, $canupdate);
            $row[] = get_string('notyetset', 'totara_program');
        }

        return $row;
    }

    /**
     * @inheritdoc
     */
    public function user_affected_count($item) {
        return 1;
    }

    /**
     * @inheritdoc
     */
    public function get_affected_users($item, int $userid = 0, bool $count = false) {
        $user = (object)array('id'=>$item->assignmenttypeid);
        return $count ? 1 : array($user);
    }

    /**
     * @inheritdoc
     */
    public function get_affected_users_by_assignment($assignment, int $userid = 0) {
        return $this->get_affected_users($assignment, $userid);
    }

    /**
     * @inheritdoc
     */
    public function get_includechildren($data, $object): int {
        return 0;
    }

    /**
     * @inheritdoc
     */
    public function get_js(int $programid): string {
        $title = addslashes_js(get_string('addindividualstoprogram', 'totara_program'));
        $url = 'find_individual.php?programid='.$programid;
        return "M.totara_programassignment.add_category({$this->id}, 'individuals', '{$url}', '{$title}');";
    }
}