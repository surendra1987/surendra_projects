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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package totara_mobile
 */


namespace totara_mobile\webapi\resolver\type;

use coding_exception;
use context_course;
use core\webapi\execution_context;
use core_course\model\course;
use totara_mobile\download\attachment_info;
use totara_mobile\download\download_helper;
use core\webapi\resolver\type\course as core_course;

class course_content extends core_course {
    /**
     * @param string $field
     * @param $course
     * @param array $args
     * @param execution_context $ec
     *
     * @return mixed
     */
    public static function resolve(string $field, $course, array $args, execution_context $ec) {
        global $CFG, $USER;

        require_once $CFG->libdir . '/grade/grade_grade.php';
        require_once $CFG->libdir . '/grade/grade_item.php';
        require_once $CFG->libdir . '/gradelib.php';

        if (!$course instanceof course) {
            throw new coding_exception('Only course objects are accepted: ' . gettype($course));
        }

        if (!$ec->get_relevant_context()) {
            $course_context = context_course::instance($course->id, IGNORE_MISSING);
            if (!$course_context) {
                // If there is no matching context we have a bad object, ignore missing so we can do our own error.
                throw new \coding_exception('Only valid course objects are accepted');
            }
        } else {
            $course_context = $ec->get_relevant_context();
        }

        if ($field == 'attachments') {
            $overviewfiles = download_helper::get_attachments_for_content($course_context, 'course', 'overviewfiles', 0);
            $summaries = download_helper::get_attachments_for_content($course_context, 'course', 'summary', 0);
            return array_merge($overviewfiles, $summaries);
        }

        if ($field == 'image') {
            $image = download_helper::get_attachments_for_content($course_context, 'course', 'images', 0);
            if (empty($image)) {
                global $PAGE, $CFG;

                $url = (course_get_image($course->to_stdClass()))->out();
                if ($PAGE->theme->name == 'inspire') {
                    $file = $CFG->dirroot. "/theme/inspire/pix_core/course_defaultimage.svg";
                } else {
                    $file = $CFG->dirroot. "/pix/course_defaultimage.png";
                }

                $image = [new attachment_info($url, filesize($file))];
            }
            return reset($image);
        }

        if (in_array($field, ['formatted_gradefinal', 'formatted_grademax'])) {
            $course_item = \grade_item::fetch_course_item($course->id);
            $grade = new \grade_grade(array('itemid' => $course_item->id, 'userid' => $USER->id));

            // The default grade decimals is 2
            $defaultdecimals = 2;
            if (property_exists($CFG, 'grade_decimalpoints')) {
                $defaultdecimals = $CFG->grade_decimalpoints;
            }
            $decimals = grade_get_setting($course->id, 'decimalpoints', $defaultdecimals);

            if ($field == 'formatted_gradefinal') {
                return format_float($grade->finalgrade, $decimals, true);
            }

            if ($field == 'formatted_grademax') {
                return format_float($grade->rawgrademax, $decimals, true);
            }
        }

        if ($field == 'sections') {
            $modinfo = \course_modinfo::instance($course->id);

            $course_format = course_get_format($course);
            $numsections = $course_format->get_last_section_number();
            $uses_sections = $course_format->uses_sections();
            $rawsections = $modinfo->get_section_info_all();

            if (has_capability('moodle/course:viewhiddensections', $course_context)) {
                return $rawsections;
            }
            $sections = [];
            foreach ($rawsections as $key => $section) {
                if ($uses_sections && $key == 0 && !($section->summary || !empty($modinfo->sections[0]))) {
                    continue;
                }
                if ($key <= $numsections && $section->__get('visible')) {
                    $sections[$key] = $section;
                }
            }

            return $sections;
        }

        $record = $course->get_entity_copy()->to_record();
        return parent::resolve($field, $record, $args, $ec);
    }
}