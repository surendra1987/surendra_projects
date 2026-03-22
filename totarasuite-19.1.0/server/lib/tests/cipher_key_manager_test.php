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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package core_cipher
 */

use core\cipher\key\manager;
use core\cipher\testing\mock_key_manager;
use core_phpunit\testcase;

/**
 * @group core_cipher
 */
class core_cipher_key_manager_test extends testcase {
    use mock_key_manager;

    /**
     * @var manager|null
     */
    protected ?manager $key_manager;

    protected function setUp(): void {
        // Key manager is a singleton, we need our own instance for these tests.
        $this->key_manager = $this->setup_mock_key_manager();
        parent::setUp();
    }

    protected function tearDown(): void {
        $this->key_manager = null;
        $this->reset_mock_key_manager();
        parent::tearDown();
    }

    public function test_add_key() {
        $key_manager = $this->key_manager;

        // Add key without specifying the key or cipher id
        $new_key_id = $key_manager->add_key();
        [$fetched_key_id, $cipher_id, $key] = $key_manager->get_key();
        $this->assertEquals($new_key_id, $fetched_key_id);

        // Add key specifying a key value
        $new_key_id = $key_manager->add_key(null, 'a_random_string');
        [$fetched_key_id, $cipher_id, $key] = $key_manager->get_key();
        $this->assertEquals($new_key_id, $fetched_key_id);
        $this->assertEquals('a_random_string', $key);
    }

    public function test_add_key_using_invalid_cipher_id() {
        $key_manager = $this->key_manager;

        // Add key without specifying the key or cipher id
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Invalid cipher_id');
        $new_key_id = $key_manager->add_key('invalid_id');
    }

    public function test_add_default_key() {
        $key_manager = $this->key_manager;

        // Add a default key
        $this->assertTrue($key_manager->add_default_key());

        // Try adding a new default key
        $this->assertFalse($key_manager->add_default_key());
    }

    public function test_get_key() {
        $key_manager = $this->key_manager;
        $key_id = $key_manager->add_key();

        // Get key by key_id
        [$fetched_key_id, $cipher_id, $key] = $key_manager->get_key($key_id);
        $this->assertEquals($key_id, $fetched_key_id);

        // add a new key
        $new_key_id = $key_manager->add_key();

        // Get the latest key
        [$fetched_key_id, $cipher_id, $key] = $key_manager->get_key();
        $this->assertEquals($new_key_id, $fetched_key_id);

        $unknown_key_id = 'an_unknown_key_id';
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage("No entity encryption key matching id '$unknown_key_id'");
        [$fetched_key_id, $cipher_id, $key] = $key_manager->get_key($unknown_key_id);
    }

    /**
     * Assert the key manager returns noop when we have no keys
     *
     * @return void
     */
    public function test_noop_key(): void {
        $key_manager = $this->key_manager;

        // Fake having no keys calculated
        $reflect = new ReflectionProperty($key_manager, 'keys');
        $reflect->setAccessible(true);
        $reflect->setValue($key_manager, []);

        $noop = $key_manager->get_key();
        $this->assertIsArray($noop);
        $this->assertSame(manager::NOOP, $noop[0]);
    }
}
