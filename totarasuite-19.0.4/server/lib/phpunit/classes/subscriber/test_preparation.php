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
use core_phpunit\testcase;
use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\Test\PreparationStartedSubscriber;

class test_preparation implements PreparationStartedSubscriber {
    /**
     * Called before each test setup method is executed, once for each test in a class.
     *
     * @param PreparationStarted $event
     * @return void
     */
    public function notify(PreparationStarted $event): void {
        testcase::setCurrentTimeStart();
        internal_util::set_running_test_method($event->test()->id());

        if (defined('PHPUNIT_PROFILING')) {
            profiling::testcase_started();
        }
    }
}
