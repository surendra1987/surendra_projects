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

use core\output\inplace_editable;
use format_pathway\blacklist_helper;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot. '/course/format/lib.php');
require_once($CFG->dirroot. '/course/format/topics/lib.php');

/**
 * Main class for the Pathway course format
 *
 * @package format_pathway
 */
class format_pathway extends format_topics {

    /**
     * @var array
     */
    public const FORMAT_OPTIONS_WHITE_LIST = ['numsections'];

    /**
     * Indicates whether the course format supports the creation of the Announcements forum.
     *
     * For course format plugin developers, please override this to return true if you want the Announcements forum
     * to be created upon course creation.
     *
     * @return bool
     */
    public function supports_news(): bool {
        // Override to prevent announcements forum.
        return false;
    }

    public function page_set_cm(moodle_page $page): void {
        $coursecontext = context_course::instance($page->course->id);
        $adminoptions = course_get_user_administration_options($page->course, $coursecontext);
        if (!$adminoptions->update) {
            if (blacklist_helper::is_blacklisted($page->cm->modname)) {
                redirect(course_get_url($page->course));
            }
        }

        parent::page_set_cm($page);
    }


    /**
     * @inheritdoc
     */
    public function get_course() {
        $course = parent::get_course();
        if ($this->format !== 'pathway') {
            return $course;
        }

        // To avoid nullable for missing course properties.
        if (!isset($course->coursedisplay)) {
            $course->coursedisplay = get_config('moodlecourse', 'coursedisplay');
        }

        if (!isset($course->headercolor)) {
            $course->headercolor = 0;
        }

        if (!isset($course->collapsiblesections)) {
            $course->collapsiblesections = 0;
        }

        return $course;
    }

    /**
     * @inheritdoc
     */
    public function course_format_options($for_edit_form = false) {
        $course_format_options = parent::course_format_options($for_edit_form);
        if ($this->format !== 'pathway') {
            return $course_format_options;
        }

        // Removed unwanted format options.
        foreach ($course_format_options as $key => $course_format_option) {
            if (!in_array($key, self::FORMAT_OPTIONS_WHITE_LIST)) {
                unset($course_format_options[$key]);
            }
        }

        return $course_format_options;
    }

    /**
     * @inheritDoc
     */
    public function supports_showdescription(): bool {
        return false;
    }

    /**
     * @return bool
     */
    public function supports_blacklisted_activity(): bool {
        return !empty(blacklist_helper::get_blacklist());
    }
}

/**
 * Implements callback inplace_editable() allowing to edit values in-place
 *
 * @param string $itemtype
 * @param int $itemid
 * @param mixed $newvalue
 * @return inplace_editable|null
 */
function format_pathway_inplace_editable($itemtype, $itemid, $newvalue): ?inplace_editable {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/course/lib.php');
    if ($itemtype === 'sectionname' || $itemtype === 'sectionnamenl') {
        $section = $DB->get_record_sql(
            'SELECT s.* FROM {course_sections} s JOIN {course} c ON s.course = c.id WHERE s.id = ? AND c.format = ?',
            array($itemid, 'pathway'),
            MUST_EXIST
        );
        return course_get_format($section->course)->inplace_editable_update_section_name($section, $itemtype, $newvalue);
    }
    return null;
}