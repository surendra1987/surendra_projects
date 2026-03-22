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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package core_phpunit
 */

namespace core_phpunit\extension\performance\events\test;

use core_phpunit\extension\performance\events\printable;
use core_phpunit\extension\performance\test_result;
use core_phpunit\internal_util;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\Passed as PassedEvent;
use PHPUnit\Event\Test\PassedSubscriber;

/**
 * Runs when the phpunit testcase passes.
 */
class passed extends printable implements PassedSubscriber {

    public function notify(PassedEvent $event): void {
        /** @var TestMethod $test */
        $test = $event->test();

        $block_timings = internal_util::reset_block_times();
        $test_result = test_result::from_test($test, test_result::PASSED, $block_timings);
        $this->printer()->add($test_result);
    }
}
