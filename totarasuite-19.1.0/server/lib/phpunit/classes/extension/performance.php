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

namespace core_phpunit\extension;

use core_phpunit\extension\performance\events\runner\finished as finished_runner;
use core_phpunit\extension\performance\events\test\failed;
use core_phpunit\extension\performance\events\test\finished;
use core_phpunit\extension\performance\events\test\passed;
use core_phpunit\extension\performance\output\printer;
use core_phpunit\internal_util;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * PHPUnit extension that configures performance results, both outputted to screen but also
 * written out to file.
 */
final class performance implements Extension {
    /**
     * @param Configuration $configuration
     * @param Facade $facade
     * @param ParameterCollection $parameters
     * @return void
     */
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void {
        global $CFG;

        // Only run if we have the group configured
        if (!$this->is_enabled($configuration)) {
            return;
        }

        $to_console = false;
        if ($parameters->has('console')) {
            $value = $parameters->get('console');
            $to_console = ($value == 1 || $value === 'true' || $value === 't');

            if ($to_console) {
                $facade->replaceProgressOutput();
            }
        }

        $console_colours = true;
        if ($parameters->has('console_colours')) {
            $value = $parameters->get('console_colours');
            $console_colours = ($value == 1 || $value === 'true' || $value === 't');
        }

        $file = null;
        if ($parameters->has('log_file')) {
            $log_file = $parameters->get('log_file');
            if (!empty($log_file)) {
                $file = "{$CFG->dataroot}/$log_file";
                if (!str_ends_with($file, '.csv')) {
                    throw new \coding_exception('performance->log_file must be in filename.csv format');
                }
                internal_util::add_dataroot_reset_skip($log_file);
            }
        }

        internal_util::record_block_times();

        // Create a shared printer
        $printer = new printer($file, $to_console, $console_colours);

        // Add our normal subscribers
        $subscribers = [
            passed::class,
            failed::class,
            finished::class,
            finished_runner::class
        ];
        foreach ($subscribers as $subscriber) {
            $facade->registerSubscriber(new $subscriber($printer));
        }
    }

    /**
     * Determine if this extension should run.
     *
     * @param Configuration $configuration
     * @return bool
     */
    private function is_enabled(Configuration $configuration): bool {
        if (!$configuration->hasGroups()) {
            return false;
        }

        $groups = $configuration->groups();
        return in_array('performance', $groups, true);
    }
}
