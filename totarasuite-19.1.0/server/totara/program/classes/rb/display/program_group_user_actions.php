<?php
/*
 * This file is part of Totara Learn
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Maxime Claudel <maxime.claudel@totara.com>
 * @package totara_program
 */

namespace totara_reportbuilder\rb\display;

class program_group_user_actions extends base {

    /**
     * Handles the display of the actions column
     * This is used in the due dates embedded report and should only be visible for group type assignments.
     *
     * @param string $value
     * @param string $format
     * @param \stdClass $row
     * @param \rb_column $column
     * @param \reportbuilder $report
     * @return string
     */
    public static function display($userid, $format, \stdClass $row, \rb_column $column, \reportbuilder $report) {
        global $CFG, $OUTPUT;

        if ($format !== 'html') {
            // Only applicable to the HTML format.
            return '';
        }

        // Note: performance here is not an issue because this column is not exported.
        require_once "$CFG->dirroot/lib/authlib.php";

        if (isguestuser($userid)) {
            // No actions for the guest user.
            return '';
        }

        $user = static::get_extrafields_row($row, $column);
        $user->id = $userid;

        $returnurl = new \moodle_url($report->get_current_url());
        $spage = optional_param('spage', '', PARAM_INT);
        if ($spage) {
            $returnurl->param('spage', $spage);
        }
        $perpage = optional_param('perpage', '', PARAM_INT);
        if ($perpage) {
            $returnurl->param('perpage', $perpage);
        }

        $buttons = array();

        $user->fullname = clean_string($user->fullname);

        $usercontext = \context_user::instance($userid);
        $candelete = has_capability('totara/program:configureassignments', $usercontext);

        // Add delete action icon.
        if ($candelete) {
            $title = get_string('delete_user_group', 'totara_program', $user->fullname);
            $buttons[] = \html_writer::link(
                $returnurl,
                $OUTPUT->flex_icon('delete', array('alt' => $title)),
                array(
                    'title' => $title,
                    'data-programid' => $user->program_id,
                    'data-userid' => $userid,
                    'data-assignmentid' => $user->assignment_id
                )
            );
        }

        if ($buttons) {
            return implode('', $buttons);
        } else {
            return '';
        }
    }

    public static function is_graphable(\rb_column $column, \rb_column_option $option, \reportbuilder $report) {
        return false;
    }
}
