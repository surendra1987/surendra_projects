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
 * Positions category of program assignment.
 */
class positions extends category {

    function __construct() {
        $this->id = assignments::ASSIGNTYPE_POSITION;
        $this->name = get_string('positions', 'totara_program');
        $this->buttonname = get_string('addpositiontoprogram', 'totara_program');
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
            get_string('positionsname', 'totara_program'),
            get_string('allbelow', 'totara_program'),
            get_string('assignmentduedate', 'totara_program') .
            $OUTPUT->help_icon('assignmentduedate', 'totara_program', null));

        if (!$program->has_expired()) {
            $this->headers[] = get_string('actualduedate', 'totara_program') .
                $OUTPUT->help_icon('groupactualduedate', 'totara_program', null);
            $this->headers[] = get_string('numlearners', 'totara_program');
        }

        // Go to the database and gets the assignments
        $items = $DB->get_records_sql(
            "SELECT pos.id, pos.fullname, pos.path,
                    prog_assignment.programid, prog_assignment.id AS assignmentid,
                    prog_assignment.includechildren, prog_assignment.completiontime,
                    prog_assignment.completionevent, prog_assignment.completioninstance,
                    prog_assignment.completionoffsetamount, prog_assignment.completionoffsetunit
               FROM {prog_assignment} prog_assignment
         INNER JOIN {pos} pos on pos.id = prog_assignment.assignmenttypeid
              WHERE prog_assignment.programid = ?
                AND prog_assignment.assignmenttype = ?", array($program->id, $this->id));

        // Convert these into html
        foreach ($items as $item) {
            $this->data[] = $this->build_row($item, !$program->has_expired());
        }
    }

    /**
     * Gets a position by id
     *
     * @param int $itemid
     * @return false|\stdClass
     */
    public function get_item($itemid) {
        global $DB;
        return $DB->get_record('pos', array('id' => $itemid));
    }

    /**
     * @inheritdoc
     */
    public function build_row($item, bool $canupdate = true): array {
        if (is_int($item)) {
            $item = $this->get_item($item);
        }

        $checked = (isset($item->includechildren) && $item->includechildren == 1) ? true : false;

        if (isset($item->programid)) {
            $programid = $item->programid;
        } else  {
            $programid = null;
        }

        $row = array();
        $row[] = $this->build_first_table_cell($item->fullname, $this->id, $item->id, $canupdate);

        if ($canupdate) {
            $row[] = \html_writer::checkbox('includechildren[' . $this->id . '][' . $item->id . ']', '', $checked);
        } else {
            $row[] = \html_writer::checkbox('includechildren[' . $this->id . '][' . $item->id . ']', '', $checked, '', array('disabled' => 'disabled'));
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

        $where = "ja.positionid = ?";
        $params = array($item->id);
        if (isset($item->includechildren) && $item->includechildren == 1 && isset($item->path)) {
            $children = $DB->get_fieldset_select('pos', 'id', $DB->sql_like('path', '?'), array($item->path . '/%'));
            $children[] = $item->id;
            // Replace the existing $params.
            list($usql, $params) = $DB->get_in_or_equal($children);
            $where = "ja.positionid $usql";
        }

        $select = $count ? 'COUNT(DISTINCT u.id)' : 'DISTINCT u.id';

        $sql = "SELECT $select
                FROM {job_assignment} ja
                INNER JOIN {user} u ON ja.userid = u.id
                WHERE $where
                AND u.deleted = 0";
        if ($userid) {
            $sql .= " AND u.id = ?";
            $params[] = $userid;
        }
        if ($count) {
            return $DB->count_records_sql($sql, $params);
        }
        else {
            return $DB->get_records_sql($sql, $params);
        }
    }

    /**
     * @inheritdoc
     */
    public function get_affected_users_by_assignment($assignment, int $userid = 0) {
        global $DB;

        // Query to retrieve the data required to determine the number of users
        // affected by an assignment.
        $sql = "SELECT pos.id,
                        pos.fullname,
                        pos.path,
                        prog_assignment.includechildren,
                        prog_assignment.completiontime,
                        prog_assignment.completionevent,
                        prog_assignment.completioninstance,
                        prog_assignment.completionoffsetamount,
                        prog_assignment.completionoffsetunit
                FROM {prog_assignment} prog_assignment
                INNER JOIN {pos} pos on pos.id = prog_assignment.assignmenttypeid
                WHERE prog_assignment.id = ?";

        if ($item = $DB->get_record_sql($sql, array($assignment->id))) {
            return $this->get_affected_users($item, $userid);
        } else {
            return array();
        }
    }

    /**
     * @inheritdoc
     */
    function get_includechildren($data, $object): int {
        if (!isset($data->includechildren[$this->id][$object->assignmenttypeid])) {
            return 0;
        } else {
            return 1;
        }
    }

    /**
     * @inheritdoc
     */
    function get_js($programid): string {
        $title = addslashes_js(get_string('addpositiontoprogram', 'totara_program'));
        $url = 'find_hierarchy.php?type=position&programid='.$programid;
        return "M.totara_programassignment.add_category({$this->id}, 'positions', '{$url}', '{$title}');";
    }
}