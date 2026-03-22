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
 * @package totara_customfield
 */

defined('MOODLE_INTERNAL') || die();

use core\hook\moodleform_process_submission;
use core_phpunit\testcase;
use totara_customfield\watcher\process_form_submission;

class totara_customfield_watcher_process_form_submission_test extends testcase {

    public function test_customfield_default_value_is_set() {
        $mform = $this->getMockBuilder(\moodleform::class)->disableOriginalConstructor()->getMock();
        $mform->_form = new \stdClass();
        $mform->_form->_defaultValues = [
            'customfield_empty' => 0.1,
            'customfield_set' => 15,
            'customfield_empty_default' => '',
            'customfield_text' => 'default_value_that_should_not_be_used',
            'unrelated_field' => 0.5,
        ];
        $mform->_form->_types = [
            'customfield_empty' => 'float',
            'customfield_set' => 'integer',
            'customfield_set_empty_default' => 'float',
            'customfield_text' => 'text',
            'unrelated_field' => 'float',
            'field_without_default' => 'text',
        ];

        $submission = [
            'customfield_empty' => '',
            'customfield_set' => 5,
            'customfield_set_empty_default' => 6.66,
            'customfield_text' => '',
            'unrelated_field' => '',
            'field_without_default' => '',
        ];
        $files = []; // Unused

        $hook = new moodleform_process_submission($mform, $submission, $files);
        process_form_submission::set_default_value($hook);

        $this->assertEquals(0.1, $hook->submission['customfield_empty']);
        $this->assertEquals(5, $hook->submission['customfield_set']);
        $this->assertEquals(6.66, $hook->submission['customfield_set_empty_default']);
        $this->assertEquals('', $hook->submission['customfield_text']);
        $this->assertEquals('', $hook->submission['unrelated_field']);
        $this->assertEquals('', $hook->submission['field_without_default']);
    }

}
