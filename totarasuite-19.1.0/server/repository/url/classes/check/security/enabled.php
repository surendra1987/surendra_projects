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

namespace repository_url\check\security;

defined('MOODLE_INTERNAL') || die();

use core\check\check;
use core\check\result;

/**
 * Verifies if URL downloader repository enabled in Totara.
 */
class enabled extends check {

    /**
     * @inheritDoc
     */
    public function get_name(): string {
        return get_string('check_enabled_name', 'repository_url');
    }

    /**
     * @inheritDoc
     */
    public function get_action_link(): ?\action_link {
        return new \action_link(
            new \moodle_url('/admin/repository.php'),
            get_string('manage', 'repository'));
    }

    /**
     * @inheritDoc
     */
    public function get_result(): result {
        global $CFG;
        require_once($CFG->dirroot . '/repository/lib.php');
        $repositorytype = \repository::get_type_by_typename('url');

        if ($repositorytype) {
            $status = result::WARNING;
            $summary = get_string('check_enabled_warning', 'repository_url');
        } else {
            $status = result::OK;
            $summary = get_string('check_enabled_ok', 'repository_url');
        }

        $details = get_string('check_enabled_details', 'repository_url');

        return new result($status, $summary, $details);
    }
}

