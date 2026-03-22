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
 * Lists resources that have allowxss setting enabled.
 */
class resourceallowxss extends check {

    /**
     * @inheritDoc
     */
    public function get_name(): string {
        return get_string('check_resourcesallowxss_name', 'admin');
    }

    /**
     * @inheritDoc
     */
    public function get_result(): result {
        $allresources = ['label', 'page', 'resource'];
        $dangerous = [];
        foreach ($allresources as $resource) {
            if (get_config($resource, 'allowxss')) {
                $dangerous[] = get_string('pluginname', $resource);
            }
        }

        if (!$dangerous) {
            $status = result::OK;
            $summary = get_string('check_resourcesallowxss_ok', 'admin');
        } else {
            $status = result::WARNING;
            $summary = get_string('check_resourcesallowxss_warning', 'admin');
        }

        $details = get_string('check_resourcesallowxss_details', 'admin', implode(', ', $dangerous));

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

