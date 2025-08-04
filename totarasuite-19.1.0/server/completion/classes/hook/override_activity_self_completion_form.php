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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author David Curry <david.curry@totara.com>
 * @package core_completion
 */

namespace core_completion\hook;

use cm_info;
use stdClass;
use totara_core\hook\base;

/**
 * Hook for allowing modifications to the activity self completion form.
 */
class override_activity_self_completion_form extends base {

    /**
     * The form to override the activity self completion form as an HTML output string.
     */
    private ?string $activity_self_completion_form = null;

    /**
     * The course module item related to the self completion header.
     */
    private cm_info $cm;

    /**
     * The (optional) course that the module belongs to, if not passed through
     * this will be loaded as needed via the $cm.
     */
    private ?stdClass $course;

    /**
     * The current users completion state for the given $cm
     */
    private string $completion_state;

    /**
     * Pass all the data required for form creation through to the hook for execution.
     *
     * @param string $completion_state
     * @param cm_info $cm - The course module class
     * @param stdClass|null $course - The course associated with the CM
     */
    public function __construct(string $completion_state, cm_info $cm, ?stdClass $course = null) {
        $this->completion_state = $completion_state;
        $this->cm = $cm;
        $this->course = $course;
    }

    /**
     * @return cm_info
     */
    public function get_cm(): cm_info {
        return $this->cm;
    }

    /**
     * If the course wasn't instantiated during construction, load from the $cm object
     *
     * @return \stdClass
     */
    public function get_course(): stdClass {
        if (empty($this->course)) {
            $this->course = get_course($this->cm->course);
        }

        return $this->course;
    }

    /**
     * Get the overriden activity form.
     *
     * @return null|string
     */
    public function get_form(): ?string {
        return $this->activity_self_completion_form;
    }

    /**
     * @param string $form - The replacement self completion form.
     * @return void
     */
    public function set_form(string $form): void {
        $this->activity_self_completion_form = $form;
    }
}
