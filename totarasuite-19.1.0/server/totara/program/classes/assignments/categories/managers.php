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
 * Managers category of program assignment.
 */
class managers extends category {

    function __construct() {
        $this->id = assignments::ASSIGNTYPE_MANAGERJA;
        $this->name = get_string('managementhierarchy', 'totara_program');
        $this->buttonname = get_string('addmanagerstoprogram', 'totara_program');
    }

    /**
     * @inheritdoc
     */
    public function build_table($programidorinstance): void {
        global $DB, $OUTPUT, $CFG;
        require_once($CFG->dirroot . '/totara/job/lib.php');

        if (is_numeric($programidorinstance)) {
            $program = new program($programidorinstance);
        } else if (get_class($programidorinstance) === 'program') {
            $program = $programidorinstance;
        } else {
            throw new \coding_exception('programidorinstance must be a program id (integer) or instance of program class');
        }

        $this->headers = array(
            get_string('managername', 'totara_program'),
            get_string('for', 'totara_program'),
            get_string('assignmentduedate', 'totara_program') .
            $OUTPUT->help_icon('assignmentduedate', 'totara_program', null));

        if (!$program->has_expired()) {
            $this->headers[] = get_string('actualduedate', 'totara_program') .
                $OUTPUT->help_icon('groupactualduedate', 'totara_program', null);
            $this->headers[] = get_string('numlearners', 'totara_program');
        }

        // Go to the database and gets the assignments.
        $usernamefields = get_all_user_name_fields(true, 'u');
        $items = $DB->get_records_sql("
            SELECT ja.id, " . $usernamefields . ", ja.managerjapath AS path, ja.fullname AS jobname, ja.idnumber, u.id AS userid,
                   prog_assignment.programid, prog_assignment.id AS assignmentid,
                   prog_assignment.includechildren, prog_assignment.completiontime,
                   prog_assignment.completionevent, prog_assignment.completioninstance,
                   prog_assignment.completionoffsetamount, prog_assignment.completionoffsetunit
              FROM {prog_assignment} prog_assignment
        INNER JOIN {job_assignment} ja ON ja.id = prog_assignment.assignmenttypeid
         LEFT JOIN {user} u ON u.id = ja.userid
             WHERE prog_assignment.programid = ?
               AND prog_assignment.assignmenttype = ?
        ", array($program->id, $this->id));

        // Convert these into html.
        if (!empty($items)) {
            foreach ($items as $item) {
                $job = clone($item);
                $job->fullname = $item->jobname;
                $item->fullname = totara_job_display_user_job($item, $job);
                //sometimes a manager may not have a job_assignment record e.g. top manager in the tree
                //so we need to set a default path
                if (empty($item->path)) {
                    $item->path = '/' . $item->id;
                }
                $this->data[] = $this->build_row($item, !$program->has_expired());
            }
        }
    }

    /**
     * Gets a manager by job assignment id
     *
     * @param int $itemid
     * @return false|\stdClass
     */
    public function get_item($itemid) {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/totara/job/lib.php');

        $usernamefields = get_all_user_name_fields(true, 'u');
        $sql = "SELECT ja.id, ja.idnumber, ja.fullname as jobname, " . $usernamefields . ", ja.managerjapath AS path
                  FROM {user} AS u
             LEFT JOIN {job_assignment} ja ON u.id = ja.userid
                 WHERE ja.id = ?";
        // Sometimes a manager may not have a job_assignment record e.g. top manager in the tree
        // so we need to set a default path.
        $item = $DB->get_record_sql($sql, array($itemid));
        $job = clone($item);
        $job->fullname = $item->jobname;
        $item->fullname = totara_job_display_user_job($item, $job);
        if (empty($item->path)) {
            $item->path = "/{$itemid}";
        }
        return $item;
    }

    /**
     * @inheritdoc
     */
    public function build_row($item, bool $canupdate = true): array {
        if (is_int($item)) {
            $item = $this->get_item($item);
        }

        if (isset($item->programid)) {
            $programid = $item->programid;
        } else  {
            $programid = null;
        }

        $selectedid = (isset($item->includechildren) && $item->includechildren == 1) ? 1 : 0;
        $options = array(
            0 => get_string('directteam', 'totara_program'),
            1 => get_string('allbelowlower', 'totara_program'));

        $row = array();
        $row[] = $this->build_first_table_cell($item->fullname, $this->id, $item->id, $canupdate);

        if ($canupdate) {
            $row[] = \html_writer::select($options, 'includechildren[' . $this->id . '][' . $item->id . ']', $selectedid);
        } else {
            $row[] = \html_writer::span($options[$selectedid]);
        }

        $row[] = $this->get_completion($item, $programid, $canupdate);

        if ($canupdate) {
            if (isset($item->programid)) {
                $viewsql = new \moodle_url('/totara/program/assignment/duedates_report.php',
                    array('programid' => $item->programid, 'assignmentid' => $item->assignmentid));
                $row[] = \html_writer::link($viewsql, get_string('viewdates', 'totara_program'),
                    array('class' => 'assignment-duedates'));
            } else {
                $row[] = get_string('notyetset', 'totara_program');
            }
            $row[] = $this->user_affected_count($item);
        }

        return $row;
    }

    /**
     * @inheritdoc
     */
    public function user_affected_count($item) {
        return $this->get_affected_users($item, 0, true);
    }

    /**
     * @inheritdoc
     */
    public function get_affected_users($item, int $userid = 0, bool $count = false) {
        global $DB;

        if (isset($item->includechildren) && $item->includechildren == 1 && isset($item->path)) {
            // For a manager's entire team.
            $where = $DB->sql_like('ja.managerjapath', '?');
            $path = $DB->sql_like_escape($item->path);
            $params = array($path . '/%');
        } else {
            // For a manager's direct team.
            $where = "ja.managerjaid = ?";
            $params = array($item->id);
        }

        $select = $count ? 'COUNT(DISTINCT ja.userid) AS id' : 'DISTINCT ja.userid AS id';

        $sql = "SELECT $select
                FROM {job_assignment} ja
                INNER JOIN {user} u ON (ja.userid = u.id AND u.deleted = 0)
                WHERE {$where}";
        if ($userid) {
            $sql .= " AND u.id = ?";
            $params[] = $userid;
        }

        if ($count) {
            return $DB->count_records_sql($sql, $params);
        } else {
            return $DB->get_records_sql($sql, $params);
        }
    }

    /**
     * @inheritdoc
     */
    public function get_affected_users_by_assignment($assignment, int $userid = 0) {
        global $DB;

        // Query to retrieves the data required to determine the number of users
        // affected by an assignment.
        $sql = "SELECT DISTINCT ja.id,
                       ja.managerjapath AS path,
                       prog_assignment.includechildren
                  FROM {prog_assignment} prog_assignment
             LEFT JOIN {job_assignment} ja ON prog_assignment.assignmenttypeid = ja.id
                 WHERE prog_assignment.id = ?";

        if ($item = $DB->get_record_sql($sql, array($assignment->id))) {
            // Sometimes a manager may not have a job_assignment record e.g. top manager in the tree.
            // So we need to set a default path.
            if (empty($item->path)) {
                $item->path = "/{$item->id}";
            }
            return $this->get_affected_users($item, $userid);
        } else {
            return array();
        }
    }

    /**
     * @inheritdoc
     */
    public function get_includechildren($data, $object): int {
        if (empty($data->includechildren[$this->id][$object->assignmenttypeid])) {
            return 0;
        } else {
            return 1;
        }
    }

    /**
     * @inheritdoc
     */
    public function get_js($programid): string {
        $title = addslashes_js(get_string('addmanagerstoprogram', 'totara_program'));
        $url = 'find_manager_hierarchy.php?programid='.$programid;
        return "M.totara_programassignment.add_category({$this->id}, 'managers', '{$url}', '{$title}');";
    }
}