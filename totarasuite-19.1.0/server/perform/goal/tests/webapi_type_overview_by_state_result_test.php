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
 * This program is distributed in the hope that it will be useful,
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
use core\testing\generator as core_generator;
use perform_goal\model\overview\item;
use perform_goal\model\target_type\date;
use perform_goal\testing\generator;
use perform_goal\testing\goal_generator_config;
use totara_webapi\phpunit\webapi_phpunit_helper;

require_once(__DIR__.'/perform_overview_helper.php');

/**
 * @group perform_goal
 */
class perform_goal_webapi_type_overview_by_state_result_test extends testcase {
    use perform_overview_helper;
    use webapi_phpunit_helper;

    private const TYPE = 'perform_goal_overview_by_state_result';

    /**
     * @covers ::resolve
     */
    public function test_invalid_input(): void {
        $this->setAdminUser();

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('requires an input array');

        $this->resolve_graphql_type(self::TYPE, 'name', new \stdClass());
    }

    /**
     * @covers ::resolve
     */
    public function test_invalid_field(): void {
        $result = ['total' => 0, 'goals' => []];
        $field = 'unknown';

        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessageMatches("/$field/");
        $this->resolve_graphql_type(self::TYPE, $field, $result);
    }

    public function test_valid(): void {
        $result = ['total' => 1, 'goals' => [$this->create_test_item()]];

        $this->assertEquals(
            $result['total'],
            $this->resolve_graphql_type(self::TYPE, 'total', $result, []),
            'wrong total'
        );

        $this->assertEquals(
            $result['goals'],
            $this->resolve_graphql_type(self::TYPE, 'goals', $result, []),
            'wrong goals'
        );
    }

    /**
     * Creates a test item.
     *
     * @return item the test item.
     */
    private function create_test_item(): item {
        $this->setAdminUser();

        $core_generator = core_generator::instance();
        $subject_id = $core_generator->create_user()->id;

        $cfg = [
            'owner_id' => $core_generator->create_user()->id,
            'user_id' => $subject_id,
            'target_type' => date::get_type(),
            'context' => context_user::instance($subject_id)
        ];

        $goal = generator::instance()
            ->create_goal(goal_generator_config::new($cfg));

        return new item($goal);
    }
}
