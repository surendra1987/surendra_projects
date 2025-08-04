<?php
/**
* This file is part of Totara Learn
*
* Copyright (C) 2022 onwards Totara Learning Solutions LTD
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
* @author  Johannes Cilliers <johannes.cilliers@totaralearning.com>
* @package tool_diagnostic
*/

namespace tool_diagnostic\provider;

use core_php_time_limit;
use tool_diagnostic\content\content;

class performance_io extends base {

    /** @var int */
    protected static $order = 120;

    /**
     * @inheritDoc
     */
    public static function get_id(): string {
        return 'performance_io';
    }

    /**
     * @inheritDoc
     */
    public function get_content(): content {
        $content = new content();
        $content->set_id(static::get_id());
        $content->set_data_type(content::TYPE_TEXT);
        $content->set_data($this->run_tests());

        return $content;
    }

    /**
     * Run IO tests.
     *
     * @return string
     */
    private function run_tests(): string {
        global $CFG;

        raise_memory_limit(MEMORY_EXTRA);
        core_php_time_limit::raise(0);

        $dataroot_file = $CFG->dataroot.'/temp/profile_test.csv';
        $cachedir_file = $CFG->cachedir.'/profile_test.csv';

        if (file_exists($dataroot_file)) {
            unlink($dataroot_file);
        }
        if (file_exists($cachedir_file)) {
            unlink($cachedir_file);
        }

        // First create some random data.
        $data = [];
        for ($i = 0; $i < 20; $i++) {
            $data[] = random_string(20);
        }

        // Run performance checks.
        $output = 'Testing dataroot file write speed:' . PHP_EOL;
        $output .= $this->write_file($dataroot_file, $data);

        $output .= 'Testing cachedir file write speed:' . PHP_EOL;
        $output .= $this->write_file($cachedir_file, $data);

        $output .='Testing dataroot file read speed:' . PHP_EOL;
        $output .= $this->read_file($dataroot_file);

        $output .= 'Testing cachedir file read speed:' . PHP_EOL;
        $output .= $this->read_file($cachedir_file);

        // Clean up.
        unlink($dataroot_file);
        unlink($cachedir_file);

        return $output;
    }

    /**
     * Get performance metrics for writing file data.
     *
     * @param string $file
     * @param array $data
     *
     * @return string
     */
    private function write_file(string $file, array $data): string {
        $start_time = microtime(true);

        $handle = fopen($file, 'w');

        for ($i = 0; $i < 100000; $i++) {
            fputcsv($handle, $data);
        }

        fclose($handle);

        $duration = round(microtime(true) - $start_time, 2);
        $file_size = filesize($file);
        $speed = $file_size / $duration;

        return "Duration: " . $duration . " seconds" . PHP_EOL
            . "File size: " . display_size($file_size) . PHP_EOL
            . "Speed: " . display_size($speed) . " per second" . PHP_EOL
            . PHP_EOL;
    }

    /**
     * Get performance metrics for reading file data.
     *
     * @param string $file
     *
     * @return string
     */
    private function read_file(string $file): string {
        $file_size = filesize($file);
        $start_time = microtime(true);

        $handle = fopen($file, 'r');
        $i = 0;
        while (fgets($handle) !== false) {
            ++$i;
        }

        $duration = round(microtime(true) - $start_time, 2);
        $speed = $file_size / $duration;

        return "Duration: " . $duration . " seconds" . PHP_EOL
            . "File size: " . display_size($file_size) . PHP_EOL
            . "Speed: " . display_size($speed) . " per second" . PHP_EOL
            . PHP_EOL;
    }

}
