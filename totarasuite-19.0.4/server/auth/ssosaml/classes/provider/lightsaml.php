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

namespace auth_ssosaml\provider;

use auth_ssosaml\entity\session;
use auth_ssosaml\exception\response_validation;
use auth_ssosaml\exception\saml_invalid_binding;
use auth_ssosaml\model\idp\config;
use auth_ssosaml\model\idp\config\bindings;
use auth_ssosaml\model\idp\config\certificates\digest_algorithms;
use auth_ssosaml\model\idp\metadata;
use auth_ssosaml\provider\data\authn_request_data;
use auth_ssosaml\provider\data\authn_response;
use auth_ssosaml\provider\data\logout_message;
use auth_ssosaml\provider\data\saml_request;
use auth_ssosaml\provider\logging\contract as logger_contract;
use auth_ssosaml\provider\logging\log_context;
use coding_exception;
use core\collection;
use DateTime;
use Exception;
use LightSaml\Binding\BindingFactory as BindingFactory;
use LightSaml\Context\Profile\MessageContext;
use LightSaml\Credential\KeyHelper;
use LightSaml\Credential\X509Certificate;
use LightSaml\Credential\X509Credential;
use LightSaml\Error\LightSamlBindingException;
use LightSaml\Error\LightSamlSecurityException;
use LightSaml\Event\MessageReceived;
use LightSaml\Helper;
use LightSaml\Model\Assertion\Assertion;
use LightSaml\Model\Assertion\EncryptedAssertionReader;
use LightSaml\Model\Assertion\Issuer;
use LightSaml\Model\Assertion\NameID;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LightSaml\Model\Metadata\AssertionConsumerService;
use LightSaml\Model\Metadata\ContactPerson;
use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\Model\Metadata\KeyDescriptor;
use LightSaml\Model\Metadata\Organization;
use LightSaml\Model\Metadata\SingleLogoutService;
use LightSaml\Model\Metadata\SpSsoDescriptor;
use LightSaml\Model\Protocol\AuthnRequest;
use LightSaml\Model\Protocol\LogoutRequest;
use LightSaml\Model\Protocol\LogoutResponse;
use LightSaml\Model\Protocol\NameIDPolicy;
use LightSaml\Model\Protocol\Response;
use LightSaml\Model\Protocol\SamlMessage;
use LightSaml\Model\XmlDSig\SignatureWriter;
use LightSaml\Model\XmlDSig\SignatureXmlReader;
use LightSaml\Model\Protocol\Status;
use LightSaml\Model\Protocol\StatusCode;
use LightSaml\SamlConstants;
use Psr\EventDispatcher\EventDispatcherInterface;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use Symfony\Component\HttpFoundation\Request;

/**
 * LightSAML implementation of the SAML contract
 */
class lightsaml implements saml_contract {

    /**
     * Service provider config.
     *
     * @var config
     */
    private config $sp_config;

    /**
     * configuration found in the IdP metadata.
     *
     * @var metadata
     */
    private metadata $idp_metadata;

    /**
     * Session manager
     *
     * @var session_manager
     */
    private session_manager $session_manager;

    /**
     * Assertion manager
     *
     * @var assertion_manager
     */
    private assertion_manager $assertion_manager;

    /**
     * Last message received by the binding, as raw XML.
     *
     * @var null|string
     */
    private ?string $last_received_xml = null;

    /**
     * Logger
     *
     * @var logger_contract
     */
    private logger_contract $logger;

    /**
     * @var Request
     */
    protected static Request $request;

    /**
     * @param config $config
     * @param metadata $idp_metadata
     * @param session_manager $session_manager
     * @param assertion_manager $assertion_manager
     * @param logger_contract $logger
     */
    private function __construct(
        config $config,
        metadata $idp_metadata,
        session_manager $session_manager,
        assertion_manager $assertion_manager,
        logger_contract $logger
    ) {
        $this->sp_config = $config;
        $this->idp_metadata = $idp_metadata;
        $this->session_manager = $session_manager;
        $this->assertion_manager = $assertion_manager;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public static function get_instance(
        config $config,
        metadata $idp_metadata,
        session_manager $session_manager,
        assertion_manager $assertion_manager,
        logger_contract $logger
    ): saml_contract {
        return new lightsaml($config, $idp_metadata, $session_manager, $assertion_manager, $logger);
    }

    /**
     * @return Request
     * @codeCoverageIgnore
     */
    protected static function get_request(): Request {
        if (!isset(self::$request)) {
            self::$request = Request::createFromGlobals();
        }

        return self::$request;
    }

    /**
     * @inheritDoc
     */
    public static function get_issuer(): string {
        $request = self::get_request();

        try {
            $binding = (new BindingFactory())->getBindingByRequest($request);
        } catch (LightSamlBindingException $exception) {
            throw new saml_invalid_binding($exception->getMessage());
        }

        $message_context = new MessageContext();

        /** @var SamlMessage $message */
        $binding->receive($request, $message_context);
        $message = $message_context->getMessage();
        $issuer = $message->getIssuer();

        // Issuer not found in message
        // If Login response try getting issuer from assertion
        if (is_null($issuer) && $message instanceof Response) {
            if (!is_null($message->getFirstEncryptedAssertion())) {
                throw new saml_invalid_binding("Can not process encrypted assertions without Issuer in the response");
            }
            $assertion = $message->getFirstAssertion();
            if (is_null($assertion)) {
                throw new saml_invalid_binding("No assertion found");
            }
            $issuer = $assertion->getIssuer();
        }

        if (is_null($issuer)) {
            throw new saml_invalid_binding("Issuer not found in message");
        }

        return $issuer->getValue();
    }

    /**
     * @inheritDoc
     */
    public function get_sp_metadata(): string {
        $entity_descriptor = $this->get_entity_descriptor();

        // Generate the XML
        $context = new SerializationContext();
        $document = $context->getDocument();

        $document->preserveWhiteSpace = false;
        $document->formatOutput = true;

        $entity_descriptor->serialize($document, $context);

        return $document->saveXML();
    }

    /**
     * @inheritDoc
     */
    public function make_login_request(?string $wants_url = null): authn_request_data {
        // Get SingleSignOnService using a supported binding.
        $sso_service = $this->get_binding($this->idp_metadata->sso_services, $this->sp_config->authnrequests_signed);

        if (is_null($sso_service)) {
            throw new coding_exception("No supported binding found");
        }

        $authn_request = (new AuthnRequest())
            ->setAssertionConsumerServiceURL($this->sp_config->acs_url)
            ->setProtocolBinding($this->sp_config->default_acs_binding)
            ->setID($this->session_manager->create_sp_initiated_session())
            ->setNameIDPolicy(new NameIDPolicy($this->sp_config->nameid_format, false))
            ->setIssueInstant(new DateTime())
            ->setDestination($sso_service['location'])
            ->setForceAuthn($this->sp_config->force_idp_login)
            ->setIssuer(new Issuer($this->sp_config->entity_id));

        if ($this->sp_config->authnrequests_signed && $sso_service['binding'] === bindings::SAML2_HTTP_POST_BINDING) {
            $authn_request->setSignature($this->get_signature());
        }
        $message_context = (new MessageContext())->setMessage($authn_request);

        $serialization_context = $message_context->getSerializationContext();
        $authn_request->serialize($serialization_context->getDocument(), $serialization_context);
        $authn_request_xml = $serialization_context->getDocument()->saveXML();

        $this->logger->log_request($authn_request->getID(), $this->logger::TYPE_LOGIN, $authn_request_xml);

        $request_params = [
            'binding' => $sso_service['binding'],
            'url' => $sso_service['location'],
            'request_id' => $authn_request->getID(),
            'data' => [],
        ];

        if (!empty($wants_url)) {
            $request_params['data']['RelayState'] = $wants_url;
        }

        if ($sso_service['binding'] === bindings::SAML2_HTTP_REDIRECT_BINDING) {
            $request_params['data']['SAMLRequest'] = base64_encode(gzdeflate($authn_request_xml));

            // Signature
            if ($this->sp_config->authnrequests_signed) {
                $key = $this->get_private_key();
                $request_params['data']['SigAlg'] = $key->type;
                $signature = $key->signData(http_build_query($request_params['data']));
                $request_params['data']['Signature'] = base64_encode($signature);
            }
        } else {
            $request_params['data']['SAMLRequest'] = base64_encode($authn_request_xml);
        }

        return authn_request_data::create($request_params);
    }

    /**
     * @inheritDoc
     */
    public function get_login_response(): authn_response {
        $request = self::get_request();

        try {
            $binding = $this->get_binding_factory()->getBindingByRequest($request);
        } catch (LightSamlBindingException $exception) {
            throw new saml_invalid_binding($exception->getMessage());
        }

        $message_context = new MessageContext();

        /** @var Response $response */
        $binding->receive($request, $message_context);
        $response = $message_context->getMessage();
        $in_response_to = $response->getInResponseTo();

        $exception = null;
        try {
            $status = $this->logger::STATUS_ERROR;
            $this->validate_response($response);

            // Parse the response
            // Working with the assumption of a single assertion with one AuthnStatement, one AttributeStatement in the response.
            $assertion = $this->get_assertion($response);

            $subject = $assertion->getSubject();
            $subject_confirmation_data = $subject->getFirstSubjectConfirmation()->getSubjectConfirmationData();
            $in_response_to = $subject_confirmation_data->getInResponseTo();
            if ($response->getStatus()->isSuccess()) {
                $status = $this->logger::STATUS_SUCCESS;
            }
        } catch (Exception $ex) {
            $exception = $ex;
            throw $ex;
        } finally {
            $log_info = null;
            $type = $this->logger::TYPE_IDP_LOGIN;

            if (!is_null($in_response_to)) {
                $log_info = log_context::create($in_response_to);
                $type = $this->logger::TYPE_LOGIN;
            }
            $log_id = $this->logger->log_response($type, $this->last_received_xml, $status, $log_info);

            if ($exception) {
                $this->logger->log_error($log_id, $exception->getMessage());
            }
        }

        $authn_statement = $assertion->getFirstAuthnStatement();

        $attributes = [];
        $attribute_statement = $assertion->getFirstAttributeStatement();
        if ($attribute_statement !== null) {
            foreach ($attribute_statement->getAllAttributes() as $attr) {
                $attributes[$attr->getName()] = $attr->getAllAttributeValues();
            }
        }

        return authn_response::make([
            'in_response_to' => $in_response_to,
            'expired_at' => $subject_confirmation_data->getNotOnOrAfterString(),
            'session_not_on_or_after' => $authn_statement ? $authn_statement->getSessionNotOnOrAfterTimestamp() : null,
            'status' => $response->getStatus()->getStatusCode()->getValue(),
            'issuer' => $assertion->getIssuer()->getValue(),
            'relay_state' => $response->getRelayState(),
            'session_index' => $authn_statement ? $authn_statement->getSessionIndex() : null, // used for single logout request
            'name_id' => $subject->getNameID()->getValue(),
            'name_id_format' => $subject->getNameID()->getFormat(),
            'attributes' => $attributes,
            'log_id' => $log_id,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function make_logout_request(session $session): saml_request {
        // Get SingleLogoutService information
        $slo_service = $this->get_binding($this->idp_metadata->slo_services);

        if (is_null($slo_service)) {
            throw response_validation\binding_not_found::make();
        }

        $logout_request = (new LogoutRequest())
            ->setID('slo_' . random_string(20))
            ->setNameID(new NameID($session->name_id, $session->name_id_format))
            ->setIssueInstant(new DateTime())
            ->setReason(SamlConstants::LOGOUT_REASON_USER)
            ->setSessionIndex($session->session_index)
            ->setDestination($slo_service['location'])
            ->setIssuer(new Issuer($this->sp_config->entity_id));

        if ($slo_service['binding'] === bindings::SAML2_HTTP_POST_BINDING) {
            $logout_request->setSignature($this->get_signature());
        }

        $message_context = (new MessageContext())->setMessage($logout_request);
        $serialization_context = $message_context->getSerializationContext();
        $logout_request->serialize($serialization_context->getDocument(), $serialization_context);
        $logout_request_xml = $serialization_context->getDocument()->saveXML();

        $this->logger->log_request($logout_request->getID(), $this->logger::TYPE_LOGOUT, $logout_request_xml, $session->user_id);

        $request_params = [
            'binding' => $slo_service['binding'],
            'url' => $slo_service['location'],
            'data' => [],
        ];

        if ($slo_service['binding'] === bindings::SAML2_HTTP_REDIRECT_BINDING) {
            $request_params['data']['SAMLRequest'] = base64_encode(gzdeflate($logout_request_xml));
            $key = $this->get_private_key();
            $request_params['data']['SigAlg'] = $key->type;
            $signature = $key->signData(http_build_query($request_params['data']));
            $request_params['data']['Signature'] = base64_encode($signature);
        } else {
            $request_params['data']['SAMLRequest'] = base64_encode($logout_request_xml);
        }

        return new saml_request(
            $request_params['binding'],
            $request_params['url'],
            $logout_request->getID(),
            $request_params['data']
        );
    }

    /**
     * @inheritDoc
     *
     * @return logout_message
     * @throws response_validation
     */
    public function get_logout_message(): logout_message {
        $request = self::get_request();

        $binding = $this->get_binding_factory()->getBindingByRequest($request);
        $message_context = new MessageContext();

        /**
         * @var LogoutResponse|LogoutRequest $logout_message
         *
         */
        $binding->receive($request, $message_context);
        $logout_message = $message_context->getMessage();

        // Handling logout response
        if ($logout_message instanceof LogoutResponse) {
            $in_response_to = $logout_message->getInResponseTo();
            $status = $logout_message->getStatus()->isSuccess() ? $this->logger::STATUS_SUCCESS : $this->logger::STATUS_ERROR;
            $log_id = $this->logger->log_response($this->logger::TYPE_LOGOUT, $this->last_received_xml, $status, log_context::create($in_response_to));

            try {
                $this->validate_logout_response($logout_message);
            } catch (Exception $exception) {
                $error_message = $exception->getMessage();
                throw $exception;
            } finally {
                if (isset($error_message)) {
                    $this->logger->log_error($log_id, $error_message);
                }
            }

            return new logout_message(
                logout_message::LOGOUT_RESPONSE,
                [
                    'status' => $logout_message->getStatus()->isSuccess(),
                    'in_response_to' => $in_response_to,
                    'status_message' => $logout_message->getStatus()->getStatusMessage()
                ]
            );
        } else if ($logout_message instanceof LogoutRequest) {
            // Handling logout request
            $log_id = $this->logger->log_request($logout_message->getID(), $this->logger::TYPE_IDP_LOGOUT, $this->last_received_xml);

            try {
                $this->validate_logout_request($logout_message);
            } catch (Exception $exception) {
                $error_message = $exception->getMessage();
                throw $exception;
            } finally {
                if (isset($error_message)) {
                    $this->logger->log_error($log_id, $error_message);
                }
            }

            return new logout_message(
                logout_message::LOGOUT_REQUEST,
                [
                    'status' => true,
                    'name_id' => $logout_message->getNameID()->getValue(),
                    'session_index' => $logout_message->getSessionIndex(),
                    'id' => $logout_message->getID(),
                    'relay_state' => $logout_message->getRelayState(),
                    'log_id' => $log_id,
                ]
            );
        }
        throw response_validation\message_type::make(null, 'Type');
    }

    /**
     * Make logout response that can be sent to IdP
     *
     * @param logout_message $message
     * @param int|null $user_id
     *
     * @return saml_request
     */
    public function make_logout_response(logout_message $message, ?int $user_id): saml_request {
        // Get SingleLogoutService information
        $slo_service = $this->get_binding($this->idp_metadata->slo_services);
        if (is_null($slo_service)) {
            throw response_validation\binding_not_found::make();
        }

        $logout_response = (new LogoutResponse())
            ->setStatus(new Status(
                new StatusCode(SamlConstants::STATUS_SUCCESS)
            ))
            ->setInResponseTo($message->id)
            ->setID(Helper::generateID())
            ->setDestination($slo_service['location'])
            ->setIssueInstant(new DateTime())
            ->setIssuer(new Issuer($this->sp_config->entity_id));

        if ($slo_service['binding'] === bindings::SAML2_HTTP_POST_BINDING) {
            $logout_response->setSignature($this->get_signature());
        }

        $message_context = (new MessageContext())->setMessage($logout_response);
        $serialization_context = $message_context->getSerializationContext();
        $logout_response->serialize($serialization_context->getDocument(), $serialization_context);
        $logout_response_xml = $serialization_context->getDocument()->saveXML();

        $this->logger->log_response(
            $this->logger::TYPE_IDP_LOGOUT,
            $logout_response_xml,
            $this->logger::STATUS_SUCCESS,
            log_context::create($message->log_id, log_context::TYPE_LOG_ID)
        );

        $response_params = [
            'binding' => $slo_service['binding'],
            'url' => $slo_service['location'],
            'data' => [],
        ];

        // Add relay state. if message request contains
        if ($message->relay_state) {
            $response_params['data']['RelayState'] = $message->relay_state;
        }

        if ($slo_service['binding'] === bindings::SAML2_HTTP_REDIRECT_BINDING) {
            $response_params['data']['SAMLResponse'] = base64_encode(gzdeflate($logout_response_xml));
            // Signature
            $key = $this->get_private_key();
            $response_params['data']['SigAlg'] = $key->type;
            $signature = $key->signData(http_build_query($response_params['data']));
            $response_params['data']['Signature'] = base64_encode($signature);
        } else {
            $response_params['data']['SAMLResponse'] = base64_encode($logout_response_xml);
        }

        return new saml_request(
            $response_params['binding'],
            $response_params['url'],
            $logout_response->getID(),
            $response_params['data']
        );
    }

    /**
     * Find a matching binding in the provided array of services.
     *
     * @param array $services
     * @param string|null $preferred_binding
     * @param array $supported_bindings
     * @return array|null
     */
    protected function find_binding_in_services(array $services, ?string $preferred_binding, array $supported_bindings): ?array {
        $services = collection::new($services);
        return (
            // try and find our preferred binding
            ($preferred_binding ? $services->find('binding', $preferred_binding) : null) ??
            // otherwise, filter to supported
            $services->find(function ($sso_service) use ($supported_bindings) {
                return in_array($sso_service['binding'], $supported_bindings);
            })
        );
    }

    /**
     * Get the binding to use for the SAML message.
     *
     * @param array $services
     * @param bool $request_signed
     * @return array|null
     */
    protected function get_binding(array $services, bool $request_signed = true): ?array {
        // Prefer POST binding if request signing is on, as AD FS does not support signed requests over HTTP Redirect binding.
        $preferred_binding = $request_signed ? bindings::SAML2_HTTP_POST_BINDING : null;
        return $this->find_binding_in_services($services, $preferred_binding, [
            bindings::SAML2_HTTP_REDIRECT_BINDING,
            bindings::SAML2_HTTP_POST_BINDING,
        ]);
    }

    /**
     * Get the entity descriptor class
     *
     * @return EntityDescriptor
     * @throws coding_exception
     */
    private function get_entity_descriptor(): EntityDescriptor {
        $entity_descriptor = new EntityDescriptor();
        $certificate = (new X509Certificate())->loadPem($this->sp_config->certificate);
        $entity_descriptor
            ->setEntityID($this->sp_config->entity_id)
            ->addItem(
                $this->get_service_provider_descriptor($certificate)
            )
            ->addOrganization(
                (new Organization())
                    ->setOrganizationName($this->sp_config->organisation_name)
                    ->setOrganizationURL($this->sp_config->organisation_url)
                    ->setOrganizationDisplayName($this->sp_config->organisation_display_name)
            )
            ->addContactPerson(
                (new ContactPerson())
                    ->setContactType($this->sp_config->contact_type)
                    ->setGivenName($this->sp_config->contact_name)
                    ->setEmailAddress($this->sp_config->contact_email)
            );

        if ($this->sp_config->sign_metadata) {
            $entity_descriptor->setSignature($this->get_signature());
        }

        return $entity_descriptor;
    }

    /**
     * Get the service provider descriptor
     *
     * @param X509Certificate $certificate
     *
     * @return SpSsoDescriptor
     */
    private function get_service_provider_descriptor(X509Certificate $certificate): SpSsoDescriptor {
        $service_provider_descriptor = (new SpSsoDescriptor())
            ->setAuthnRequestsSigned($this->sp_config->authnrequests_signed)
            ->setWantAssertionsSigned($this->sp_config->wants_assertions_signed);

        // Signing KeyDescriptor
        $service_provider_descriptor->addKeyDescriptor(
            (new KeyDescriptor())
                ->setUse(KeyDescriptor::USE_SIGNING)
                ->setCertificate($certificate)
        );

        // Encryption KeyDescriptor
        $service_provider_descriptor->addKeyDescriptor(
            (new KeyDescriptor())
                ->setUse(KeyDescriptor::USE_ENCRYPTION)
                ->setCertificate($certificate)
        );

        // NameIdFormat
        $service_provider_descriptor->addNameIDFormat($this->sp_config->nameid_format);

        // Assertion Consumer Service
        foreach ($this->sp_config->supported_acs_bindings as $binding) {
            $service_provider_descriptor->addAssertionConsumerService(
                (new AssertionConsumerService())
                    ->setBinding($binding)
                    ->setIsDefault($binding === $this->sp_config->default_acs_binding)
                    ->setLocation($this->sp_config->acs_url)
            );
        }

        // Single logout Service
        foreach ($this->sp_config->supported_slo_bindings as $binding) {
            $service_provider_descriptor->addSingleLogoutService(
                (new SingleLogoutService())
                    ->setBinding($binding)
                    ->setLocation($this->sp_config->slo_url)
            );
        }

        return $service_provider_descriptor;
    }

    /**
     * Get SP Signature used to sign data
     *
     * @return SignatureWriter
     * @throws coding_exception
     */
    private function get_signature(): SignatureWriter {
        $certificate = (new X509Certificate())->loadPem($this->sp_config->certificate);
        $signing_algorithm = $this->sp_config->signing_algorithm;
        $private_key = $this->get_private_key();

        $digest_algorithms = [
            digest_algorithms::ALGORITHM_SHA1 => XMLSecurityDSig::SHA1,
            digest_algorithms::ALGORITHM_SHA256 => XMLSecurityDSig::SHA256,
            digest_algorithms::ALGORITHM_SHA384 => XMLSecurityDSig::SHA384,
            digest_algorithms::ALGORITHM_SHA512 => XMLSecurityDSig::SHA512,
        ];
        if (empty($digest_algorithms[$signing_algorithm])) {
            throw new coding_exception("Unsupported digest algorithm used");
        }

        return new SignatureWriter($certificate, $private_key, $digest_algorithms[$signing_algorithm]);
    }

    /**
     * Load Service Provider private key and return the XMLSecurityKey object.
     *
     * @return XMLSecurityKey
     * @throws coding_exception
     */
    private function get_private_key(): XMLSecurityKey {
        $signing_algorithm = $this->sp_config->signing_algorithm;
        $supported_algorithms = [
            digest_algorithms::ALGORITHM_SHA1 => XMLSecurityKey::RSA_SHA1,
            digest_algorithms::ALGORITHM_SHA256 => XMLSecurityKey::RSA_SHA256,
            digest_algorithms::ALGORITHM_SHA384 => XMLSecurityKey::RSA_SHA384,
            digest_algorithms::ALGORITHM_SHA512 => XMLSecurityKey::RSA_SHA512,
        ];

        if (empty($supported_algorithms[$signing_algorithm])) {
            throw new coding_exception("Unsupported xml security algorithm used");
        }

        $private_key = new XMLSecurityKey(
            $supported_algorithms[$signing_algorithm],
            ['type' => 'private']
        );
        $private_key->passphrase = $this->sp_config->passphrase;
        $private_key->loadKey($this->sp_config->private_key);

        return $private_key;
    }

    /**
     * Return all the signing certificates in credentials format.
     *
     * @return array
     */
    private function get_idp_signing_credentials(): array {
        $credentials = [];
        foreach ($this->idp_metadata->signing_certificates as $signing_certificate) {
            $cert = new X509Certificate();
            $cert->setData($signing_certificate);
            $credentials[] = new X509Credential($cert);
        }

        return $credentials;
    }

    /**
     * Validate the authn response based on the SAML specification.
     *
     * @param Response $response
     *
     * @return void
     * @throws response_validation
     */
    private function validate_response(SamlMessage $response): void {

        $signing_credentials = $this->get_idp_signing_credentials();

        /** @var SignatureXmlReader $response_signature */
        $response_signature = $response->getSignature();
        if (!empty($response_signature)) {
            try {
                $response_signature->validateMulti($signing_credentials);
            } catch (LightSamlSecurityException $ex) {
                throw response_validation\response_signature_failure::make($ex->getMessage());
            }
        }

        $issuer = $response->getIssuer();
        if (!is_null($issuer)) {
            if ($this->idp_metadata->entity_id !== $issuer->getValue()) {
                throw response_validation\issuer_invalid::make();
            }
            if (!in_array($issuer->getFormat(), [null, 'urn:oasis:names:tc:SAML:2.0:nameid-format:entity'])) {
                throw response_validation\issuer_format_invalid::make();
            }
        }

        $assertion = $this->get_assertion($response);

        /** @var SignatureXmlReader $assertion_signature */
        $assertion_signature = $assertion->getSignature();
        if (empty($assertion_signature) && $this->sp_config->wants_assertions_signed) {
            throw response_validation\assertions_unsigned::make();
        }

        if (!empty($assertion_signature)) {
            try {
                $assertion_signature->validateMulti($signing_credentials);
            } catch (LightSamlSecurityException $ex) {
                throw response_validation\assertion_signature_failure::make($ex->getMessage());
            }
        }

        // Either the Assertion or the Response must be signed.
        if (empty($assertion_signature) && empty($response_signature)) {
            throw response_validation\unsigned::make();
        }

        // Assertion Issuer
        $assertion_issuer = $assertion->getIssuer();
        if (empty($assertion_issuer) || $this->idp_metadata->entity_id !== $assertion_issuer->getValue()) {
            throw response_validation\assertion_issuer_invalid::make();
        }
        if (!in_array($assertion_issuer->getFormat(), [null, 'urn:oasis:names:tc:SAML:2.0:nameid-format:entity'])) {
            throw response_validation\assertion_issuer_format_invalid::make();
        }

        // AuthnStatement
        $authn_statement = $assertion->getFirstAuthnStatement();
        if (is_null($authn_statement)) {
            throw response_validation\no_statement::make();
        }

        // IdP supporting single logout must have SessionIndex
        $slo_urls = $this->idp_metadata->slo_services;
        if (!empty($slo_urls)) {
            if (empty($authn_statement->getSessionIndex())) {
                throw response_validation\missing_field::make(null, 'SessionIndex');
            }
        }

        // Subject
        $subject = $assertion->getSubject();
        if (is_null($subject)) {
            throw response_validation\missing_field::make(null, 'Subject');
        }
        $subject_confirmation = $subject->getFirstSubjectConfirmation();
        if (is_null($subject_confirmation) || $subject_confirmation->getMethod() !== 'urn:oasis:names:tc:SAML:2.0:cm:bearer') {
            throw response_validation\missing_field::make(null, 'SubjectConfirmation');
        }

        $subject_confirmation_data = $subject_confirmation->getSubjectConfirmationData();
        if (is_null($subject_confirmation_data)) {
            throw response_validation\missing_field::make(null, 'SubjectConfirmation');
        }

        if ($subject_confirmation_data->getRecipient() !== $this->sp_config->acs_url) {
            throw response_validation\invalid_recipient::make($subject_confirmation_data->getRecipient());
        }
        $assertion_expiry = $subject_confirmation_data->getNotOnOrAfterTimestamp();

        // Validate expiry of NotOnOrAfter
        if (empty($assertion_expiry) || $assertion_expiry <= time()) {
            throw response_validation\missing_field::make(null, 'NotOnOrAfter');
        }

        $address = $subject_confirmation_data->getAddress();
        if (!empty($address)) {
            if ($address !== $this->sp_config->acs_url) {
                throw response_validation\invalid_address::make();
            }
        }

        if (!empty($subject_confirmation_data->getNotBeforeString())) {
            throw response_validation\includes_not_before::make();
        }

        // Verify the session was initiated by Totara
        $in_response_to = $subject_confirmation_data->getInResponseTo();
        if (!empty($in_response_to) && !$this->session_manager->verify_sp_initiated_request($in_response_to)) {
            throw response_validation\invalid_session_request::make();
        }

        // Assert Audience restriction
        $has_audience_restriction = false;
        $conditions = $assertion->getConditions();
        if (is_null($conditions)) {
            throw response_validation\missing_field::make(null, 'Assertion Conditions');
        }
        foreach ($conditions->getAllAudienceRestrictions() as $audience_restriction) {
            if ($audience_restriction->hasAudience($this->sp_config->entity_id)) {
                $has_audience_restriction = true;
            }
        }
        if (!$has_audience_restriction) {
            throw response_validation\missing_field::make(null, 'AudienceRestrictions');
        }

        // Assert this isn't a replayed message
        if ($assertion->getId()) {
            if (!$this->assertion_manager->is_assertion_unique($assertion->getId())) {
                throw response_validation\assertion_replayed::make();
            }

            $this->assertion_manager->record_assertion($assertion->getId(), $subject_confirmation_data->getNotOnOrAfterTimestamp());
        }
    }

    /**
     * @param Response $response
     *
     * @return Assertion
     * @throws response_validation
     */
    private function get_assertion(Response $response): Assertion {
        // check for encrypted assertions
        /** @var EncryptedAssertionReader $encrypted_assertion */
        $encrypted_assertion = $response->getFirstEncryptedAssertion();

        if (!is_null($encrypted_assertion)) {
            $deserialization_context = new DeserializationContext();

            $assertion = $encrypted_assertion->decryptAssertion($this->get_private_key(), $deserialization_context);
        } else {
            $assertion = $response->getFirstAssertion();
        }
        if (is_null($assertion)) {
            throw response_validation\missing_field::make(null, 'Assertions');
        }
        return $assertion;
    }

    /**
     * Get the binding factory.
     *
     * @return BindingFactory
     */
    private function get_binding_factory(): BindingFactory {
        $this->last_received_xml = null;

        $set_last_received_xml = function (?string $str) {
            $this->last_received_xml = $str;
        };

        // Create an anonymous event dispatcher implementation to grab the
        // received XML contents.
        // Anonymous classes cannot capture variables from the parent scope, so
        // we need to pass a callback in via the constructor.
        $event_dispatcher = new class($set_last_received_xml) implements EventDispatcherInterface {
            private $set_last_received_xml;

            public function __construct($set_last_received_xml) {
                $this->set_last_received_xml = $set_last_received_xml;
            }

            public function dispatch(object $event) {
                if ($event instanceof MessageReceived && !empty($event->message)) {
                    call_user_func($this->set_last_received_xml, $event->message);
                }
            }
        };

        return new BindingFactory($event_dispatcher);
    }

    /**
     * @param LogoutRequest $request
     * @return void
     * @throws response_validation
     */
    private function validate_logout_request(LogoutRequest $request): void {
        /** @var SignatureXmlReader $request_signature */
        $request_signature = $request->getSignature();

        try {
            $request_signature->validateMulti($this->get_idp_signing_credentials());
        } catch (LightSamlSecurityException $ex) {
            throw response_validation\assertion_signature_failure::make($ex->getMessage());
        }

        // Validate request issuer
        $issuer = $request->getIssuer();

        if (is_null($issuer)) {
            throw response_validation\issuer_invalid::make();
        }

        if ($this->idp_metadata->entity_id !== $issuer->getValue()) {
            throw response_validation\issuer_invalid::make();
        }

        if (!in_array($issuer->getFormat(), [null, 'urn:oasis:names:tc:SAML:2.0:nameid-format:entity'])) {
            throw response_validation\issuer_format_invalid::make();
        }

        if (empty($request->getSessionIndex())) {
            throw response_validation\missing_field::make(null, 'SessionIndex');
        }
    }

    /**
     * @param LogoutResponse $response
     * @return void
     * @throws response_validation
     */
    private function validate_logout_response(LogoutResponse $response): void {
        /** @var SignatureXmlReader $response_signature */
        $response_signature = $response->getSignature();

        try {
            $response_signature->validateMulti($this->get_idp_signing_credentials());
        } catch (LightSamlSecurityException $ex) {
            throw response_validation\assertion_signature_failure::make($ex->getMessage());
        }

        // Validate response issuer
        $issuer = $response->getIssuer();

        if (is_null($issuer)) {
            throw response_validation\issuer_invalid::make();
        }

        if ($this->idp_metadata->entity_id !== $issuer->getValue()) {
            throw response_validation\issuer_invalid::make();
        }

        if (!in_array($issuer->getFormat(), [null, 'urn:oasis:names:tc:SAML:2.0:nameid-format:entity'])) {
            throw response_validation\issuer_format_invalid::make();
        }

        if ($response->getStatus()->isSuccess() != 'urn:oasis:names:tc:SAML:2.0:status:Success') {
            throw response_validation\missing_field::make(null, 'Status');
        }

        if (empty($response->getInResponseTo())) {
            throw response_validation\missing_field::make(null, 'InResponseTo');
        }
    }
}
