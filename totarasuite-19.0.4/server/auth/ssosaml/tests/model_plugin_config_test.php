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
 * @package auth_ssosaml
 */

use auth_ssosaml\model\plugin_config;
use core\collection;

require_once __DIR__ . '/base_saml_testcase.php';

/**
 * @coversDefaultClass \auth_ssosaml\model\plugin_config
 * @group auth_ssosaml
 */
class auth_ssosaml_model_plugin_config_test extends base_saml_testcase {
    /**
     * @return void
     */
    public function test_get_fields(): void {
        $config = new plugin_config();

        set_config('field_lock_firstname', 'unlocked', 'auth_ssosaml');
        set_config('field_lock_lastname', 'locked', 'auth_ssosaml');
        set_config('field_lock_email', 'unlockedifempty', 'auth_ssosaml');
        set_config('field_lock_city', 'unlocked', 'auth_ssosaml');
        set_config('field_lock_country', 'unlocked', 'auth_ssosaml');
        set_config('field_lock_institution', false, 'auth_ssosaml');

        set_config('field_unlocked_visible', 'city,country', 'auth_ssosaml');

        $fields = collection::new($config->get_fields())->key_by('id')->all(true);

        $this->assertEqualsCanonicalizing(['lastname', 'email', 'city', 'country'], array_keys($fields));

        $this->assertEquals('LOCKED', $fields['lastname']['locked']);
        $this->assertEquals(get_string('lastname'), $fields['lastname']['label']);
        $this->assertEquals('UNLOCKED_IF_EMPTY', $fields['email']['locked']);
        $this->assertEquals(get_string('email'), $fields['email']['label']);
        $this->assertEquals('UNLOCKED', $fields['city']['locked']);
        $this->assertEquals(get_string('city'), $fields['city']['label']);
        $this->assertEquals('UNLOCKED', $fields['country']['locked']);
        $this->assertEquals(get_string('country'), $fields['country']['label']);
    }

    /**
     * @return void
     */
    public function test_set_fields(): void {
        $config = new plugin_config();

        set_config('field_lock_department', 'locked', 'auth_ssosaml');

        $config->set_fields([
            ['id' => 'firstname', 'locked' => 'UNLOCKED'],
            ['id' => 'lastname', 'locked' => 'LOCKED'],
            ['id' => 'email', 'locked' => 'UNLOCKED_IF_EMPTY'],
            ['id' => 'city', 'locked' => 'UNLOCKED'],
            ['id' => 'country', 'locked' => 'UNLOCKED'],
        ]);

        $this->assertEquals('unlocked', get_config('auth_ssosaml', 'field_lock_firstname'));
        $this->assertEquals('locked', get_config('auth_ssosaml', 'field_lock_lastname'));
        $this->assertEquals('unlockedifempty', get_config('auth_ssosaml', 'field_lock_email'));
        $this->assertEquals('unlocked', get_config('auth_ssosaml', 'field_lock_city'));
        $this->assertEquals('unlocked', get_config('auth_ssosaml', 'field_lock_country'));
        $this->assertEquals('firstname,city,country', get_config('auth_ssosaml', 'field_unlocked_visible'));

        // not present in set call -- unlocked
        $this->assertEquals('unlocked', get_config('auth_ssosaml', 'field_lock_department'));
        $this->assertEquals('unlocked', get_config('auth_ssosaml', 'field_lock_address'));
    }
}
