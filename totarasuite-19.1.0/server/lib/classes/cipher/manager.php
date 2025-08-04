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

use coding_exception;
use core\cipher\contract as cipher_contract;
use core\cipher\key\manager as key_manager;
use core_component;

/**
 * Entry point to encrypt/decrypt data using the defined cipher libraries.
 */
class manager {
    /**
     * Cache of namespaced classes
     *
     * @var array|string[]|null
     */
    protected static ?array $cipher_classes;

    /**
     * @var array|cipher_contract[]
     */
    protected array $cipher_cache = [];

    /**
     * Key manager
     *
     * @var key_manager
     */
    protected key_manager $key_manager;

    /**
     * @param key_manager|null $key_manager
     */
    public function __construct(?key_manager $key_manager = null) {
        $this->key_manager = $key_manager ?? key_manager::instance();
    }

    /**
     * Confirms if the provided text is encrypted or plain text.
     * Does not perform any decryption.
     *
     * @param string $text
     * @return bool
     */
    public function is_encrypted_value(string $text): bool {
        return (bool) preg_match('/^\d+::.+/', $text);
    }

    /**
     * Encrypt the provided text with the appropriate cipher & key.
     * Returns the encrypted string if successful, or false.
     *
     * @param string $clear_text The text to encrypt
     * @param string $entity_id
     * @param string $entity_class
     * @return false|string
     */
    public function encrypt(string $clear_text, string $entity_id, string $entity_class) {
        [$key_id, $cipher] = $this->get_cipher();

        // Noop means we have no cipher support, so don't encrypt anything
        if ($key_id === key_manager::NOOP) {
            return $clear_text;
        }

        /** @var $cipher cipher_contract */
        $encrypted = $cipher->encrypt($clear_text, $entity_id, $entity_class);
        if ($encrypted === false) {
            return false;
        }

        // Prepend the key on it
        return implode('::', [$key_id, $encrypted]);
    }

    /**
     * Decrypt the provided text with the appropriate cipher & key.
     * Returns the plain string if successful or false.
     *
     * @param string $encrypted
     * @param string $entity_id
     * @param string $entity_class
     * @return false|string
     */
    public function decrypt(string $encrypted, string $entity_id, string $entity_class) {
        // Don't attempt if it doesn't match the signature
        if (!$this->is_encrypted_value($encrypted)) {
            return false;
        }

        [$key_id, $encrypted_text] = explode('::', $encrypted, 2);
        [$key_id, $cipher] = $this->get_cipher($key_id);
        /** @var $cipher cipher_contract */

        // Rare, but catching the edge cases
        if ($key_id === key_manager::NOOP) {
            return false;
        }

        return $cipher->decrypt($encrypted_text, $entity_id, $entity_class);
    }

    /**
     * Find the specific cipher instance based on the key
     *
     * @param string|null $key_id
     * @return array
     */
    protected function get_cipher(?string $key_id = null): array {
        [$key_id, $cipher_id, $master_key] = $this->key_manager->get_key($key_id);

        if ($key_id === key_manager::NOOP) {
            return [$key_id, null];
        }

        if (isset($this->cipher_cache[$key_id])) {
            return [$key_id, $this->cipher_cache[$key_id]];
        }

        $cipher = null;

        if (!isset(self::$cipher_classes) || self::$cipher_classes === null) {
            self::$cipher_classes = core_component::get_namespace_classes('cipher', cipher_contract::class);
        }
        foreach (self::$cipher_classes as $class) {
            /** @var $class cipher_contract */
            $accepts = $class::accepts();
            if (in_array($cipher_id, $accepts)) {
                $cipher = $class::make($cipher_id, $master_key);
                break;
            }
        }
        if (!$cipher) {
            throw new coding_exception('No valid cipher class was found for cipher ' . $cipher_id . ' from keyid ' . $key_id);
        }

        $this->cipher_cache[$key_id] = $cipher;
        return [$key_id, $cipher];
    }
}