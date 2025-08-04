<?php
/*
 * This file is part of Totara Learn
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
 * @author Simon Coggins <simon.coggins@totaralms.com>
 * @package totara
 * @subpackage totara_job
 */

use totara_job\job_assignment;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/totara/core/dialogs/dialog_content_hierarchy.class.php');
require_once($CFG->dirroot . '/totara/job/lib.php');

$userid = required_param('userid', PARAM_INT);

require_login(null, false, null, false, true);

// First check that the user really does exist and that they're not a guest.
$userexists = !isguestuser($userid) && $DB->record_exists('user', array('id' => $userid, 'deleted' => 0));

$context = $userid == 0 ? context_system::instance() : context_user::instance($userid);
$PAGE->set_context($context);

// Check if the current user can edit the given user's job assignments.
$canedit = $userexists && totara_job_can_edit_job_assignments($userid);

if (!$canedit) {
    print_error('nopermissions', '', '', 'Assign appraiser');
}

$logged_user = \core\entity\user::logged_in();
$context = context_user::instance($logged_user->id);
$appraisers = job_assignment::get_potential_appraisers_for_job_assignment($userid, isset($context->tenantid) ? $context->tenantid : null);

foreach ($appraisers as $appraiser) {
    $appraiser->fullname = fullname($appraiser);
}

$dialog = new totara_dialog_content();
$dialog->searchtype = 'user';
$dialog->items = $appraisers;
$dialog->customdata['current_user'] = $userid;
$dialog->urlparams['userid'] = $userid;
$dialog->set_context($context);

echo $dialog->generate_markup();