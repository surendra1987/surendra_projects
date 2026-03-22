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

namespace totara_program\user_assignment\categories;

use html_writer;
use totara_program\user_assignment\user_assignment;

/**
 * A representation of a group assignment to a program via a manager
 */
class managers extends user_assignment {

    /**
     * @inheritDoc
     */
    public function display_criteria(): string {
        global $DB;
        $usernamefields = get_all_user_name_fields(true);
        $jobassignment = \totara_job\job_assignment::get_with_id($this->assignment->assignmenttypeid);
        $managers_name = $DB->get_record_select('user', "id = ?", array($jobassignment->userid), $usernamefields);
        $out = '';
        if (empty($managers_name)) {
            // This shouldn't happen at this stage, but if it does, there's no need to kill the page for the user.
            return $out;
        }
        $managers_name->fullname = fullname($managers_name);
        $out .= html_writer::start_tag('li', array('class' => 'assignmentcriteria'));
        $out .= html_writer::start_tag('span', array('class' => 'criteria'));
        $out .= get_string('partofteam', 'totara_program', $managers_name->fullname);
        $out .= html_writer::end_tag('span');
        $out .= html_writer::end_tag('li');
        return $out;
    }
}