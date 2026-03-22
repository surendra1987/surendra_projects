<?php
/**
 * This file is part of Totara Talent Experience Platform
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * @package auth_ssosaml
 */

use totara_webapi\phpunit\webapi_phpunit_helper;

require_once __DIR__ . '/base_saml_testcase.php';

/**
 * @coversDefaultClass \auth_ssosaml\webapi\resolver\mutation\update_plugin_config
 * @group auth_ssosaml
 */
class auth_ssosaml_webapi_resolver_mutation_update_plugin_config_test extends base_saml_testcase {
    use webapi_phpunit_helper;

    private const MUTATION = 'auth_ssosaml_update_plugin_config';

    /**
     * Assert the plugin config values are saved
     *
     * @return void
     */
    public function test_executing_mutation(): void {
        $this->setAdminUser();

        // Reset to defaults
        set_config('field_unlocked_visible', '', 'auth_ssosaml');
        $field = get_config('auth_ssosaml', 'field_unlocked_visible');
        $this->assertEquals('', $field);

        set_config('field_lock_firstname', '', 'auth_ssosaml');
        set_config('field_lock_lastname', '', 'auth_ssosaml');
        set_config('field_lock_email', '', 'auth_ssosaml');

        $field = get_config('auth_ssosaml', 'field_lock_firstname');
        $this->assertEquals('', $field);
        $field = get_config('auth_ssosaml', 'field_lock_lastname');
        $this->assertEquals('', $field);
        $field = get_config('auth_ssosaml', 'field_lock_email');
        $this->assertEquals('', $field);

        $args = [
            'input' => [
                'fields' => [
                    ['id' => 'firstname', 'locked' => 'LOCKED'],
                    ['id' => 'lastname', 'locked' => 'UNLOCKED'],
                    ['id' => 'email', 'locked' => 'UNLOCKED_IF_EMPTY'],
                ],
            ],
        ];
        $result = $this->execute_graphql_operation(self::MUTATION, $args);
        $this->assertArrayHasKey('result', $result->data);
        $this->assertTrue($result->data['result']);

        $field = get_config('auth_ssosaml', 'field_lock_firstname');
        $this->assertEquals('locked', $field);
        $field = get_config('auth_ssosaml', 'field_lock_lastname');
        $this->assertEquals('', $field);
        $field = get_config('auth_ssosaml', 'field_lock_email');
        $this->assertEquals('unlockedifempty', $field);
        $field = get_config('auth_ssosaml', 'field_unlocked_visible');
        $this->assertEquals('lastname', $field);
    }

    /**
     * Assert validation middleware is called
     *
     * @return void
     */
    public function test_query_requires_site_config_capability() {
        $this->expectException(required_capability_exception::class);
        $this->resolve_graphql_mutation(self::MUTATION);
    }
}
