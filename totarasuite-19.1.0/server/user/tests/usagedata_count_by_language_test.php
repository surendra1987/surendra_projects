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
use core_user\usagedata\count_by_language;

class core_user_usagedata_count_by_language_test extends testcase {
    public function test_export(): void {
        $results = (new count_by_language())->export();
        $this->assertEquals(2, $results['en']);

        $generator = $this->getDataGenerator();
        // 4 'en' + the 2 in initial result
        $generator->create_user(['lang' => 'en']);
        $generator->create_user(['lang' => 'en']);
        $generator->create_user(['lang' => 'en']);
        $generator->create_user(['lang' => 'en']);

        // 2 'zh'
        $generator->create_user(['lang' => 'zh']);
        $generator->create_user(['lang' => 'zh']);

        // 5 'ko'
        $generator->create_user(['lang' => 'ko']);
        $generator->create_user(['lang' => 'ko']);
        $generator->create_user(['lang' => 'ko']);
        $generator->create_user(['lang' => 'ko']);
        $generator->create_user(['lang' => 'ko']);

        // 1 'it'
        $generator->create_user(['lang' => 'it']);

        $results = (new count_by_language())->export();

        $this->assertEquals(6, $results['en']);
        $this->assertEquals(2, $results['zh']);
        $this->assertEquals(5, $results['ko']);
        $this->assertEquals(1, $results['it']);

    }
}