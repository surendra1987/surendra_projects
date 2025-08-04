<?php
/**
 * This file is part of Totara Core
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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package theme_inspire
 */

use core_phpunit\testcase;
use totara_webapi\phpunit\webapi_phpunit_helper;

class theme_inspire_webapi_resolver_mutation_set_navigation_state_test extends testcase {
    private const MUTATION = 'theme_inspire_set_navigation_state';

    use webapi_phpunit_helper;

    /**
     * @return void
     */
    public function test_set_inspire_theme_navigation_state(): void {
        global $SESSION;
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $result = $this->resolve_graphql_mutation(self::MUTATION, ['input' => ['state' => 'expanded']]);
        self::assertTrue($result);
        self::assertEquals('expanded', $SESSION->theme_inspire_navigation_state);
        unset($SESSION->theme_inspire_navigation_state);
    }

    /**
     * @return void
     */
    public function test_set_inspire_theme_navigation_state_logged_out(): void {
        global $SESSION;
        $result = $this->resolve_graphql_mutation(self::MUTATION, ['input' => ['state' => 'expanded']]);
        self::assertTrue($result);
        self::assertEquals('expanded', $SESSION->theme_inspire_navigation_state);
        unset($SESSION->theme_inspire_navigation_state);
    }

    /**
     * @return void
     */
    public function test_set_inspire_theme_navigation_state_with_random_input(): void {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        self::expectException(\moodle_exception::class);
        $result = $this->resolve_graphql_mutation(self::MUTATION, ['input' => ['state' => 'invalid']]);
    }

    /**
     * @return void
     */
    public function test_set_inspire_theme_navigation_state_with_ajax(): void {
        global $SESSION;
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $result = $this->execute_graphql_operation(self::MUTATION, ['input' => ['state' => 'expanded']]);
        $data = $result->data;
        self::assertTrue($data['result']);
        self::assertEquals('expanded', $SESSION->theme_inspire_navigation_state);

        $result = $this->execute_graphql_operation(self::MUTATION, ['input' => ['state' => null]]);
        $data = $result->data;
        self::assertTrue($data['result']);
        self::assertNull($SESSION->theme_inspire_navigation_state);
        unset($SESSION->theme_inspire_navigation_state);
    }

}