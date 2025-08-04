<?php
/**
 * This file is part of Totara Core
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
 * @author Sam Hemelryk <sam.hemelryk@totaralearning.com>
 * @package core
 */

namespace core\check\security;

defined('MOODLE_INTERNAL') || die();

use core\check\result;
use core\check\check;

/**
 * Check that:
 *       i) Configured OAuth2 issuers will verify email address;
 *      ii) Totara users not permitted to share an email address.
 *
 * It is possible for a user to compromise another user account when shared email addresses are
 * permitted either in Totara, or by a third party OAuth 2 issuer (e.g. see MDL-66598).
 */
class oauth2verify extends check {

    /**
     * @inheritDoc
     */
    public function get_name(): string {
        return get_string('check_oauth2verify_name', 'admin');
    }

    /**
     * @inheritDoc
     */
    public function get_result(): result {
        global $DB;

        if (get_config('auth_oauth2', 'allowautolinkingexisting')) {
            $badconf = $DB->count_records('oauth2_issuer', ['enabled' => 1, 'showonloginpage' => 1,  'requireconfirmation' => 0]);
            if ($badconf == 0) {
                // There are no OAUTH 2 issuers that would allow linking of logins without email confirmation.
                $status = result::OK;
                $summary = get_string('check_oauth2verify_info_confirmationrequired', 'admin');
            } else {
                // There is at least one OAUTH 2 issuers that allows linking of logins without email confirmation.
                $status = result::CRITICAL;
                $summary = get_string('check_oauth2verify_info_serious', 'admin');
            }
        } else {
            // Accounts cannot be linked, other external auths do not require email verification either when adding new accounts
            // or check email duplicity, so this cannot be considered a security issue.
            $status = result::OK;
            $summary = get_string('check_oauth2verify_info_nolinking', 'admin');
        }

        $details = get_string('check_oauth2verify_details', 'admin');

        return new result($status, $summary, $details);
    }
}

