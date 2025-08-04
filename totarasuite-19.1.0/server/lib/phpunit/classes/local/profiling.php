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

namespace core_phpunit\local;

/**
 * Helper class used during PHPUnit profiling.
 */
class profiling {
    /** @var null|resource */
    private static $fp;

    private static int $start_memory;

    private static float $start_time;

    private static float $total_wait_for_seconds = 0;

    /** @var null|float|int */
    private static $last_class_start_time = null;

    /**
     * @param float $wait
     * @return void
     */
    public static function increment_total_wait_for_seconds(float $wait): void {
        self::$total_wait_for_seconds += $wait;
    }

    /**
     * Run before an individual testcase runs, prepares the profiling file &
     * logs the start times.
     *
     * @return void
     */
    public static function testcase_started(): void {
        // Load the filepath if it hasn't already
        self::get_filepath();

        self::$start_memory = memory_get_usage();
        self::$total_wait_for_seconds = 0;
        self::$start_time = microtime(true);
    }

    /**
     * Run after each individual test finishes (but before the databases are reset).
     * Logs the results.
     *
     * @param string $test_name
     * @param string $test_class_name
     * @return void
     */
    public static function testcase_finished(string $test_name, string $test_class_name): void {
        $total_time = microtime(true) - self::$start_time;
        $memory_diff = memory_get_usage() - self::$start_memory;

        $fp = self::get_filepath();
        fputcsv(
            $fp,
            [
                number_format($total_time, 2),
                number_format(self::$total_wait_for_seconds, 2),
                $memory_diff,
                $test_name,
                $test_class_name
            ]
        );
    }

    /**
     * Called in tearDownAfterClass method of each testcase, records the
     * time taken in each class.
     *
     * @param string $class_name
     * @return void
     */
    public static function cleanup_after_class(string $class_name): void {
        if (self::$last_class_start_time === null) {
            @unlink(self::get_profiling_filepath_class());
            self::$last_class_start_time = filectime(self::get_profiling_filepath_method());
        }

        $total_time = microtime(true) - self::$last_class_start_time;

        $fp = fopen(self::get_profiling_filepath_class(), 'a+');
        fputcsv($fp, [number_format($total_time, 2), $class_name]);
        fclose($fp);

        self::$last_class_start_time = microtime(true);
    }

    private static function get_filepath() {
        if (self::$fp === null) {
            $filepath = self::get_profiling_filepath_method();
            @unlink($filepath);
            self::$fp = fopen($filepath, 'w+');

            // The file handle will get closed automatically at the end when phpunit terminates.
            fputcsv(self::$fp, ['execution time', 'waiting time', 'memory increase', 'method', 'class']);
        }

        return self::$fp;
    }

    /**
     * @return string
     * @internal
     */
    private static function get_profiling_filepath_method() {
        global $DB;
        return __DIR__ . '/../../../../../phpunit_profile_methods_' . $DB->get_dbfamily() . '.csv';
    }

    /**
     * @return string
     * @internal
     */
    private static function get_profiling_filepath_class() {
        global $DB;
        return __DIR__ . '/../../../../../phpunit_profile_classes_' . $DB->get_dbfamily() . '.csv';
    }

}
