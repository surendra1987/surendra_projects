<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2022 onwards Totara Learning Solutions LTD
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
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package totara_program
 * @deprecated since Totara 18.0
 */

namespace totara_program\message\non_eventbased;

use totara_program\message\message_data;
use totara_program\message\message_manager;
use totara_program\message\non_eventbased_message;

/**
 * Class courseset_completed_message
 *
 * @package totara_program\message\non_eventbased
 * @deprecated since Totara 18.0
 */
class courseset_completed_message extends non_eventbased_message {
    public function __construct($programid, $messageob=null, $uniqueid=null) {
        global $CFG;

        parent::__construct($programid, $messageob, $uniqueid);

        $this->messagetype = message_manager::MESSAGETYPE_COURSESET_COMPLETED;
        $this->helppage = 'coursesetcompletedmessage';
        $this->fieldsetlegend = get_string('legend:coursesetcompletedmessage', 'totara_program');

        $managermessagedata = array(
            'roleid'            => $this->managerrole,
            'subject'           => get_string('coursesetcompleted', 'totara_program'),
            'fullmessage'       => $this->managermessage,
            'contexturl'        => $CFG->wwwroot.'/totara/program/view.php?id='.$this->programid,
            'contexturlname'    => get_string('launchprogram', 'totara_program'),
        );

        $this->managermessagedata = new message_data($managermessagedata);
    }
}