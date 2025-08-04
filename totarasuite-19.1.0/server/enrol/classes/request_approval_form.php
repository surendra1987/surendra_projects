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

use core_enrol\model\user_enrolment_application;
use html_writer;
use mod_approval\controllers\application\edit;
use mod_approval\controllers\application\view;
use moodleform;

class request_approval_form extends moodleform {

    /**
     * @inheritdoc
     */
    public function definition($mform = null) {
        if ($mform === null) {
            $mform = $this->_form;
        }

        $data = $this->_customdata;
        $instance = $data['instance'];
        $plugin = enrol_get_plugin($instance->enrol);

        /** @var \mod_approval\model\application\application $application */
        $application = $data['application'];
        $stage = $application->get_current_stage();

        // The name of the form determines its uniqueness. We need to give each instance
        // a different name in order to be able to assign the correct links to the form.
        $this->_formname = 'core_enrol_request_approval_form_' . $application->id;

        // Get status.
        if ($application->get_current_approval_level()) {
            $status = get_string('status_pending_level', 'mod_approval', $application->get_current_approval_level()->name);
        } else {
            $status = get_string('status_pending_level', 'mod_approval', $stage->name);
            if (in_array($application->get_overall_progress(), ['FINISHED', 'REJECTED'])) {
                $status = $application->get_overall_progress_label();
            }
        }

        // Append label into status.
        if ($application->get_overall_progress() === 'WITHDRAWN') {
            $status = html_writer::span($status) . '  ' . html_writer::span(get_string('overall_progress_withdrawn', 'mod_approval'), 'label label-default');
        } else if ($application->get_overall_progress() === 'REJECTED') {
            $status = html_writer::span($status) . '  ' . html_writer::span(get_string('overall_progress_rejected', 'mod_approval'), 'label label-danger');
        }

        $mform->addElement('header', 'requestapprovalheader', $plugin->get_instance_name($instance));

        $course = user_enrolment_application::find_with_application_id($application->id)->get_course();
        $return_address = '/enrol/index.php?id=' . $course->id;
        $return_address_label = get_string('back_to_course', 'core_enrol');

        if ($application->get_overall_progress() === 'DRAFT') {
            $application_url = edit::get_url_for($application->id, $return_address, $return_address_label);
        } else {
            $application_url = view::get_url_for($application->id, $return_address, $return_address_label);
        }
        $application_url = html_writer::link($application_url, get_string('yourapplication', 'core_enrol'));

        $mform->addElement('html', html_writer::tag('p', get_string('yourapplicationpending', 'core_enrol', $application_url)));

        $stage_number_name = get_string('stage_number_name', 'mod_approval', ['ordinal_number' => $stage->ordinal_number, 'name' => $stage->name]);
        $mform->addElement('html', html_writer::tag('p', get_string('yourapplicationin', 'core_enrol', $stage_number_name)));
        $mform->addElement('html', html_writer::tag('p', get_string('yourapplicationstatus', 'core_enrol', $status)));

        $mform->addElement('hidden', 'create_new_application');
        $mform->setType('create_new_application', PARAM_BOOL);
        $mform->setDefault('create_new_application', enrolment_approval_helper::needs_create_new_application($application));

        enrolment_approval_helper::add_action_buttons($mform, enrolment_approval_helper::get_approval_button_name($application));
    }
}