<?php
/**
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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package core_phpunit
 */

namespace core_phpunit\subscriber;

use core_phpunit\internal_util;
use core_phpunit\local\profiling;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\FinishedSubscriber;

class test_finished implements FinishedSubscriber {
    /**
     * Whether the test was marked for isolation. Cannot tell process-level isolation.
     * @var bool
     */
    private bool $is_isolated;

    /**
     * @param bool $isolated
     */
    public function __construct(bool $isolated) {
        $this->is_isolated = $isolated;
    }

    /**
     * Called when a specific test has finished running normally (the test method had to be executed).
     * Only tests that attempt to execute will call this event.
     *
     * @param Finished $event
     * @return void
     */
    public function notify(Finished $event): void {
        if (defined('PHPUNIT_PROFILING')) {
            $class_name = !$event->test()->isPhpt() ? $event->test()->className() : $event->test()->file();
            profiling::testcase_finished($event->test()->name(), $class_name);
        }

        // The tearDown should have already reset everything, so this only runs if somehow that was bypassed.
        // Do some cleanup and then let the test fatally fail.
        if (!$this->is_isolated && !internal_util::test_was_fully_reset()) {
            // Report the debugging messages
            internal_util::report_debugging_messages();

            // Reset the data
            internal_util::reset_all_data();

            // This is exceptional behaviour, make some noise
            throw new \Exception(sprintf('parent::tearDown() failed to be called for "%s"', $event->test()->id()));
        }
    }
}


