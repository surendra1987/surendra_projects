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

use core\format;
use core\webapi\execution_context;
use core\webapi\formatter\field\string_field_formatter;
use core\webapi\type_resolver;
use format_pathway\blacklist_helper;

/**
 * This type resolver is the first nested layer in the series of resolvers
 * that handle the `get_course_navigation` query for pathway courses.
 * This resolver is responsible for handling information about the
 * sections (topics) of a course.
 * This resolver is called indirectly by the `course_navigation` resolver for
 * each section in a course. The expected shape of
 * the return data is shown in `get_course_navigation.graphql`.
 */
class course_section extends type_resolver {

    /**
     * @inheritDoc
     */
    public static function resolve(string $field, $section, array $args, execution_context $ec) {
        global $USER;

        if (!$section instanceof \section_info) {
            throw new \coding_exception('Only section_info objects are accepted: ' . gettype($section));
        }
        $course = $section->course;
        $context = \context_course::instance($course);

        switch ($field) {
            case 'id':
                return $section->id;
            case 'available':
                return $section->available;
            case 'availablereason':
                if ($section->available) {
                    return [];
                }
                $availableinfo = $section->availableinfo;

                if (!empty($availableinfo)) {
                    // Pre-load the module and context information.
                    $modinfo = get_fast_modinfo($course, $USER->id);
                    return \core_availability\info::webapi_parse_available_info($availableinfo, $context, $modinfo);
                }
                return [];
            case 'modules':
                //Don't show the modules if the user can't see restricted sections.
                if (!$section->uservisible && !has_capability('moodle/course:viewhiddensections', $context, $USER->id)) {
                    return [];
                }

                $seeall = has_capability('moodle/course:viewhiddenactivities', $context, $USER->id);
                $cms = $section->modinfo->get_cms();

                if (is_numeric($course)) {
                    $adminoptions = course_get_user_administration_options(get_course($course), $context);
                } else {
                    $adminoptions = course_get_user_administration_options($course, $context);
                }

                $sectionmodules = [];
                foreach ($cms as $mod) {
                    // If the activity is blacklisted and the user cannot manage sections and activities then exclude it.
                    if (!$adminoptions->update) {
                        if (blacklist_helper::is_blacklisted($mod->modname)) {
                            continue;
                        }
                    }

                    // Limit this to visible course modules in the section.
                    $same_section = $mod->section == $section->id;
                    $module_is_visible = $mod->visible && ($mod->uservisible || !empty($mod->availableinfo));
                    if ($same_section && ($seeall || $module_is_visible)) {
                        $sectionmodules[] = $mod;
                    }
                }

                return $sectionmodules;
            case 'title':
                $formatter = new string_field_formatter(format::FORMAT_PLAIN, $ec->get_relevant_context());
                $cformat = course_get_format($course);
                return $formatter->format($cformat->get_section_name($section));
            case 'visible':
                return $section->visible;
            default:
                return null;
        }
    }
}
