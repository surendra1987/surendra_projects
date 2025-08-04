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
 * @package mfa_totp
 */

namespace mfa_totp;

use core\encoding\base32;
use pdf;

/**
 * Validate TOTP tokens
 */
class totp {
    /**
     * Default step size when calculating the TOTP token taken
     * from the specification {@link https://datatracker.ietf.org/doc/html/rfc6238#section-5.2}.
     */
    const TIME_STEP = 30;

    /**
     * What algorithm to use in the generation of the token.
     */
    const DEFAULT_DIGEST = 'sha1';

    /**
     * Number of digits to use in the token.
     */
    const DEFAULT_DIGITS = 6;

    /**
     * Default number of random bytes to use in the secret.
     */
    const DEFAULT_SECRET_LENGTH = 12;

    /**
     * @var string
     */
    protected string $secret;

    /**
     * @var string
     */
    protected string $digest;

    /**
     * @var int
     */
    protected int $digits;

    /**
     * @param string|null $secret The base32 encoded secret. One will be generated if left null.
     * @param string|null $digest
     * @param int|null $digits
     */
    public function __construct(?string $secret = null, string $digest = null, int $digits = null) {
        $this->secret = $secret ?? self::generate_secret();
        $this->digest = $digest ?? self::DEFAULT_DIGEST;
        $this->digits = $digits ?? self::DEFAULT_DIGITS;
    }

    /**
     * @return string
     */
    public function get_secret(): string {
        return $this->secret;
    }

    /**
     * Generate a token for the provided timestamp.
     *
     * @param int $timestamp
     * @return string
     */
    public function generate(int $timestamp): string {
        $step = $this->step($timestamp);
        $secret = $this->decode_secret();

        $message = pack('N*', 0) . pack('N*', $step);
        $hash = hash_hmac($this->digest, $message, $secret, true);
        $hash = array_values(unpack('C*', $hash));

        // Juggle it back into numeric token form
        // Logic outlined in the algorithm https://datatracker.ietf.org/doc/html/rfc6238#appendix-A
        $offset = $hash[count($hash) - 1] & 0xf;
        $binary = ($hash[$offset] & 0x7f) << 24 | ($hash[$offset + 1] & 0xff) << 16 | ($hash[$offset + 2] & 0xff) << 8 | ($hash[$offset + 3] & 0xff);

        $token = (string) ($binary % (10 ** $this->digits));

        return str_pad($token, $this->digits, '0', STR_PAD_LEFT);
    }

    /**
     * Validate the provided timestamp is correct.
     * Important: Tokens must be validated with this function, never using direct == or === conditions
     * to prevent timing attacks.
     *
     * @param int $timestamp
     * @param string $provided_token
     * @return bool
     */
    public function validate(int $timestamp, string $provided_token): bool {
        $generated_token = $this->generate($timestamp);
        return hash_equals($generated_token, $provided_token);
    }

    /**
     * Generate an OATH uri for this specific user's instance.
     *
     * @param object $user The user object.
     * @param string $issuer Typically the site name.
     * @return string
     */
    public function oath_uri(object $user, string $issuer): string {
        $user_label = $user->email;
        if (empty($user->email)) {
            $user_label = $user->username;
        }
        $user_label = rawurlencode($user_label);
        $issuer = rawurlencode($issuer);

        $uri = "otpauth://totp/{$issuer}:{$user_label}?";

        $params = [
            'secret' => $this->secret,
            'issuer' => $issuer,
            'algorithm' => $this->digest,
            'digits' => $this->digits,
            'period' => self::TIME_STEP,
        ];

        return $uri . http_build_query($params);
    }

    /**
     * Convert the provided oath URL into a QR code string using the pdf library.
     */
    public function qr_code(string $oath_uri): string {
        global $CFG;
        require_once $CFG->libdir . '/pdflib.php';

        $qr_code = pdf::qr($oath_uri);
        return "data:image/png;base64, {$qr_code}";
    }

    /**
     * Returns a random secret
     *
     * @return string
     */
    public static function generate_secret(): string {
        return base32::encode(random_bytes(self::DEFAULT_SECRET_LENGTH));
    }

    /**
     * The step to use in the calculation.
     *
     * @param int $timestamp
     * @return int
     * @link https://datatracker.ietf.org/doc/html/rfc6238#section-4.1
     */
    protected function step(int $timestamp): int {
        return floor($timestamp / self::TIME_STEP);
    }

    /**
     * @return string
     */
    protected function decode_secret(): string {
        return base32::decode($this->secret);
    }
}