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
 * @author Simon Chester <simon.chester@totara.com>
 * @package core_mfa
 */

use core_mfa\escalation\login;
use core_phpunit\testcase;
use core_mfa\model\instance as instance_model;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * @coversDefaultClass \core\webapi\resolver\mutation\mfa_verify_factor
 * @group core_mfa
 */
class core_mfa_webapi_resolver_mutation_verify_factor_test extends testcase {
    use webapi_phpunit_helper;

    private const MUTATION = 'core_mfa_verify_factor';

    /** @inheritDoc */
    protected function setUp(): void {
        set_config('mfa_plugins', 'button');
    }

    /**
     * @return void
     */
    public function test_verify_factor_valid(): void {
        global $CFG;
        $user = self::getDataGenerator()->create_user();
        (new login($user->id))->trigger();

        instance_model::create($user->id, 'button', 'My Button', []);

        $args = ['input' => ['factor' => 'button', 'data' => '{}']];
        $data = $this->resolve_graphql_mutation(self::MUTATION, $args);

        $this->assertTrue($data['success']);
        $this->assertSame($CFG->wwwroot . '/mfa/complete.php', $data['next_url']);
    }

    /**
     * @return void
     */
    public function test_verify_factor_invalid(): void {
        $user = self::getDataGenerator()->create_user();
        (new login($user->id))->trigger();

        instance_model::create($user->id, 'button', 'My Button', []);

        $args = ['input' => ['factor' => 'button', 'data' => '{"invalid": true}']];
        $data = $this->resolve_graphql_mutation(self::MUTATION, $args);

        $this->assertFalse($data['success']);
        $this->assertNull($data['next_url']);
    }
}
