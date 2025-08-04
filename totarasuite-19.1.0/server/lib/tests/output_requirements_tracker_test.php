<?php

use core\output\requirements_tracker;

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

class core_output_requirements_tracker_test extends \core_phpunit\testcase {
    public function test_add_and_retrieve() {
        $tracker = new requirements_tracker();
        $tracker->add('foo', 1);
        $tracker->add('foo', 2);
        $tracker->add('foo', 3);
        $tracker->add('bar', 4);

        $this->assertEquals([1, 2, 3], $tracker->get_entries('foo'));
        $this->assertEquals([4], $tracker->get_entries('bar'));
    }

    public function test_unique_keys() {
        $tracker = new requirements_tracker();
        $tracker->add('foo', 1, 'keyA');
        $tracker->add('foo', 2, 'keyA');
        $tracker->add('foo', 3, 'keyB');

        $this->assertEquals([2, 3], $tracker->get_entries('foo'));
        $this->assertEquals(2, $tracker->get_by_key('foo', 'keyA'));
        $this->assertEquals(3, $tracker->get_by_key('foo', 'keyB'));
    }

    public function test_source_tracking() {
        $tracker = new requirements_tracker();
        $this->assertEquals(requirements_tracker::DEFAULT_ORIGIN, $tracker->get_origin());
        $tracker->add('foo', 1);
        $tracker->with_origin('shell', function () use ($tracker) {
            $this->assertEquals('shell', $tracker->get_origin());
            $tracker->add('foo', 2);

            // test nested
            $tracker->with_origin(requirements_tracker::DEFAULT_ORIGIN, function () use ($tracker) {
                $this->assertEquals(requirements_tracker::DEFAULT_ORIGIN, $tracker->get_origin());
                $tracker->add('foo', 3);
            });

            $tracker->with_origin('shell', function () use ($tracker) {
                $this->assertEquals('shell', $tracker->get_origin());
                $tracker->add('foo', 4);
            });
        });
        $tracker->add('foo', 5);
        $this->assertEquals(requirements_tracker::DEFAULT_ORIGIN, $tracker->get_origin());

        // non-source-aware mode should preserve original page_requirements_manager behavior
        $this->assertEquals([1, 2, 3, 4, 5], $tracker->get_entries('foo'));
        // but source-aware should return each source's view of requirements
        $this->assertEquals([1, 3, 5], $tracker->get_entries('foo', requirements_tracker::DEFAULT_ORIGIN));
        $this->assertEquals([2, 4], $tracker->get_entries('foo', 'shell'));
    }

    public function test_source_tracking_with_unique_keys() {
        $tracker = new requirements_tracker();
        $tracker->add('foo', 1, 'keyA');
        $tracker->add('foo', 44, 'key44');

        $tracker->with_origin('shell', function () use ($tracker) {
            $tracker->add('foo', 2, 'keyA');
            $tracker->add('foo', 55, 'key55');
        });

        // non-source-aware mode should preserve original page_requirements_manager behavior
        $this->assertEquals([2, 44, 55], $tracker->get_entries('foo'));
        $this->assertEquals(2, $tracker->get_by_key('foo', 'keyA'));
        // but source-aware should return each source's view of requirements
        $this->assertEquals([1, 44], $tracker->get_entries('foo', requirements_tracker::DEFAULT_ORIGIN));
        $this->assertEquals([2, 55], $tracker->get_entries('foo', 'shell'));
        $this->assertEquals(1, $tracker->get_by_key('foo', 'keyA', requirements_tracker::DEFAULT_ORIGIN));
        $this->assertEquals(2, $tracker->get_by_key('foo', 'keyA', 'shell'));
    }
}
