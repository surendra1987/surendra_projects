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
 * @author Alastair Munro <alastair.munro@totaralms.com>
 * @package totara
 * @subpackage program
 */

define('AJAX_SCRIPT', true);

use totara_certification\totara_notification\resolver\extension_requested as certification_extension_request_resolver;
use totara_program\totara_notification\resolver\extension_requested as extension_request_resolver;
use totara_notification\external_helper;
use totara_program\program;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/totara/program/lib.php');

require_login();
require_sesskey();

$programid = required_param('id', PARAM_INT);
$userid = required_param('userid', PARAM_INT);
$extensionrequest = optional_param('extrequest', false, PARAM_BOOL);
$extensiondate = optional_param('extdate', '', PARAM_TEXT);
$extensionhour = optional_param('exthour', 0, PARAM_INT);
$extensionminute = optional_param('extminute', 0, PARAM_INT);
$extensionreason = optional_param('extreason', '', PARAM_TEXT);

$PAGE->set_context(context_program::instance($programid));

$OUTPUT->header();

$result = array();

$program = new program($programid);

// Validate if user can request extension.
if (!$program->can_request_extension($userid)
        || !$extensionrequest
        || !$extensiondate
        || !$extensionreason) {
    $result['success'] = false;
    $result['message'] = get_string('error:processingextrequest', 'totara_program');
    echo json_encode($result);
    return;
}

// We receive the date as a string, but also need to append hour and minute so that the timestamp includes those.
// This appended string is used for processing only and is not shown to users.
$extensionhour = sprintf("%02d", $extensionhour);
$extensionminute = sprintf("%02d", $extensionminute);
$extensiondate_timestamp = totara_date_parse_from_format(get_string('datepickerlongyearparseformat', 'totara_core').' H:i',
    $extensiondate.' '.$extensionhour.':'.$extensionminute);  // convert to timestamp

$extension = new stdClass;
$extension->programid = $program->id;
$extension->userid = $userid;
$extension->extensiondate = $extensiondate_timestamp;

// Validated extension date to make sure it is after
// current due date and not in the past.
// Existing prog_completion record already validated by previous $program->can_request_extension() call.
if ($prog_completion = $DB->get_record('prog_completion', array(
        'programid' => $program->id,
        'userid' => $userid, 'coursesetid' => 0))) {
    $duedate = empty($prog_completion->timedue) ? 0 : $prog_completion->timedue;

    if ($extensiondate_timestamp < $duedate) {
        $result['success'] = false;
        $result['message'] = get_string('extensionearlierthanduedate', 'totara_program');
        echo json_encode($result);
        return;
    }
}

$now = time();
if ($extensiondate_timestamp < $now) {
    $result['success'] = false;
    $result['message'] = get_string('extensionbeforenow', 'totara_program');
    echo json_encode($result);
    return;
}

$extension->extensionreason = $extensionreason;
$extension->status = 0;

if ($extensionid = $DB->insert_record('prog_extension', $extension)) {
    if (!isset($program->certifid)) { // If the certifid isn't set, it's a program, so we use the default resolver
        $resolver = new extension_request_resolver([
            'program_id' => $extension->programid,
            'subject_user_id' => $extension->userid,
            'extension_request_id' => $extensionid
        ]);
    } else {
        $resolver = new certification_extension_request_resolver([
            'program_id' => $extension->programid,
            'subject_user_id' => $extension->userid,
            'extension_request_id' => $extensionid
        ]);
    }
    external_helper::create_notifiable_event_queue($resolver);

    $result['success'] = true;
    $result['message'] = get_string('pendingextension', 'totara_program');
    echo json_encode($result);
    return;
} else {
    $result['success'] = false;
    $result['message'] = get_string('extensionrequestfailed', 'totara_program');
    echo json_encode($result);
    return;
}
