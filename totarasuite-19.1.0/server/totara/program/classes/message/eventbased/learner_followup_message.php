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

namespace totara_program\message\eventbased;

use html_writer;
use totara_program\message\eventbased_message;
use totara_program\message\message_manager;

/**
 * Class learner_followup_message
 *
 * @package totara_program\message\eventbased
 * @deprecated since Totara 18.0
 */
class learner_followup_message extends eventbased_message {

    public function __construct($programid, $messageob=null, $uniqueid=null) {

        parent::__construct($programid, $messageob, $uniqueid);

        $this->messagetype = message_manager::MESSAGETYPE_LEARNER_FOLLOWUP;
        $this->helppage = 'learnerfollowupmessage';
        $this->fieldsetlegend = get_string('legend:learnerfollowupmessage', 'totara_program');
        $this->triggereventstr = get_string('afterprogramiscompleted', 'totara_program');
        $this->notifymanager = false;
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
        $templatehtml .= $this->get_generic_trigger_fields_template($mform, $template_values, $formdataobject, $updateform);
        $templatehtml .= $this->get_generic_basic_fields_template($mform, $template_values, $formdataobject, $updateform);

        $templatehtml .= html_writer::end_tag('fieldset');

        return $templatehtml;
    }
}