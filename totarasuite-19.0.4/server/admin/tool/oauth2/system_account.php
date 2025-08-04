<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package core_oauth2
 */

use core\oauth2\api;
use core\oauth2\system_account;
use core\output\notification;
use tool_oauth2\form\system_account as system_account_form;

global $CFG, $PAGE, $OUTPUT;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');

$PAGE->set_url('/admin/tool/oauth2/issuers.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$heading = get_string('oauth2systemaccount', 'tool_oauth2');
$PAGE->set_title($heading);
$PAGE->set_heading($heading);

require_login();

require_capability('moodle/site:config', context_system::instance());

$renderer = $PAGE->get_renderer('tool_oauth2');

$issuerid = required_param('issuerid', PARAM_INT);
$issuer = null;
$system_account = null;

if ($issuerid) {
    $issuer = api::get_issuer($issuerid);
    if ($issuer && $issuer->is_system_account_connected()) {
        $system_account = system_account::get_record(['issuerid' => $issuerid]);
    }
}

if (!$issuer || !$system_account) {
    print_error('invaliddata');
}

$PAGE->navbar->add(get_string('editsystemaccount', 'tool_oauth2', format_string($issuer->get('name'))));
$form = new system_account_form(null, ['persistent' => $system_account, 'issuerid' => $issuer->get('id')]);

if ($form->is_cancelled()) {
    redirect(new moodle_url('/admin/tool/oauth2/issuers.php'));
}

if ($data = $form->get_data()) {
    try {
        api::update_system_account($issuer, $data->username, $data->email);
    } catch (Exception $e) {
        redirect($PAGE->url, $e->getMessage(), null, notification::NOTIFY_ERROR);
    }

    redirect($PAGE->url, get_string('changessaved'), null, notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();
echo $OUTPUT->page_main_heading(get_string('editsystemaccount', 'tool_oauth2', format_string($issuer->get('name'))));
$form->display();
echo $OUTPUT->footer();
