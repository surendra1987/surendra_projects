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

use auth_ssosaml\exception\invalid_algorithm_exception;
use auth_ssosaml\exception\no_openssl_exception;
use auth_ssosaml\model\idp\config\certificates\digest_algorithms;
use auth_ssosaml\model\idp\config\certificates\generator as certificate_generator;

require_once __DIR__ . '/base_saml_testcase.php';

/**
 * @coversDefaultClass \auth_ssosaml\model\idp\config\certificates\generator
 * @group auth_ssosaml
 */
class auth_ssosaml_certificates_generator_test extends base_saml_testcase {
    /**
     * @return array[]
     */
    public static function available_algorithm_data(): array {
        return [
            [digest_algorithms::ALGORITHM_SHA256, 'RSA-SHA256'],
            [digest_algorithms::ALGORITHM_SHA512, 'RSA-SHA512'],
            [digest_algorithms::ALGORITHM_SHA384, 'RSA-SHA384'],
        ];
    }

    /**
     * @param string $algorithm
     * @param string $expected
     * @return void
     * @dataProvider available_algorithm_data
     */
    public function test_algorithms(string $algorithm, string $expected): void {
        $this->skip_on_no_openssl();

        $generator = certificate_generator::make(['algorithm' => $algorithm]);
        $certificates = $generator->generate('123');
        $this->assert_certificate_valid($certificates);

        // Confirm the certificate is of the algorithm we expect
        $cert = openssl_x509_parse($certificates['certificate']);
        self::assertArrayHasKey('signatureTypeSN', $cert);
        self::assertSame($expected, $cert['signatureTypeSN']);
    }

    /**
     * Assert we can generate certificates
     *
     * @return void
     */
    public function test_certificate_defaults(): void {
        $this->skip_on_no_openssl();

        $generator = certificate_generator::make();
        $certificates = $generator->generate('abc');
        $this->assert_certificate_valid($certificates);

        // Generate another and make sure they don't match
        $certificates2 = $generator->generate('abc');
        $this->assert_certificate_valid($certificates);

        self::assertNotSame($certificates['certificate'], $certificates2['certificate']);
        self::assertNotSame($certificates['private_key'], $certificates2['private_key']);
    }

    /**
     * Assert the email can be set.
     *
     * @return void
     */
    public function test_certificate_email(): void {
        $this->skip_on_no_openssl();

        $generator = certificate_generator::make(['dn_email' => 'cert_test@example.com']);
        $certificates = $generator->generate('555');
        $this->assert_certificate_valid($certificates);

        $parsed = openssl_x509_parse($certificates['certificate']);
        self::assertArrayHasKey('issuer', $parsed);

        $issuer = $parsed['issuer'];
        self::assertArrayHasKey('emailAddress', $issuer);
        self::assertSame('cert_test@example.com', $issuer['emailAddress']);

        // Confirm the default
        $generator = certificate_generator::make();
        $certificates = $generator->generate('abc');
        $this->assert_certificate_valid($certificates);

        $parsed = openssl_x509_parse($certificates['certificate']);
        self::assertArrayHasKey('issuer', $parsed);

        $method = new ReflectionMethod(certificate_generator::class, 'get_default_dn_email');
        $method->setAccessible(true);
        $default = $method->invoke($generator);

        $issuer = $parsed['issuer'];
        self::assertArrayHasKey('emailAddress', $issuer);
        self::assertSame($default, $issuer['emailAddress']);
    }

    /**
     * Assert the organization name can be set.
     *
     * @return void
     */
    public function test_certificate_issuer(): void {
        $this->skip_on_no_openssl();

        $generator = certificate_generator::make(['organization_name' => 'ABC123']);
        $certificates = $generator->generate('abc');
        $this->assert_certificate_valid($certificates);

        $parsed = openssl_x509_parse($certificates['certificate']);
        self::assertArrayHasKey('issuer', $parsed);

        $issuer = $parsed['issuer'];
        self::assertArrayHasKey('O', $issuer);
        self::assertSame('ABC123', $issuer['O']);

        // Confirm the default
        $generator = certificate_generator::make();
        $certificates = $generator->generate('abc');
        $this->assert_certificate_valid($certificates);

        $parsed = openssl_x509_parse($certificates['certificate']);
        self::assertArrayHasKey('issuer', $parsed);

        $method = new ReflectionMethod(certificate_generator::class, 'get_default_organization_name');
        $method->setAccessible(true);
        $default = $method->invoke($generator);

        $issuer = $parsed['issuer'];
        self::assertArrayHasKey('O', $issuer);
        self::assertSame($default, $issuer['O']);
    }

    /**
     * Assert the certificate lifetime can be set.
     *
     * @return void
     */
    public function test_certificate_lifetime(): void {
        $this->skip_on_no_openssl();

        $generator = certificate_generator::make(['certificate_lifetime' => 5]);
        $certificates = $generator->generate('abc');
        $this->assert_certificate_valid($certificates);

        $parsed = openssl_x509_parse($certificates['certificate']);
        self::assertArrayHasKey('validFrom', $parsed);
        self::assertArrayHasKey('validTo', $parsed);

        $valid_from = DateTime::createFromFormat('ymdHise', $parsed['validFrom']);
        $valid_to = DateTime::createFromFormat('ymdHise', $parsed['validTo']);

        $diff = $valid_to->diff($valid_from);
        self::assertEquals(5, $diff->format('%a'));
    }

    /**
     * Assert that the provided algorithm is validated and rejected.
     *
     * @return void
     */
    public function test_invalid_algorithm(): void {
        $this->expectException(invalid_algorithm_exception::class);
        certificate_generator::make(['algorithm' => 'nope']);
    }

    /**
     * Assert that no certificates are generated when openssl is disabled (and that we don't see any errors).
     *
     * @return void
     */
    public function test_openssl_disabled(): void {
        $generator = certificate_generator::make();

        // Force OpenSSL detection to false
        $property = new ReflectionProperty(certificate_generator::class, 'has_openssl_support');
        $property->setAccessible(true);
        $property->setValue($generator, false);

        $this->expectException(no_openssl_exception::class);

        $generator->generate('abc');
    }

    /**
     * Helper function to assert the certificates generated are valid.
     *
     * @param $results
     */
    private function assert_certificate_valid($results): void {
        self::assertIsArray($results);
        self::assertArrayHasKey('certificate', $results);
        self::assertArrayHasKey('private_key', $results);

        self::assertMatchesRegularExpression('/BEGIN CERTIFICATE/', $results['certificate']);
        self::assertMatchesRegularExpression('/BEGIN ENCRYPTED PRIVATE KEY/', $results['private_key']);
    }
}
