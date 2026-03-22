<?php
/**
 * This file is part of Totara Talent Experience Platform
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * @package auth_ssosaml
 */

use auth_ssosaml\local\util;
use auth_ssosaml\model\idp_user_map;
use core\orm\query\exceptions\record_not_found_exception;

global $CFG, $PAGE, $OUTPUT, $USER, $SESSION;
require_once(__DIR__ . '/../../config.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/auth/ssosaml/verify.php');
$PAGE->set_pagelayout('login');

util::assert_saml_enabled();

$action = optional_param('action', null, PARAM_ALPHA);

$render = function (string $title, string $content, bool $login_button = false, bool $logout_button = false) use ($PAGE, $OUTPUT) {
    $PAGE->set_title($title);
    echo $OUTPUT->header();
    echo $OUTPUT->box_start('loginbox onecolumn');

    echo html_writer::start_tag('h3');
    echo $title;
    echo html_writer::end_tag('h3');
    echo html_writer::start_div();
    echo html_writer::start_tag('p');
    echo $content;
    echo html_writer::end_tag('p');

    if ($login_button) {
        echo html_writer::start_tag('p');
        echo $OUTPUT->single_button(get_login_url(), get_string('login'), 'get');
        echo html_writer::end_tag('p');
    } else if ($logout_button) {
        echo $OUTPUT->single_button(new moodle_url('/login/logout.php', ['sesskey' => sesskey()]), get_string('logout'));
    }

    echo html_writer::end_div();
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
    exit;
};

// If we're already logged in, show an error
if (isloggedin()) {
    $title = get_string('verification_logged_in_title', 'auth_ssosaml');
    $description = get_string('verification_logged_in_description', 'auth_ssosaml');

    $render($title, $description, false, true);
}

// If action was provided, then show either the verification error or the confirmation message.
if ($action === 'error') {
    $verification_error = $SESSION->ssosaml['verification_error'] ?? null;

    if ($verification_error) {
        $user_identifier = $verification_error['user_identifier'];
        $error = $verification_error['error'];

        $title_string = $error . '_title';
        $description_string = $error . '_description' . (!empty($user_identifier) ? '_with_identifier' : '_unknown_identifier');

        $title = get_string($title_string, 'auth_ssosaml');
        $description = get_string($description_string, 'auth_ssosaml', $user_identifier);

        $SESSION->ssosaml['verification_error'] = null;
        $render($title, $description, true);
    }

    $title = get_string('verification_failure_title', 'auth_ssosaml');
    $description = get_string('verification_failure_description_unknown_identifier', 'auth_ssosaml');

    $render($title, $description, true);
} else if ($action === 'confirm') {
    $SESSION->wantsurl = $CFG->wwwroot . '/';
    $minutes = round(idp_user_map::CONFIRMATION_EXPIRY / 60, 0);
    $render(
        get_string('verification_pending_title', 'auth_ssosaml'),
        get_string('verification_pending_description', 'auth_ssosaml', $minutes),
        true
    );
}

// Otherwise we must be trying to validate the user. Token and the ID are mandatory here.
$token = required_param('token', PARAM_ALPHANUM);
$map_id = required_param('id', PARAM_INT);

try {
    $map = idp_user_map::load_by_id($map_id);
    $user = $map->validate_user_with_code($token);
} catch (record_not_found_exception $exception) {
    $user = null;
}

if (!$user) {
    $render(
        get_string('verification_invalid_code_title', 'auth_ssosaml'),
        get_string('verification_invalid_code_description', 'auth_ssosaml'),
        true
    );
}

unset($SESSION->loginerrormsg);
$render(
    get_string('verification_complete_title', 'auth_ssosaml'),
    get_string('verification_complete_description', 'auth_ssosaml'),
    true
);
