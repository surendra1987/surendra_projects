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

namespace totara_program\message;

/**
 * Class message_data
 *
 * @package totara_program\message
 * @deprecated since Totara 18.0
 */
class message_data {
    public $userto, $userfrom, $roleid;
    public $subject, $fullmessage;
    public $contexturl, $contexturlname;
    public $sendemail, $msgtype, $urgency;
    public $icon;

    public function __construct($messagedata) {
        (isset($messagedata['userto']))            && ($this->userto = $messagedata['userto']);
        (isset($messagedata['userfrom']))          && ($this->userfrom = $messagedata['userfrom']);
        (isset($messagedata['roleid']))            && ($this->roleid = $messagedata['roleid']);
        (isset($messagedata['subject']))           && ($this->subject = $messagedata['subject']);
        (isset($messagedata['fullmessage']))       && ($this->fullmessage = $messagedata['fullmessage']);
        (isset($messagedata['contexturl']))        && ($this->contexturl = $messagedata['contexturl']);
        (isset($messagedata['contexturlname']))    && ($this->contexturlname = $messagedata['contexturlname']);
        (isset($messagedata['icon']))              && ($this->icon = $messagedata['icon']);

        $this->msgtype   = isset($messagedata['msgtype']) ? $messagedata['msgtype'] : TOTARA_MSG_TYPE_UNKNOWN;
        $this->urgency   = isset($messagedata['urgency']) ? $messagedata['urgency'] : TOTARA_MSG_URGENCY_NORMAL;
    }
}