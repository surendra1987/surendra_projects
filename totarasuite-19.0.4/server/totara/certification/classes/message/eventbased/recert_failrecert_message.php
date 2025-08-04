<?php
/**
 * This file is part of Totara Perform
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
 * @package totara_certification
 * @deprecated since Totara 18.0
 */

namespace totara_certification\message\eventbased;

use html_writer;
use totara_program\message\eventbased_message;
use totara_program\message\message_manager;
use totara_program\message\message_data;

/**
 * Class recert_failrecert_message
 *
 * @package totara_certification\message\eventbased
 * @deprecated since Totara 18.0
 */
class recert_failrecert_message extends eventbased_message {

    public function __construct($programid, $messageob = null, $uniqueid = null) {
        parent::__construct($programid, $messageob, $uniqueid);
        global $CFG;

        $this->messagetype = message_manager::MESSAGETYPE_RECERT_FAILRECERT;
        $this->helppage = 'recertfailrecertmessage';
        $this->fieldsetlegend = get_string('legend:recertfailrecertmessage', 'totara_certification');
        $managermessagedata = array(
            'roleid'            => $this->managerrole,
            'subject'           => get_string('recertfailrecert', 'totara_certification'),
            'fullmessage'       => $this->managermessage,
            'contexturl'        => $CFG->wwwroot.'/totara/program/view.php?id='.$this->programid,
            'contexturlname'    => get_string('launchprogram', 'totara_program'),
        );

        $this->managermessagedata = new message_data($managermessagedata);
    }

    public function get_message_form_template(&$mform, &$template_values, &$formdataobject, $updateform = true) : string {
        global $OUTPUT;
        $prefix = $this->get_message_prefix();

        $helpbutton = $OUTPUT->help_icon($this->helppage, 'totara_certification');

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