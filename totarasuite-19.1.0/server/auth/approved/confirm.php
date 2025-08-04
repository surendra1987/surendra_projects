<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2017 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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
 * @author Petr Skoda <petr.skoda@totaralearning.com>
 *
 * @package auth_approved
 */

require(__DIR__ . '/../../config.php');

global $OUTPUT, $PAGE;

$token = optional_param('token', '', PARAM_ALPHANUM);

$PAGE->set_url('/auth/approved/confirm.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('login');

if (!is_enabled_auth('approved') or empty($CFG->registerauth) or $CFG->registerauth !== 'approved') {
    print_error('plugindisabled', 'auth_approved');
}

$currentdata = array('token' => $token);
$form = new \auth_approved\form\confirm($currentdata);

if ($data = $form->get_data()) {
    ignore_user_abort(true); // Make sure we do not get interrupted!

    $PAGE->set_title(get_string('emailconfirm', 'auth_approved'));
    echo $OUTPUT->header();
    echo $OUTPUT->box_start('loginbox onecolumn');

    list($success, $message, $continuebutton) = \auth_approved\request::confirm_request($token);
    $class = $success ? 'notifysuccess' : 'notifyproblem';
    echo $OUTPUT->notification($message, $class);

    if ($continuebutton) {
        echo $OUTPUT->render($continuebutton);
    }

    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
    die;
}

$PAGE->set_title(get_string('emailconfirm', 'auth_approved'));

echo $OUTPUT->header();
echo $OUTPUT->box_start('loginbox onecolumn');

echo $OUTPUT->page_main_heading(get_string('confirm_prompt_heading', 'auth_approved'));
echo \html_writer::tag('p', get_string('confirm_prompt', 'auth_approved'));

echo $form->render();

echo $OUTPUT->box_end();
echo $OUTPUT->footer();
