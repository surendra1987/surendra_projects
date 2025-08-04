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
 * Encrypt using aes256
 */
class openssl_aes256 implements contract {
    protected const DIVIDER = '::';

    /**
     * @var array|string[]
     */
    private static array $algorithms = [
        'aes256' => 'aes-256-gcm',
    ];

    /**
     * @var string
     */
    private string $cipher_id;

    /**
     * @var string
     */
    private string $master_key;

    /**
     * @param string $cipher_id
     * @param string $master_key
     */
    private function __construct(string $cipher_id, string $master_key) {
        $this->cipher_id = $cipher_id;
        $this->master_key = $master_key;
    }

    /**
     * @return array
     */
    public static function accepts(): array {
        $accepts = [];
        $accessible = openssl_get_cipher_methods();
        foreach (self::$algorithms as $cipher_id => $algorithm) {
            if (in_array($algorithm, $accessible)) {
                $accepts[] = $cipher_id;
            }
        }

        return $accepts;
    }

    /**
     * @param string $cipher_id
     * @param string $master_key
     * @return openssl_aes256
     */
    public static function make(string $cipher_id, string $master_key): openssl_aes256 {
        return new static($cipher_id, $master_key);
    }

    /**
     * @param string $clear_value
     * @param string $entity_id
     * @param string $entity_class
     * @return false|string
     */
    public function encrypt(string $clear_value, string $entity_id, string $entity_class) {
        $algorithm = self::$algorithms[$this->cipher_id];
        $key = $this->get_symmetric_key($entity_class);
        $ivlen = openssl_cipher_iv_length($algorithm);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $cipher_text = openssl_encrypt($clear_value, $algorithm, $key, 0, $iv, $tag, $entity_id);

        if ($cipher_text !== false) {
            $cipher = [
                base64_encode($cipher_text),
                base64_encode($iv),
                base64_encode($tag)
            ];
            $cipher_text = implode(self::DIVIDER, $cipher);
        }

        return $cipher_text;
    }

    /**
     * @param string $encrypted_text
     * @param string $entity_id
     * @param string $entity_class
     * @return false|string
     */
    public function decrypt(string $encrypted_text, string $entity_id, string $entity_class) {
        $algorithm = self::$algorithms[$this->cipher_id];
        [$cipher_text_encoded, $iv_encoded, $tag_encoded] = explode(self::DIVIDER, $encrypted_text);
        $key = $this->get_symmetric_key($entity_class);
        return openssl_decrypt(base64_decode($cipher_text_encoded), $algorithm, $key, 0, base64_decode($iv_encoded), base64_decode($tag_encoded), $entity_id);
    }

    /**
     * @param string $entity_class
     * @return string
     */
    private function get_symmetric_key(string $entity_class): string {
        return hash_hmac('sha256', $entity_class, $this->master_key);
    }
}