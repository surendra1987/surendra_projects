<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2010 onwards Totara Learning Solutions LTD
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
 * @author Valerii Kuznetsov <valerii.kuznetsov@totaralms.com>
 * @package totara_core
 *
 * Unit tests for phpseclib
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/totara/core/totara.php');

/**
 * Test phpseclib for encryption-decryption
 */
class totara_core_phpseclib_test extends \core_phpunit\testcase {

    /**
     * Test totara encryption.
     */
    public function test_encrypt_data() {
        // Encrypt using public Totara key.
        $ciphertext = encrypt_data('secret');

        // Pretty much any result is ok, we need to detect notices and errors here,
        // the actual encryption is tested next.
        $this->assertSame(128, strlen($ciphertext));
    }

    /**
     * Test key creation, encryption and decryption
     */
    public function test_rsa() {
        $privatekey = \phpseclib3\Crypt\RSA::createKey();
        $publickey = $privatekey->getPublicKey();

        $data = array('site' => 'Super site', 'data' => 'my data', 3 => 5, '6' => '9', 'test');
        $sdata = json_encode($data);
        $format_private = '/-----BEGIN RSA PRIVATE KEY-----[A-Z0-9\\n\\r-\\/+=]+-----END RSA PRIVATE KEY-----/im';
        $format_public = '/-----BEGIN PUBLIC KEY-----[A-Z0-9\\n\\r-\\/+=]+-----END PUBLIC KEY-----/im';

        $this->assertEquals(1, preg_match($format_private, $privatekey->toString('PKCS1')));
        $this->assertEquals(1, preg_match($format_public, $publickey->toString('PKCS8')));


        $ciphertext = encrypt_data($sdata, $publickey);
        $this->assertNotEmpty($ciphertext);

        $newsdata = self::decrypt_data($ciphertext, $privatekey);
        $newdata = json_decode($newsdata, true);
        $this->assertNotEmpty($newdata);
        $this->assertEquals($data, $newdata);
    }

    /**
     * Assert that larger payloads can be chunked and encrypted.
     */
    public function test_cipher_chunking() {
        $private = \phpseclib3\Crypt\RSA::createKey();
        $public = $private->getPublicKey();

        $payload = json_encode(['data' => random_string(5000)]);
        $cipher = encrypt_data($payload, $public->toString('PKCS1'));

        $this->assertNotEmpty($cipher);
        $this->assertNotSame($cipher, $payload);

        // Decrypt
        $decrypted = self::decrypt_data($cipher, $private->toString('PKCS1'));

        $this->assertNotEmpty($decrypted);
        $this->assertSame($payload, $decrypted);
    }

    /**
     * Decrypt previously encrypted text
     *
     * @param string $ciphertext
     * @param string $privatekey
     * @return string
     */
    protected static function decrypt_data($ciphertext, $privatekey) {
        /** @var \phpseclib3\Crypt\RSA\PrivateKey $rsa_key */
        $rsa_key = \phpseclib3\Crypt\RSA::loadPrivateKey($privatekey);
        $rsa_key = $rsa_key->withPadding(\phpseclib3\Crypt\RSA::ENCRYPTION_PKCS1 | \phpseclib3\Crypt\RSA::SIGNATURE_PKCS1)
            ->withHash('sha1')
            ->withMGFHash('sha1')
        ;

        // We have to chunk the cipher text back (which phpseclib2 used to do)
        // See https://github.com/phpseclib/phpseclib/issues/1566#issuecomment-752282678
        $length = $rsa_key->getLength() >> 3;
        $ciphertext = str_split($ciphertext, $length);
        $ciphertext[count($ciphertext) - 1] = str_pad($ciphertext[count($ciphertext) - 1], $length, chr(0), STR_PAD_LEFT);

        $plain = '';
        foreach ($ciphertext as $chunk) {
            $plain .= $rsa_key->decrypt($chunk);
        }

        return $plain;
    }
}
