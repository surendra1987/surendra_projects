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
 * @author Nathaniel Walmsley <nathaniel.walmsley@totara.com>
 * @package format_pathway
 */

namespace format_pathway\webapi\resolver\type;

use context_module;
use core\format;
use core\webapi\execution_context;
use core\webapi\formatter\field\string_field_formatter;
use core\webapi\type_resolver;
use format_pathway\blacklist_helper;
use moodle_url;

/**
 * This type resolver is the second nested layer in the series of resolvers
 * that handle the `get_course_navigation` query for pathway courses.
 * This resolver is responsible for handling information about individual
 * modules inside a section (topic) of a course.
 * This resolver is called indirectly by the `course_section` resolver for
 * each module in a section. The expected shape of
 * the return data is shown in `get_course_navigation.graphql`.
 */
class course_module extends type_resolver {

    /**
     * @inheritDoc
     */
    public static function resolve(string $field, $cm_info, array $args, execution_context $ec) {
        global $USER;

        if (!$cm_info instanceof \cm_info) {
            throw new \coding_exception('Only cm_info objects are accepted: ' . gettype($cm_info));
        }

        switch ($field) {
            case 'id':
                return $cm_info->id;
            case 'available':
                return $cm_info->available;
            case 'name':
                $formatter = new string_field_formatter(format::FORMAT_PLAIN, $ec->get_relevant_context());
                return $formatter->format($cm_info->name);
            case 'modtype':
                return $cm_info->modname;
            case 'availablereason':
                $canviewhidden = has_capability('moodle/course:viewhiddenactivities', $cm_info->context);
                $availableinfo = $cm_info->availableinfo;
                if (($cm_info->available || empty($availableinfo)) && !$canviewhidden) {
                    return [];
                }

                // Pre-load the module and context information.
                $course = $cm_info->get_course();
                $modinfo = get_fast_modinfo($course->id, $USER->id);
                $coursecontext = \context_course::instance($course->id);
                if (empty($availableinfo) && $canviewhidden) {
                    $ci = new \core_availability\info_module($cm_info);
                    if ($ci->get_full_information()) {
                        $availableinfo = $ci->get_full_information();
                    }
                }

                if (empty($availableinfo)) {
                    return [];
                }

                return \core_availability\info::webapi_parse_available_info($availableinfo, $coursecontext, $modinfo);
            case 'visible':
                return $cm_info->visible;
            case 'completion':
                switch ($cm_info->completion) {
                    case COMPLETION_TRACKING_NONE:
                        return 'tracking_none';
                    case COMPLETION_TRACKING_MANUAL:
                        return 'tracking_manual';
                    case COMPLETION_TRACKING_AUTOMATIC:
                        return 'tracking_automatic';
                    default:
                        return 'unknown';
                }
            case 'completionenabled':
                $course = $cm_info->get_course();
                $completioninfo = new \completion_info($course);
                return $completioninfo->is_enabled($cm_info) > COMPLETION_TRACKING_NONE;
            case 'blacklisted':
                return blacklist_helper::is_blacklisted($cm_info->modname);
            case 'viewurl':
                $url = $cm_info->get_url();
                // If there is no URL then we redirect to the edit page.
                if (is_null($url)) {
                    if (has_capability('moodle/course:manageactivities', $cm_info->context)) {
                        return new moodle_url('/course/modedit.php', ['update' => $cm_info->id]);
                    } else {
                        // This shouldn't happen. For activities with no view page, in pathway, either users can't see the
                        // activity or they can edit it. We'll redirect them to the course page as a fallback.
                        return new moodle_url('/course/view.php', ['id' => $cm_info->get_course()->id]);
                    }
                }
                return $url->out(false);
            default:
                return null;
        }
    }
}
