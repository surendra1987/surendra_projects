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

namespace core_phpunit\extension\performance;

use PHPUnit\Event\Code\TestMethod;

/**
 * Represents the results of a single testcase.
 */
final class test_result {

    public const FAILED = 0;
    public const PASSED = 1;

    /**
     * @var string Unique test ID
     */
    public readonly string $id;

    /**
     * @var string Name to be presented back for the entire block.
     */
    protected string $test_case_name;

    /**
     * @var array The collection of timings captured during this execution.
     */
    protected array $time_blocks;

    /**
     * @var int State whether it passed or failed.
     */
    protected int $state;

    /**
     * @param string $id
     * @param string $test_case_name
     * @param int $state
     * @param array $time_blocks
     */
    private function __construct(string $id, string $test_case_name, int $state, array $time_blocks) {
        $this->id = $id;
        $this->test_case_name = $test_case_name;
        $this->state = $state;
        $this->time_blocks = $time_blocks;
    }

    /**
     * Create a new test result from a test method object.
     *
     * @param TestMethod $test
     * @param int $state
     * @param array $timings
     * @return self
     */
    public static function from_test(\PHPUnit\Event\Code\TestMethod $test, int $state, array $timings): self {
        return new self($test->id(), $test->nameWithClass(), $state, $timings);
    }

    /**
     * Convert the result into a console-friendly output
     *
     * @return array
     */
    public function to_console(): array {
        $colour = $this->state === self::PASSED ? 'green' : 'yellow';

        $results = [
            sprintf('<fg=%s>%s</>', $colour, $this->test_case_name),
        ];

        if (empty($this->time_blocks)) {
            $results[] = '  No timing assertions made';
            return $results;
        }

        // Widths calculation
        $widest = max(array_map('mb_strlen', array_keys($this->time_blocks))) + 2;
        if ($widest < 50) {
            $widest = 50;
        }

        foreach ($this->time_blocks as $label => $times) {
            [$execution_time, $time_limit] = $times;

            $execution_time_seconds = $execution_time / 1e+9;
            $results[] = sprintf(
                '  %s <fg=%s>%s %s</> (%s)',
                str_pad($label, $widest, '.'),
                $execution_time_seconds > $time_limit ? 'red' : 'green',
                $execution_time_seconds > $time_limit ? '⨯' : '✓',
                number_format($execution_time_seconds, 5),
                number_format($time_limit, 2),
            );
        }

        return $results;
    }

    /**
     * Convert the test result into a CSV row per time block recorded.
     *
     * @return array
     */
    public function to_csv(): array {
        $results = [];
        foreach ($this->time_blocks as $label => $time) {
            $results[] = [
                $this->test_case_name,
                $label,
                ...$time,
            ];
        }

        return $results;
    }
}

