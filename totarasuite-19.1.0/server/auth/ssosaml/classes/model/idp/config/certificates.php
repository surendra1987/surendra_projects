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
 * @package auth_ssosaml
 */

namespace auth_ssosaml\model\idp\config;

use auth_ssosaml\exception\no_openssl_exception;
use auth_ssosaml\model\idp\config\certificates\generator;

/**
 * Handle the certificates used by the IdP.
 */
class certificates {

    /**
     * @param $certificates string
     * @return array
     */
    public static function get_certificates(string $certificates): array {
        if (empty($certificates)) {
            throw new \coding_exception('No certificates have been generated and stored.');
        }
        $decoded = json_decode(base64_decode($certificates), true);

        if (!array_key_exists('certificate', $decoded) || !array_key_exists('private_key', $decoded)) {
            throw new \coding_exception('Must have both certificate and private_key when reading a certificate.');
        }

        return $decoded;
    }

    /**
     * @return string|null
     */
    public static function create_certificates(): ?string {
        $generator = generator::make();
        try {
            $certificates = $generator->generate(get_site_identifier());
            if (!array_key_exists('certificate', $certificates) || !array_key_exists('private_key', $certificates)) {
                throw new \coding_exception('Must have both certificate and private_key when writing a certificate.');
            }
            $generated_certificates = base64_encode(json_encode($certificates));
        } catch (no_openssl_exception $exception) {
            $generated_certificates = null;
        }
        return $generated_certificates;
    }
}