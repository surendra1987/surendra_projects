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

use core\check\check;
use core\check\result;

/**
 * Lists all users with XSS risk, it would be great to combine this with risk trusts in user table,
 * unfortunately nobody implemented user trust UI yet :-(
 */
class riskallowxss extends check {

    /**
     * @inheritDoc
     */
    public function get_name(): string {
        return get_string('check_riskallowxss_name', 'admin');
    }

    /**
     * @inheritDoc
     */
    public function get_result(): result {
        global $DB;
        $userfields = \user_picture::fields('u');
        $sqlfrom = "SELECT DISTINCT $userfields 
                      FROM (SELECT DISTINCT rcx.roleid, rcx.contextid  
                       FROM {role_capabilities} rcx
                       JOIN {capabilities} cap ON (cap.name = rcx.capability AND ".$DB->sql_bitand('cap.riskbitmask', RISK_ALLOWXSS)." <> 0)
                       WHERE rcx.permission = :capallow) rc,
                     {context} c,
                     {context} sc,
                     {role_assignments} ra,
                     {user} u
               WHERE c.id = rc.contextid
                     AND (sc.path = c.path OR sc.path LIKE ".$DB->sql_concat('c.path', "'/%'")." OR c.path LIKE ".$DB->sql_concat('sc.path', "'/%'").")
                     AND u.id = ra.userid AND u.deleted = 0
                     AND ra.contextid = sc.id AND ra.roleid = rc.roleid";
        $params = [
            'capallow' => CAP_ALLOW
        ];
        $users = $DB->get_records_sql($sqlfrom, $params, 0, 100);
        $count = count($users);

        if ($count === 0) {
            // Totara: no users means no warning, this is good for new installs.
            $status = result::OK;
        } else {
            $status = result::WARNING;
        }
        $summary = get_string('check_riskallowxss_warning', 'admin', $count);

        foreach ($users as $uid=>$user) {
            $users[$uid] = fullname($user);
        }
        $users = implode(', ', $users);
        $details = get_string('check_riskallowxss_details', 'admin', $users);

        return new result($status, $summary, $details);
    }

    /**
     * @inheritDoc
     * @return bool
     */
    public static function include_in_classification(): bool {
        global $CFG;
        return (empty($CFG->disableconsistentcleaning));
    }
}

