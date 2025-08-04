<?php
/*
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
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package format_pathway
 */

use format_pathway\overview_helper;

defined('MOODLE_INTERNAL') || die();

global $CFG, $USER;

$context = context_course::instance($course->id);

// Retrieve course format option fields and add them to the $course object.
$course = course_get_format($course)->get_course();

// If not editing and there are no activities then we show a message instead.
if (!overview_helper::is_user_editing()) {
    $first_cm = overview_helper::get_first_course_module_for_user($course->id, $USER->id);
    if (!$first_cm) {
        core\notification::info(get_string('noactivitiesavailable', 'format_pathway'));
        return;
    }
}

// User will be redirected from this view for Overview page.

// Replicate topics format for editing mode and other pages.
require_once($CFG->dirroot . '/course/format/topics/format.php');