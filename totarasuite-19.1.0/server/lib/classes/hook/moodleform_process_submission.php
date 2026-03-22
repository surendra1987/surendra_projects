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
 * @author Mark Metcalfe <mark.metcalfe@totara.com>
 * @package core
 */

namespace core\hook;

use totara_core\hook\base;

defined('MOODLE_INTERNAL') || die();

/**
 * Hook to allow modification of Moodleform submission data before it is processed.
 */
class moodleform_process_submission extends base {

    /**
     * @var \moodleform
     */
    public \moodleform $mform;

    /**
     * @var array
     */
    public array $submission;

    /**
     * @var array
     */
    public array $files;

    /**
     * @param \moodleform $mform, moodle form
     * @param array $submission
     * @param array $files
     */
    public function __construct(\moodleform $mform, array $submission, array $files) {
        $this->mform = $mform;
        $this->submission = $submission;
        $this->files = $files;
    }

}
