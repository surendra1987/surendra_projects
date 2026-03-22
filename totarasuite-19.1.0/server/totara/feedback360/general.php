<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2010 onwards Totara Learning Solutions LTD
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
 * @author Valerii Kuznetsov <valerii.kuznetsov@totaralms.com>
 * @package totara
 * @subpackage totara_feedback360
 */

use core\notification;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot . '/totara/feedback360/lib.php');
require_once($CFG->dirroot . '/totara/feedback360/feedback360_forms.php');

// Check if 360 Feedbacks are enabled.
feedback360::check_feature_enabled();

$id = optional_param('id', 0, PARAM_INT);

// Disable the function of creating feedback when read only enable
if (empty($id)) {
    feedback360::read_only_debugging('Creating legacy feedback 360');
}

admin_externalpage_setup('managefeedback360');
$systemcontext = context_system::instance();
require_capability('totara/feedback360:managefeedback360', $systemcontext);

$returnurl = new moodle_url('/totara/feedback360/manage.php');

$feedback360 = new feedback360($id);
$isdraft = feedback360::is_draft($feedback360);
$isreadonly = feedback360::is_read_only();
$defaults = $feedback360->get();
$defaults->descriptionformat = FORMAT_HTML;
$defaults = file_prepare_standard_editor($defaults, 'description', $TEXTAREA_OPTIONS, $TEXTAREA_OPTIONS['context'],
        'totara_feedback360', 'feedback360', $id);
$mform = new feedback360_edit_form(null, array('id' => $id, 'feedback360' => $defaults, 'readonly' => (!$isdraft || $isreadonly)));

if ($mform->is_cancelled()) {
    redirect($returnurl);
}
if ($fromform = $mform->get_data()) {
    // Disable the post function of the edit form
    feedback360::read_only_debugging('Updating legacy feedback 360');
    if (empty($fromform->submitbutton)) {
        notification::error(get_string('error:unknownbuttonclicked', 'totara_feedback360'));
        redirect($returnurl);
    }

    $todb = new stdClass();
    $todb->name = $fromform->name;
    $todb->anonymous = $fromform->anonymous;
    $todb->selfevaluation = $fromform->selfevaluation;
    $feedback360->set($todb);

    if ($feedback360->id < 1) {
        $feedback360->save();
    }
    $todb->description_editor = $fromform->description_editor;
    $todb = file_postupdate_standard_editor($todb, 'description', $TEXTAREA_OPTIONS, $TEXTAREA_OPTIONS['context'],
        'totara_feedback360', 'feedback360', $feedback360->id);
    $feedback360->description = $todb->description;
    $feedback360->save();

    if ($id > 0) {
        notification::success(get_string('feedback360updated', 'totara_feedback360'));
        redirect($returnurl);
    } else {
        $stageurl = new moodle_url('/totara/feedback360/general.php', array('id' => $feedback360->id));
        notification::success(get_string('feedback360updated', 'totara_feedback360'));
        redirect($stageurl);
    }
}

$output = $PAGE->get_renderer('totara_feedback360');
echo $output->header();

feedback360::add_read_only_notification($isreadonly, $isdraft);

if ($feedback360->id) {
    echo $output->heading($feedback360->name);
    echo $output->feedback360_additional_actions($feedback360->status, $feedback360->id);
} else {
    echo $output->heading(get_string('createfeedback360heading', 'totara_feedback360'));
}

echo $output->feedback360_management_tabs($feedback360->id, 'general');

$mform->display();
echo $output->footer();
