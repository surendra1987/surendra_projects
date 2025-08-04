<?php
/*
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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package totara_cohort
 */

/**
 * Output renderer for totara_cohort module
 */
class totara_cohort_renderer extends plugin_renderer_base {

    /**
     * Print a message along with Cancel button when a user is prevented from deleting a cohort.
     *
     * @param string $cohort_name Name of cohort
     * @param string $reason Reason cohort can not be deleted
     * @param int $page_context_id Context ID in the page url
     * @return string HTML fragment
     */
    public function show_prevent_delete_warning(string $cohort_name, string $reason, int $page_context_id): string {
        $cancel = html_writer::link(
            new moodle_url('/cohort/index.php', ['contextid' => $page_context_id]),
            get_string('cancel'), ['class' => 'btn singlecancel']
        );

        $output = $this->box_start('generalbox modal modal-dialog modal-in-page show');
        $output .= $this->box_start('modal-content');
        $output .= $this->box_start('modal-header');
        $output .= html_writer::tag(
            'h2',
            get_string('deletecohort', 'totara_cohort', $cohort_name)
        );
        $output .= $this->box_end();
        $output .= $this->box_start('modal-body');
        $output .= html_writer::tag(
            'p',
            html_writer::tag(
                'strong',
                get_string('cannotdeletecohort', 'totara_cohort')
            )
        );
        $output .= html_writer::tag('p', $reason);
        $output .= $this->box_end();
        $output .= $this->box_start('modal-footer');
        $output .= html_writer::tag('div', $cancel, array('class' => 'buttons'));
        $output .= $this->box_end();
        $output .= $this->box_end();
        $output .= $this->box_end();

        return $output;
    }
}
