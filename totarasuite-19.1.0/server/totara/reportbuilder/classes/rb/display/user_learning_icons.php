<?php
/*
 * This file is part of Totara Learn
 *
 * Copyright (C) 2018 onwards Totara Learning Solutions LTD
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
 * @author Simon Player <simon.player@totaralearning.com>
 * @package totara_reportbuilder
 */

namespace totara_reportbuilder\rb\display;

use context_user;
use totara_core\advanced_feature;

/**
 * Display class intended for users learning items
 *
 * @author Simon Player <simon.player@totaralearning.com>
 * @package totara_reportbuilder
 */
class user_learning_icons extends base {

    /**
     * Handles the display
     *
     * @param string $value
     * @param string $format
     * @param \stdClass $row
     * @param \rb_column $column
     * @param \reportbuilder $report
     * @return string
     */
    public static function display($value, $format, \stdClass $row, \rb_column $column, \reportbuilder $report) {
        global $CFG, $OUTPUT, $USER;

        static $systemcontext;
        if (!isset($systemcontext)) {
            $systemcontext = \context_system::instance();
        }
        $user_context = context_user::instance($value);

        $disp = \html_writer::start_tag('span', array('style' => 'white-space:nowrap;'));

        // Learning Records icon.
        // We need to perform a similar check to the rb_plan_courses_embedded->is_capable method here.
        if (advanced_feature::is_enabled('recordoflearning') && (
            \totara_job\job_assignment::is_managing($USER->id, $value) ||
            has_capability('totara/plan:accessanyplan', $systemcontext, $USER->id) ||
            has_capability('totara/core:viewrecordoflearning', $user_context, $USER->id))
        ) {
            $disp .= \html_writer::start_tag('a', array('href' => $CFG->wwwroot . '/totara/plan/record/index.php?userid=' . $value));
            $disp .= $OUTPUT->flex_icon('recordoflearning', ['classes' => 'ft-size-300']);
            $disp .= \html_writer::end_tag('a');
        }

        // Face To Face Bookings icon.
        if ($report->src->get_staff_f2f()) {
            $disp .= \html_writer::start_tag('a', array('href' => $CFG->wwwroot . '/my/bookings.php?userid=' . $value));
            $disp .= $OUTPUT->flex_icon('calendar', ['classes' => 'ft-size-300']);
            $disp .= \html_writer::end_tag('a');
        }

        // Individual Development Plans icon.
        if (advanced_feature::is_enabled('learningplans') && dp_can_view_users_plans($value, $USER->id)) {
            $disp .= \html_writer::start_tag('a', array('href' => $CFG->wwwroot . '/totara/plan/index.php?userid=' . $value));
            $disp .= $OUTPUT->flex_icon('learningplan', ['classes' => 'ft-size-300']);
            $disp .= \html_writer::end_tag('a');
        }

        $disp .= \html_writer::end_tag('span');

        return $disp;
    }

    /**
     * Is this column graphable?
     *
     * @param \rb_column $column
     * @param \rb_column_option $option
     * @param \reportbuilder $report
     * @return bool
     */
    public static function is_graphable(\rb_column $column, \rb_column_option $option, \reportbuilder $report) {
        return true;
    }
}
