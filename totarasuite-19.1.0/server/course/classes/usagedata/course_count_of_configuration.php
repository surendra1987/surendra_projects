<?php
/**
 * This file is part of Totara Talent Experience Platform
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package core_course
 */

namespace core_course\usagedata;

use tool_usagedata\export;

class course_count_of_configuration implements export {

    /**
     * @throws \coding_exception
     */
    public function get_summary(): string {
        return get_string('course_count_of_configuration_summary');
    }

    /**
     * @inheritDoc
     */
    public function get_type(): int {
        return export::TYPE_OBJECT;
    }

    /**
     * @throws \dml_exception
     */
    public function get_toggle_options(): array {
        global $DB;

        $configuration_options = [
            'showgrades',
            'startdate',
            'enddate',
            'marker',
            'legacyfiles',
            'visible',
            'requested',
            'enablecompletion',
            'completionstartonenrol',
            'completionprogressonview',
            'completionnotify',
            'groupmodeforce',
        ];

        $result = [];
        foreach ($configuration_options as $option) {
            $result[$option] = $DB->count_records_select('course', $option . ' <> 0');
        }

        return $result;
    }

    /**
     * @throws \dml_exception
     */
    public function get_course_formats(): array {
        global $DB;

        $formats = $DB->get_records_sql_menu('SELECT c.format, COUNT(c.id) as formatcount FROM {course} c GROUP BY c.format');

        $return = [];
        foreach ($formats as $format => $count) {
            $return[] = [
                'format' => $format,
                'count' => (int) $count,
            ];
        }

        return $return;
    }

    /**
     * @throws \dml_exception
     */
    public function get_summary_formats(): array {
        global $DB;

        $summary_formats = $DB->get_records_sql_menu('SELECT c.summaryformat, COUNT(c.id) as summaryformatcount FROM {course} c GROUP BY c.summaryformat');

        $return = [];
        foreach ($summary_formats as $summary_format => $count) {
            $return[] = [
                'format' => $summary_format,
                'count' => (int) $count,
            ];
        }

        return $return;
    }

    /**
     * @throws \dml_exception
     */
    public function get_group_mode(): array {
        global $DB;

        $group_modes = $DB->get_records_sql_menu(
            'SELECT c.groupmode, COUNT(c.id) as groupmodecount FROM {course} c GROUP BY c.groupmode'
        );

        $return = [];
        foreach ($group_modes as $group_mode => $count) {
            $return[] = [
                'mode' => $group_mode,
                'count' => (int) $count,
            ];
        }
        return $return;
    }

    /**
     * @throws \dml_exception
     */
    public function get_lang(): array {
        global $DB;

        $langs = $DB->get_records_sql_menu('SELECT c.lang, COUNT(c.id) as langcount FROM {course} c GROUP BY c.lang');

        $return = [];
        foreach ($langs as $lang => $count) {
            $return[] = [
                'lang' => $lang,
                'count' => (int) $count,
            ];
        }

        return $return;
    }

    /**
     * @throws \dml_exception
     */
    public function get_audience_visible(): array {
        global $DB;

        $audience_visibles = $DB->get_records_sql_menu(
            'SELECT c.audiencevisible, COUNT(c.id) as audiencevisiblecount FROM {course} c GROUP BY c.audiencevisible'
        );

        $return = [];
        foreach ($audience_visibles as $audience_visible => $count) {
            $return[] = [
                'visible' => $audience_visible,
                'count' => (int) $count,
            ];
        }

        return $return;
    }

    /**
     * @throws \dml_exception
     */
    public function get_course_types(): array {
        global $DB;

        $course_types = $DB->get_records_sql_menu(
            'SELECT c.coursetype as type, COUNT(c.id) as coursetypecount FROM {course} c GROUP BY c.coursetype'
        );

        $return = [];
        foreach ($course_types as $type => $count) {
            $return[] = [
                'type' => $type,
                'count' => (int) $count,
            ];
        }
        return $return;
    }

    /**
     * @throws \dml_exception
     */
    public function get_container_type(): array {
        global $DB;

        $types = $DB->get_records_sql_menu('SELECT c.containertype, COUNT(c.id) as containertypecount FROM {course} c GROUP BY c.containertype');

        $return = [];
        foreach ($types as $type => $count) {
            $return[] = [
                'type' => $type,
                'count' => (int) $count,
            ];
        }

        return $return;
    }

    /**
     * @throws \dml_exception
     */
    public function get_course_aggregates(): array {
        global $DB;

        // Fetch any course records matching our criteria
        $courses = $DB->get_records_select(
            'course',
            'icon IS NOT NULL OR duedate IS NOT NULL OR duedateoffsetamount IS NOT NULL OR duedateoffsetunit IS NOT NULL OR theme <> \'\'',
            null,
            '',
            'id, icon, duedate, duedateoffsetamount, duedateoffsetunit, theme'
        );

        $counts = [
            'icon' => 0,
            'duedate' => 0,
            'duedateoffsetamount' => 0,
            'duedateoffsetunit' => 0,
            'theme' => 0,
        ];

        foreach ($courses as $course) {
            if ($course->icon !== null) {
                $counts['icon']++;
            }

            if ($course->duedate !== null) {
                $counts['duedate']++;
            }

            if ($course->duedateoffsetamount !== null) {
                $counts['duedateoffsetamount']++;
            }

            if ($course->duedateoffsetunit !== null) {
                $counts['duedateoffsetunit']++;
            }

            if ($course->theme !== '') {
                $counts['theme']++;
            }
        }

        return $counts;
    }

    /**
     * @throws \dml_exception
     */
    public function export(): array {
        global $DB;

        return array_merge(
            $this->get_toggle_options(),
            $this->get_course_aggregates(),
            [
                'total' => $DB->count_records('course'),
                'format' => $this->get_course_formats(),
                'summaryformat' => $this->get_summary_formats(),
                'audiencevisible' => $this->get_audience_visible(),
                'groupmode' => $this->get_group_mode(),
                'lang' => $this->get_lang(),
                'coursetype' => $this->get_course_types(),
                'containertype' => $this->get_container_type(),
            ]
        );
    }
}