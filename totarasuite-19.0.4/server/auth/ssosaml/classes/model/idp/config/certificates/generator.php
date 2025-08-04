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

namespace auth_ssosaml\model\idp\config\certificates;

use auth_ssosaml\exception\invalid_algorithm_exception;
use auth_ssosaml\exception\no_openssl_exception;
use auth_ssosaml\local\util;
use core_user;

/**
 * Handles generation of X509 certificates.
 */
class generator {
    /**
     * Number of days the certificate defaults to.
     */
    const DEFAULT_LIFETIME = 3650;

    /**
     * @var string What algorithm to use in signing.
     */
    private string $algorithm;

    /**
     * @var int Number of days the certificate is valid for.
     */
    private int $certificate_lifetime;

    /**
     * @var string Email used to sign the certificate.
     */
    private string $dn_email;

    /**
     * @var string Name of the organization to use in the certificate.
     */
    private string $organization_name;

    /**
     * @var bool|null Whether the openssl extension is available.
     */
    private ?bool $has_openssl_support = null;

    /**
     * @param string $algorithm
     * @param int $certificate_lifetime
     * @param string $dn_email
     * @param string $organization_name
     */
    private function __construct(
        string $algorithm,
        int $certificate_lifetime,
        string $dn_email,
        string $organization_name
    ) {
        $this->algorithm = $algorithm;
        $this->certificate_lifetime = $certificate_lifetime;
        $this->dn_email = $dn_email;
        $this->organization_name = $organization_name;
    }

    /**
     * Create a new instance of the generator using the provided options.
     *
     * @param array $options Collection of options to configure the certificate.
     *                       All are optional and will fallback to sensible defaults.
     * @return static
     */
    public static function make(array $options = []): self {
        // Optional options
        $algorithm = $options['algorithm'] ?? digest_algorithms::ALGORITHM_SHA256;
        $certificate_lifetime = $options['certificate_lifetime'] ?? self::DEFAULT_LIFETIME;
        $organization_name = $options['organization_name'] ?? self::get_default_organization_name();
        $dn_email = $options['dn_email'] ?? self::get_default_dn_email();

        $digest_algorithms = digest_algorithms::make();
        if (!$digest_algorithms->valid($algorithm)) {
            throw new invalid_algorithm_exception();
        }

        return new self(
            $algorithm,
            $certificate_lifetime,
            $dn_email,
            $organization_name
        );
    }

    /**
     * Load the email used inside the internal certificate.
     * If none could be determined, we return a fake email so the certificate is
     * still generated.
     *
     * @return string
     */
    private static function get_default_dn_email(): string {
        global $CFG;

        $support_user = core_user::get_support_user();
        if ($support_user && $support_user->email) {
            return $support_user->email;
        }

        if (!empty($CFG->noreplyaddress)) {
            return $CFG->noreplyaddress;
        }

        return 'totara_csr@example.com';
    }

    /**
     * Load the name used inside the internal certificate.
     *
     * @return string
     */
    private static function get_default_organization_name(): string {
        global $SITE;

        if ($SITE->shortname) {
            return $SITE->shortname;
        }

        // This string is not translated as it is internal and shouldn't change based on language.
        return 'TotaraSAML Service Provider';
    }

    /**
     * Generate a public & private pair of certificates.
     * Throws an exception\no_openssl_exception if openssl is unavailable.
     *
     * @param string $private_key_pass
     * @return array
     */
    public function generate(string $private_key_pass): array {
        $this->assert_openssl_supported();

        $open_ssl_args = [
            'digest_alg' => $this->algorithm,
        ];

        if (array_key_exists('OPENSSL_CONF', $_SERVER)) {
            $open_ssl_args['config'] = $_SERVER['OPENSSL_CONF'];
        }

        $key = openssl_pkey_new($open_ssl_args);
        $csr = openssl_csr_new($this->distinguished_names(), $key, $open_ssl_args);
        $public_csr = openssl_csr_sign($csr, null, $key, $this->certificate_lifetime, $open_ssl_args);

        // Create the public key
        openssl_x509_export($public_csr, $certificate);
        openssl_pkey_export($key, $private_key, $private_key_pass, $open_ssl_args);

        return compact('certificate', 'private_key');
    }

    /**
     * Asserts that openssl has been installed. Will throw an exception otherwise.
     */
    private function assert_openssl_supported(): void {
        if ($this->has_openssl_support === null) {
            $this->has_openssl_support = util::is_openssl_loaded();
        }

        if (!$this->has_openssl_support) {
            throw new no_openssl_exception();
        }
    }

    /**
     * Create the collection of DN-specific details.
     *
     * @return array
     */
    private function distinguished_names(): array {
        return [
            'commonName' => 'TotaraSAML',
            'countryName' => 'NZ',
            'localityName' => 'Unsigned',
            'emailAddress' => $this->dn_email,
            'organizationName' => $this->organization_name,
            'stateOrProvinceName' => 'Totara',
            'organizationalUnitName' => 'Totara',
        ];
    }
}
