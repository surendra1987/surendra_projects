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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package core_cipher
 */
use core\cipher\openssl_aes256;
use core_phpunit\testcase;

/**
 * @group core_cipher
 */
class core_cipher_noop_test extends testcase {
    /**
     * Asset we're able to encrypt/decrypt without openssl (basically don't do anything)
     *
     * @return void
     */
    public function test_encrypt_decrypt(): void {
        $entity_id = 12345;
        $entity_class = 'this_is_a_class';

        $key_manager = \core\cipher\key\manager::instance();
        // No keys = noop
        $reflect = new ReflectionProperty($key_manager, 'keys');
        $reflect->setAccessible(true);
        $reflect->setValue($key_manager, []);

        $manager = new \core\cipher\manager($key_manager);

        // Expect to see the same value returned
        $encrypted = $manager->encrypt('ABCDEFG', $entity_id, $entity_class);
        $this->assertSame('ABCDEFG', $encrypted);

        // Expect to fail on the decryption
        $decrypted = $manager->decrypt($encrypted, $entity_id, $entity_class);
        $this->assertFalse($decrypted);

        // Let's test we can roll forward with openssl
        // To prevent tests from dying when a system doesn't have openssl (possible), check first
        if (!openssl_aes256::is_available()) {
            return;
        }

        // We now read keys from the file (by setting it to null)
        $reflect->setValue($key_manager, null);
        $encrypted = $manager->encrypt('ABCDEFG', $entity_id, $entity_class);
        $this->assertTrue($manager->is_encrypted_value($encrypted));
        $this->assertNotEquals('ABCDEFG', $encrypted);

        $decrypted = $manager->decrypt($encrypted, $entity_id, $entity_class);
        $this->assertFalse($manager->is_encrypted_value($decrypted));
        $this->assertSame('ABCDEFG', $decrypted);
    }
}
