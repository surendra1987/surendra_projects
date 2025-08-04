<?php
/**
 * This file is part of Totara Learn
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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package approvalform_enrol
 */

namespace approvalform_enrol\observer;

use approvalform_enrol\enrol;
use core\entity\user;
use core_enrol\event\user_enrolment_application_created;
use core_enrol\model\user_enrolment_application;
use mod_approval\form\approvalform_core_enrol_base;
use mod_approval\model\application\application;
use mod_approval\model\application\application_submission;
use mod_approval\model\form\form_data;
use mod_approval\model\json_trait;

class user_enrolment_application_created_observer {

    use json_trait;

    public static function update_draft_application_course_fields(user_enrolment_application_created $event): void {
        $data = $event->get_data();
        $user_enrolment_application = user_enrolment_application::load_by_id($data['objectid']);
        $application = application::load_by_id($user_enrolment_application->approval_application_id);

        // Make sure this application belongs to an approvalform_enrol form.
        if ($application->get_approvalform_plugin()::class != enrol::class) {
            return;
        }

        // Create a draft submission
        $draft_submission = application_submission::create_or_update($application, user::logged_in()->id, form_data::create_empty());

        // Override the formview controls in the model by writing directly to the submission entity.
        $draft_submission_entity = new \mod_approval\entity\application\application_submission($draft_submission->id);
        $raw_form_data = [
            'course_id' => $user_enrolment_application->course->id,
            'course_link' => approvalform_core_enrol_base::encode_course_link_data($user_enrolment_application->course)
        ];
        $merged_form_data = $draft_submission->form_data_parsed->concat(form_data::from_array($raw_form_data));
        $draft_submission_entity->form_data = $merged_form_data->to_json();
        $draft_submission_entity->save();
    }
}