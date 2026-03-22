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

use core\cipher\contract as cipher_contract;
use core\cipher\key\manager as key_manager;
use core\cipher\manager;
use core_phpunit\testcase;

/**
 * @coversDefaultClass \core\cipher\manager
 * @group core_cipher
 */
class core_cipher_manager_test extends testcase {
    /**
     * Assert that we can encrypt/decrypt via the manager
     *
     * @return void
     */
    public function test_manager_encrypt_decrypt(): void {
        $cipher = new class implements cipher_contract {
            public static function accepts(): array {
                return ['mock'];
            }

            public static function make(string $cipher_id, string $master_key): cipher_contract {
                return new self();
            }

            public function encrypt(string $clear_value, string $entity_id, string $entity_class) {
                return 'encrypted_' . $clear_value;
            }

            public function decrypt(string $encrypted_text, string $entity_id, string $entity_class) {
                return 'decrypted';
            }
        };
        $this->set_cipher($cipher);

        $key_manager = $this->mock_key_manager('2020010101', 'mock');
        $manager = new manager($key_manager);
        $result = $manager->encrypt('abcd', 1, 'my_class');
        $this->assertSame('2020010101::encrypted_abcd', $result);

        $result = $manager->encrypt('abcdef', 2, 'my_class');
        $this->assertSame('2020010101::encrypted_abcdef', $result);

        $result = $manager->decrypt('2020010101::encrypted', 1, 'my_class');
        $this->assertSame('decrypted', $result);
    }

    /**
     * Assert that an exception is thrown when a named cipher is not found.
     *
     * @return void
     */
    public function test_no_matching_cipher_class(): void {
        $key_manager = $this->mock_key_manager('2020010101', 'mock');

        $this->expectException(\coding_exception::class);
        $this->expectExceptionMessageMatches('/No valid cipher class was found for cipher mock from keyid 2020010101/');

        $manager = new manager($key_manager);
        $manager->encrypt('abcd', 1, 'my_class');
    }

    /**
     * Assert that failures from the underlying provider are handled.
     *
     * @return void
     */
    public function test_openssl_failures(): void {
        $cipher = new class implements cipher_contract {
            public static function accepts(): array {
                return ['mock2'];
            }

            public static function make(string $cipher_id, string $master_key): cipher_contract {
                return new self();
            }

            public function encrypt(string $clear_value, string $entity_id, string $entity_class) {
                return false;
            }

            public function decrypt(string $encrypted_text, string $entity_id, string $entity_class) {
                return false;
            }
        };
        $this->set_cipher($cipher);
        $key_manager = $this->mock_key_manager('2020010101', 'mock2');

        $manager = new manager($key_manager);
        $result = $manager->encrypt('abcd', 1, 'my_class');
        $this->assertFalse($result);

        $result = $manager->decrypt('2020010101::encrypted', 1, 'my_class');
        $this->assertFalse($result);
    }

    /**
     * Helper method to force the mock cipher instance to be resolved.
     *
     * @param cipher_contract $mock_cipher
     * @return void
     */
    protected function set_cipher(cipher_contract $mock_cipher): void {
        $reflected = new ReflectionClass(manager::class);
        $reflected->setStaticPropertyValue('cipher_classes', [get_class($mock_cipher)]);
    }

    /**
     * Cleanup any changes to the cipher manager from these tests.
     *
     * @return void
     */
    protected function tearDown(): void {
        $reflected = new ReflectionClass(manager::class);
        $reflected->setStaticPropertyValue('cipher_classes', null);

        parent::tearDown();
    }

    /**
     * @param string $key_id
     * @param string $cipher_id
     * @return key_manager
     */
    private function mock_key_manager(string $key_id, string $cipher_id): key_manager {
        $key_manager = $this->createMock(key_manager::class);
        $key_manager->expects($this->any())
            ->method('get_key')
            ->willReturn([$key_id, $cipher_id, 'abcdefg']);

        return $key_manager;
    }
}
