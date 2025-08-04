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

namespace core_phpunit\extension\performance\events;

use core_phpunit\extension\performance\output\printer;

/**
 * An event that can work with our timings printer.
 */
abstract class printable {
    /**
     * @var printer
     */
    private printer $printer;

    /**
     * @param printer $printer
     */
    public function __construct(printer $printer) {
        $this->printer = $printer;
    }

    /**
     * @return printer
     */
    public function printer(): printer {
        return $this->printer;
    }
}
