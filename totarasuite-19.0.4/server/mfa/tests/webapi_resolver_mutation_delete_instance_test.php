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
 * @author Navjeet Singh <navjeet.singh@totara.com>
 * @package core_mfa
 */

use core_mfa\entity\instance as instance_entity;
use core_mfa\model\instance as instance_model;
use core_phpunit\testcase;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * @coversDefaultClass \core\webapi\resolver\mutation\mfa_delete_instance
 * @group core_mfa
 */
class core_mfa_webapi_resolver_mutation_delete_instance_test extends testcase {
    use webapi_phpunit_helper;

    private const MUTATION = 'core_mfa_delete_instance';

    /**
     * @return void
     */
    public function test_delete(): void {
        require_once __DIR__ . '/fixtures/mock_mfa_fixture.php';

        $user = self::getDataGenerator()->create_user();
        $this->setUser($user);

        $model = instance_model::create($user->id, 'unittest', 'Label', []);
        $args = ['input' => ['id' => $model->id]];
        $result = $this->execute_graphql_operation(self::MUTATION, $args);
        $data = $result->toArray()['data'];
        $this->assertTrue($data['result']);

        $this->assertNull(instance_entity::repository()->find($model->id));
    }

    /**
     * @return void
     */
    public function test_with_different_user() {
        require_once __DIR__ . '/fixtures/mock_mfa_fixture.php';

        $user = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();
        $this->setUser($user);

        $model = instance_model::create($user2->id, 'unittest', 'Label 2', []);
        $args = ['input' => ['id' => $model->id]];

        $this->expectException(\coding_exception::class);
        $this->expectExceptionMessage('User is not allowed to access MFA instance');
        $this->resolve_graphql_mutation(self::MUTATION, $args);
    }
}

