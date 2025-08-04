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

use core\cipher\openssl_aes256;
use core_phpunit\testcase;

/**
 * @group core_cipher
 */
class core_cipher_openssl_aes256_test extends testcase {
    /**
     * @return void
     */
    public function test_encrypt_decrypt(): void {
        if (!function_exists('openssl_encrypt')) {
            $this->markTestSkipped('openssl is not available');
        }

        $entity_id = 12345;
        $entity_id_2 = 123452;
        $entity_class = 'this_is_a_class';
        $entity_class_2 = 'this_is_a_class_2';

        $instance = openssl_aes256::make('aes256', 'ABCDEFG');

        $test_value = 'Alphabet Soup';
        $encrypted = $instance->encrypt($test_value, $entity_id, $entity_class);

        self::assertNotEmpty($encrypted);
        self::assertNotEquals($test_value, $encrypted);

        // Make sure we get different values for entity_ids and classes
        $encrypted_2 = $instance->encrypt($test_value, $entity_id_2, $entity_class);
        $encrypted_3 = $instance->encrypt($test_value, $entity_id, $entity_class_2);
        $encrypted_4 = $instance->encrypt($test_value, $entity_id_2, $entity_class_2);

        self::assertNotSame($encrypted, $encrypted_2);
        self::assertNotSame($encrypted, $encrypted_3);
        self::assertNotSame($encrypted, $encrypted_4);
        self::assertNotSame($encrypted_2, $encrypted_3);
        self::assertNotSame($encrypted_2, $encrypted_4);
        self::assertNotSame($encrypted_3, $encrypted_4);

        // Now decrypt them
        $clear_value = $instance->decrypt($encrypted, $entity_id, $entity_class);
        self::assertSame($test_value, $clear_value);

        $failed_decrypt = $instance->decrypt($encrypted, $entity_id_2, $entity_class);
        $failed_decrypt_2 = $instance->decrypt($encrypted, $entity_id, $entity_class_2);
        $failed_decrypt_3 = $instance->decrypt($encrypted, $entity_id_2, $entity_class_2);
        self::assertFalse($failed_decrypt);
        self::assertFalse($failed_decrypt_2);
        self::assertFalse($failed_decrypt_3);
    }
}
