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
 * Check the presence of the node_modules directory.
 *
 * We don't want this present on production sites, even if not publicly available as NPM code should
 * never be trusted.
 */
class nodemodules extends check {

    /**
     * @inheritDoc
     */
    public function get_name(): string {
        return get_string('check_nodemodules_name', 'admin');
    }

    /**
     * @inheritDoc
     */
    public function get_result(): result {
        global $CFG;

        if (is_dir($CFG->dirroot.'/node_modules')) {
            $status = result::WARNING;
            $summary = get_string('check_nodemodules_warning', 'admin');
        } else {
            $status = result::OK;
            $summary = get_string('check_nodemodules_info', 'admin');
        }
        $details = get_string('check_nodemodules_details', 'admin', ['path' => $CFG->dirroot.'/node_modules']);

        return new result($status, $summary, $details);
    }
}

