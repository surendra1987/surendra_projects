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
 * @author Simon Chester <simon.chester@totara.com>
 * @package auth_ssosaml
 */

namespace auth_ssosaml\model\idp;

use auth_ssosaml\exception\invalid_metadata_exception;
use auth_ssosaml\model\idp\metadata\loader;
use coding_exception;
use JsonSerializable;
use SimpleXMLElement;

/**
 * IdP metadata model
 *
 * @property-read string $source
 * @property-read string|null $url
 * @property-read string|null $xml
 * @property-read string|null $remote_xml
 *
 * Properties from parsing the IdP metadata
 * @property-read string $entity_id
 * @property-read string $signing_certificate
 * @property-read array|string[] $signing_certificates
 * @property-read array $sso_services Single sign on services with the following properties
 *      @type string $binding
 *      @type string $location
 * @property-read array $slo_services Single logout services with the following properties
 *      @type string $binding
 *      @type string $location
 * @property-read array $ar_services Artifact resolution services with the following properties
 *      @type string $binding
 *      @type string $location
 */
class metadata implements JsonSerializable {
    public const SOURCE_NONE = 'NONE';
    public const SOURCE_URL = 'URL';
    public const SOURCE_XML = 'XML';

    /**
     * @var array Internal storage for the metadata properties
     */
    private array $data;

    /**
     * Parsed IdP metadata xml.
     *
     * @var SimpleXMLElement
     */
    private SimpleXMLElement $parsed_xml;

    /**
     * Verify the source is acceptable.
     *
     * @param $source
     * @return bool
     */
    public static function valid_source($source): bool {
        return in_array($source, [self::SOURCE_NONE, self::SOURCE_URL, self::SOURCE_XML], true);
    }

    /**
     * @param string $source
     * @param string|null $url
     * @param string|null $xml
     * @param string|null $remote_xml
     */
    private function __construct(string $source, ?string $url, ?string $xml, ?string $remote_xml) {
        $data = compact('source', 'url', 'xml', 'remote_xml');
        $data['load_remote'] = false;
        if ($source === self::SOURCE_XML && empty($remote_xml)) {
            $data['load_remote'] = true;
        }
        $this->data = $data;
    }

    /**
     * @param string|null $input
     * @return static
     */
    public static function from_serialized(?string $input): self {
        if (empty($input)) {
            return self::none();
        }

        $data = json_decode($input, true, 512, JSON_THROW_ON_ERROR);

        // We don't specifically save the source, we derive it from the attributes
        $source = self::SOURCE_NONE;

        if (!empty($data['url'])) {
            $source = self::SOURCE_URL;
        } else if (!empty($data['xml'])) {
            $source = self::SOURCE_XML;
        }

        return new static(
            $source,
            $data['url'] ?? null,
            $data['xml'] ?? null,
            $data['remote_xml'] ?? null,
        );
    }

    /**
     * Create an empty idp_metadata instance.
     *
     * @return static
     */
    public static function none(): self {
        return new static(self::SOURCE_NONE, null, null, null);
    }

    /**
     * Magic getter.
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name) {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        $method_name = "get_$name" . "_attribute";
        if (method_exists($this, $method_name)) {
            return $this->$method_name();
        }

        // Handle normal failures
        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
        E_USER_NOTICE);
        return null;
    }

    /**
     * Get EntityID
     *
     * @return string|null
     * @throws coding_exception
     */
    private function get_entity_id_attribute(): ?string {
        $xml = $this->get_parsed_xml();
        $entity_descriptor = $xml->xpath('/metadata:EntityDescriptor');

        if (empty($entity_descriptor)) {
            return null;
        }
        $attributes = (array) $entity_descriptor[0]->attributes();

        return $attributes['@attributes']['entityID'] ?? null;
    }

    /**
     * Get all provided signing certificates.
     *
     * @return array
     */
    private function get_signing_certificates_attribute(): array {
        $xml = $this->get_parsed_xml();

        // Return all signing certificates
        $certificates = $xml->xpath(
            '/metadata:EntityDescriptor/metadata:IDPSSODescriptor/metadata:KeyDescriptor[@use="signing"]
            /signature:KeyInfo/signature:X509Data/signature:X509Certificate'
        );

        // If we cannot find any signing certificates, find any certificates instead
        if (empty($certificates)) {
            $certificates = $xml->xpath(
                '/metadata:EntityDescriptor/metadata:IDPSSODescriptor/metadata:KeyDescriptor
                /signature:KeyInfo/signature:X509Data/signature:X509Certificate'
            );
        }

        if (empty($certificates)) {
            return [];
        }

        // Force them to strings
        return array_map(fn($certificate) => (string) $certificate, $certificates);
    }

    /**
     * Returns the first found signing certificate.
     *
     * @return string|null
     */
    private function get_signing_certificate_attribute(): ?string {
        $certificates = $this->get_signing_certificates_attribute();
        return empty($certificates) ? null : $certificates[0];
    }

    /**
     * Get List of Single Sign On Services
     * @return array
     * @throws coding_exception
     */
    private function get_sso_services_attribute(): array {
        $xml = $this->get_parsed_xml();
        $sso_elements = $xml->xpath('/metadata:EntityDescriptor/metadata:IDPSSODescriptor/metadata:SingleSignOnService');

        $sso_services = [];
        foreach ($sso_elements as $sso_element) {
            $attributes = (array) $sso_element->attributes();
            $attributes = $attributes['@attributes'];
            $sso_services[] = [
                'binding' => $attributes['Binding'],
                'location' => $attributes['Location'],
            ];
        }

        return $sso_services;
    }

    /**
     * Get List of Single Logout Services
     * @return array
     * @throws coding_exception
     */
    private function get_slo_services_attribute(): array {
        $xml = $this->get_parsed_xml();
        $slo_elements = $xml->xpath('/metadata:EntityDescriptor/metadata:IDPSSODescriptor/metadata:SingleLogoutService');

        $slo_services = [];
        foreach ($slo_elements as $slo_element) {
            $attributes = (array) $slo_element->attributes();
            $attributes = $attributes['@attributes'];
            $slo_services[] = [
                'binding' => $attributes['Binding'],
                'location' => $attributes['Location'],
            ];
        }

        return $slo_services;
    }

    /**
     * @return SimpleXMLElement
     * @throws coding_exception
     */
    private function get_parsed_xml(): SimpleXMLElement {
        // Parse and set the xml if it hasn't been done.
        if (empty($this->parsed_xml)) {
            $xml = $this->get_xml();

            if (is_null($xml)) {
                throw new invalid_metadata_exception('No XML');
            }

            $parsed_xml = simplexml_load_string($xml);
            if (!$parsed_xml) {
                throw new invalid_metadata_exception('Invalid XML');
            }
            $namespaces = [
                'metadata' => 'urn:oasis:names:tc:SAML:2.0:metadata',
                'assertions' => 'urn:oasis:names:tc:SAML:2.0:assertion',
                'signature' => 'http://www.w3.org/2000/09/xmldsig#',
            ];

            // Register the namespaces.
            foreach ($namespaces as $prefix => $namespace) {
                $parsed_xml->registerXPathNamespace($prefix, $namespace);
            }

            $this->parsed_xml = $parsed_xml;
        }

        return $this->parsed_xml;
    }

    /**
     * Indicates if the property exists.
     *
     * @param string $name
     * @return bool
     */
    public function __isset(string $name): bool {
        if (array_key_exists($name, $this->data)) {
            return true;
        }
        $method_name = "get_$name" . "_attribute";
        if (method_exists($this, $method_name)) {
            return true;
        }

        return false;
    }

    /**
     * Does the XML need to be fetched from the URL?
     *
     * @param bool $refetch Fetch even if XML has already been fetched.
     * @return $this
     */
    public function fetch_xml(bool $refetch = false): self {
        if ($this->source !== self::SOURCE_URL) {
            return $this;
        }

        if (empty($this->load_remote) && !$refetch) {
            return $this;
        }

        $this->data['remote_xml'] = loader::make()->load_metadata($this->url);
        $this->data['load_remote'] = false;

        return $this;
    }

    /**
     * Returns the XML, either the entered or loaded version.
     * Will return a null if no XML was found.
     *
     * @return string|null
     */
    public function get_xml(): ?string {
        if ($this->source === self::SOURCE_URL) {
            return $this->remote_xml ?? null;
        }

        if ($this->source === self::SOURCE_XML) {
            return $this->xml ?? null;
        }

        return null;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): ?array {
        if ($this->source === self::SOURCE_URL) {
            return [
                'url' => $this->url,
                'remote_xml' => $this->remote_xml,
            ];
        }

        if ($this->source === self::SOURCE_XML) {
            return [
                'xml' => $this->xml
            ];
        }

        return [];
    }

    /**
     * Update metadata from user input.
     *
     * @param array $metadata
     * @return metadata
     */
    public function set(array $metadata): self {
        $data = $this->data;
        $source = $metadata['source'] ?? $data['source'] ?? null;
        $url = $metadata['url'] ?? $data['url'] ?? null;
        $xml = $metadata['xml'] ?? $data['xml'] ?? null;

        // If $source is none or is url but no URl was provided, empty everything
        if ($source === self::SOURCE_NONE || $source === self::SOURCE_URL && empty($url)) {
            $this->data['url'] = $this->data['xml'] = $this->data['remote_xml'] = null;
            $this->data['load_remote'] = false;
            return $this;
        } else if ($source === self::SOURCE_URL) {
            // If the source is URL and it hasn't changed, there's nothing to update
            if ($url === $data['url'] && !empty($data['remote_xml'])) {
                return $this;
            }

            $data['source'] = self::SOURCE_URL;
            $data['url'] = clean_param($url, PARAM_URL);
            $data['xml'] = null;
            $data['load_remote'] = true; // Reset the fetch
        } else if ($source === self::SOURCE_XML) {
            $data['source'] = static::SOURCE_XML;
            $data['url'] = null;
            $data['remote_xml'] = null;
            $data['xml'] = $xml;
            $data['load_remote'] = false;
        } else {
            throw new coding_exception("Unknown metadata source");
        }

        $this->data = $data;

        return $this;
    }

    /**
     * Check validity of metadata. Throws an invalid_metadata_exception if invalid.
     *
     * @return void
     */
    public function validate(): self {
        $xml = $this->get_parsed_xml();

        $entity_descriptor = $xml->xpath('/metadata:EntityDescriptor');
        if (empty($entity_descriptor)) {
            throw new invalid_metadata_exception('Missing EntityDescriptor');
        }

        if ($this->entity_id === null) {
            throw new invalid_metadata_exception('Missing entity ID');
        }

        $sso_descriptor = $xml->xpath('/metadata:EntityDescriptor/metadata:IDPSSODescriptor');
        if (empty($sso_descriptor)) {
            throw new invalid_metadata_exception('Missing IDPSSODescriptor');
        }

        if (empty($this->signing_certificates)) {
            throw new invalid_metadata_exception('Missing signing certificate');
        }

        if (empty($this->sso_services)) {
            throw new invalid_metadata_exception('At least one SingleSignOnService must be present');
        }

        return $this;
    }
}
