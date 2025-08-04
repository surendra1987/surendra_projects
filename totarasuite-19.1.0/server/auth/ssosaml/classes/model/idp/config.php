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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Kunle Odusan <kunle.odusan@totara.com>
 */

namespace auth_ssosaml\model\idp;

use auth_ssosaml\model\idp\config\bindings;
use auth_ssosaml\model\idp\config\certificates\digest_algorithms;
use auth_ssosaml\model\idp\config\nameid;
use coding_exception;
use moodle_url;

/**
 * Service provider(Totara) metadata configuration class for an IdP.
 * Contains the following properties:
 *
 * @property-read int $idp_id ID of the IdP
 * @property-read bool $sign_metadata If the metadata should be signed
 * @property-read string $signing_algorithm Signing algorithm used to sign the metadata/AuthnRequests
 * @property-read string $certificate Service provider's certificate
 * @property-read string $private_key Service provider's private key
 * @property-read string $passphrase Passphrase used for generating the certificate
 * @property-read string $default_acs_binding Which AssertionConsumerService binding has the default="true" attribute
 * @property-read array $supported_acs_bindings Array containing supported bindings for AssertionConsumerService
 * @property-read array $supported_slo_bindings Array containing supported bindings for SingleLogoutService
 * @property-read string $nameid_format Specified NameID used in the metadata
 * @property-read string $metadata_url SP Metadata url
 * @property-read string $entity_id SP entity ID
 * @property-read string $default_entity_id Default SP entity ID
 * @property-read string $slo_url SingleLogoutService url
 * @property-read string $acs_url AssertionConsumerService url
 * @property-read bool $force_idp_login Login requests will set force to true.
 * @property-read bool $authnrequests_signed SPSSODescriptor AuthnRequestsSigned attribute.
 *                 Indicates AuthnRequests sent out will be signed
 * @property-read bool $wants_assertions_signed SPSSODescriptor WantAssertionsSigned attribute.
 *                Requires all assertions in response to be signed
 *
 * Organization details:
 * @property-read string $organisation_name Organisation name as shown in the Metadata
 * @property-read string $organisation_display_name Organisation display name as shown in the Metadata
 * @property-read string $organisation_url Organisation url as shown in the Metadata
 *
 * Contact details:
 * @property-read string $contact_type Contact type attribute as shown in the Metadata
 * @property-read string $contact_name Contact name as shown in the Metadata
 * @property-read string $contact_email Contact email as shown in the Metadata
 *
 * Config groups:
 * @property-read array $idp_config IDP-specific config
 *
 * @property-read bool $test_mode Indicates this IdP is being tested and so requests behave differently.
 */
class config {
    /**
     * Properties that are exposed in GraphQL at the IdP level and the defaults.
     *
     * @var array
     */
    private static array $idp_config_defaults = [
        'sign_metadata' => false,
        'signing_algorithm' => digest_algorithms::ALGORITHM_SHA256,
        'wants_assertions_signed' => false,
        'authnrequests_signed' => false,
        'nameid_format' => nameid::FORMAT_UNSPECIFIED,
        'entity_id' => null,
    ];

    /**
     * @var array Certificate information.
     */
    private array $certificates;

    /**
     * @var string
     */
    private string $certificates_passphrase;

    /**
     * @var array The set IDP-specific config.
     */
    private array $idp_config;

    /**
     * @var array Configuration that's combined between global, IdP and hard-coded values.
     */
    private array $combined_config;

    /**
     * @var int ID of the IDP
     */
    private int $idp_id;

    /**
     * @var bool Indicates we're in test mode
     */
    private bool $test_mode = false;

    /**
     * Create a new instance of this IDP with config.
     *
     * @param int $idp_id
     * @param array $idp_config
     * @param array $certificates
     * @param string|null $certificates_passphrase
     */
    public function __construct(int $idp_id, array $idp_config, array $certificates, ?string $certificates_passphrase = null) {
        $this->idp_id = $idp_id;
        $this->idp_config = $idp_config;
        $this->certificates = $certificates;
        $this->certificates_passphrase = $certificates_passphrase ?? get_site_identifier();

        if (empty($this->certificates['private_key']) || empty($this->certificates['certificate'])) {
            throw new coding_exception('Cannot access the config without a certificate and private key.');
        }
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name) {
        $method = "get_$name";
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        $config = $this->get_combined_config();
        if (array_key_exists($name, $config)) {
            return $config[$name];
        }

        throw new coding_exception("Unknown property $name in sp config");
    }

    /**
     * Handle the magic isset checks for the dynamic properties.
     *
     * @param $name
     * @return bool
     */
    public function __isset($name) {
        if (method_exists($this, 'get_' . $name)) {
            return true;
        }

        $config = $this->get_combined_config();
        if (array_key_exists($name, $config)) {
            return true;
        }

        return false;
    }

    /**
     * Get the ID of the IdP.
     *
     * @return int
     */
    private function get_idp_id(): int {
        return $this->idp_id;
    }

    /**
     * @return array Get the IDP specific config.
     */
    private function get_idp_config(): array {
        return array_merge(
            self::$idp_config_defaults,
            $this->idp_config,
        );
    }

    /**
     * Combine all the individual configs into one master copy for lookup.
     *
     * @return array
     */
    private function get_combined_config(): array {
        global $SITE, $CFG;

        if (isset($this->combined_config)) {
            return $this->combined_config;
        }

        $this->combined_config = array_merge(
            [
                'passphrase' => $this->certificates_passphrase,
                'supported_acs_bindings' => [
                    bindings::SAML2_HTTP_POST_BINDING,
                ],
                'supported_slo_bindings' => [
                    bindings::SAML2_HTTP_REDIRECT_BINDING,
                    bindings::SAML2_HTTP_POST_BINDING,
                ],
                'default_acs_binding' => bindings::SAML2_HTTP_POST_BINDING,
                'organisation_name' => $SITE->shortname,
                'organisation_display_name' => $SITE->fullname,
                'organisation_url' => $CFG->wwwroot,
                'contact_type' => 'technical',
                'contact_name' => !empty($CFG->supportname) ? $CFG->supportname : get_string('administrator'),
                'contact_email' => !empty($CFG->supportemail) ? $CFG->supportemail : $CFG->noreplyaddress,
                'metadata_url' => $this->url('sp/metadata.php', ['idp' => $this->idp_id]),
                'slo_url' => $this->url('sp/slo.php'),
                'acs_url' => $this->url('sp/acs.php'),
                'default_entity_id' => $this->url('sp/idp-' . $this->idp_id)
            ],
            $this->get_idp_config() // Config specific to this IDP
        );
        return $this->combined_config;
    }

    /**
     * @return bool
     */
    private function get_force_idp_login(): bool {
        if ($this->test_mode) {
            return true;
        }

        return false;
    }

    /**
     * @param string $path
     * @param array $params
     * @return string
     */
    private function url(string $path, array $params = []): string {
        return (new moodle_url('/auth/ssosaml/' . $path, $params))->out(false);
    }

    /**
     * @return array
     */
    public static function get_idp_config_defaults(): array {
        return self::$idp_config_defaults;
    }

    /**
     * Return only the options that differ from the defaults, for when we save.
     *
     * @param array $idp_config
     * @return array
     */
    public static function filter_idp_defaults(array $idp_config): array {
        $idp_config_clean = [];
        foreach ($idp_config as $key => $value) {
            if (array_key_exists($key, self::$idp_config_defaults)) {
                $default = self::$idp_config_defaults[$key];
                if ($default !== $value) {
                    $idp_config_clean[$key] = $value;
                }
            }
        }
        return $idp_config_clean;
    }

    /**
     * Get entity id. Defaults to metadata_url
     *
     * @return string
     */
    private function get_entity_id(): string {
        return $this->idp_config['entity_id'] ?? $this->default_entity_id;
    }

    /**
     * Certificates are handled at the site level, so are called differently.
     *
     * @return string
     */
    protected function get_certificate(): string {
        return $this->certificates['certificate'] ?? '';
    }

    /**
     * Certificates are handled at the site level, so are called differently.
     *
     * @return string
     */
    protected function get_private_key(): string {
        return $this->certificates['private_key'] ?? '';
    }

    /**
     * Run this configuration in test mode. Some values will change over.
     *
     * @param bool $test_mode
     * @return $this
     */
    public function set_test_mode(bool $test_mode): self {
        $this->test_mode = $test_mode;
        return $this;
    }

    /**
     * @return bool
     */
    private function get_test_mode(): bool {
        return $this->test_mode;
    }
}
