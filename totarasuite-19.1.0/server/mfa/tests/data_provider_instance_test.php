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

use core_mfa\data_provider\instance as instance_provider;
use core_mfa\model\instance as instance_model;
use core_phpunit\testcase;

/**
 * @coversDefaultClass \core_mfa\data_provider\instance
 * @group core_mfa
 */
class core_mfa_data_provider_instance_test extends testcase {
    /**
     * @return void
     */
    public function test_get_user_instances(): void {
        set_config('mfa_plugins', 'button');
        $generator = self::getDataGenerator();
        $user = $generator->create_user();
        $user2 = $generator->create_user();

        set_config('mfa_plugins', 'button');

        $this->assertEmpty(instance_provider::get_user_instances($user->id));

        instance_model::create($user->id, 'button', 'Samsung Refrigerator', ['secret' => 'abcdef']);
        instance_model::create($user->id, 'button', 'Nokia 3310', ['secret' => 'ghijkl']);
        instance_model::create($user2->id, 'button', 'Toast-o-matic', ['secret' => 'mnopqr']);

        // enable the button mfa plugin:
        $instances = instance_provider::get_user_instances($user->id);

        $this->assertCount(2, $instances);
        $instance = $instances->find('label', 'Samsung Refrigerator');
        $this->assertInstanceOf(instance_model::class, $instance);
    }

    /**
     * @return void
     */
    public function test_get_user_instances_for_factor(): void {
        $generator = self::getDataGenerator();
        $user = $generator->create_user();
        $user2 = $generator->create_user();

        set_config('mfa_plugins', 'button');

        $this->assertEmpty(instance_provider::get_user_instances_for_factor($user->id, 'button'));

        instance_model::create($user->id, 'totp', 'Samsung Refrigerator', ['secret' => 'abcdef']);
        instance_model::create($user->id, 'button', 'Nokia 3310', ['secret' => 'ghijkl']);
        instance_model::create($user2->id, 'button', 'Toast-o-matic', ['secret' => 'mnopqr']);

        $instances = instance_provider::get_user_instances_for_factor($user->id, 'button');

        $this->assertCount(1, $instances);
        $instance = $instances->find('label', 'Nokia 3310');
        $this->assertInstanceOf(instance_model::class, $instance);
    }

    public function test_delete_user_instances(): void {
        $generator = self::getDataGenerator();
        $user = $generator->create_user();

        set_config('mfa_plugins', 'button, totp');

        // Create instsances
        instance_model::create($user->id, 'totp', 'Samsung Refrigerator', ['secret' => 'abcdef']);
        instance_model::create($user->id, 'button', 'Nokia 3310', ['secret' => 'ghijkl']);

        $this->assertCount(2, instance_provider::get_user_instances($user->id));

        instance_provider::delete_user_instances($user->id);
        $this->assertCount(0, instance_provider::get_user_instances($user->id));
    }
}
