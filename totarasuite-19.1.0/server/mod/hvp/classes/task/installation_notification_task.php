<?php
/*
 * This file is part of Totara LMS
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package mod_hvp
 */

namespace mod_hvp\task;

use context_user;
use core\message\message;
use core\task\adhoc_task;
use core_user;
use moodle_url;

class installation_notification_task extends adhoc_task {
    /**
     * Constructor.
     */
    public function __construct() {
        $this->set_component('mod_hvp');
    }

    /**
     * Send out messages.
     */
    public function execute() {
        global $CFG, $OUTPUT;

        $userids = explode(',', $CFG->siteadmins);

        $url = new moodle_url('/admin/settings.php?section=modsettinghvp');
        $button = $OUTPUT->single_button($url, get_string('notification_set_up_hvp_button', 'mod_hvp'), 'get');

        foreach ($userids as $userid) {
            $user = core_user::get_user($userid);
            if (empty($user)) {
                // User doesn't exist. Should never happen, nothing to do.
                mtrace("User {$userid} not found.");
                continue;
            }
            $context = context_user::instance($user->id, IGNORE_MISSING);
            if ($context === false) {
                // User context doesn't exist. Should never happen, nothing to do.
                mtrace("No user context for user {$userid}.");
                continue;
            }
            $msgdata = new message();
            $msgdata->courseid          =  SITEID;
            $msgdata->component         = 'mod_hvp'; // Your component name.
            $msgdata->name              = 'set_up_hvp'; // This is the message name from messages.php.
            $msgdata->userfrom          = core_user::get_noreply_user();
            $msgdata->userto            = $user;
            $msgdata->subject           = get_string('notification_set_up_hvp_subject', 'mod_hvp');
            $msgdata->fullmessage       = get_string('notification_set_up_hvp_body_text', 'mod_hvp', $url->out());
            $msgdata->fullmessageformat = FORMAT_HTML;
            $msgdata->fullmessagehtml   = get_string('notification_set_up_hvp_body_html', 'mod_hvp', $button);
            $msgdata->smallmessage      = '';
            $msgdata->notification      = 1; // This is only set to 0 for personal messages between users.

            mtrace("Sending message to the user with id " . $msgdata->userto->id . "...");
            message_send($msgdata);
            mtrace("Sent.");
        }
    }
}
