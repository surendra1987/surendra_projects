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

use auth_ssosaml\exception\invalid_metadata_exception;
use auth_ssosaml\model\idp\metadata;

require_once __DIR__ . '/base_saml_testcase.php';

/**
 * @coversDefaultClass \auth_ssosaml\model\idp\metadata
 * @group auth_ssosaml
 */
class auth_ssosaml_model_idp_metadata_test extends base_saml_testcase {
    /**
     * @return void
     */
    public function test_create(): void {
        $metadata = metadata::none();
        $this->assertEquals(metadata::SOURCE_NONE, $metadata->source);
        $this->assertNull($metadata->url);
        $this->assertNull($metadata->xml);

        $metadata = $metadata->set(["source" => "URL", "url" => "http://example.come/sso/metadata", "xml" => ""]);
        $this->assertEquals(metadata::SOURCE_URL, $metadata->source);
        $this->assertEquals('http://example.come/sso/metadata', $metadata->url);
        $this->assertNull($metadata->xml);

        $metadata->set(["source" => "XML", "url" => "", "xml" => "<xml></xml>"]);
        $this->assertEquals(metadata::SOURCE_XML, $metadata->source);
        $this->assertNull($metadata->url);
        $this->assertSame('<xml></xml>', $metadata->xml);
    }

    /**
     * @return void
     */
    public function test_from_serialized(): void {
        $metadata = metadata::from_serialized(json_encode(["source" => "URL", "url" => "http://example.come/sso/metadata", "xml" => ""]));
        $this->assertEquals(metadata::SOURCE_URL, $metadata->source);
        $this->assertEquals('http://example.come/sso/metadata', $metadata->url);

        $metadata = metadata::from_serialized(json_encode(["source" => "XML", "url" => "", "xml" => "<xml></xml>"]));
        $this->assertEquals(metadata::SOURCE_XML, $metadata->source);
        $this->assertSame('<xml></xml>', $metadata->xml);
    }

    public static function signing_certificates_provider(): array {
        return [
            [
                '<EntityDescriptor xmlns="urn:oasis:names:tc:SAML:2.0:metadata" entityID="some_entity">
                    <IDPSSODescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
                        <KeyDescriptor use="signing">
                            <KeyInfo xmlns="http://www.w3.org/2000/09/xmldsig#">
                                <X509Data><X509Certificate>cert 1</X509Certificate></X509Data>
                            </KeyInfo>
                        </KeyDescriptor>
                    </IDPSSODescriptor>
                </EntityDescriptor>',
                ['cert 1'],
            ],
            [
                '<EntityDescriptor xmlns="urn:oasis:names:tc:SAML:2.0:metadata" entityID="some_entity">
                    <IDPSSODescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
                        <KeyDescriptor use="signing">
                            <KeyInfo xmlns="http://www.w3.org/2000/09/xmldsig#">
                                <X509Data><X509Certificate>cert 1</X509Certificate></X509Data>
                            </KeyInfo>
                        </KeyDescriptor>
                        <KeyDescriptor use="signing">
                            <KeyInfo xmlns="http://www.w3.org/2000/09/xmldsig#">
                                <X509Data><X509Certificate>cert 2</X509Certificate></X509Data>
                            </KeyInfo>
                        </KeyDescriptor>
                    </IDPSSODescriptor>
                </EntityDescriptor>',
                ['cert 1', 'cert 2'],
            ],
            [
                '<EntityDescriptor xmlns="urn:oasis:names:tc:SAML:2.0:metadata" entityID="some_entity">
                    <IDPSSODescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
                        <KeyDescriptor>
                            <KeyInfo xmlns="http://www.w3.org/2000/09/xmldsig#">
                                <X509Data><X509Certificate>cert 3</X509Certificate></X509Data>
                            </KeyInfo>
                        </KeyDescriptor>
                    </IDPSSODescriptor>
                </EntityDescriptor>',
                ['cert 3'],
            ]
        ];
    }

    /**
     * @dataProvider signing_certificates_provider
     * @param string $xml
     * @param array $expected
     * @return void
     */
    public function test_signing_certificates(string $xml, array $expected): void {
        $metadata = metadata::none();
        $metadata->set(['source' => 'XML', 'xml' => $xml]);

        $results = $metadata->signing_certificates;
        self::assertNotEmpty($results);
        self::assertEqualsCanonicalizing($expected, $results);
    }

    /**
     * @dataProvider validation_provider
     * @param string $xml
     * @param bool $valid
     * @return void
     */
    public function test_validation($xml, $valid): void {
        if (!$valid) {
            $this->expectException(invalid_metadata_exception::class);
        }
        $metadata = metadata::none();
        $metadata->set(['source' => 'XML', 'xml' => $xml]);
        $metadata->validate();
    }

    /**
     * @return array
     */
    public static function validation_provider(): array {
        return [
            // minimal valid
            [
                'xml' => '
                    <EntityDescriptor xmlns="urn:oasis:names:tc:SAML:2.0:metadata" entityID="some_entity">
                        <IDPSSODescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
                            <KeyDescriptor>
                                <KeyInfo xmlns="http://www.w3.org/2000/09/xmldsig#">
                                    <X509Data><X509Certificate>cert</X509Certificate></X509Data>
                                </KeyInfo>
                            </KeyDescriptor>
                            <SingleSignOnService
                                Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect"
                                Location="https://foo/"
                            />
                        </IDPSSODescriptor>
                    </EntityDescriptor>
                ',
                true
            ],
            // more complex valid
            [
                'xml' => file_get_contents(__DIR__ . '/fixtures/idp/sample_idp.xml'),
                true
            ],
            // missing EntityDescriptor (wrong tag)
            [
                'xml' => '
                    <Foo xmlns="urn:oasis:names:tc:SAML:2.0:metadata" entityID="some_entity">
                    </Foo>
                ',
                false
            ],
            // missing entityID
            [
                'xml' => '
                    <EntityDescriptor xmlns="urn:oasis:names:tc:SAML:2.0:metadata">
                        <IDPSSODescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
                            <KeyDescriptor>
                                <KeyInfo xmlns="http://www.w3.org/2000/09/xmldsig#">
                                    <X509Data><X509Certificate>cert</X509Certificate></X509Data>
                                </KeyInfo>
                            </KeyDescriptor>
                            <SingleSignOnService
                                Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect"
                                Location="https://foo/"
                            />
                        </IDPSSODescriptor>
                    </EntityDescriptor>
                ',
                false
            ],
            // missing IDPSSODescriptor
            [
                'xml' => '
                    <EntityDescriptor xmlns="urn:oasis:names:tc:SAML:2.0:metadata" entityID="some_entity">
                    </EntityDescriptor>
                ',
                false
            ],
            // missing certificate
            [
                'xml' => '
                    <EntityDescriptor xmlns="urn:oasis:names:tc:SAML:2.0:metadata" entityID="some_entity">
                        <IDPSSODescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
                            <SingleSignOnService
                                Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect"
                                Location="https://foo/"
                            />
                        </IDPSSODescriptor>
                    </EntityDescriptor>
                ',
                false
            ],
            // missing SSO service
            [
                'xml' => '
                    <EntityDescriptor xmlns="urn:oasis:names:tc:SAML:2.0:metadata" entityID="some_entity">
                        <IDPSSODescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
                            <KeyDescriptor>
                                <KeyInfo xmlns="http://www.w3.org/2000/09/xmldsig#">
                                    <X509Data><X509Certificate>cert</X509Certificate></X509Data>
                                </KeyInfo>
                            </KeyDescriptor>
                        </IDPSSODescriptor>
                    </EntityDescriptor>
                ',
                false
            ],
        ];
    }
}
