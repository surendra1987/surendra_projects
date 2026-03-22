<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package totara_webhook
 */

use core\event\user_profile_viewed;
use core_phpunit\testcase;
use totara_webhook\data_processor\data_sanitiser;

defined('MOODLE_INTERNAL') || die();

class totara_webhook_totara_webhook_data_sanitiser_test extends testcase {

    /**
     * Testing the raw data always remains the same
     *
     * @return void
     */
    public function test_raw_data(): void {
        $data = [
            'secret' => 'secret',
            'password' => 'password',
            'auth' => 'auth',
            'name' => 'test_name',
            'id' => 1,
        ];
        $data_sanitiser = new data_sanitiser(user_profile_viewed::class, $data);
        $raw_data = $data_sanitiser->get_raw_data();
        foreach ($data as $key => $val) {
            $this->assertSame($val, $raw_data[$key]);
        }
    }

    /**
     * Test that the event class is returned correctly
     *
     * @return void
     */
    public function test_get_event_class(): void {
        $data = [
            'secret' => 'secret',
            'password' => 'password',
            'auth' => 'auth',
            'name' => 'test_name',
            'id' => 1,
        ];
        $data_sanitiser = new data_sanitiser(user_profile_viewed::class, $data);
        $this->assertSame(user_profile_viewed::class, $data_sanitiser->get_event_class());
    }

    /**
     * Testing the sanitised data removes the risky fields on initialisation
     *
     * @return void
     */
    public function test_get_exposed_data(): void {
        $data = [
            'secret' => 'secret',
            'password' => 'password',
            'auth' => 'MANUAL',
            'name' => 'test_name',
            'id' => 1,
        ];
        $data_sanitiser = new data_sanitiser(user_profile_viewed::class, $data);
        $exposed_data = $data_sanitiser->get_exposed_data();
        $this->assertCount(3, $exposed_data);
        $expected_data = ['name' => 'test_name', 'id' => 1];
        foreach ($expected_data as $key => $val) {
            $this->assertSame($val, $exposed_data[$key]);
        }
    }

    /**
     * Testing the redacted data returns the risky fields on initialisation
     *
     * @return void
     */
    public function test_get_redacted_data(): void {
        $data = [
            'secret' => 'secret',
            'password' => 'password',
            'auth' => 'MANUAL',
            'name' => 'test_name',
            'id' => 1,
        ];
        $data_sanitiser = new data_sanitiser(user_profile_viewed::class,  $data);
        $redacted_data = $data_sanitiser->get_redacted_data();
        $this->assertCount(2, $redacted_data);
        $expected_redacted_data = ['secret' => data_sanitiser::REDACTED_MASK, 'password' => data_sanitiser::REDACTED_MASK];
        foreach ($expected_redacted_data as $key => $val) {
            $this->assertSame($val, $redacted_data[$key]);
        }
    }

    /**
     * Test that expose data allows you to move an item from redacted back into the sanitised data
     *
     * @return void
     */
    public function test_expose_data_known_redacted_key(): void {
        $data = [
            'secret' => 'secret',
            'password' => 'password',
            'auth' => 'MANUAL',
            'name' => 'test_name',
            'id' => 1,
        ];
        // check the fields originally redacted
        $data_sanitiser = new data_sanitiser(user_profile_viewed::class, $data);
        $redacted_data = $data_sanitiser->get_redacted_data();
        $this->assertCount(2, $redacted_data);
        $expected_redacted_data = ['secret' => data_sanitiser::REDACTED_MASK, 'password' => data_sanitiser::REDACTED_MASK];
        foreach ($expected_redacted_data as $key => $val) {
            $this->assertSame($val, $redacted_data[$key]);
        }
        // check the data was exposed
        $data_sanitiser->expose_data('secret');
        $exposed_data = $data_sanitiser->get_exposed_data();
        $this->assertCount(4, $exposed_data);
        $expected_exposed_data = ['secret' => 'secret', 'auth' => 'MANUAL', 'name' => 'test_name', 'id' => 1];
        foreach ($expected_exposed_data as $key => $val) {
            $this->assertSame($val, $exposed_data[$key]);
        }
        // get the updated redacted data
        $redacted_data = $data_sanitiser->get_redacted_data();
        $expected_redacted_data = ['password' => data_sanitiser::REDACTED_MASK];
        foreach ($expected_redacted_data as $key => $val) {
            $this->assertSame($val, $redacted_data[$key]);
        }
    }

    /**
     * Test that calling expose on an unknown redacted value doesn't cause any exceptions
     * and still returns the data sanitiser as it previously was
     *
     * @return void
     */
    public function test_expose_data_unknown_redacted_key(): void {
        $data = [
            'secret' => 'secret',
            'password' => 'password',
            'auth' => 'MANUAL',
            'name' => 'test_name',
            'id' => 1,
        ];
        $data_sanitiser = new data_sanitiser(user_profile_viewed::class, $data);
        $redacted_data = $data_sanitiser->get_redacted_data();
        $this->assertCount(2, $redacted_data);
        $exception_throw = false;
        try {
            $data_sanitiser->expose_data('unknown_key');
        } catch (\Exception $e) {
            $exception_throw = true;
        }
        $this->assertFalse($exception_throw);
        $exposed_data = $data_sanitiser->get_exposed_data();
        $this->assertCount(3, $exposed_data);
    }

    /**
     * Test that calling redact_data on a known key in the exposed data removes
     * that value from the sanitised data and adds it to the redacted data
     *
     * @return void
     */
    public function test_redact_data_known_exposed_key(): void {
        $data = [
            'secret' => 'secret',
            'password' => 'password',
            'auth' => 'MANUAL',
            'name' => 'test_name',
            'id' => 1,
        ];
        $data_sanitiser = new data_sanitiser(user_profile_viewed::class, $data);
        $redacted_data = $data_sanitiser->get_redacted_data();
        $this->assertCount(2, $redacted_data);
        $exposed_data = $data_sanitiser->get_exposed_data();
        $this->assertCount(3, $exposed_data);
        // redact names - move it from exposed to redacted
        $data_sanitiser->redact_data('name');
        // check the exposed data
        $redacted_data = $data_sanitiser->get_redacted_data();
        $this->assertCount(3, $redacted_data);
        $this->assertArrayHasKey('name', $redacted_data);
        $exposed_data = $data_sanitiser->get_exposed_data();
        $this->assertCount(2, $exposed_data);
        $this->assertArrayNotHasKey('name', $exposed_data);
    }

    /**
     * Test that calling redact on an unknown exposed value doesn't cause any exceptions
     * and still returns the data sanitiser as it previously was
     *
     * @return void
     */
    public function test_redact_data_unknown_exposed_key(): void {
        $data = [
            'secret' => 'secret',
            'password' => 'password',
            'auth' => 'auth',
            'name' => 'test_name',
            'id' => 1,
        ];
        $data_sanitiser = new data_sanitiser(user_profile_viewed::class, $data);
        $redacted_data = $data_sanitiser->get_redacted_data();
        $this->assertCount(2, $redacted_data);
        $exposed_data = $data_sanitiser->get_exposed_data();
        $this->assertCount(3, $exposed_data);
        $exception_throw = false;
        try {
            $data_sanitiser->redact_data('unknown_key');
        } catch (\Exception $e) {
            $exception_throw = true;
        }
        $this->assertFalse($exception_throw);
        $redacted_data = $data_sanitiser->get_redacted_data();
        $this->assertCount(2, $redacted_data);
        $this->assertArrayNotHasKey('unknown_key', $redacted_data);
        $exposed_data = $data_sanitiser->get_exposed_data();
        $this->assertCount(3, $exposed_data);
        $this->assertArrayNotHasKey('unknown_key', $exposed_data);
    }

    /**
     * Test that calling get_sanitised_data returns the sanitised data,
     * which is a combination of the redacted and exposed data.
     *
     * @return void
     */
    public function test_get_sanitised_data(): void {
        $data = [
            'secret' => 'secret',
            'password' => 'password',
            'auth' => 'MANUAL',
            'name' => 'test_name',
            'id' => 1,
        ];
        $data_sanitiser = new data_sanitiser(user_profile_viewed::class, $data);
        $redacted_data = $data_sanitiser->get_redacted_data();
        $this->assertCount(2, $redacted_data);
        $exposed_data = $data_sanitiser->get_exposed_data();
        $this->assertCount(3, $exposed_data);
        $sanitised_data = $data_sanitiser->get_sanitised_data();
        $this->assertCount(5, $sanitised_data);
        $secret_val = $sanitised_data['secret'];
        $this->assertSame(data_sanitiser::REDACTED_MASK, $secret_val);
        $password_val = $sanitised_data['password'];
        $this->assertSame(data_sanitiser::REDACTED_MASK, $password_val);
        $auth_val = $sanitised_data['auth'];
        $this->assertSame('MANUAL', $auth_val);
        $name_val = $sanitised_data['name'];
        $this->assertSame('test_name', $name_val);
        $id_val = $sanitised_data['id'];
        $this->assertSame(1, $id_val);
    }
}
