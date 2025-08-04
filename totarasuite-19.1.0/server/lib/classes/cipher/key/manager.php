<?php
/*
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

namespace core\cipher\key;

use coding_exception;
use core\cipher\contract as cipher_contract;
use core\cipher\openssl_aes256;
use core\event\cipher_key_added;

/**
 * Cipher key manager class(Singleton)
 * This class is responsible for creating the default keys and managing the key storage in the data root
 */
class manager {

    public const NOOP = 'noop';

    /**
     * Default key length.
     *
     * @var int
     */
    private const DEFAULT_KEY_LENGTH = 32;

    /**
     * Instance of the key manager
     * @var manager|null
     */
    private static ?manager $instance = null;

    /**
     * Keys available to use.
     *
     * @var array|null
     */
    private ?array $keys = null;

    /**
     * @var string
     */
    private string $key_file_path;

    /**
     * Private constructor. Call the ::instance() method to get the key manager object.
     */
    private function __construct(string $key_file_path) {
        $this->key_file_path = $key_file_path;
    }

    /**
     * Get an instance of the key manager.
     * Loads & caches the keys from the file system to avoid multiple file reads
     *
     * @return self
     */
    public static function instance(): self {
        global $CFG;
        if (is_null(self::$instance)) {
            self::$instance = new self($CFG->dataroot . '/encryption_keys.json');
        }

        return self::$instance;
    }

    /**
     * Add a key to the key manager. Defaults to aes256 cipherid and auto-generates a key value.
     * If a cipherid and key value is provided, those values will be used.
     *
     * @param string|null $cipher_id
     * @param string|null $value
     * @return string Key id
     */
    public function add_key(string $cipher_id = null, string $value = null): string {
        $this->load_keys_from_dataroot();
        if (is_null($value)) {
            $value = random_string(self::DEFAULT_KEY_LENGTH);
        }

        // Fallback, if no $cipher_id is provided, use AES256
        $cipher_id ??= openssl_aes256::DEFAULT;

        if (!$this->is_cipher_id_valid($cipher_id)) {
            throw new coding_exception("Invalid cipher_id");
        }

        $id = (string) time();
        $this->keys[$id] = [
            'cipher_id' => $cipher_id,
            'key' => base64_encode($value),
        ];

        // save the keys
        $handle = fopen($this->key_file_path, 'w');
        fwrite($handle, json_encode($this->keys));
        fclose($handle);

        $event = cipher_key_added::create_anonymous();
        $event->trigger();

        return $id;
    }

    /**
     * Adds a default key if no key has been created.
     *
     * @return bool
     */
    public function add_default_key(): bool {
        $this->load_keys_from_dataroot();
        if (!empty($this->keys)) {
            // key already exists
            return false;
        }

        // We hard-code our key checks by default, however they can be
        // overridden by the cli script.
        if (openssl_aes256::is_available()) {
            $this->add_key(openssl_aes256::DEFAULT);
            return true;
        }

        return false;
    }

    /**
     * Get latest id & key pair or get key with id specified
     *
     * @param string|null $id
     * @return array
     */
    public function get_key(?string $id = null): array {
        $this->load_keys_from_dataroot();

        // If we have no valid keys, return nothing
        if (empty($this->keys)) {
            return [
                self::NOOP,
                null,
                null,
            ];
        }

        if (!empty($id)) {
            // Return key with the specified id
            if (empty($this->keys[$id])) {
                throw new coding_exception("No entity encryption key matching id '$id'");
            }
            $key_details = $this->keys[$id];
        } else {
            // Return the most recent key
            $key_details = end($this->keys);
            $id = key($this->keys);
        }

        return [
            (string) $id,
            $key_details['cipher_id'],
            base64_decode($key_details['key']),
        ];
    }

    /**
     * Is the cipher_id valid
     * @param string $cipher_id
     * @return bool
     */
    private function is_cipher_id_valid(string $cipher_id): bool {
        $cipher_classes = \core_component::get_namespace_classes('cipher', cipher_contract::class);
        $is_valid = false;

        foreach ($cipher_classes as $cipher_class) {
            /** @var $cipher_class cipher_contract */
            $ids = $cipher_class::accepts();

            if (in_array($cipher_id, $ids)) {
                $is_valid = true;
                break;
            }
        }

        return $is_valid;
    }

    /**
     * Load keys from file storage.
     *
     * @return void
     */
    private function load_keys_from_dataroot(): void {
        if (is_array($this->keys)) {
            return;
        }
        $keys = [];
        $keys_path = $this->key_file_path;
        if (file_exists($keys_path)) {
            $existing_keys = file_get_contents($keys_path);

            if (!empty($existing_keys)) {
                $existing_keys = json_decode($existing_keys, true);

                if (is_array($existing_keys)) {
                    $keys = $existing_keys;
                }
            }
        }

        $this->keys = $keys;
    }
}
