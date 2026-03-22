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

use totara_program\assignments\categories\cohorts;
use totara_program\assignments\categories\groups;
use totara_program\assignments\categories\individuals;
use totara_program\assignments\categories\managers;
use totara_program\assignments\categories\organisations;
use totara_program\assignments\categories\plans;
use totara_program\assignments\categories\positions;
use totara_program\entity\program_assignment;
use totara_program\program;
use totara_program\exception\manager as exception_manager;
use totara_program\progress\program_progress_cache;
use totara_program\utils;

/**
 * Abstract class for a category which appears on the program assignments screen. Implemented by categories/ classes.
 */
abstract class category {
    /** @var int */
    public $id;

    /** @var string */
    public $name = '';

    /** @var string */
    public $table = '';

    /** @var string */
    protected $buttonname = '';

    /** @var array */
    protected $headers = array(); // array of headers as strings?

    /** @var array  */
    protected $data = array(); // array of arrays of strings (html)

    /**
     * Prints out the actual html for the category, by looking at the headers
     * and data which should have been set by sub class
     *
     * @param bool $canadd If the group can have data added to it or not.
     * @return string html
     */
    public function display(bool $canadd = true): string {
        global $PAGE;
        $renderer = $PAGE->get_renderer('totara_program');
        return $renderer->assignment_category_display($this, $this->headers, $this->buttonname, $this->data, $canadd);
    }

    /**
     * Checks whether this category has any items by looking
     *
     * @return int
     */
    public function has_items(): int {
        return count($this->data);
    }

    /**
     * Builds the table that appears for this category by filling $this->headers
     * and $this->data
     *
     * @param int|program $programidorinstance - id or instance of the program.
     *    Instance of program accepted since 10 (prior to this, only int was accepted).
     * @return void
     */
    abstract public function build_table($programidorinstance): void;

    /**
     * Builds a single row by looking at the passed in item
     *
     * @param object $item
     * @param bool $canupdate - true if user will be able to update data for this table.
     *   Since Totara 10.
     * @return array
     */
    abstract public function build_row($item, bool $canupdate = true): array;

    /**
     * Returns any javascript that should be loaded to be used by the category
     *
     * @param   int     $programid
     */
    abstract public function get_js(int $programid): string;

    /**
     * Gets the number of affected users
     *
     * @param object $item
     * @return array|int
     */
    abstract public function user_affected_count($item);

    /**
     * Gets the affected users for the given item record
     *
     * @param object $item An object containing data about the assignment
     * @param int $userid (optional) Only look at this user
     * @param bool $count Whether to return a count or not
     * @return array|int an array of user ids or else 0 if no users were found
     */
    abstract public function get_affected_users($item, int $userid = 0, bool $count = false);

    /**
     * Retrieves an array of all the users affected by an assignment based on the
     * assignment record
     *
     * @param object $assignment The db record from 'prog_assignment' for this assignment
     * @param int $userid (optional) only look at this user
     * @return array|int an array of user ids or else 0 if no users were found
     */
    abstract public function get_affected_users_by_assignment($assignment, int $userid = 0);

    /**
     * Updates the assignments by looking at the post data
     *
     * @param object $data  The data we will be updating assignments with
     * @param bool $delete  A flag to stop deletion/rebuild from external pages
     * @return void
     */
    public function update_assignments($data, bool $delete = true): void {
        global $DB;

        // Store list of seen ids
        $seenids = array();

        // Clear the completion caches in all cases
        if (isset($data->id)) {
            program_progress_cache::mark_program_cache_stale($data->id);
        }

        // If theres inputs for this assignment category (this)
        if (isset($data->item[$this->id])) {

            // Get the list of item ids
            $itemids = array_keys($data->item[$this->id]);
            $seenids = $itemids;

            $insertssql = array();
            $insertsparams = array();
            // Get a list of assignments
            $sql = "SELECT p.assignmenttypeid as hashkey, p.* FROM {prog_assignment} p WHERE programid = ? AND assignmenttype = ?";
            $assignment_hashmap = $DB->get_records_sql($sql, array($data->id, $this->id));

            foreach ($itemids as $itemid) {
                $object = isset($assignment_hashmap[$itemid]) ? $assignment_hashmap[$itemid] : false;
                if ($object !== false) {
                    $original_object = clone $object;
                }

                if (!$object) {
                    $object = new \stdClass(); //same for all cats
                    $object->programid = $data->id; //same for all cats
                    $object->assignmenttype = $this->id;
                    $object->assignmenttypeid = $itemid;
                }

                // Let the inheriting object deal with the include children field as it's specific to them
                $object->includechildren = $this->get_includechildren($data, $object);

                // Get the completion time.
                $object->completiontime = !empty($data->completiontime[$this->id][$itemid]) ?
                    $data->completiontime[$this->id][$itemid] : assignments::COMPLETION_TIME_NOT_SET;

                // Get the completion event.
                $object->completionevent = isset($data->completionevent[$this->id][$itemid]) ?
                    $data->completionevent[$this->id][$itemid] : assignments::COMPLETION_EVENT_NONE;

                // Get the completion time offset amount.
                $object->completionoffsetamount = isset($data->completionoffsetamount[$this->id][$itemid]) ?
                    $data->completionoffsetamount[$this->id][$itemid] : null;

                // Get the completion time offset unit.
                $object->completionoffsetunit = !empty($data->completionoffsetunit[$this->id][$itemid]) ?
                    $data->completionoffsetunit[$this->id][$itemid] : null;

                // Get the completion instance.
                $object->completioninstance = !empty($data->completioninstance[$this->id][$itemid]) ?
                    $data->completioninstance[$this->id][$itemid] : 0;

                if ($object->completiontime != assignments::COMPLETION_TIME_NOT_SET) {
                    if ($object->completionevent == assignments::COMPLETION_EVENT_NONE) {
                        // Convert fixed dates.
                        $hour = isset($data->completiontimehour[$this->id][$itemid]) ? sprintf("%02d", $data->completiontimehour[$this->id][$itemid]) : '00';
                        $minute = isset($data->completiontimeminute[$this->id][$itemid]) ? sprintf("%02d", $data->completiontimeminute[$this->id][$itemid]) : '00';
                        $object->completiontime = totara_date_parse_from_format(get_string('datepickerlongyearparseformat', 'totara_core').' H:i', $object->completiontime.' '.$hour.':'.$minute);
                    } else {
                        // Convert relative dates.
                        [$num, $period] = explode(' ', $object->completiontime);
                        if (!isset($num) || !isset($period)) {
                            continue;
                        }
                        $object->completiontime = null;
                        $object->completionoffsetamount = $num;
                        $object->completionoffsetunit = $period;
                    }
                } else {
                    if ($object->completionevent != assignments::COMPLETION_EVENT_NONE && isset($object->completionoffsetamount)) {
                        // Else it's a relative date and completiontime should be ignored.
                        $object->completiontime = null;
                    }
                }

                if (isset($object->id)) {
                    // Check if we actually need an update..
                    if ($original_object->includechildren != $object->includechildren ||
                        $original_object->completiontime != $object->completiontime ||
                        $original_object->completionoffsetamount != $object->completionoffsetamount ||
                        $original_object->completionoffsetunit != $object->completionoffsetunit ||
                        $original_object->completionevent != $object->completionevent ||
                        $original_object->completioninstance != $object->completioninstance) {

                        if (!$DB->update_record('prog_assignment', $object)) {
                            print_error('error:updatingprogramassignment', 'totara_program');
                        }
                    }
                } else {
                    // Create new assignment
                    $insertssql[] = "(?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $insertsparams[] = [
                        $object->programid,
                        $object->assignmenttype,
                        $object->assignmenttypeid,
                        $object->includechildren,
                        $object->completiontime,
                        $object->completionoffsetamount,
                        $object->completionoffsetunit,
                        $object->completionevent,
                        $object->completioninstance,
                    ];
                    $this->_add_assignment_hook($object);
                }
            }

            // Execute inserts
            if (count($insertssql) > 0) {
                $sql = "INSERT INTO {prog_assignment} (programid, assignmenttype, assignmenttypeid, includechildren,
                            completiontime, completionoffsetamount, completionoffsetunit, completionevent, completioninstance)
                            VALUES " . implode(', ', $insertssql);
                $params = array();
                foreach ($insertsparams as $p) {
                    $params = array_merge($params, $p);
                }
                $DB->execute($sql, $params);
            }
        }

        if ($delete) {
            // Delete any records which exist in the prog_assignment table but that
            // weren't submitted just now. Also delete any existing exceptions that
            // related to the assignment being deleted
            $where = "programid = ? AND assignmenttype = ?";
            $params = array($data->id, $this->id);
            if (count($seenids) > 0) {
                list($idssql, $idsparams) = $DB->get_in_or_equal($seenids, SQL_PARAMS_QM, 'param', false);
                $where .= " AND assignmenttypeid {$idssql}";
                $params = array_merge($params, $idsparams);
            }
            $assignments_to_delete = $DB->get_records_select('prog_assignment', $where, $params);
            foreach ($assignments_to_delete as $assignment_to_delete) {
                // delete any exceptions related to this assignment
                exception_manager::delete_exceptions_by_assignment($assignment_to_delete->id);

                // delete any future user assignments related to this assignment
                $DB->delete_records('prog_future_user_assignment', array('assignmentid' => $assignment_to_delete->id, 'programid' => $data->id));
            }
            $DB->delete_records_select('prog_assignment', $where, $params);
        }
    }

    /**
     * Remove user assignments from programs where users not longer belong to the category assignment.
     *
     * @param int $programid Program ID where users are assigned
     * @param int $assignmenttypeid
     * @param array $userids Array of user IDs that we want to remove
     * @return bool $success True if the delete statement was successfully executed.
     */
    public function remove_outdated_assignments(int $programid, int $assignmenttypeid, array $userids): bool {
        global $DB;

        // Do nothing if it's not a group assignment or the id of the assignment type is not given or no users are passed.
        if ($this->id == assignments::ASSIGNTYPE_INDIVIDUAL ||
            empty($programid) ||
            empty($assignmenttypeid) ||
            empty($userids)) {
            return false;
        }

        $result = true;

        // Divide the users into batches to prevent sql problems.
        $batches = array_chunk($userids, $DB->get_max_in_params());
        unset($userids);

        // Process each batch of user ids.
        foreach ($batches as $userids) {
            list($sql, $params) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
            $params['programid'] = $programid;
            $params['assigntype'] = $this->id;
            $params['assigntypeid'] = $assignmenttypeid;

            $sql = "DELETE FROM {prog_user_assignment}
                     WHERE userid {$sql}
                       AND programid = :programid
                       AND EXISTS (SELECT 1
                         FROM {prog_assignment} pa
                         WHERE pa.assignmenttype = :assigntype
                           AND pa.assignmenttypeid = :assigntypeid
                           AND pa.id = {prog_user_assignment}.assignmentid)";
            $result &= $DB->execute($sql, $params);
        }

        // Clear the program completion caches for this program
        program_progress_cache::mark_program_cache_stale($programid);

        return $result;
    }

    /**
     * Called when an assignment of this category is going to be added
     *
     * @param $object
     */
    protected function _add_assignment_hook($object) {
        return true;
    }

    /**
     * Called when an assignment of this list is going to be deleted
     *
     * @param $object
     */
    protected function _delete_assignment_hook($object) {
        return true;
    }

    /**
     * Gets the include children part from the post data
     *
     * @param \stdClass $data the post data object
     * @param \stdClass $object the program assignment object
     * @return int 0 or 1
     */
    abstract public function get_includechildren($data, $object): int;

    /**
     * Outputs html for a given set of completion criteria.
     *
     * Will be a link if updating the criteria is allowed.
     * Will be fixed text if updating is not allowed.
     *
     * Hidden input fields will be included for updating of data.
     *
     * @param \stdClass $item containing any existing completion criteria.
     * @param null|int $programid
     * @param bool $canupdate set to true if the user can update the due date criteria here.
     * @return string of html containing due date criteria, will be as a link if update is allowed.
     */
    public function get_completion($item, ?int $programid = null, bool $canupdate = true): string {
        global $OUTPUT;

        if ($canupdate) {
            $completion_string = get_string('setduedate', 'totara_program');
        } else {
            $completion_string = get_string('noduedate', 'totara_program');
        }

        $hour = 0;
        $minute = 0;

        $show_deletecompletionlink = false;

        if (empty($item->completiontime)) {
            $item->completiontime = assignments::COMPLETION_TIME_NOT_SET;
        }

        if (!isset($item->completionevent)) {
            $item->completionevent = 0;
        }

        if (!isset($item->completioninstance)) {
            $item->completioninstance = 0;
        }

        if (!isset($item->completionoffsetamount)) {
            $item->completionoffsetamount = null;
        }

        if (empty($item->completionoffsetunit)) {
            $item->completionoffsetunit = null;
        }

        if ($item->completionevent == assignments::COMPLETION_EVENT_NONE) {
            // Completiontime must be a timestamp.
            if ($item->completiontime != assignments::COMPLETION_TIME_NOT_SET) {
                $hour = (int)userdate($item->completiontime, '%H', 99, false);
                $minute = (int)userdate($item->completiontime, '%M', 99, false);
                // Print a date.
                $item->completiontime = trim(userdate($item->completiontime,
                    get_string('datepickerlongyearphpuserdate', 'totara_core'), 99, false));
                $completion_string = self::build_completion_string($item->completiontime, $hour, $minute);
                $show_deletecompletionlink = true;
            }
        } else {
            $completion_string = self::build_relative_completion_string(
                $item->completionoffsetamount,
                $item->completionoffsetunit,
                $item->completionevent,
                $item->completioninstance
            );
            $show_deletecompletionlink = true;
        }

        if (!$canupdate) {
            $show_deletecompletionlink = false;
        }

        $html = \html_writer::start_tag('div', array('class' => "completionlink_{$item->id}"));
        if ($item->completiontime != assignments::COMPLETION_TIME_NOT_SET && !empty($item->completiontime)) {
            $html .= \html_writer::empty_tag('input', array('type' => 'hidden',
                'name' => 'completiontime['.$this->id.']['.$item->id.']', 'value' => $item->completiontime));
            $html .= \html_writer::empty_tag('input', array('type' => 'hidden',
                'name' => 'completiontimehour['.$this->id.']['.$item->id.']', 'value' => $hour));
            $html .= \html_writer::empty_tag('input', array('type' => 'hidden',
                'name' => 'completiontimeminute['.$this->id.']['.$item->id.']', 'value' => $minute));
        } else if ($item->completionevent != assignments::COMPLETION_EVENT_NONE) {
            $html .= \html_writer::empty_tag('input', array('type' => 'hidden',
                'name' => 'completiontime['.$this->id.']['.$item->id.']', 'value' => $item->completionoffsetamount . ' ' . $item->completionoffsetunit));
            $html .= \html_writer::empty_tag('input', array('type' => 'hidden',
                'name' => 'completiontimehour['.$this->id.']['.$item->id.']', 'value' => 0));
            $html .= \html_writer::empty_tag('input', array('type' => 'hidden',
                'name' => 'completiontimeminute['.$this->id.']['.$item->id.']', 'value' => 0));
        }
        if ($item->completionevent != assignments::COMPLETION_EVENT_NONE) {
            $html .= \html_writer::empty_tag('input', array('type' => 'hidden',
                'name' => 'completionevent['.$this->id.']['.$item->id.']', 'value' => $item->completionevent));
        }
        if (!empty($item->completioninstance)) {
            $html .= \html_writer::empty_tag('input', array('type' => 'hidden',
                'name' => 'completioninstance['.$this->id.']['.$item->id.']', 'value' => $item->completioninstance));
        }

        if ($canupdate) {
            $html .= \html_writer::link('#', $completion_string, array('class' => 'completionlink'));
        } else {
            $html .= \html_writer::span($completion_string);
        }

        $html .= \html_writer::empty_tag('input',
            array('type' => 'hidden', 'class' => 'completionprogramid', 'value' => $programid));

        if ($show_deletecompletionlink) {
            $html .= $OUTPUT->action_icon('#', new \pix_icon('t/delete', get_string('removeduedate', 'totara_program')), null,
                array('class' => 'deletecompletiondatelink'));
        }

        $html .= \html_writer::end_tag('div');
        return $html;
    }

    public function build_first_table_cell($name, $id, $itemid, $canupdate = true) {
        global $OUTPUT;
        $output = \html_writer::start_tag('div', array('class' => 'totara-item-group'));
        $output .= format_string($name);

        if ($canupdate) {
            $output .= $OUTPUT->action_icon('#', new \pix_icon('t/delete', get_string('delete')), null,
                array('class' => 'deletelink totara-item-group-icon'));
        }

        $output .= \html_writer::end_tag('div');
        $output .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'item['.$id.']['.$itemid.']', 'value' => '1'));
        return $output;
    }

    /**
     * Creates completion string for a fixed completion date/time.
     *
     * @param      $completiontime
     * @param int  $completiontime_hour
     * @param int  $completiontime_minute
     * @return string
     */
    public static function build_completion_string($completiontime, int $completiontime_hour = 0, int $completiontime_minute = 0): string {
        $date_pattern = get_string('datepickerlongyearregexphp', 'totara_core');
        if (preg_match($date_pattern, $completiontime, $matches) == 0) {
            return '';
        } else {
            $completiontime_hour = sprintf("%02d", $completiontime_hour);
            $completiontime_minute = sprintf("%02d", $completiontime_minute);
            // To ensure multi-language compatibility, we must work out the timestamp and then convert that
            // to a string in the user's language.
            $timestamp = totara_date_parse_from_format(
                get_string('datepickerlongyearparseformat', 'totara_core') . ' H:i',
                $completiontime . ' ' . $completiontime_hour . ':' . $completiontime_minute
            );
            $completiontimestring = userdate($timestamp, get_string('strfdateattime', 'langconfig'));

            return get_string('completebytime', 'totara_program', $completiontimestring);
        }
    }

    /**
     * Creates completion string for a relative completion date.
     *
     * @param int|null $offset_amount
     * @param int|null $offset_unit
     * @param int $completion_event
     * @param int $completion_instance
     *
     * @return string
     */
    public static function build_relative_completion_string(?int $offset_amount, ?int $offset_unit, int $completion_event, int $completion_instance): string {
        if (isset(assignments::COMPLETION_EVENTS_CLASSNAMES[$completion_event])) {
            $class = assignments::COMPLETION_EVENTS_CLASSNAMES[$completion_event];
            $eventobject = new $class;

            if (!isset($offset_amount) || empty($offset_unit)) {
                return '';
            }

            $a = new \stdClass();
            $a->num = $offset_amount;
            $a->period = get_string(utils::$timeallowancestrings[$offset_unit], 'totara_program');
            $a->event = $eventobject->get_completion_string();
            $a->instance = $eventobject->get_item_name($completion_instance);

            if (!empty($a->instance)) {
                $a->instance = "'$a->instance'";
            }

            return get_string('completewithinevent', 'totara_program', $a);
        }
        return '';
    }

    /**
     * Get assignment categories
     *
     * @param bool $excludeui If true only assignment types that should be shown in the user interface are returned
     * @return array
     */
    public static function get_categories(bool $excludeui = false): array {
        $tempcategories = array(
            new organisations(),
            new positions(),
            new cohorts(),
            new managers(),
            new individuals(),
            new plans(),
            new groups(),
        );
        $categories = array();
        foreach ($tempcategories as $category) {
            if ($excludeui && !$category->show_in_ui()) {
                continue;
            }
            $categories[$category->id] = $category;
        }
        return $categories;
    }

    /**
     * Should the assignment type be used via the user interface?
     *
     * @return bool
     */
    public static function show_in_ui(): bool {
        return true;
    }

    /**
     * Get a list of all assignments in the program which are applicable to the user.
     *
     * 'Applicable' means that the assignment is related to the user in some way.
     * It is not necessary to return assignments where the user is already assigned, because
     * all assignments where the learner is already assigned should be included when this
     * function is used, but they likewise do not need to be excluded.
     *
     * @param int $program_id
     * @param int $user_id
     * @return program_assignment[]
     */
    public static function get_applicable_assignments(int $program_id, int $user_id): array {
        return [];
    }
}