<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package core_enrol
 */

namespace core_enrol;

global $CFG;

require_once "$CFG->dirroot/lib/formslib.php";

use html_writer;
use moodleform;

/**
 * The form is for creating new application if there is no application but user enrolment is pending.
 */
class create_new_application_form extends moodleform {

    /**
     * @inheritdoc
     */
    protected function definition($mform = null) {
        global $USER;

        if ($mform === null) {
            $mform = $this->_form;
        }

        $data = $this->_customdata;
        $instance = $data['instance'];

        $plugin = enrol_get_plugin($instance->enrol);
        $mform->addElement('header', 'requestapprovalheader', $plugin->get_instance_name($instance));

        // Add job assignment selector if workflow requires manager approval.
        $job_selector_added = enrolment_approval_helper::plugin_form_add_job_selector($instance->workflow_id, $USER->id, $mform);

        $mform->addElement('html', html_writer::tag('p', get_string('requestapprovalrequirement', 'enrol_self')));

        if (!$job_selector_added) {
            enrolment_approval_helper::add_action_buttons($mform, get_string('application:create_application', 'core_enrol'));
        }
    }
}