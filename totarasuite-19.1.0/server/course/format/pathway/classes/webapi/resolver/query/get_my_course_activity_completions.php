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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package format_pathway
 */

namespace format_pathway\webapi\resolver\query;

use core\webapi\execution_context;
use core\webapi\middleware\reopen_session_for_writing;
use course_modinfo;
use format_pathway\webapi\resolver\middleware\validate_format_pathway_course;
use moodle_exception;

class get_my_course_activity_completions extends \core\webapi\query_resolver {

    /**
     * @inheritDoc
     * @throws moodle_exception
     */
    public static function resolve(array $args, execution_context $ec) {
        global $CFG, $USER;
        require_once($CFG->dirroot . '/course/lib.php');

        if (!isset($args['course'])) {
            throw new \moodle_exception('invalidcourse');
        }

        $course = $args['course'];

        try {
            // Always prevent redirects for GraphQL requests
            // and we do not need to set the wantsurl to the current url
            \require_login($course, false, null, false, true);
        } catch (\require_login_exception $rle) {
            return [];
        }

        $completion_info = new \completion_info($course);

        // get the course modules
        /** @var course_modinfo $mod_info */
        $mod_info = get_fast_modinfo($course, $USER->id);
        $cms = $mod_info->get_cms();
        $completions = [];
        foreach ($cms as $cm) {
            // calculate if the completion is enabled
            $is_completion_enabled = true;
            $completion_enabled_data = $completion_info->is_enabled($cm);

            // This must match the format_pathway_course_activity_completion_tracking in the schema.
            switch($completion_enabled_data) {
                case COMPLETION_TRACKING_MANUAL:
                    $tracking_type = 'MANUAL';
                    break;
                case COMPLETION_TRACKING_AUTOMATIC:
                    $tracking_type = 'AUTOMATIC';
                    break;
                case COMPLETION_TRACKING_NONE:
                default:
                    $tracking_type = 'NONE';
                    $is_completion_enabled = false;
                    $completion_status = false;
                    break;
            }

            $completion_status_description = '';
            if ($completion_enabled_data !== COMPLETION_TRACKING_NONE) {
                // calculate if the module has been completed
                $completion_data = $completion_info->get_data($cm);
                $completion_status = false;
                $state = $completion_data->completionstate;

                switch ($state) {
                    case COMPLETION_COMPLETE_PASS:
                        $completion_status_description = get_string('completion_pass_grade', 'format_pathway');
                    case COMPLETION_COMPLETE:
                        $completion_status = true;
                        break;
                    case COMPLETION_COMPLETE_FAIL:
                        $completion_status_description = get_string('completion_not_pass_grade', 'format_pathway');
                        $completion_status = true;
                        break;
                }
            }

            $completions[] = [
                'cmid' => (int)$cm->id,
                'completionstatus' => $completion_status,
                'activitycompletionenabled' => $is_completion_enabled,
                'activitycompletiontracking' => $tracking_type,
                'activity_url' => is_null($cm->url) ? '' : $cm->url->out(false),
                'completion_status_description' => $completion_status_description
            ];
        }

        // ensure order by cmid
        usort($completions, function ($a, $b) {
            if ($a['cmid'] === $b['cmid']) { // this should never happen
                return 0;
            }
            return $a['cmid'] < $b['cmid'] ? -1 : 1;
        });

        return $completions;
    }

    /**
     * @return array
     */
    public static function get_middleware(): array {
        return [
            new reopen_session_for_writing(),
            new validate_format_pathway_course('course_id')
        ];
    }
}
