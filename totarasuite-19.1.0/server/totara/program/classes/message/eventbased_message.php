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

use core_date;
use core_user;
use html_writer;
use moodle_url;
use stdClass;
use totara_program\assignments\assignments;

/**
 * Class eventbased_message
 *
 * @package totara_program\message
 * @deprecated since Totara 18.0
 */
class eventbased_message extends message {

    public function __construct($programid, $messageob=null, $uniqueid=null) {
        global $CFG;

        parent::__construct($programid, $messageob, $uniqueid);

        $studentmessagedata = array(
            'roleid'            => $this->studentrole,
            'subject'           => $this->messagesubject,
            'fullmessage'       => $this->mainmessage,
            'contexturl'        => $CFG->wwwroot.'/totara/program/view.php?id='.$this->programid,
            'contexturlname'    => get_string('launchprogram', 'totara_program'),
        );

        $this->studentmessagedata = new message_data($studentmessagedata);
    }

    public function save_message() {
        global $DB, $CFG;
        // If this message already exists in the database, check if the trigger time has changed and delete all
        // message logs for this message unless it is prevented by configuration.
        if ($this->id > 0 && empty($CFG->program_message_prevent_resend_on_schedule_change)) {
            $triggertime = $DB->get_field('prog_message', 'triggertime', array('id' => $this->id));
            if ($triggertime != $this->triggertime) {
                $DB->delete_records('prog_messagelog', array('messageid' => $this->id));
            }
        }

        return parent::save_message();
    }

    /**
     * Sends the message to the specified recipient
     *
     * @param object $recipient A user record
     * @param object $sender An optional user record
     * @param array $options An optional array containing options for the message
     * @return bool Success
     */
    public function send_message($recipient, $sender=null, $options=array()) {
        global $DB, $USER;

        $result = true;

        $coursesetid = isset($options['coursesetid']) ? $options['coursesetid'] : 0;
        // Only send the message if it has not already been sent to the recipient.
        if ($DB->get_record('prog_messagelog', array('messageid' => $this->id, 'userid' => $recipient->id, 'coursesetid' => $coursesetid), 'id', IGNORE_MULTIPLE)) {
            return true;
        }

        $this->set_replacementvars($recipient, $options);

        // Verify the $sender of the email
        if ($sender == null) { //null check on $sender, default to manager or no-reply accordingly
            $sender = (\totara_job\job_assignment::is_managing($USER->id, $recipient->id)) ? $USER : core_user::get_support_user();
        } else if ($sender->id == $USER->id) { //make sure $sender is currently logged in
            $sender = $USER;
        } else if (\totara_job\job_assignment::is_managing($USER->id, $recipient->id)) { // Sender is not logged in, see if it is their manager.
            $sender = $USER;
        } else { //last option, the no-reply address
            $sender = core_user::get_support_user();
        }

        // Send the message to the learner.
        $studentdata = new stdClass();
        $studentdata->userto = $recipient;
        $studentdata->userfrom = $sender;
        $studentdata->subject = $this->replacevars($this->studentmessagedata->subject);
        $studentdata->fullmessage = $this->replacevars($this->studentmessagedata->fullmessage);
        $studentdata->contexturl = $this->studentmessagedata->contexturl;
        $studentdata->icon = 'program-regular';
        $studentdata->msgtype = TOTARA_MSG_TYPE_PROGRAM;
        $result = $result && tm_alert_send($studentdata);

        // If the message was sent, add a record to the message log.
        if ($result) {
            $ob = new stdClass();
            $ob->messageid = $this->id;
            $ob->userid = $recipient->id;
            $ob->coursesetid = isset($options['coursesetid']) ? $options['coursesetid'] : 0;
            $ob->timeissued = time();
            $DB->insert_record('prog_messagelog', $ob);
        }

        // Don't send to the manager if the recipient is suspended.
        if (!$recipient->suspended && $this->notifymanager) {
            // Send the message to all of the recipients managers.
            $managers = \totara_job\job_assignment::get_all_manager_userids($recipient->id);
            $managersubject = empty($this->managersubject) ? $this->managermessagedata->subject : $this->managersubject;
            if ($result && !empty($managers)) {
                $contexturl = (
                new moodle_url(
                    '/totara/program/required.php',
                    array(
                        'id' => $this->programid,
                        'userid' => $recipient->id
                    )
                )
                )->out();
                foreach ($managers as $managerid) {
                    $manager = core_user::get_user($managerid, '*', MUST_EXIST);

                    //Set the completion time for the manager
                    //using the attribute $completiontime from a super class
                    //to modify the attribute $replacementvars so that it can
                    //convert the time for specific manager language configuration
                    $completiontime = $this->get_completion_time();
                    if ($completiontime && $completiontime != assignments::COMPLETION_TIME_NOT_SET) {
                        $lang = isset($manager->lang) ? $manager->lang : null;
                        $datetimeformat = get_string_manager()->get_string("strftimedatefulllong", "langconfig", null, $lang);
                        $timezone = core_date::get_user_timezone($manager);

                        $this->replacementvars['duedate'] = userdate($completiontime, $datetimeformat, $timezone, false);
                    }

                    $managerdata = new stdClass();
                    $managerdata->userto = $manager;
                    //ensure the message is actually coming from $user, default to support
                    $managerdata->userfrom = ($USER->id == $recipient->id) ? $recipient : core_user::get_support_user();
                    $managerdata->subject = $this->replacevars($managersubject);
                    $managerdata->fullmessage = $this->replacevars($this->managermessagedata->fullmessage);
                    $managerdata->contexturl = $contexturl;
                    $managerdata->icon = 'program-regular';
                    $managerdata->msgtype = TOTARA_MSG_TYPE_PROGRAM;
                    $result = $result && tm_alert_send($managerdata);
                }
            }
        }

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
        $templatehtml .= $this->get_generic_manager_fields_template($mform, $template_values, $formdataobject, $updateform);

        $templatehtml .= html_writer::end_tag('fieldset');

        return $templatehtml;
    }
}