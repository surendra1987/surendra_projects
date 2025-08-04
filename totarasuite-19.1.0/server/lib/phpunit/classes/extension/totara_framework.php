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

namespace core_phpunit\extension;

use core_phpunit\subscriber\test_finished;
use core_phpunit\subscriber\test_preparation;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * Extension that handles resetting the environment and database between each test.
 * Defined in the phpunit.xml file as an extension.
 */
final class totara_framework implements Extension {
    /**
     * @param Configuration $configuration
     * @param Facade $facade
     * @param ParameterCollection $parameters
     * @return void
     */
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void {
        // Setup before each test
        $facade->registerSubscriber(new test_preparation());

        // Reset modified data after each test
        $facade->registerSubscriber(new test_finished($configuration->processIsolation()));
    }
}
