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

use html_writer;
use totara_program\message\message_data;
use totara_program\message\message_manager;
use totara_program\message\non_eventbased_message;

/**
 * Class exception_report_message
 *
 * @package totara_program\message\non_eventbased
 * @deprecated since Totara 18.0
 */
class exception_report_message extends non_eventbased_message {

    public function __construct($programid, $messageob = null, $uniqueid = null) {
        global $CFG;

        parent::__construct($programid, $messageob, $uniqueid);

        $this->messagetype = message_manager::MESSAGETYPE_EXCEPTION_REPORT;
        $this->helppage = 'exceptionreportmessage';
        $this->sortorder = 2;
        $this->fieldsetlegend = get_string('legend:exceptionreportmessage', 'totara_program');

        $studentmessagedata = array(
            'roleid' => $this->studentrole,
            'subject' => $this->messagesubject,
            'fullmessage' => $this->mainmessage,
            'contexturl' => $CFG->wwwroot . '/totara/program/exceptions.php?id=' . $this->programid,
            'contexturlname' => get_string('viewexceptions', 'totara_program'),
        );

        $this->studentmessagedata = new message_data($studentmessagedata);
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