<?php
/*
 * This file is part of Totara LMS
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Rami Habib <rami.habib@totara.com>
 * @package enrol_totara_learningplan
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Certification module PHPUnit archive test class.
 *
 * To test, run this from the command line from the $CFG->dirroot.
 * vendor/bin/phpunit --verbose enrol_totara_learningplan_lib_testcase enrol/tests/totara_learningplan_lib_test.php
 */
class core_enrol_totara_learningplan_lib_test extends \core_phpunit\testcase {

    /**
     * Test that add_instance is working correctly.
     *
     */
    public function test_add_instance() {
        global $DB;

        $enabled = enrol_get_plugins(true);
        $enabled['totara_learningplan'] = true;
        $enabled = array_keys($enabled);
        set_config('enrol_plugins_enabled', implode(',', $enabled));

        // Create course enrolment instance with out of the box enrolment values.
        $generator = $this->getDataGenerator();
        $plugin = enrol_get_plugin('totara_learningplan');

        $enrol1 = $DB->get_record(
            'enrol',
            ['id' => $plugin->add_default_instance($generator->create_course())],
            '*',
            MUST_EXIST
        );

        self::assertEquals(
            $DB->get_record('role', ['shortname' => 'student'])->id,
            $enrol1->roleid
        );

        self::assertEquals(0, $enrol1->enrolperiod);

        // Create course enrolment instance with custom enrolment values.
        $new_role_id = $DB->get_record('role', ['shortname' => 'editingteacher'])->id;
        $plugin->set_config('roleid', $new_role_id);

        $enrol2 = $DB->get_record(
            'enrol',
            ['id' => $plugin->add_default_instance($generator->create_course())],
            '*',
            MUST_EXIST
        );

        self::assertEquals($new_role_id, $enrol2->roleid);
    }
}
