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

namespace totara_program\assignments\completion_events;

use totara_program\assignments\assignments;
use totara_program\assignments\completion_event;

/**
 * Course completion event.
 */
class course_completion extends completion_event {

    /** @var array */
    private $names;

    /** @var array */
    private $timestamps;

    /**
     * @inheritdoc
     */
    public function get_id(): int {
        return assignments::COMPLETION_EVENT_COURSE_COMPLETION;
    }

    /**
     * @inheritdoc
     */
    public function get_name(): string {
        return get_string('coursecompletion', 'totara_program');
    }

    /**
     * @inheritdoc
     */
    public function get_script(): string {
        global $CFG;

        if (empty($this->programid)) {
            throw new \coding_exception('Program id must be defined for js that will call the completion ajax scripts.');
        }

        return "
            totaraDialogs['completionevent'].default_url = '$CFG->wwwroot/totara/program/assignment/completion/find_course.php?programid="
            . $this->programid . "';
            totaraDialogs['completionevent'].open();

            $('#instancetitle').unbind('click').click(function() {
                handle_completion_selection();
                return false;
            });

            $('.folder').removeClass('clickable').addClass('unclickable');
        ";
    }

    /**
     * @inheritdoc
     */
    public function get_item_name(int $instanceid): string {
        global $DB;

        // Lazy load names when required.
        if (!isset($this->names)) {
            $this->names = $DB->get_records_select('course', '', null, '', 'id, fullname');
        }

        if (isset($this->names[$instanceid]->fullname)) {
            return $this->names[$instanceid]->fullname;
        } else {
            return get_string('itemdeleted', 'totara_program');
        }
    }

    /**
     * @inheritdoc
     */
    public function get_completion_string(): string {
        return get_string('completionofcourse', 'totara_program');
    }

    /**
     * @inheritdoc
     */
    public function get_timestamp(int $userid, $assignobject) {
        global $DB;

        // First time calling this function.
        if (!isset($this->timestamps)) {
            $this->timestamps = array();
        }

        // First time calling this function for this courseid.
        $courseid = $assignobject->completioninstance;
        if (!isset($this->timestamps[$courseid])) {
            $params = array('course' => $courseid);
            $this->timestamps[$courseid] = $DB->get_records('course_completions', $params, '', 'userid, timecompleted');
        }

        if (isset($this->timestamps[$courseid][$userid])) {
            return $this->timestamps[$courseid][$userid]->timecompleted;
        }

        return false;
    }
}