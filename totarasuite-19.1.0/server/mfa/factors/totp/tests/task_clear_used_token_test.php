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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Navjeet Singh <navjeet.singh@totara.com>
 * @package mfa_totp
 */

use core_phpunit\testcase;

use mfa_totp\entity\token as token_entity;
use mfa_totp\task\clear_used_token;

/**
 * @coversDefaultClass \mfa_totp\task\clear_used_token
 * @group mfa_totp
 */
class mfa_totp_task_clear_used_token_test extends testcase {

    /**
     * @return void
     */
    public function test_clear_used_token(): void {
        set_config('mfa_plugins', 'totp');

        $this->token_test_data();

        $this->assertCount(4, token_entity::repository()->get());

        // Execute the task
        $task = new clear_used_token();
        $task->execute();

        $used_token = token_entity::repository()->get()->all();

        $tokens = [];

        foreach ($used_token as $item) {
            $tokens[] = $item->token;
        }
        $this->assertEquals(2, count($tokens));
        $this->assertSame(['123456', '456789'], $tokens);
    }

    /**
     * Generate test totp token data
     *
     * @return void
     */
    protected function token_test_data() {
        $user = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();

        $test_data = [
            [
                "user_id" => $user->id,
                "token" => '123456',
                "created_at" => time()
            ],
            [
                "user_id" => $user2->id,
                "token" => '456789',
                "created_at" => time()
            ],
            [
                "user_id" => $user2->id,
                "token" => random_string(6),
                "created_at" => time() - (HOURSECS + 30)
            ],
            [
                "user_id" => $user->id,
                "token" => random_string(6),
                "created_at" => time() - (HOURSECS + 30)
            ],
        ];

        foreach ($test_data as $item) {
            (new token_entity([
                'user_id' => $item['user_id'],
                'token' => $item['token'],
                'created_at' => $item['created_at'],
            ]))->save();
        }
    }
}