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
use PHPUnit\Event\Test\Finished as FinishedEvent;
use PHPUnit\Event\Test\FinishedSubscriber;

/**
 * Runs when the phpunit testcase finishes.
 * Will progressively print out the results.
 */
class finished extends printable implements FinishedSubscriber {

    public function notify(FinishedEvent $event): void {
        // Print the console/file, and then reset the results memory
        $this->printer()
            ->output_console()
            ->output_file()
            ->clear();
    }
}
