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
 * @author David Curry <david.curry@totaralms.com>
 * @package totara
 * @subpackage totara_feedback360
 */

use core\task\manager;
use core\notification;
use totara_feedback360\task\close_feedback360_task;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot . '/totara/feedback360/lib.php');

// Check if 360 Feedbacks are enabled.
feedback360::check_feature_enabled();

$action = optional_param('action', '', PARAM_ACTION);

admin_externalpage_setup('managefeedback360');
$systemcontext = context_system::instance();
require_capability('totara/feedback360:managefeedback360', $systemcontext);

$renderer = $PAGE->get_renderer('totara_feedback360');

$feedback360s = feedback360::get_manage_list();

switch ($action) {
    case 'delete':
        $returnurl = new moodle_url('/totara/feedback360/manage.php');
        $id = required_param('id', PARAM_INT);
        $feedback360 = new feedback360($id);

        if (in_array($feedback360->status, array(feedback360::STATUS_DRAFT, feedback360::STATUS_CLOSED))) {
            $confirm = optional_param('confirm', 0, PARAM_INT);
            if ($confirm == 1) {
                require_sesskey();
                $feedback360->delete();
                notification::success(get_string('deletedfeedback360', 'totara_feedback360'));
                redirect($returnurl);
            }
        } else {
            notification::error(get_string('error:feedback360isactive', 'totara_feedback360'));
            redirect($returnurl);
        }
        break;
    case 'copy':
        // Disable the function of copy feedback when read only enable
        feedback360::read_only_debugging('Duplicating feedback 360');
        $id = required_param('id', PARAM_INT);

        $cloned_feedback360_id = feedback360::duplicate($id);

        $returnurl = new moodle_url('/totara/feedback360/general.php', array('id' => $cloned_feedback360_id));

        notification::success(get_string('feedback360cloned', 'totara_feedback360'));
        redirect($returnurl);
        break;
}

echo $renderer->header();
$legacy_message = get_string('legacy_info', 'totara_appraisal');
if (feedback360::is_read_only()) {
    $key = manager::get_adhoc_tasks(close_feedback360_task::class)
        ? 'readonly_adhoc_scheduled_admin_activity'
        : 'legacy_feedback_read_only';

    $legacy_message .= "<br/>" . get_string($key, 'totara_feedback360');
}
echo notification::info($legacy_message);
switch ($action) {
    case 'delete':
        echo $renderer->heading(get_string('deletefeedback360s', 'totara_feedback360', $feedback360->name));
        echo $renderer->confirm_delete_feedback360($feedback360);
        break;
    default:
        echo $renderer->heading(get_string('managefeedback360:utf8', 'totara_feedback360'));
        echo $renderer->create_feedback360_button();
        echo $renderer->feedback360_manage_table($feedback360s);
}
echo $renderer->footer();
