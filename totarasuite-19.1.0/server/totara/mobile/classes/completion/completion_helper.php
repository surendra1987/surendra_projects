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
 * @author Katherine Galano <katherine.galano@totara.com>
 * @package totara_mobile
 */

namespace totara_mobile\completion;

use cm_info;
use totara_mobile\exception\activity_completion_exception;

class completion_helper {
    private function __construct() {
        // Prevent being initialized
    }

    /**
     * @param cm_info $cm_info
     *
     * @return string
     */
    public static function get_completion_value(cm_info $cm_info): string {
        switch ($cm_info->completion) {
            case COMPLETION_TRACKING_NONE :
                return 'tracking_none';
            case COMPLETION_TRACKING_MANUAL :
                return 'tracking_manual';
            case COMPLETION_TRACKING_AUTOMATIC :
                return 'tracking_automatic';
            default :
                return 'unknown';
        }
    }

    /**
     *
     * @return string
     */
    public static function get_completionstatus_value(cm_info $cm_info): string {
        if ($cm_info->available) {
            $completion_info = new \completion_info($cm_info->get_course());
            $completion_data = $completion_info->get_data($cm_info);
            switch ($completion_data->completionstate) {
                case COMPLETION_INCOMPLETE :
                    return 'incomplete';
                case COMPLETION_COMPLETE :
                    return 'complete';
                case COMPLETION_COMPLETE_PASS :
                    return 'complete_pass';
                case COMPLETION_COMPLETE_FAIL :
                    return 'complete_fail';
                default :
                    return 'unknown';
            }
        }

        return 'unknown';
    }

    /**
     * @param cm_info $cm_info
     *
     * @return bool
     */
    public static function get_completionenabled(cm_info $cm_info): bool {
        $completion_info = new \completion_info($cm_info->get_course());
        return $completion_info->is_enabled($cm_info) > COMPLETION_TRACKING_NONE;
    }

    /**
     * @param string $completion_tracking
     *
     * @return int
     * @throws activity_completion_exception
     */
    public static function convert_completion_tracking_value(string $completion_tracking): int {
        if ($completion_tracking === 'TRACKING_MANUAL') {
            return COMPLETION_TRACKING_MANUAL;
        }

        if ($completion_tracking === 'TRACKING_AUTOMATIC') {
            return COMPLETION_TRACKING_AUTOMATIC;
        }

        if ($completion_tracking === 'TRACKING_NONE') {
            return COMPLETION_TRACKING_NONE;
        }

        throw new activity_completion_exception('Unsupported completion tracking value: ' . $completion_tracking);
    }
}
