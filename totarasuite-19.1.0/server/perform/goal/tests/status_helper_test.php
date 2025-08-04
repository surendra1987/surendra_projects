<?php
/**
 * This file is part of Totara Perform
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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package perform_goal
 */

use core_phpunit\testcase;
use perform_goal\model\status\cancelled;
use perform_goal\model\status\status_helper;

/**
 * Unit tests for the perform_goal status_helper
 *
 * @group perform_goal
 */
class perform_goal_status_helper_test extends testcase {

    public function test_status_from_code() {
        $this->assertInstanceOf(cancelled::class, status_helper::status_from_code('cancelled'));

        try {
            $status = status_helper::status_from_code('fluffy ducks');
            $this->fail('Expected coding exception');
        } catch (coding_exception $e) {
            $this->assertStringContainsString('Status code fluffy ducks is not implemented', $e->getMessage());
        }
    }

    public function test_all_status_codes() {
        $expected = ['not_started', 'in_progress', 'completed', 'cancelled'];
        $this->assertEquals($expected, status_helper::all_status_codes());
    }

    public function test_not_started_codes() {
        $expected = ['not_started'];
        $this->assertEqualsCanonicalizing($expected, status_helper::not_started_status_codes());
    }

    public function test_in_progress_status_codes() {
        $expected = ['in_progress'];
        $this->assertEqualsCanonicalizing($expected, status_helper::in_progress_status_codes());
    }

    public function test_achieved_status_codes() {
        $expected = ['completed'];
        $this->assertEqualsCanonicalizing($expected, status_helper::achieved_status_codes());
    }

    public function test_closed_status_codes() {
        $expected = ['cancelled', 'completed'];
        $this->assertEqualsCanonicalizing($expected, status_helper::closed_status_codes());
    }

    public function test_all_status_labels(): void {
        $expected = [
            'not_started' => 'Not started',
            'in_progress' => 'In progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];

        $actual = status_helper::all_status_labels();
        $this->assertSame($expected, $actual);
    }
}
