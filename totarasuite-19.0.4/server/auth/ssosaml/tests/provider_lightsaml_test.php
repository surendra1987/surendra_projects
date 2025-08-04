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
 * @package auth_ssosaml
 */

use auth_ssosaml\entity\session as idp_session;
use auth_ssosaml\exception\response_validation\binding_not_found;
use auth_ssosaml\exception\saml_invalid_binding;
use auth_ssosaml\model\idp;
use auth_ssosaml\model\idp\config;
use auth_ssosaml\model\idp\config\bindings;
use auth_ssosaml\model\idp\config\certificates\digest_algorithms;
use auth_ssosaml\model\idp\config\nameid;
use auth_ssosaml\model\idp\metadata;
use auth_ssosaml\provider\data\authn_response;
use auth_ssosaml\provider\data\logout_message;
use auth_ssosaml\provider\factory;
use auth_ssosaml\provider\lightsaml;
use auth_ssosaml\provider\logging\null_logger;
use LightSaml\Credential\X509Certificate;
use LightSaml\SamlConstants;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/base_saml_testcase.php';

/**
 * @coversDefaultClass \auth_ssosaml\provider\lightsaml
 * @group auth_ssosaml
 */
class auth_ssosaml_provider_lightsaml_test extends base_saml_testcase {
    /**
     * @return void
     */
    protected function setUp(): void {
        parent::setUp();
        // Due to library conflicts, SimpleSAML & lightsaml cannot be loaded together.
        // These unit test will skip if we see SimpleSAML exists.
        // To run them, call the folder directly with phpunit instead.
        if (class_exists('SimpleSAML\Configuration')) {
            $this->markTestSkipped('Detected SimpleSAMLphp, lightsaml testcase cannot be executed due to library conflict. Run these tests directly via folder instead.');
        }
    }

    /**
     * @return void
     */
    public function test_generate_unsigned_metadata(): void {
        $idp = idp::create(['status' => false], []);
        $certificates = $idp->certificates;
        $sp_config = new config($idp->id, [
            'sign_metadata' => false,
            'passphrase' => get_site_identifier(),
            'authnrequests_signed' => true,
            'organisation_name' => 'LightSAML Phpunit',
            'organisation_display_name' => 'LiteSAML Phpunit dev',
            'organisation_url' => 'https://totara.phpunit.com',
            'contact_name' => 'Totara dev',
            'contact_email' => 'dev@totara.phpunit',
        ], $certificates);

        $lightsaml = lightsaml::get_instance(
            $sp_config,
            $idp->metadata,
            $idp->get_session_manager(),
            $idp->get_assertion_manager(),
            new null_logger()
        );
        $metadata_xml = $lightsaml->get_sp_metadata();
        $parsed_xml = simplexml_load_string($metadata_xml);

        $this->assert_metadata_fields($parsed_xml, $sp_config, $certificates);
    }

    /**
     * @return array[]
     */
    public static function data_provider_signing_algorithms(): array {
        return [
            [
                'algorithm' => digest_algorithms::ALGORITHM_SHA1,
            ],
            [
                'algorithm' => digest_algorithms::ALGORITHM_SHA256,
            ],
            [
                'algorithm' => digest_algorithms::ALGORITHM_SHA384,
            ],
            [
                'algorithm' => digest_algorithms::ALGORITHM_SHA512,
            ],
        ];
    }

    /**
     * @dataProvider data_provider_signing_algorithms
     * @param string $algorithm
     * @return void
     */
    public function test_generate_signed_metadata(string $algorithm): void {
        $idp = idp::create(['status' => false], []);
        $sp_config = new config(
            $idp->id, [
            'sign_metadata' => true,
            'signing_algorithm' => $algorithm,
            'passphrase' => get_site_identifier(),
            'authnrequests_signed' => true,
            'organisation_name' => 'LightSAML Phpunit',
            'organisation_display_name' => 'LiteSAML Phpunit dev',
            'organisation_url' => 'https://totara.phpunit.com',
            'contact_name' => 'Totara dev',
            'contact_email' => 'dev@totara.phpunit',
        ],
            $idp->certificates
        );

        $lightsaml = lightsaml::get_instance(
            $sp_config,
            $idp->metadata,
            $idp->get_session_manager(),
            $idp->get_assertion_manager(),
            new null_logger()
        );
        $metadata_xml = $lightsaml->get_sp_metadata();
        $parsed_xml = simplexml_load_string($metadata_xml);
        $children = $parsed_xml->children("ds", true);

        // Assert signed metadata properties are found:
        $this->assertNotEmpty($children->Signature->SignatureValue);
        $this->assertNotEmpty($children->Signature->SignedInfo->Reference->DigestValue);

        $certificate = (new X509Certificate())->loadPem($idp->certificates['certificate']);
        $this->assertEquals(
            $certificate->getData(),
            $children->Signature->KeyInfo->X509Data->X509Certificate
        );

        $this->assert_metadata_fields($parsed_xml, $sp_config, $idp->certificates);
    }

    /**
     * @return void
     */
    public function test_make_authn_request_for_idp_with_post_binding(): void {
        $idp_xml = file_get_contents(__DIR__ . '/fixtures/idp/namespaced_sample_idp_post_binding.xml');

        $idp = idp::create(
            [
                'status' => false,
                'metadata' => [
                    'xml' => $idp_xml,
                    'source' => metadata::SOURCE_XML
                ]
            ],
            []
        );
        $sp_config = new config($idp->id, [
            'passphrase' => get_site_identifier(),
        ], $idp->certificates);
        $request_data = lightsaml::get_instance(
            $sp_config,
            $idp->metadata,
            $idp->get_session_manager(),
            $idp->get_assertion_manager(),
            new null_logger()
        )
            ->make_login_request();

        $this->assertEquals('http://example.com/single_sign_on', $request_data->url);
        $this->assertEquals(bindings::SAML2_HTTP_POST_BINDING, $request_data->binding);
        $this->assertEqualsCanonicalizing(['SAMLRequest'], array_keys($request_data->data));
    }

    /**
     * @return void
     */
    public function test_make_authn_request_for_idp_with_redirect_binding(): void {
        $idp_xml = file_get_contents(__DIR__ . '/fixtures/idp/namespaced_sample_idp_redirect_binding.xml');
        $idp = idp::create(
            [
                'status' => false,
                'metadata' => [
                    'xml' => $idp_xml,
                    'source' => metadata::SOURCE_XML
                ]
            ],
            []
        );
        $sp_config = new config($idp->id, [
            'passphrase' => get_site_identifier(),
            'authnrequests_signed' => true,
        ], $idp->certificates);

        $request_data = lightsaml::get_instance(
            $sp_config,
            $idp->metadata,
            $idp->get_session_manager(),
            $idp->get_assertion_manager(),
            new null_logger()
        )
            ->make_login_request();

        $this->assertEquals('http://example.com/single_sign_on', $request_data->url);
        $this->assertEquals(bindings::SAML2_HTTP_REDIRECT_BINDING, $request_data->binding);
        $this->assertEqualsCanonicalizing(['SAMLRequest', 'SigAlg', 'Signature'], array_keys($request_data->data));
    }

    /**
     * @return void
     */
    public function test_make_authn_request_for_idp_without_supported_binding(): void {
        $idp_xml = file_get_contents(__DIR__ . '/fixtures/idp/namespaced_sample_idp_unsupported_binding.xml');
        $idp = idp::create(
            [
                'status' => false,
                'metadata' => [
                    'xml' => $idp_xml,
                    'source' => metadata::SOURCE_XML
                ]
            ],
            []
        );
        $sp_config = new config($idp->id, [
            'passphrase' => get_site_identifier(),
        ], $idp->certificates);
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('No supported binding found');
        lightsaml::get_instance(
            $sp_config,
            $idp->metadata,
            $idp->get_session_manager(),
            $idp->get_assertion_manager(),
            new null_logger()
        )
            ->make_login_request();
    }

    /**
     * @return void
     */
    public function test_generate_signed_metadata_with_invalid_algorithm(): void {
        $idp = idp::create(['status' => false], []);
        $sp_config = new config($idp->id, [
            'sign_metadata' => true,
            'signing_algorithm' => 'unknown_algo',
        ], $idp->certificates);

        $lightsaml = lightsaml::get_instance(
            $sp_config,
            $idp->metadata,
            $idp->get_session_manager(),
            $idp->get_assertion_manager(),
            new null_logger()
        );
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage("Unsupported xml security algorithm used");
        $lightsaml->get_sp_metadata();
    }

    /**
     * Assert we can generate logout post binding request
     *
     * @return void
     */
    public function test_make_logout_request_post_binding(): void {
        $user = $this->getDataGenerator()->create_user();
        $idp_xml = file_get_contents(__DIR__ . '/fixtures/idp/namespaced_sample_idp_post_binding.xml');
        $idp = idp::create(
            [
                'status' => true,
                "metadata" => [
                    'xml' => $idp_xml,
                    'source' => metadata::SOURCE_XML
                ],
                'logout_idp' => true
            ],
            []
        );

        $session = $this->create_idp_initiated_session($idp, $user->id);

        $logout_request = factory::get_provider($idp)->make_logout_request($session);
        $this->assertEquals('http://example.com/single_logout_service', $logout_request->url);
        $this->assertEquals(bindings::SAML2_HTTP_POST_BINDING, $logout_request->binding);
        $this->assertEqualsCanonicalizing(['SAMLRequest'], array_keys($logout_request->data));
        $this->assertNotEmpty($logout_request->request_id);
    }

    /**
     * Assert we can generate logout redirect binding request
     *
     * @return void
     */
    public function test_make_logout_request_redirect_binding(): void {
        $user = $this->getDataGenerator()->create_user();
        $idp_xml = file_get_contents(__DIR__ . '/fixtures/idp/namespaced_sample_idp_redirect_binding.xml');
        $idp = idp::create(
            [
                'status' => true,
                "metadata" => [
                    'xml' => $idp_xml,
                    'source' => metadata::SOURCE_XML
                ],
                'logout_idp' => true
            ],
            []
        );

        $session = $this->create_idp_initiated_session($idp, $user->id);

        $logout_request = factory::get_provider($idp)->make_logout_request($session);
        $this->assertEquals('http://example.com/single_logout_service', $logout_request->url);
        $this->assertEquals(bindings::SAML2_HTTP_REDIRECT_BINDING, $logout_request->binding);
        $this->assertEqualsCanonicalizing(['SAMLRequest', 'SigAlg', 'Signature'], array_keys($logout_request->data));
        $this->assertNotEmpty($logout_request->request_id);
    }

    /**
     * Assert requests prefer POST binding
     *
     * @return void
     */
    public function test_prefers_post_binding(): void {
        $user = $this->getDataGenerator()->create_user();
        $idp_xml = <<<EOF
            <EntityDescriptor
                ID="_random_value" entityID="http://example.com/metadata" cacheDuration="PT15M" validUntil="2015-20-25T00:00:00Z"
                xmlns:saml2="urn:oasis:names:tc:SAML:2.0:assertion" xmlns="urn:oasis:names:tc:SAML:2.0:metadata"
            >
                <IDPSSODescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
                    <KeyDescriptor>
                        <KeyInfo xmlns="http://www.w3.org/2000/09/xmldsig#">
                            <X509Data><X509Certificate>foo</X509Certificate></X509Data>
                        </KeyInfo>
                    </KeyDescriptor>
                    <SingleLogoutService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect"
                        Location="https://example.com/logout"/>
                    <SingleLogoutService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST"
                        Location="https://example.com/logout"/>
                    <SingleSignOnService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect"
                        Location="https://example.com/login"/>
                    <SingleSignOnService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST"
                        Location="https://example.com/login"/>
                </IDPSSODescriptor>
            </EntityDescriptor>
        EOF;
        $idp = idp::create(
            [
                'status' => true,
                'metadata' => ['xml' => $idp_xml, 'source' => metadata::SOURCE_XML],
                'logout_idp' => true
            ],
            []
        );

        $instance = lightsaml::get_instance(
            $idp->sp_config,
            $idp->metadata,
            $idp->get_session_manager(),
            $idp->get_assertion_manager(),
            new null_logger()
        );

        $login_request = $instance->make_login_request();
        $this->assertEquals(bindings::SAML2_HTTP_REDIRECT_BINDING, $login_request->binding);

        $idp = $idp->update([], ['authnrequests_signed' => true]);

        $instance = lightsaml::get_instance(
            $idp->sp_config,
            $idp->metadata,
            $idp->get_session_manager(),
            $idp->get_assertion_manager(),
            new null_logger()
        );

        $login_request = $instance->make_login_request();
        $this->assertEquals(bindings::SAML2_HTTP_POST_BINDING, $login_request->binding);

        $session = $this->create_idp_initiated_session($idp, $user->id);

        $logout_request = $instance->make_logout_request($session);
        $this->assertEquals(bindings::SAML2_HTTP_POST_BINDING, $logout_request->binding);

        // Set log_id to 0 to fake a pending IdP-initiated logout request log with the null_logger
        $logout_response = $instance->make_logout_response(
            new logout_message(logout_message::LOGOUT_REQUEST, ['id' => 'foobar', 'log_id' => 0]),
            $user->id
        );
        $this->assertEquals(bindings::SAML2_HTTP_POST_BINDING, $logout_response->binding);
    }

    /**
     * Assert we can throw exception on logout request with unknown binding
     *
     * @return void
     */
    public function test_make_logout_request_without_binding(): void {
        $user = $this->getDataGenerator()->create_user();
        $idp = idp::create(
            [
                'status' => true,
                "metadata" => [
                    "source" => metadata::SOURCE_XML,
                    "xml" => file_get_contents(__DIR__ . '/fixtures/idp/sample_idp_2.xml'),
                ],
                'logout_idp' => true
            ],
            []
        );

        $session = $this->create_idp_initiated_session($idp, $user->id);

        $this->expectException(binding_not_found::class);
        $this->expectExceptionMessage('There was no binding available to process this message');
        factory::get_provider($idp)->make_logout_request($session);
    }

    /**
     * Assert the get_issuer function can parse and return an issuer.
     *
     * @return void
     */
    public function test_get_issuer(): void {
        $request = $this->createConfiguredMock(Request::class, [
            'getMethod' => 'POST',
        ]);

        $response = file_get_contents(__DIR__ . '/fixtures/response/saml_login_response.xml');
        $response_xml = strtr($response, [
            '{response.in_response_to}' => '',
            '{response.issue_instant}' => gmdate('Y-m-d\TH:i:s\Z', time()),
            '{response.issuer.name}' => 'My Issuer',
            '{response.issuer.format}' => nameid::FORMAT_ENTITY,
            '{response.status_code}' => 'urn:oasis:names:tc:SAML:2.0:status:Success',
            '{assertion}' => '',
        ]);

        $request->request = new InputBag([
            'SAMLResponse' => base64_encode($response_xml),
        ]);

        $this->setStaticProperty(lightsaml::class, 'request', $request);

        $result = lightsaml::get_issuer();
        $this->assertEquals('My Issuer', $result);

        // Now try with no issuer
        $request->request = new InputBag([
            'SAMLResponse' => 'invalid',
        ]);
        $request = $this->createConfiguredMock(Request::class, [
            'getMethod' => 'PUT',
        ]);
        $this->setStaticProperty(lightsaml::class, 'request', $request);

        $this->expectException(saml_invalid_binding::class);
        lightsaml::get_issuer();
    }

    /**
     * Assert we can load the issuer from an assertion if it wasn't provided.
     *
     * @return void
     */
    public function test_get_issuer_from_assertion(): void {
        $request = $this->createConfiguredMock(Request::class, [
            'getMethod' => 'POST',
        ]);

        $response = file_get_contents(__DIR__ . '/fixtures/response/saml_login_response_no_issuer.xml');
        $response_assertion = file_get_contents(__DIR__ . '/fixtures/response/parts/assertion.xml');
        $response_assertion_xml = strtr($response_assertion, [
            '{assertion.issue_instant}' => gmdate('Y-m-d\TH:i:s\Z', time()),
            '{assertion.issuer.name}' => 'My Assertion Issuer',
            '{assertion.issuer.format}' => nameid::FORMAT_ENTITY,
            '{assertion.subject}' => '',
            '{assertion.conditions}' => '',
            '{assertion.authnstatement}' => '',
        ]);
        $response_xml = strtr($response, [
            '{response.in_response_to}' => '',
            '{response.issue_instant}' => gmdate('Y-m-d\TH:i:s\Z', time()),
            '{response.issuer.name}' => '',
            '{response.issuer.format}' => nameid::FORMAT_ENTITY,
            '{response.status_code}' => 'urn:oasis:names:tc:SAML:2.0:status:Success',
            '{assertion}' => $response_assertion_xml,
        ]);

        $request->request = new InputBag([
            'SAMLResponse' => base64_encode($response_xml),
        ]);

        $this->setStaticProperty(lightsaml::class, 'request', $request);

        $result = lightsaml::get_issuer();
        $this->assertEquals('My Assertion Issuer', $result);
    }

    /**
     * @param SimpleXMLElement $xml
     * @param string $attribute
     * @return string|null
     */
    private function get_xml_attribute(SimpleXMLElement $xml, string $attribute): ?string {
        $attributes = $xml->attributes();

        foreach ($attributes as $attr => $value) {
            if ($attr === $attribute) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Assert the xml fields.
     *
     * @param SimpleXMLElement $metadata_xml
     * @param config $sp_config
     * @param $certificates
     * @return void
     */
    private function assert_metadata_fields(SimpleXMLElement $metadata_xml, config $sp_config, $certificates): void {
        $this->assertEquals($sp_config->default_entity_id, $this->get_xml_attribute($metadata_xml, 'entityID'));

        // SpSSODescriptor Attributes
        $this->assertEquals(
            SamlConstants::PROTOCOL_SAML2,
            $this->get_xml_attribute($metadata_xml->SPSSODescriptor, 'protocolSupportEnumeration')
        );
        $this->assertEquals(
            $sp_config->authnrequests_signed ? 'true' : 'false',
            $this->get_xml_attribute($metadata_xml->SPSSODescriptor, 'AuthnRequestsSigned')
        );
        $this->assertEquals(
            $sp_config->wants_assertions_signed ? 'true' : 'false',
            $this->get_xml_attribute($metadata_xml->SPSSODescriptor, 'WantAssertionsSigned')
        );

        // SpSSODescriptor details
        $this->assertEquals(nameid::FORMAT_UNSPECIFIED, $metadata_xml->SPSSODescriptor->NameIDFormat);

        // KeyDescriptor details
        $this->assertEquals('signing', $this->get_xml_attribute($metadata_xml->SPSSODescriptor->KeyDescriptor[0], 'use'));
        $this->assertEquals('encryption', $this->get_xml_attribute($metadata_xml->SPSSODescriptor->KeyDescriptor[1], 'use'));

        // Certificate data used
        $certificate = (new X509Certificate())->loadPem($certificates['certificate']);
        foreach ($metadata_xml->SPSSODescriptor->KeyDescriptor as $key_descriptor) {
            $children = $key_descriptor->children("ds", true);
            $this->assertEquals(
                $certificate->getData(),
                $children->KeyInfo->X509Data->X509Certificate
            );
        }

        // AssertionConsumerService details
        $acs_bindings = [];
        $valid_acs_urls = [
            $sp_config->acs_url => true,
        ];
        foreach ($metadata_xml->SPSSODescriptor->AssertionConsumerService as $acs) {
            $location = $this->get_xml_attribute($acs, 'Location');
            $this->assertArrayHasKey($location, $valid_acs_urls);
            $binding = $this->get_xml_attribute($acs, 'Binding');
            $is_default = $sp_config->default_acs_binding === $binding && $location === $sp_config->acs_url;
            $this->assertEquals(
                $is_default ? 'true' : 'false',
                $this->get_xml_attribute($acs, 'isDefault')
            );
            $acs_bindings[$binding] = $binding;
        }
        $this->assertEquals($sp_config->supported_acs_bindings, array_values($acs_bindings));

        // SingleLogoutService details
        $slo_bindings = [];
        foreach ($metadata_xml->SPSSODescriptor->SingleLogoutService as $slo) {
            $this->assertEquals($sp_config->slo_url, $this->get_xml_attribute($slo, 'Location'));
            $slo_bindings[] = $this->get_xml_attribute($slo, 'Binding');
        }
        $this->assertEqualsCanonicalizing($sp_config->supported_slo_bindings, $slo_bindings);

        // Assert organization details
        $this->assertEquals('LightSAML Phpunit', $metadata_xml->Organization->OrganizationName);
        $this->assertEquals('LiteSAML Phpunit dev', $metadata_xml->Organization->OrganizationDisplayName);
        $this->assertEquals('https://totara.phpunit.com', $metadata_xml->Organization->OrganizationURL);

        // Assert contact details
        $this->assertEquals('technical', $this->get_xml_attribute($metadata_xml->ContactPerson, 'contactType'));
        $this->assertEquals('Totara dev', $metadata_xml->ContactPerson->GivenName);
        $this->assertEquals('dev@totara.phpunit', $metadata_xml->ContactPerson->EmailAddress);
    }

    /**
     * @param idp $idp
     * @param $user_id
     * @return idp_session
     */
    private function create_idp_initiated_session(idp $idp, $user_id): \auth_ssosaml\entity\session {
        $session = $idp->get_session_manager()->create_idp_initiated_session(
            $user_id,
            authn_response::make([
                'session_not_on_or_after' => time() - HOURMINS, // Set the session_not_on_or_after to an hour earlier
                'status' => 'success',
                'issuer' => 'idp',
                'name_id' => 'test_name_id',
                'name_id_format' => nameid::FORMAT_PERSISTENT,
            ])
        );
        $session_id = md5('test');
        $session->session_id = $session_id;
        $session->save();
        return $session;
    }
}
