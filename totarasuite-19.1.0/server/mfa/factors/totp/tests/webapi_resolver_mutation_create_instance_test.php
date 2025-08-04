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
 * @package mfa_totp
 */

use core_mfa\entity\instance;
use core_phpunit\testcase;
use mfa_totp\totp;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * @coversDefaultClass \mfa_totp\webapi\resolver\mutation\create_instance
 * @group mfa_totp
 */
class mfa_totp_webapi_resolver_mutation_create_instance_test extends testcase {
    use webapi_phpunit_helper;

    private const MUTATION = 'mfa_totp_create_instance';

    /**
     * @return void
     */
    public function test_create_instance(): void {
        global $USER;

        set_config('mfa_plugins', 'totp');

        $this->setAdminUser();

        $totp = new totp();
        $result = $this->resolve_graphql_mutation(self::MUTATION, [
            'input' => [
                'secret' => $totp->get_secret(),
                'token' => $totp->generate(time()),
            ],
        ]);

        $entity = new instance($result->id);

        $this->assertSame('totp', $entity->type);
        $this->assertNull($entity->label);
        $this->assertSame($USER->id, $entity->user_id);
        $this->assertNotNull($entity->secure_config);

        $model = new core_mfa\model\instance($entity);
        $this->assertSame(['secret' => $totp->get_secret()], $model->secure_config);
    }
}
