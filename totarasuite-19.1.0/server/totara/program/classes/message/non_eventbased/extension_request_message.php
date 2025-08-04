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

use core_user;
use html_writer;
use stdClass;
use totara_program\message\message_data;
use totara_program\message\non_eventbased_message;
use totara_program\message\message_manager;

/**
 * The extension_request_message class is a little different from most
 * messages because it cannot be edited by a program creator. It is a fixed
 * message that gets sent when a learner requests an extension to a program.
 * The message is only sent to the learner's manager.
 *
 * @deprecated since Totara 18.0
 */
class extension_request_message extends non_eventbased_message {

    public function __construct($programid, $userid, $messageob, $uniqueid, $data) {
        global $CFG;

        parent::__construct($programid, $messageob, $uniqueid);

        $this->messagetype = message_manager::MESSAGETYPE_EXTENSION_REQUEST;
        $this->helppage = 'extensionrequestmessage';
        $this->fieldsetlegend = get_string('legend:extensionrequestmessage', 'totara_program');
        $this->userid = $userid;
        $this->extensiondata = $data;

        $managermessagedata = array(
            'roleid'            => $this->managerrole,
            'subject'           => $this->messagesubject,
            'fullmessage'       => $this->mainmessage,
            'contexturl'        => $CFG->wwwroot.'/totara/program/manageextensions.php?userid='.$this->userid,
            'contexturlname'    => get_string('manageextensionrequests', 'totara_program'),
        );

        $this->managermessagedata = new message_data($managermessagedata);
    }

    public function send_message($recipient, $sender=null, $options=array()) {
        global $CFG, $USER;

        //ensure that $sender is defined and logged in, default to support
        if ($sender == null || ($USER->id != $sender->id)) {
            $sender == core_user::get_support_user();
        }

        // send the message to the Manager
        $managerdata = new stdClass();
        $managerdata->userto = $recipient;
        $managerdata->userfrom = $sender;
        $managerdata->subject = $this->replacevars($this->managermessagedata->subject);
        $managerdata->fullmessage = $this->replacevars($this->managermessagedata->fullmessage);

        if (!empty($this->managermessagedata->acceptbutton)) {
            $onaccept = new stdClass();
            $onaccept->action = 'prog_extension';
            $onaccept->text = $this->managermessagedata->accepttext;
            $onaccept->data = array();
            $onaccept->data['userid'] = $this->userid;
            $onaccept->data['extensionid'] = $this->extensiondata['extensionid'];
            $onaccept->data['programid'] = $this->programid;
            $onaccept->acceptbutton = $this->managermessagedata->acceptbutton;
            $managerdata->onaccept = $onaccept;
        }
        if (!empty($this->managermessagedata->rejectbutton)) {
            $onreject = new stdClass();
            $onreject->action = 'prog_extension';
            $onreject->text = $this->managermessagedata->rejecttext;
            $onreject->data = array();
            $onreject->data['userid'] = $this->userid;
            $onreject->data['extensionid'] = $this->extensiondata['extensionid'];
            $onreject->data['programid'] = $this->programid;
            $onreject->rejectbutton = $this->managermessagedata->rejectbutton;
            $managerdata->onreject = $onreject;
        }

        if (!empty($this->managermessagedata->infobutton)) {
            $oninfo = new stdClass();
            $oninfo->action = 'prog_extension';
            $oninfo->text = $this->managermessagedata->infotext;
            $oninfo->data = array('userid' => $this->userid);
            $oninfo->data['redirect'] = $this->managermessagedata->contexturl;
            $oninfo->infobutton = $this->managermessagedata->infobutton;
            $managerdata->oninfo = $oninfo;
        }

        $result = tm_task_send($managerdata);

        return $result;
    }

    public function get_message_form_template(&$mform, &$template_values, &$formdataobject, $updateform = true) : string {
        global $OUTPUT;
        $prefix = $this->get_message_prefix();

        $helpbutton = $OUTPUT->help_icon($this->helppage, 'totara_program');

        $templatehtml = '';
        $templatehtml .= html_writer::start_tag('fieldset', array('id' => $prefix, 'class' => 'message surround'));
        $templatehtml .= html_writer::tag('legend', $this->fieldsetlegend . ' ' . $helpbutton);
        $templatehtml .= $this->get_generic_hidden_fields_template($mform, $template_values, $formdataobject, $updateform);
        $templatehtml .= $this->get_generic_message_buttons_template($mform, $template_values, $formdataobject, $updateform);
        $templatehtml .= $this->get_generic_basic_fields_template($mform, $template_values, $formdataobject, $updateform);
        $templatehtml .= html_writer::end_tag('fieldset');

        return $templatehtml;
    }
}