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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package core_cipher
 */

namespace core\cipher\testing;

use core\cipher\key\manager;
use ReflectionClass;

/**
 * Helper trait for when we need to test something related to encryption without impacting
 * the global site encryption file.
 *
 * Should only be used when you're directly adding keys via the key manager.
 */
trait mock_key_manager {
    /**
     * Create a custom key manager with its own file & stores it globally.
     * @return manager
     */
    protected function setup_mock_key_manager(): manager {
        global $CFG;
        $mock_key_file = $CFG->dataroot . '/phpunit_encryption_keys.json';
        $reflect = new ReflectionClass(manager::class);
        $constructor = $reflect->getConstructor();
        $constructor->setAccessible(true);
        $obj = $reflect->newInstanceWithoutConstructor();
        $constructor->invoke($obj, $mock_key_file);

        // Override the instance
        $reflect = new \ReflectionProperty(manager::class, 'instance');
        $reflect->setAccessible(true);
        $reflect->setValue(null, $obj);

        return $obj;
    }

    /**
     * Remove the mock key file & undo the global mock object.
     *
     * @return void
     */
    protected function reset_mock_key_manager(): void {
        global $CFG;
        $mock_key_file = $CFG->dataroot . '/phpunit_encryption_keys.json';
        if (file_exists($mock_key_file)) {
            unlink($mock_key_file);
        }

        // Remove our default instance
        $reflect = new \ReflectionProperty(manager::class, 'instance');
        $reflect->setAccessible(true);
        $reflect->setValue(null, null);
    }

    /**
     * Apply mock keys for specific testing cases where you want
     * known keys with known results. Otherwise, the default generated
     * keys will be used instead.
     *
     * @return void
     */
    protected function use_mock_encryption_keys(): void {
        // Mock the keys in this case
        $key_manager = manager::instance();
        $reflected = new \ReflectionProperty(manager::class, 'keys');
        $reflected->setAccessible(true);
        $reflected->setValue($key_manager, [
            '1655208000' => ['cipher_id' => 'aes256', 'key' => '5503dc14a1a61aa1d13e5472b503169dfe9644db4e5b0f3091c2e07d7204bf2b'],
            '1686744000' => ['cipher_id' => 'aes256', 'key' => '254b21904501c628c4f11f8f8c89ff7f90845083751df9f83ad94424a01b92c7'],
        ]);
    }
}
