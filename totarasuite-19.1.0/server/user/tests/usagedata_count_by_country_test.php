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
 * @author Ning Zhou <ning.zhou@totara.com>
 * @package user
 * @category usagedata
 */

use core_phpunit\testcase;
use core_user\usagedata\count_by_country;
use tool_usagedata\export;

class core_user_usagedata_count_by_country_test extends testcase {
    public function test_export(): void {
        $results = (new count_by_country())->export();
        $this->assertEquals(2, $results[export::NOTSET]);

        $generator = $this->getDataGenerator();
        // 5 'zh'
        $generator->create_user(['country' => 'zh']);
        $generator->create_user(['country' => 'zh']);
        $generator->create_user(['country' => 'zh']);
        $generator->create_user(['country' => 'zh']);
        $generator->create_user(['country' => 'zh']);

        // 3 'jp'
        $generator->create_user(['country' => 'jp']);
        $generator->create_user(['country' => 'jp']);
        $generator->create_user(['country' => 'jp']);

        // 7 'uk'
        $generator->create_user(['country' => 'uk']);
        $generator->create_user(['country' => 'uk']);
        $generator->create_user(['country' => 'uk']);
        $generator->create_user(['country' => 'uk']);
        $generator->create_user(['country' => 'uk']);
        $generator->create_user(['country' => 'uk']);
        $generator->create_user(['country' => 'uk']);

        $results = (new count_by_country())->export();

        $this->assertEquals(5, $results['zh']);
        $this->assertEquals(3, $results['jp']);
        $this->assertEquals(7, $results['uk']);
    }
}
