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
 * @author Jay Watt <jay.watt@totara.com>
 * @package core_phpunit
 */

namespace core_phpunit\extension\performance\output;

use core_phpunit\extension\performance\test_result;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The printer class handles gathering the test results and converting them to readable output.
 * It covers both writing to the console (using the symphony console that PHP Unit requires) or
 * directly to a file in dataroot.
 */
final class printer {
    /** @var array|test_result[] */
    protected array $test_results = [];

    /** @var OutputInterface */
    protected $output;

    /** @var null|resource */
    protected $file_pointer = null;

    /** @var string|null */
    protected ?string $csv_file;

    /** @var bool */
    protected bool $to_console;

    /** @var bool */
    protected bool $to_file;

    /**
     * @param string|null $csv_file
     * @param bool $to_console
     * @param bool $console_colours
     */
    public function __construct(?string $csv_file, bool $to_console, bool $console_colours) {
        $this->output = new ConsoleOutput(OutputInterface::VERBOSITY_NORMAL, $console_colours);
        $this->csv_file = $csv_file;
        $this->to_file = !empty($csv_file);
        $this->to_console = $to_console;
    }

    /**
     * @param test_result $test_result
     * @return void
     */
    public function add(test_result $test_result): void {
        $this->test_results[$test_result->id] = $test_result;
    }

    /**
     * Write the results to the console (if enabled).
     *
     * @return printer
     */
    public function output_console(): self {
        if (!$this->to_console) {
            return $this;
        }

        foreach ($this->test_results as $test_result) {
            $this->output->writeln(['']);
            $this->output->writeln($test_result->to_console());
        }

        $this->output->writeln(['']);

        return $this;
    }

    /**
     * Write the results to the file (if enabled).
     *
     * @return $this
     */
    public function output_file(): self {
        if (!$this->to_file) {
            return $this;
        }

        // Write the header before logging the first result
        if (!$this->file_pointer) {
            $this->file_pointer = fopen($this->csv_file, 'w');
            fputcsv($this->file_pointer, ['test', 'block', 'time', 'expected']);
        }

        foreach ($this->test_results as $test_result) {
            foreach ($test_result->to_csv() as $block_result) {
                fputcsv($this->file_pointer, $block_result);
            }
        }

        return $this;
    }

    /**
     * Close the hanging file pointer
     *
     * @return $this
     */
    public function close_file(): self {
        if ($this->file_pointer) {
            fclose($this->file_pointer);
            $this->file_pointer = null;
        }

        return $this;
    }

    /**
      * Write the relative file path to the console.
      *
     * @return $this
     */
    public function print_file_location(): self {
        if (!empty($this->csv_file)) {
            $this->output->writeln(['', sprintf('<fg=cyan>Performance log file can be found in "%s"</>', $this->csv_file), '']);
        }

        return $this;
    }

    /**
      * Reset the current test results memory.
      *
     * @return $this
     */
    public function clear(): self {
        $this->test_results = [];
        return $this;
    }
}
