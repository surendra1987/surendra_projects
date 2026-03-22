<?php
/**
 * This file is part of Totara Learn
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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package core_enrol
 */

namespace core_enrol\testing;

use context_course;
use enrol_instance_edit_form;
use moodle_url;
use stdClass;

global $CFG;
require_once($CFG->dirroot.'/enrol/editinstance_form.php');
require_once($CFG->libdir . '/enrollib.php');

/**
 * Use this trait wherever you need an enrolment instance settings form, or the parameters used for one.
 */
trait enrol_instance_edit_form_test_setup {

    /**
     * Construct parameters for a common enrolment moodle form for use in tests.
     *
     * @param ?stdClass $course Leave null to generate a new course
     * @return array list($instance, $plugin, $context, $type, $return)
     */
    private function enrol_instance_edit_form_params(stdClass $course = null): array {
        if (is_null($course)) {
            $course = $this->getDataGenerator()->create_course();
        }
        $context = context_course::instance($course->id);

        // Choose 'self' as an arbitrary plugin type
        $type = 'self';
        $plugin = enrol_get_plugin($type);
        $instance = (object)$plugin->get_instance_defaults();
        $instance->id       = null;
        $instance->courseid = $course->id;
        $instance->status   = ENROL_INSTANCE_ENABLED;
        $instance->enrol    = 'self';
        // This is the URL to which the user would be redirected on success
        $return = new moodle_url('/enrol/instances.php', array('id' => $course->id));
        return array($instance, $plugin, $context, $type, $return);
    }

    /**
     * Construct a common enrolment moodle form for use in tests
     *
     * @return enrol_instance_edit_form
     */
    private function create_new_enrol_instance_edit_form(): enrol_instance_edit_form {
        return new enrol_instance_edit_form(null, $this->enrol_instance_edit_form_params());
    }

    /**
     * Minimal data required to validate the enrol_instance_edit_form, because null coalescing operators weren't a thing
     * when the self-enrolment plugin was written.
     *
     * @return array
     */
    private function get_validation_test_data(): array {
        return [
            'enrolstartdate' => '',
            'enrolenddate' => '',
            'name' => '',
            'customint1' => '',
            'customint2' => '',
            'customint3' => '',
            'customint4' => '',
            'customint5' => '',
            'customint6' => '',
            'status' => '',
            'enrolperiod' => '',
            'expirynotify' => '',
            'roleid' => '',
            'password' => '',
            'expirythreshold' => '',
        ];
    }
}