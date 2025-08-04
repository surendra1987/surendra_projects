<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @package core
 */

namespace core\check\security;

defined('MOODLE_INTERNAL') || die();

use core\check\check;
use core\check\result;
use core\files\curl_security_helper;

/**
 * Verifies if the curlsecurityblockedhosts setting is enable for a site.
 */
class curlsecurityblockedhosts extends check {

    /**
     * @inheritDoc
     */
    public function get_name(): string {
        return get_string('check_curlsecurityblockedhosts_name', 'admin');
    }

    /**
     * @inheritDoc
     */
    public function get_action_link(): ?\action_link {
        return new \action_link(
            new \moodle_url('/admin/settings.php?section=httpsecurity#admin-curlsecurityblockedhosts'),
            get_string('httpsecurity', 'admin')
        );
    }

    /**
     * @inheritDoc
     */
    public function get_result(): result {
        $curl_helper = new curl_security_helper();

        // test against
        $local_ips = [
            '127.0.0.1',
            '192.168.1.10',
            '10.0.5.3',
            '172.16.1.5',
            '0.0.0.0',
            'localhost',
            '169.254.169.254',
            '::1',
        ];

        $reflect = new \ReflectionMethod($curl_helper, 'host_is_blocked');
        $blocked = true;
        foreach ($local_ips as $local_ip) {
            // Hack our way into the curl check
            if (!$reflect->invoke($curl_helper, $local_ip)) {
                $blocked = false;
                break;
            }
        }

        if ($blocked) {
            $status = result::OK;
            $summary = get_string('check_curlsecurityblockedhosts_ok', 'admin');
        } else {
            $status = result::WARNING;
            $summary = get_string('check_curlsecurityblockedhosts_warning', 'admin');
        }

        // Reference server/admin/settings/security.php > curlsecurityblockedhosts list
        $blockedhostsdefault = [
            '127.0.0.0/8',
            '192.168.0.0/16',
            '10.0.0.0/8',
            '172.16.0.0/12',
            '0.0.0.0',
            'localhost',
            '169.254.169.254',
            '0000::1',
        ];
        $list = '<pre>' . implode("\n", $blockedhostsdefault) . '</pre>';

        $details = get_string('check_curlsecurityblockedhosts_details', 'admin', $list);

        return new result($status, $summary, $details);
    }
}

