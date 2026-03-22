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

namespace core\cipher;

/**
 * Interface for specific encryption implementations.
 */
interface contract {
    /**
     * Return the list of ciphers this class accepts as an array.
     *
     * @return array
     */
    public static function accepts(): array;

    /**
     * Create a new instance of the cipher.
     *
     * @param string $cipher_id
     * @param string $master_key
     * @return self
     */
    public static function make(string $cipher_id, string $master_key): self;

    /**
     * Encrypt the provided string with the most appropriate cipher and key.
     * The end result is an encrypted string prefixed with the $key_id.
     *
     * The $entity_id and $entity_class may be used by implementations to tie the value
     * to specific entities, preventing copy/pasting of values between models.
     *
     * Will return false if the value failed to encrypt.
     *
     * @param string $clear_value
     * @param string $entity_id
     * @param string $entity_class
     * @return array|false
     */
    public function encrypt(string $clear_value, string $entity_id, string $entity_class);

    /**
     * Decrypt the provided $encrypted_text value back into the original form.
     * Will return false if the text could not be decrypted.
     *
     * @param string $encrypted_text
     * @param string $entity_id
     * @param string $entity_class
     * @return string|false
     */
    public function decrypt(string $encrypted_text, string $entity_id, string $entity_class);
}
