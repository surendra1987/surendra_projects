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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package auth_ssosaml
 */

use auth_ssosaml\local\util;
use auth_ssosaml\provider\process\login\handle_response;
use core\notification;

global $CFG, $PAGE, $SESSION;
require_once(__DIR__ . '/../../../config.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/auth/ssosaml/sp/acs.php');

util::assert_saml_enabled();

$is_test = !empty($SESSION->ssosaml['test_login']);
$idp = auth_ssosaml\provider\factory::get_issuer_idp(!$is_test);
$login_user = (new handle_response($idp))->execute();

switch ($login_user['action']) {
    case 'error':
        if (!empty($login_user['error'])) {
            $SESSION->ssosaml['verification_error'] = [
                'error' => $login_user['error'],
                'user_identifier' => $login_user['user_identifier'],
            ];
        }

        redirect(new \moodle_url('/auth/ssosaml/verify.php', ['action' => 'error']));
        exit;

    case 'login_warning':
        redirect('/', get_string('warning_user_already_logged_in', 'auth_ssosaml'), null, notification::WARNING);
        exit;

    case 'confirm':
        redirect(new \moodle_url('/auth/ssosaml/verify.php', ['action' => 'confirm']));
        exit;

    case 'test':
        redirect(new \moodle_url('/auth/ssosaml/idp_test.php', ['id' => $idp->id, 'test_action' => 'login_callback']));
        exit;
}

throw new coding_exception('Unexpected call to SAML acs');
