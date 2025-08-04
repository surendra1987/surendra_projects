<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be use`ful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author  Murali Nair <murali.nair@totaralearning.com>
 * @package perform_goal
 */

use core_phpunit\testcase;
use perform_goal\model\status\in_progress;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * @group perform_goal
 */
class perform_goal_webapi_type_goal_status_test extends testcase {
    use webapi_phpunit_helper;

    private const TYPE = 'perform_goal_status';

    /**
     * @covers ::resolve
     */
    public function test_invalid_input(): void {
        $this->setAdminUser();

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage(goal::class);

        $this->resolve_graphql_type(self::TYPE, 'name', new \stdClass());
    }

    /**
     * @covers ::resolve
     */
    public function test_invalid_field(): void {
        $this->setAdminUser();

        $field = 'unknown';

        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessage($field);
        $this->resolve_graphql_type(self::TYPE, $field, new in_progress());
    }

    /**
     * @covers ::resolve
     */
    public function test_resolve(): void {
        $this->setAdminUser();

        $obj = new in_progress();

        $testcases = [
            'id' => ['id', $obj::get_code()],
            'label' => ['label', $obj::get_label()]
        ];

        foreach ($testcases as $id => $testcase) {
            [$field, $expected] = $testcase;

            $value = $this->resolve_graphql_type(self::TYPE, $field, $obj, []);
            self::assertEquals($expected, $value, "[$id] wrong value");
        }
    }
}
