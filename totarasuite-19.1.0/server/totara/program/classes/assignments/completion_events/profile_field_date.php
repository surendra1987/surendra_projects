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
 * Profile field date completion event.
 */
class profile_field_date extends completion_event {

    /** @var array */
    private $names;

    /** @var array */
    private $timestamps;

    /**
     * @inheritdoc
     */
    public function get_id(): int {
        return assignments::COMPLETION_EVENT_PROFILE_FIELD_DATE;
    }

    /**
     * @inheritdoc
     */
    public function get_name(): string {
        return get_string('profilefielddate', 'totara_program');
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
            totaraDialogs['completionevent'].default_url = '$CFG->wwwroot/totara/program/assignment/completion/find_profile_field.php?programid="
            . $this->programid . "';
            totaraDialogs['completionevent'].open();

            $('#instancetitle').unbind('click').click(function() {
                handle_completion_selection();
                return false;
            });
        ";
    }

    /**
     * @inheritdoc
     */
    public function get_item_name(int $instanceid): string {
        global $DB;

        // Lazy load names when required.
        if (!isset($this->names)) {
            $this->names = $DB->get_records_select('user_info_field', '', null, '', 'id, name');
        }

        if (isset($this->names[$instanceid]->name)) {
            return $this->names[$instanceid]->name;
        } else {
            return get_string('itemdeleted', 'totara_program');
        }
    }

    /**
     * @inheritdoc
     */
    public function get_completion_string(): string {
        return get_string('dateinprofilefield', 'totara_program');
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

        // First time calling this function for this fieldid. We can't narrow this down to only the users in this assignment
        // because it's possible that those records haven't yet been created. But doing it this way means that we can reuse
        // the same custom field if it is used in more than one assignment.
        $fieldid = $assignobject->completioninstance;
        if (!isset($this->timestamps[$fieldid])) {
            $params = array('fieldid' => $fieldid);
            $this->timestamps[$fieldid] = $DB->get_records('user_info_data', $params, '', 'userid, data');
        }

        if (!isset($this->timestamps[$fieldid][$userid])) {
            return false;
        }

        $date = $this->timestamps[$fieldid][$userid]->data;
        $date = trim($date);

        if (empty($date)) {
            return false;
        }

        // Check if the profile field contains a date in UNIX timestamp form..
        $timestamppattern = '/^[0-9]+$/';
        if (preg_match($timestamppattern, $date, $matches) > 0) {
            return $date;
        }

        // Check if the profile field contains a date in the specified format.
        $result = totara_date_parse_from_format(get_string('customfieldtextdateformat', 'totara_customfield'), $date);
        if ($result > 0) {
            return $result;
        }

        // Last ditch attempt, try using strtotime to convert the string into a timestamp..
        $result = strtotime($date);
        if ($result != false) {
            return $result;
        }

        // Else we couldn't match a date, so return false.
        return false;
    }
}