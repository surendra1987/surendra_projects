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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package auth_ssosaml
 */

use auth_ssosaml\data_provider\user_fields;

require_once __DIR__ . '/base_saml_testcase.php';

/**
 * @coversDefaultClass \auth_ssosaml\data_provider\user_fields
 * @group auth_ssosaml
 */
class auth_ssosaml_data_provider_user_fields_test extends base_saml_testcase {
    /**
     * @return void
     */
    public function test_get_info(): void {
        global $CFG;
        require_once($CFG->dirroot . '/user/profile/lib.php');
        require_once($CFG->dirroot . '/user/profile/definelib.php');
        require_once($CFG->dirroot . '/user/profile/field/checkbox/define.class.php');

        $generator = $this->getDataGenerator();

        // Create custom fields
        /** @var \core_user\testing\generator $user_generator */
        $user_generator = $generator->get_plugin_generator('core_user');
        $user_generator->create_custom_field('checkbox', 'developer', 0, 'developer');
        $user_generator->create_custom_field('checkbox', 'reviewer', 0, 'reviewer');

        $fields = user_fields::get_info();

        // Check id is not included
        $this->assertArrayNotHasKey('id', $fields);

        $this->assertArrayHasKey('profile_field_developer', $fields);
        $this->assertArrayHasKey('profile_field_reviewer', $fields);

        // Check special fields
        $this->assertEquals(get_string('language'), $fields['lang']['label']);
        $this->assertFalse($fields['lang']['lockable']);
        unset($fields['lang']);

        $this->assertEquals(get_string('webpage'), $fields['url']['label']);
        $this->assertTrue($fields['url']['lockable']);
        unset($fields['url']);

        $this->assertEquals(get_string('username'), $fields['username']['label']);
        $this->assertFalse($fields['username']['lockable']);
        unset($fields['username']);

        // Check custom fields
        $this->assertEquals('developer', $fields['profile_field_developer']['label']);
        $this->assertTrue($fields['profile_field_developer']['lockable']);
        unset($fields['profile_field_developer']);
        $this->assertEquals('reviewer', $fields['profile_field_reviewer']['label']);
        $this->assertTrue($fields['profile_field_reviewer']['lockable']);
        unset($fields['profile_field_reviewer']);

        foreach ($fields as $field_id => $field) {
            $this->assertEquals($field_id, $field['id']);
            $this->assertEquals(get_string($field_id), $field['label'], $field_id);
            $this->assertTrue($field['lockable'], $field_id);
        }
    }
}