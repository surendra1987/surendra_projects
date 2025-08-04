<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package core_tag
 */

namespace core\webapi\resolver\query;

use core\webapi\execution_context;
use core\webapi\middleware\require_authenticated_user;
use core\webapi\middleware\require_system_capability;
use core\webapi\query_resolver;
use core_ai\subsystem;
use core_tag\ai\interaction\suggest_tags;
use core_tag\ai\interaction\suggest_tags_input;
use core_tag\model\tag;
use core_tag_area;

/**
 * Endpoint for suggesting tags for a specific course.
 * This is an experimental endpoint.
 */
class ai_course_suggest_tags extends query_resolver {
    /**
     * @param array $args
     * @param execution_context $ec
     * @return array
     */
    public static function resolve(array $args, execution_context $ec) {
        global $CFG;

        $input = $args['input'] ?? [];
        if (!empty($input['course_id'])) {
            $course_context = \context_course::instance($input['course_id']);
            require_capability('moodle/course:tag', $course_context);

            $ec->set_relevant_context($course_context);
        }

        // Only run if AI subsystem is ready & tags are enabled for courses.
        if (!subsystem::is_ready() || empty($CFG->usetags) || !core_tag_area::is_enabled('core', 'course')) {
            return [
                'items' => [],
                'total' => 0,
            ];
        }

        // Build our content document up
        $suggested_tags_input = static::create_suggested_tags_input($input);

        // Run the suggestions
        $interaction = new suggest_tags('core', 'course');
        $suggest_tags_output = $interaction->run($suggested_tags_input);
        return [
            'items' => $suggest_tags_output->suggested_tags ?? [],
            'total' => $suggest_tags_output->get_count() ?? 0
        ];
    }

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            new require_authenticated_user(),
            new require_system_capability('moodle/course:tag'),
        ];
    }

    /**
     * Build all necessary data for suggest tags interaction.
     *
     * @param array $input
     * @return suggest_tags_input
     */
    protected static function create_suggested_tags_input(array $input): suggest_tags_input {
        $existing_tag_ids = [];
        $content = [];

        // Pull out from the existing course record
        $course_id = $input['course_id'] ?? null;
        $format_options = [];
        if (!empty($course_id)) {
            $course = \core_course\model\course::load_by_id($course_id);
            $existing_tag_ids = array_map(function (tag $tag) {
                return $tag->get_id();
            }, tag::get_tags_by_item($course->get_id(), 'course', 'core'));
            $context = \context_course::instance($course->get_id());

            $format_options = ['context' => $context];

            // Build our document up
            $content = [
                'fullname' => format_string($course->fullname, true, $format_options),
                'shortname' => format_string($course->shortname, true, $format_options),
                'description' => html_to_text(format_text($course->summary, $course->summaryformat, $format_options)),
            ];
        }

        // If posted content is provided, override the fields
        if (!empty($input['course'])) {
            $course = $input['course'];
            if (!empty($course['fullname'])) {
                $content['fullname'] = format_string($course['fullname'], $format_options);
            }
            if (!empty($course['shortname'])) {
                $content['shortname'] = format_string($course['shortname'], $format_options);
            }
            if (!empty($course['description'])) {
                $format = $course['description_format'] ?? FORMAT_PLAIN;
                $content['description'] = html_to_text(format_text($course['description'], $format, $format_options));
            }
        }

        // Strip out any blank entries
        $content = array_filter($content, function ($value) {
            return !empty(trim($value ?? ''));
        });
        $content = implode("\n", $content);

        return new suggest_tags_input($content, $existing_tag_ids);
    }
}
