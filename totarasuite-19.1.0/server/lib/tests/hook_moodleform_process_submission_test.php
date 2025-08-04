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

use core\hook\moodleform_process_submission;
use core_phpunit\testcase;
use totara_core\hook\manager;

defined('MOODLE_INTERNAL') || die();

class core_hook_moodleform_process_submission_test extends testcase {

    public function test_moodleform_process_submission_hook() {
        $self = $this;
        $hook = function ($hook) use ($self) {
            $self->assertInstanceOf(moodleform_process_submission::class, $hook);
        };

        $watchers = [
            [
                'hookname' => moodleform_process_submission::class,
                'callback' => $hook,
            ],
        ];
        manager::phpunit_replace_watchers($watchers);

        $mform = $this->getMockBuilder(\moodleform::class)->disableOriginalConstructor()->getMock();
        $submission = ['field' => 'test_field'];
        $files = ['file' => 'test_file'];

        $instance = new moodleform_process_submission($mform, $submission, $files);
        $instance->execute();
        
        $this->assertInstanceOf(\moodleform::class, $instance->mform);
        $this->assertEquals($submission, $instance->submission);
        $this->assertEquals($files, $instance->files);

        totara_core\hook\manager::phpunit_reset();
    }

}
