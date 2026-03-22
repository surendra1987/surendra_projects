<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Verifies fatal misconfiguration of dataroot
 *
 * @package    core
 * @category   check
 * @copyright  2020 Brendan Heywood <brendan@catalyst-au.net>
 * @copyright  2008 petr Skoda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core\check\security;

defined('MOODLE_INTERNAL') || die();

use core\check\result;

/**
 * Verifies fatal misconfiguration of dataroot
 *
 * @copyright  2020 Brendan Heywood <brendan@catalyst-au.net>
 * @copyright  2008 petr Skoda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class unsecuredataroot extends \core\check\check {

    /**
     * Get the short check name
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('check_unsecuredataroot_name', 'admin');
    }

    /**
     * Return result
     * @return result
     */
    public function get_result(): result {

        global $CFG;
        require_once($CFG->libdir.'/adminlib.php');

        $details = get_string('check_unsecuredataroot_details', 'admin');

        $insecuredataroot = is_dataroot_insecure(true);

        if ($insecuredataroot == INSECURE_DATAROOT_WARNING) {
            $status = result::ERROR;
            $summary = get_string('check_unsecuredataroot_warning', 'admin', $CFG->dataroot);

        } else if ($insecuredataroot == INSECURE_DATAROOT_ERROR) {
            $status = result::CRITICAL;
            $summary = get_string('check_unsecuredataroot_error', 'admin', $CFG->dataroot);

        } else {
            $status = result::OK;
            $summary = get_string('check_unsecuredataroot_ok', 'admin');
        }
        return new result($status, $summary, $details);
    }
}


