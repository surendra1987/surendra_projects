<?php
/*
 * This file is part of Totara Learn
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Matthias Bonk <matthias.bonk@totaralearning.com>
 * @package tool_diagnostic
 */

use core_phpunit\testcase;
use tool_diagnostic\provider\db_row_counts;

/**
 * @group tool_diagnostic
 */
class tool_diagnostic_provider_db_row_counts_test extends testcase {

    public function test_content(): void {
        $provider = new db_row_counts(["enabled" => true]);
        $content = $provider->get_content()->get_data();

        $lines = explode(PHP_EOL, $content);
        $table_counts = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $line_elements = explode(' ', $line);
                $table_counts[$line_elements[1]] = $line_elements[0];
            }
        }

        $this->assertGreaterThanOrEqual(50, count($table_counts));

        // Just check a few expected things that should always be there.
        $this->assertArrayHasKey('user', $table_counts);
        $this->assertArrayHasKey('role_capabilities', $table_counts);
        $this->assertGreaterThanOrEqual(2, $table_counts['user']);
    }
}
