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

use core_mfa\model\instance as instance_model;
use core_phpunit\testcase;
use mfa_totp\entity\token as token_entity;
use mfa_totp\factor;
use mfa_totp\totp;

/**
 * @group core_mfa
 * @group mfa_totp
 */
class mfa_totp_factor_test extends testcase {
    /**
     * @return void
     */
    public function test_verify(): void {
        $user = self::getDataGenerator()->create_user();
        $this->setUser($user);

        $secret = 'foobar';
        $model = instance_model::create($user->id, 'totp', 'Label', [], ['secret' => $secret]);

        // Fixed time
        $time = 1728433602;

        $token = (new totp($secret))->generate($time);
        $factor = $this->factor_with_fixed_time($time);
        $this->assertTrue($factor->verify($user->id, ['token' => $token], [$model]));
    }

    /**
     * @return void
     */
    public function test_verify_with_invalid_token(): void {
        $user = self::getDataGenerator()->create_user();
        $this->setUser($user);

        $secret = 'foobar';
        $model = instance_model::create($user->id, 'totp', 'Label', [], ['secret' => $secret]);

        $token = '99999';

        $factor = new factor();
        $this->assertFalse($factor->verify($user->id, ['token' => $token], [$model]));
    }

    /**
     * @return void
     */
    public function test_verify_with_token_already_used(): void {
        $user = self::getDataGenerator()->create_user();
        $this->setUser($user);

        $secret = 'foobar';
        $model = instance_model::create($user->id, 'totp', 'Label', [], ['secret' => $secret]);

        // Fixed time
        $time = 1728433602;

        $token = (new totp($secret))->generate($time);

        (new token_entity([
            'user_id' => $user->id,
            'token' => $token,
        ]))->save();

        $factor = $this->factor_with_fixed_time($time);
        $this->assertFalse($factor->verify($user->id, ['token' => $token], [$model]));
    }

    /**
     * Create a factor object with fixed time set.
     *
     * @param int $fixed_time
     * @return factor
     */
    private function factor_with_fixed_time(int $fixed_time): factor {
        $factor = new factor();
        $callable = function () use ($fixed_time) {
            return $fixed_time;
        };
        $this->callInternalMethod($factor, 'set_time_provider', [$callable]);
        return $factor;
    }
}
