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

use auth_ssosaml\exception\response_validation;
use auth_ssosaml\local\util;
use auth_ssosaml\model\idp;
use auth_ssosaml\model\idp\config\bindings;
use auth_ssosaml\model\idp\config\certificates\generator as certificate_generator;
use auth_ssosaml\model\idp\config\nameid;
use auth_ssosaml\provider\lightsaml;
use auth_ssosaml\provider\logging\null_logger;
use LightSaml\Context\Profile\MessageContext;
use LightSaml\Credential\X509Certificate;
use LightSaml\Helper;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\Model\Metadata\IdpSsoDescriptor;
use LightSaml\Model\Metadata\KeyDescriptor;
use LightSaml\Model\Metadata\SingleLogoutService;
use LightSaml\Model\Metadata\SingleSignOnService;
use LightSaml\Model\Protocol\Response;
use LightSaml\Model\Protocol\SamlMessage;
use LightSaml\Model\XmlDSig\SignatureWriter;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

require_once __DIR__ . '/base_saml_testcase.php';

/**
 * @coversDefaultClass \auth_ssosaml\provider\lightsaml::validate_response
 * @group auth_ssosaml
 */
class auth_ssosaml_provider_lightsaml_validate_login_response_test extends base_saml_testcase {
    /**
     * @var idp
     */
    private $idp;

    /**
     * @var X509Certificate
     */
    private $certificate;

    /**
     * @var XMLSecurityKey
     */
    private $private_key;

    /**
     * @return array[]
     */
    public static function validation_provider(): array {
        return [
            [
                'properties' => [
                    'response' => ['response.issuer.name' => 'example'],
                ],
                'exception' => response_validation\issuer_invalid::class,
            ],
            [
                'properties' => [],
                'exception' => response_validation\assertions_unsigned::class,
                'sign_response' => false,
            ],
            [
                'properties' => [
                    'response' => ['response.issuer.format' => 'nameid-format:example'],
                ],
                'exception' => response_validation\issuer_format_invalid::class,
            ],
            [
                'properties' => [
                    'assertion' => ['assertion.issuer.name' => 'example'],
                ],
                'exception' => response_validation\assertion_issuer_invalid::class,
            ],
            [
                'properties' => [
                    'assertion' => ['assertion.issuer.format' => 'nameid-format:example'],
                ],
                'exception' => response_validation\assertion_issuer_format_invalid::class,
            ],
            [
                'properties' => [
                    'assertion' => ['assertion.authnstatement' => ''],
                ],
                'exception' => response_validation\no_statement::class,
            ],
            [
                'properties' => [
                    'assertion' => ['authnstatement.sessionindex' => ''],
                ],
                'exception' => response_validation\missing_field::class,
            ],
            [
                'properties' => [
                    'assertion' => ['assertion.subject' => ''],
                ],
                'exception' => response_validation\missing_field::class,
            ],
            [
                'properties' => [
                    'assertion' => ['subject.confirmation.method' => 'saml2.0:incorrect_method'],
                ],
                'exception' => response_validation\missing_field::class,
            ],
            [
                'properties' => [
                    'assertion' => ['subject.confirmation.data' => ''],
                ],
                'exception' => response_validation\missing_field::class,
            ],
            [
                'properties' => [
                    'assertion' => ['subject.confirmation.data.recipient' => 'http://example.com/invalid'],
                ],
                'exception' => response_validation\invalid_recipient::class,
            ],
            [
                'properties' => [
                    'assertion' => ['subject.confirmation.data.notonorafter' => '2023-02-21T21:02:59.580Z'],
                ],
                'exception' => response_validation\missing_field::class,
            ],
            [
                'properties' => [
                    'assertion' => ['subject.confirmation.data.address' => 'Address="http://example.com/invalid-address"'],
                ],
                'exception' => response_validation\invalid_address::class,
            ],
            [
                'properties' => [
                    'assertion' => ['subject.confirmation.data.notbefore' => 'NotBefore="2023-02-21T21:02:59.580Z"'],
                ],
                'exception' => response_validation\includes_not_before::class,
            ],
            [
                'properties' => [
                    'assertion' => ['subject.confirmation.data.inresponseto' => 'invalid_request_id'],
                ],
                'exception' => response_validation\invalid_session_request::class,
            ],
            [
                'properties' => [
                    'assertion' => ['assertion.conditions' => ''],
                ],
                'exception' => response_validation\missing_field::class,
            ],
            [
                'properties' => [
                    'assertion' => ['conditions.audience' => 'http://another.example.com/audience'],
                ],
                'exception' => response_validation\missing_field::class,
            ],
        ];
    }

    /**
     * @dataProvider validation_provider
     *
     * @param array $properties
     * @param string $exception
     * @param bool $sign_response
     * @return void
     */
    public function test_response_validation(
        array $properties,
        string $exception,
        bool $sign_response = true
    ) {
        if (!util::is_openssl_loaded()) {
            $this->markTestSkipped("Openssl not enabled");
        }

        $response_xml = $this->generate_response($properties, $sign_response);
        $saml_response = Response::fromXML($response_xml, (new MessageContext())->getDeserializationContext());

        $lightsaml = lightsaml::get_instance(
            $this->idp->sp_config,
            $this->idp->metadata,
            $this->idp->get_session_manager(),
            $this->idp->get_assertion_manager(),
            new null_logger()
        );
        $method = new ReflectionMethod($lightsaml, 'validate_response');
        $method->setAccessible(true);

        $this->expectException($exception);
        $method->invoke($lightsaml, $saml_response);
    }

    /**
     * @return void
     */
    public function test_no_signature_in_response(): void {
        if (!util::is_openssl_loaded()) {
            $this->markTestSkipped("Openssl not enabled");
        }

        // set wants_assertions_signed to false for idp
        $this->idp = $this->idp->update([], ['wants_assertions_signed' => false]);

        $response_xml = $this->generate_response([], false);
        $saml_response = Response::fromXML($response_xml, (new MessageContext())->getDeserializationContext());

        $lightsaml = lightsaml::get_instance(
            $this->idp->sp_config,
            $this->idp->metadata,
            $this->idp->get_session_manager(),
            $this->idp->get_assertion_manager(),
            new null_logger()
        );
        $method = new ReflectionMethod($lightsaml, 'validate_response');
        $method->setAccessible(true);

        $this->expectException(response_validation\unsigned::class);
        $method->invoke($lightsaml, $saml_response);
    }

    /**
     * @return void
     */
    protected function setUp(): void {
        // Create IdP & certificates
        $entity_descriptor = new EntityDescriptor();
        $generator = certificate_generator::make();
        $certificates = $generator->generate('random_string');

        $this->certificate = (new X509Certificate())->loadPem($certificates['certificate']);
        $entity_descriptor
            ->setID(Helper::generateID())
            ->setEntityID("http://example.com/metadata")
            ->addItem(
                (new IdpSsoDescriptor())
                    ->addKeyDescriptor(
                        (new KeyDescriptor())
                            ->setUse(KeyDescriptor::USE_SIGNING)
                            ->setCertificate($this->certificate)
                    )
                    ->addSingleSignOnService(new SingleSignOnService("http://example.com/sso", bindings::SAML2_HTTP_POST_BINDING))
                    ->addSingleLogoutService(new SingleLogoutService("http://example.com/slo", bindings::SAML2_HTTP_POST_BINDING))
            );

        $private_key = new XMLSecurityKey(
            XMLSecurityKey::RSA_SHA256,
            ['type' => 'private']
        );
        $private_key->passphrase = 'random_string';
        $private_key->loadKey($certificates['private_key']);
        $this->private_key = $private_key;

        $context = new SerializationContext();
        $entity_descriptor->serialize($context->getDocument(), $context);
        $idp_xml = $context->getDocument()->saveXML();

        $this->idp = idp::create(
            [
                'status' => true,
                "metadata" => [
                    "source" => idp\metadata::SOURCE_XML,
                    "xml" => $idp_xml,
                ],
                'logout_idp' => false,
            ],
            [
                'wants_assertions_signed' => true,
            ]
        );

        parent::setUp();
    }

    /**
     * @return void
     */
    protected function tearDown(): void {
        $this->idp = $this->certificate = $this->private_key = null;
        parent::tearDown();
    }

    /**
     * @param array $properties
     * @param bool $sign_response
     * @return string
     */
    private function generate_response(array $properties, bool $sign_response): string {
        $response = $properties['response'] ?? [];
        $assertions = $this->generate_assertion($properties['assertion'] ?? []);
        $data = [
            '{response.in_response_to}' => isset($response['response.in_response_to'])
                ? sprintf('InResponseTo="%s"', $response['response.in_response_to'])
                : '',
            '{response.destination}' => $response['response.destination'] ?? $this->idp->sp_config->acs_url,
            '{response.issue_instant}' => $response['response.issue_instant'] ?? gmdate('Y-m-d\TH:i:s\Z', time()),
            '{response.issuer.name}' => $response['response.issuer.name'] ?? $this->idp->metadata->entity_id,
            '{response.issuer.format}' => $response['response.issuer.format'] ?? nameid::FORMAT_ENTITY,
            '{response.status_code}' => $response['response.status_code'] ?? 'urn:oasis:names:tc:SAML:2.0:status:Success',
            '{assertion}' => $assertions,
        ];
        $response = file_get_contents(__DIR__ . '/fixtures/response/saml_login_response.xml');
        $response_xml = strtr($response, $data);

        // Add signature to response
        $response = SamlMessage::fromXML($response_xml, new DeserializationContext());
        $signature = new SignatureWriter(
            $this->certificate,
            $this->private_key,
            XMLSecurityDSig::SHA256
        );

        if ($sign_response) {
            $response->setSignature($signature);
            $response->getFirstAssertion()->setSignature($signature);
        }

        $context = new SerializationContext();
        $response->serialize($context->getDocument(), $context);

        return $context->getDocument()->saveXML();
    }

    /**
     * @param array $properties
     * @return string
     */
    private function generate_assertion(array $properties): string {
        $data = [
            '{assertion.issuer.name}' => $properties['assertion.issuer.name'] ?? $this->idp->metadata->entity_id,
            '{assertion.issuer.format}' => $properties['assertion.issuer.format'] ?? nameid::FORMAT_ENTITY,
            '{assertion.issue_instant}' => $properties['assertion.issue_instant'] ?? gmdate('Y-m-d\TH:i:s\Z', time()),

            // subject
            '{assertion.subject}' => $properties['assertion.subject']
                ?? strtr(
                    file_get_contents(__DIR__ . '/fixtures/response/parts/assertion.subject.xml'),
                    [
                        '{subject.nameid}' => $properties['subject.nameid'] ?? 'test_subject_nameid',
                        '{subject.nameid_format}' => $properties['subject.nameid_format'] ?? nameid::FORMAT_TRANSIENT,
                        '{subject.confirmation.data}' => $properties['subject.confirmation.data']
                            ?? strtr(
                                file_get_contents(__DIR__ . '/fixtures/response/parts/assertion.subject.confirmation.data.xml'),
                                [
                                    '{subject.confirmation.data.notonorafter}' => $properties['subject.confirmation.data.notonorafter'] ??
                                        gmdate('Y-m-d\TH:i:s\Z', time() + HOURSECS),
                                    '{subject.confirmation.data.notbefore}' => $properties['subject.confirmation.data.notbefore'] ?? '',
                                    '{subject.confirmation.data.address}' => $properties['subject.confirmation.data.address'] ?? '',
                                    '{subject.confirmation.data.recipient}' => $properties['subject.confirmation.data.recipient'] ?? $this->idp->sp_config->acs_url,
                                    '{subject.confirmation.data.inresponseto}' => $properties['subject.confirmation.data.inresponseto'] ??
                                        $this->idp->get_session_manager()->create_sp_initiated_session(),
                                ]
                            ),
                        '{subject.confirmation.method}' => $properties['subject.confirmation.method'] ?? 'urn:oasis:names:tc:SAML:2.0:cm:bearer',
                    ]
                ),
            // conditions
            '{assertion.conditions}' => $properties['assertion.conditions']
                ?? strtr(
                    file_get_contents(__DIR__ . '/fixtures/response/parts/assertion.conditions.xml'),
                    [
                        '{conditions.notbefore}' => $properties['conditions.notbefore'] ?? gmdate('Y-m-d\TH:i:s\Z', time()),
                        '{conditions.notonorafter}' => $properties['conditions.notonorafter'] ?? gmdate('Y-m-d\TH:i:s\Z', time() + HOURSECS),
                        '{conditions.audience}' => $properties['conditions.audience'] ?? $this->idp->sp_config->entity_id,
                    ]
                ),
            // authnstatement
            '{assertion.authnstatement}' => $properties['assertion.authnstatement']
                ?? strtr(
                    file_get_contents(__DIR__ . '/fixtures/response/parts/assertion.authn_statement.xml'),
                    [
                        '{authnstatement.sessionindex}' => $properties['authnstatement.sessionindex'] ?? 'SessionIndex="a_random_index"',
                        '{authnstatement.authninstant}' => $properties['authnstatement.authninstant'] ?? gmdate('Y-m-d\TH:i:s\Z', time()),
                    ]
                ),
        ];
        $response = file_get_contents(__DIR__ . '/fixtures/response/parts/assertion.xml');

        return strtr($response, $data);
    }
}
