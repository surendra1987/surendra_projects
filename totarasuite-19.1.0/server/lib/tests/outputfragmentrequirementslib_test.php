<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Simon Chester <simon.chester@totara.com>
 * @package core
 */

global $CFG;
require_once($CFG->libdir . '/outputfragmentrequirementslib.php');

class core_outputfragmentrequirementslib_test extends \core_phpunit\testcase {
    /**
     * Test that the fragment requirements manager can track the requirements of a page.
     *
     * @dataProvider provide_requirements_data
     */
    public function test_requirements_tracking(array $data): void {
        if (isset($data['expected_head_code'])) {
            return;
        }

        $requires = new fragment_requirements_manager();

        foreach ($data['calls'] as $call) {
            $method = $data['method'];
            $requires->$method(...$call);
        }

        if (isset($data['expected_end_code'])) {
            $html = $requires->get_end_code();

            foreach ($data['expected_end_code'] ?? [] as $expected) {
                $this->assert_string_contains_string_once($expected, $html);
            }
        }
    }

    /**
     * @return array
     */
    public static function provide_requirements_data() {
        return include(__DIR__ . '/fixtures/page_requirements_test_data.php');
    }

    /**
     * @return void
     */
    private function assert_string_contains_string_once($needle, $haystack): void {
        $this->assertEquals(1, substr_count($haystack, $needle), "Expected '$needle' to appear once in '$haystack'");
    }
}
