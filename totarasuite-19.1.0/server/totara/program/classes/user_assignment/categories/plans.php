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
use stdClass;
use totara_program\user_assignment\user_assignment;

/**
 * A representation of a group assignment to a program via a plan
 */
class plans extends user_assignment {

    /**
     * @inheritDoc
     */
    public function display_criteria(): string {
        global $DB;

        // get plan name.
        $a = new stdClass();
        $a->planname = format_string($DB->get_field('dp_plan', 'name', ['id' => $this->assignment->assignmenttypeid]));

        $out = html_writer::start_tag('li', array('class' => 'assignmentcriteria'));
        $out .= html_writer::start_tag('span', array('class' => 'criteria'));
        $out .= get_string('assignedvialearningplan', 'totara_program', $a);
        $out .= html_writer::end_tag('span');
        $out .= html_writer::end_tag('li');

        return $out;
    }
}